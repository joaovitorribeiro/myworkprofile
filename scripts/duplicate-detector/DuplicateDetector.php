<?php

class DuplicateDetector
{
    private $config;
    private $duplicates = [];
    private $processedFiles = [];
    private $codeBlocks = [];
    private $totalLines = 0;
    private $totalBlocks = 0;
    private $ignorePatterns = [];

    public function __construct($configPath = null)
    {
        $configPath = $configPath ?: __DIR__ . '/config.json';
        $this->config = json_decode(file_get_contents($configPath), true);
        $this->loadIgnorePatterns();
    }

    private function loadIgnorePatterns()
    {
        $ignoreFile = __DIR__ . '/.duplicateignore';
        
        if (file_exists($ignoreFile)) {
            $lines = file($ignoreFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                // Ignorar comentários e linhas vazias
                if (empty($line) || $line[0] === '#') {
                    continue;
                }
                
                $this->ignorePatterns[] = $line;
            }
        }
        
        // Adicionar padrões da configuração JSON
        if (isset($this->config['ignore']['patterns'])) {
            $this->ignorePatterns = array_merge(
                $this->ignorePatterns, 
                $this->config['ignore']['patterns']
            );
        }
    }

    public function scan($directory)
    {
        echo "🔍 Iniciando detecção de duplicatas em: {$directory}\n";
        
        $files = $this->getFiles($directory);
        echo "📁 Encontrados " . count($files) . " arquivos para análise\n";

        foreach ($files as $file) {
            $this->analyzeFile($file);
        }

        $this->findDuplicates();
        return $this->generateReport();
    }

    private function getFiles($directory)
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($this->shouldProcessFile($file)) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function shouldProcessFile($file)
    {
        $path = $file->getPathname();
        $extension = '.' . $file->getExtension();

        // Verificar extensões permitidas
        if (!in_array($extension, $this->config['settings']['extensions'])) {
            return false;
        }

        // Verificar padrões do .duplicateignore
        if ($this->isIgnored($path)) {
            return false;
        }

        // Verificar caminhos excluídos
        foreach ($this->config['settings']['excludePaths'] as $excludePath) {
            if (strpos($path, $excludePath) !== false) {
                return false;
            }
        }

        // Verificar arquivos excluídos
        foreach ($this->config['settings']['excludeFiles'] as $pattern) {
            if (fnmatch($pattern, basename($path))) {
                return false;
            }
        }

        return true;
    }

    private function isIgnored($filePath)
    {
        // Normalizar o caminho do arquivo
        $normalizedPath = str_replace('\\', '/', $filePath);
        $relativePath = $this->getRelativePath($normalizedPath);
        
        foreach ($this->ignorePatterns as $pattern) {
            // Padrão de negação (forçar inclusão)
            if ($pattern[0] === '!') {
                $pattern = substr($pattern, 1);
                if ($this->matchesPattern($relativePath, $pattern)) {
                    return false; // Forçar inclusão
                }
                continue;
            }
            
            if ($this->matchesPattern($relativePath, $pattern)) {
                return true;
            }
        }
        
        return false;
    }

    private function getRelativePath($absolutePath)
    {
        $cwd = str_replace('\\', '/', getcwd());
        
        if (strpos($absolutePath, $cwd) === 0) {
            return ltrim(substr($absolutePath, strlen($cwd)), '/');
        }
        
        return $absolutePath;
    }

    private function matchesPattern($path, $pattern)
    {
        // Converter padrão glob para regex
        $regex = $this->globToRegex($pattern);
        
        // Verificar se o padrão corresponde ao caminho completo ou apenas ao nome do arquivo
        return preg_match($regex, $path) || preg_match($regex, basename($path));
    }

    private function globToRegex($pattern)
    {
        // Escapar caracteres especiais do regex, exceto * e ?
        $pattern = preg_quote($pattern, '/');
        
        // Converter padrões glob para regex
        $pattern = str_replace('\*', '.*', $pattern);
        $pattern = str_replace('\?', '.', $pattern);
        
        // Se o padrão termina com /, é um diretório
        if (substr($pattern, -1) === '/') {
            $pattern = $pattern . '.*';
        }
        
        return '/^' . $pattern . '$/i';
    }

    private function analyzeFile($filePath)
    {
        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        
        $this->processedFiles[] = $filePath;
        
        // Extrair blocos de código
        $this->extractCodeBlocks($filePath, $lines);
    }

    private function extractCodeBlocks($filePath, $lines)
    {
        $minLines = $this->config['settings']['minLines'];
        $totalLines = count($lines);

        for ($i = 0; $i <= $totalLines - $minLines; $i++) {
            $block = array_slice($lines, $i, $minLines);
            $normalizedBlock = $this->normalizeCode($block);
            
            if ($this->isValidBlock($normalizedBlock)) {
                $hash = $this->generateHash($normalizedBlock);
                
                if (!isset($this->codeBlocks[$hash])) {
                    $this->codeBlocks[$hash] = [];
                }
                
                $this->codeBlocks[$hash][] = [
                    'file' => $filePath,
                    'startLine' => $i + 1,
                    'endLine' => $i + $minLines,
                    'code' => $block,
                    'normalized' => $normalizedBlock
                ];
            }
        }
    }

    private function normalizeCode($lines)
    {
        $normalized = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Ignorar linhas vazias se configurado
            if ($this->config['settings']['ignoreWhitespace'] && empty($line)) {
                continue;
            }
            
            // Ignorar comentários se configurado
            if ($this->config['settings']['ignoreComments']) {
                if (preg_match('/^\s*(\/\/|\/\*|\*|#)/', $line)) {
                    continue;
                }
            }
            
            // Normalizar espaços em branco
            if ($this->config['settings']['ignoreWhitespace']) {
                $line = preg_replace('/\s+/', ' ', $line);
            }
            
            $normalized[] = $line;
        }
        
        return $normalized;
    }

    private function isValidBlock($normalizedBlock)
    {
        // Verificar se o bloco tem conteúdo suficiente
        $content = implode(' ', $normalizedBlock);
        $tokenCount = str_word_count($content);
        
        return $tokenCount >= $this->config['settings']['minTokens'];
    }

    private function generateHash($normalizedBlock)
    {
        return md5(implode("\n", $normalizedBlock));
    }

    private function findDuplicates()
    {
        echo "🔎 Analisando blocos de código para duplicatas...\n";
        
        foreach ($this->codeBlocks as $hash => $blocks) {
            if (count($blocks) > 1) {
                $this->duplicates[] = [
                    'hash' => $hash,
                    'count' => count($blocks),
                    'blocks' => $blocks,
                    'similarity' => $this->calculateSimilarity($blocks)
                ];
            }
        }
        
        // Ordenar por número de duplicatas
        usort($this->duplicates, function($a, $b) {
            return $b['count'] - $a['count'];
        });
    }

    private function calculateSimilarity($blocks)
    {
        if (count($blocks) < 2) return 1.0;
        
        $first = $blocks[0]['normalized'];
        $similarities = [];
        
        for ($i = 1; $i < count($blocks); $i++) {
            $similarities[] = $this->stringSimilarity($first, $blocks[$i]['normalized']);
        }
        
        return array_sum($similarities) / count($similarities);
    }

    private function stringSimilarity($str1, $str2)
    {
        $str1 = implode("\n", $str1);
        $str2 = implode("\n", $str2);
        
        similar_text($str1, $str2, $percent);
        return $percent / 100;
    }

    private function generateReport()
    {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'config' => $this->config,
            'summary' => [
                'filesProcessed' => count($this->processedFiles),
                'duplicateGroups' => count($this->duplicates),
                'totalDuplicates' => array_sum(array_column($this->duplicates, 'count'))
            ],
            'duplicates' => $this->duplicates,
            'processedFiles' => $this->processedFiles
        ];

        // Salvar relatório
        $outputFile = $this->config['reporting']['outputFile'];
        file_put_contents($outputFile, json_encode($report, JSON_PRETTY_PRINT));
        
        echo "\n📊 Relatório gerado: {$outputFile}\n";
        echo "📁 Arquivos processados: " . $report['summary']['filesProcessed'] . "\n";
        echo "🔍 Grupos de duplicatas: " . $report['summary']['duplicateGroups'] . "\n";
        echo "📋 Total de duplicatas: " . $report['summary']['totalDuplicates'] . "\n";
        
        return $report;
    }

    public function removeDuplicates($report = null)
    {
        if (!$report) {
            $report = json_decode(file_get_contents($this->config['reporting']['outputFile']), true);
        }

        echo "\n🧹 Iniciando remoção de duplicatas...\n";
        
        $removedCount = 0;
        
        foreach ($report['duplicates'] as $duplicate) {
            if ($duplicate['similarity'] >= $this->config['settings']['similarity']) {
                $removedCount += $this->removeDuplicateGroup($duplicate);
            }
        }
        
        echo "✅ Remoção concluída. Removidos: {$removedCount} blocos duplicados\n";
        return $removedCount;
    }

    private function removeDuplicateGroup($duplicate)
    {
        $blocks = $duplicate['blocks'];
        $removed = 0;
        
        // Manter apenas o primeiro bloco, remover os demais
        for ($i = 1; $i < count($blocks); $i++) {
            $block = $blocks[$i];
            if ($this->removeCodeBlock($block)) {
                $removed++;
            }
        }
        
        return $removed;
    }

    private function removeCodeBlock($block)
    {
        $filePath = $block['file'];
        $startLine = $block['startLine'] - 1; // Converter para índice 0
        $endLine = $block['endLine'] - 1;
        
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        
        // Remover as linhas duplicadas
        array_splice($lines, $startLine, $endLine - $startLine + 1);
        
        // Salvar arquivo modificado
        file_put_contents($filePath, implode("\n", $lines));
        
        echo "  ✂️  Removido bloco duplicado de {$filePath} (linhas {$block['startLine']}-{$block['endLine']})\n";
        
        return true;
    }
}