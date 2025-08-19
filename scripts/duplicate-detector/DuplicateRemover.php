<?php

class DuplicateRemover
{
    private $config;
    private $backupDir;
    private $removedCount = 0;
    private $preservedCount = 0;
    private $log = [];

    public function __construct($configPath = null)
    {
        $configPath = $configPath ?: __DIR__ . '/config.json';
        $this->config = json_decode(file_get_contents($configPath), true);
        $this->backupDir = __DIR__ . '/backups/' . date('Y-m-d_H-i-s');
    }

    public function removeIntelligent($reportPath)
    {
        echo "🧹 Iniciando remoção inteligente de duplicatas...\n";
        
        if (!file_exists($reportPath)) {
            throw new Exception("Arquivo de relatório não encontrado: {$reportPath}");
        }

        $report = json_decode(file_get_contents($reportPath), true);
        
        if (empty($report['duplicates'])) {
            echo "✅ Nenhuma duplicata para remover!\n";
            return ['removed' => 0, 'preserved' => 0];
        }

        // Criar backup antes de modificar
        $this->createBackup($report['processedFiles']);
        
        echo "📊 Processando " . count($report['duplicates']) . " grupos de duplicatas...\n";
        
        foreach ($report['duplicates'] as $index => $duplicate) {
            echo "\n🔍 Grupo #" . ($index + 1) . " (" . $duplicate['count'] . " duplicatas)\n";
            $this->processDuplicateGroup($duplicate);
        }
        
        $this->generateRemovalReport();
        
        echo "\n✅ Remoção concluída!\n";
        echo "📋 Removidos: {$this->removedCount} blocos\n";
        echo "💾 Preservados: {$this->preservedCount} blocos\n";
        echo "🗂️  Backup criado em: {$this->backupDir}\n";
        
        return [
            'removed' => $this->removedCount,
            'preserved' => $this->preservedCount,
            'backup' => $this->backupDir
        ];
    }

    private function createBackup($files)
    {
        echo "💾 Criando backup dos arquivos...\n";
        
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $backupPath = $this->backupDir . '/' . basename($file);
                copy($file, $backupPath);
            }
        }
        
        echo "✅ Backup criado: {$this->backupDir}\n";
    }

    private function processDuplicateGroup($duplicate)
    {
        $blocks = $duplicate['blocks'];
        
        if (count($blocks) < 2) {
            return;
        }
        
        // Estratégia: escolher o melhor bloco para preservar
        $bestBlock = $this->selectBestBlock($blocks);
        $bestIndex = array_search($bestBlock, $blocks);
        
        echo "  💎 Preservando: {$bestBlock['file']} (linhas {$bestBlock['startLine']}-{$bestBlock['endLine']})\n";
        $this->preservedCount++;
        
        // Remover os outros blocos
        foreach ($blocks as $index => $block) {
            if ($index !== $bestIndex) {
                if ($this->removeBlock($block)) {
                    echo "  ✂️  Removido: {$block['file']} (linhas {$block['startLine']}-{$block['endLine']})\n";
                    $this->removedCount++;
                    
                    $this->log[] = [
                        'action' => 'removed',
                        'file' => $block['file'],
                        'startLine' => $block['startLine'],
                        'endLine' => $block['endLine'],
                        'preservedIn' => $bestBlock['file']
                    ];
                }
            }
        }
    }

    private function selectBestBlock($blocks)
    {
        // Critérios para escolher o melhor bloco:
        // 1. Arquivo com mais contexto (mais linhas)
        // 2. Arquivo com melhor localização (src > app > outros)
        // 3. Arquivo modificado mais recentemente
        // 4. Arquivo com melhor nome/estrutura
        
        $scored = [];
        
        foreach ($blocks as $block) {
            $score = 0;
            $file = $block['file'];
            
            // Pontuação por localização do arquivo
            if (strpos($file, '/src/') !== false) {
                $score += 10;
            } elseif (strpos($file, '/app/') !== false) {
                $score += 8;
            } elseif (strpos($file, '/resources/') !== false) {
                $score += 6;
            }
            
            // Pontuação por tipo de arquivo
            if (strpos($file, 'Controller.php') !== false) {
                $score += 5;
            } elseif (strpos($file, 'Model.php') !== false) {
                $score += 4;
            } elseif (strpos($file, '.tsx') !== false || strpos($file, '.jsx') !== false) {
                $score += 3;
            }
            
            // Pontuação por contexto (linhas ao redor)
            $contextLines = $this->getContextLines($file, $block['startLine'], $block['endLine']);
            $score += min($contextLines / 10, 5); // Máximo 5 pontos
            
            // Pontuação por data de modificação
            if (file_exists($file)) {
                $mtime = filemtime($file);
                $daysSinceModified = (time() - $mtime) / (24 * 60 * 60);
                $score += max(0, 5 - ($daysSinceModified / 30)); // Mais recente = melhor
            }
            
            // Evitar arquivos de teste ou temporários
            if (strpos($file, 'test') !== false || strpos($file, 'temp') !== false) {
                $score -= 10;
            }
            
            $scored[] = ['block' => $block, 'score' => $score];
        }
        
        // Ordenar por pontuação (maior primeiro)
        usort($scored, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return $scored[0]['block'];
    }

    private function getContextLines($file, $startLine, $endLine)
    {
        if (!file_exists($file)) {
            return 0;
        }
        
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $totalLines = count($lines);
        
        // Contar linhas não vazias antes e depois do bloco
        $contextBefore = 0;
        for ($i = max(0, $startLine - 10); $i < $startLine - 1; $i++) {
            if (isset($lines[$i]) && trim($lines[$i]) !== '') {
                $contextBefore++;
            }
        }
        
        $contextAfter = 0;
        for ($i = $endLine; $i < min($totalLines, $endLine + 10); $i++) {
            if (isset($lines[$i]) && trim($lines[$i]) !== '') {
                $contextAfter++;
            }
        }
        
        return $contextBefore + $contextAfter;
    }

    private function removeBlock($block)
    {
        $file = $block['file'];
        $startLine = $block['startLine'] - 1; // Converter para índice 0
        $endLine = $block['endLine'] - 1;
        
        if (!file_exists($file)) {
            echo "    ⚠️  Arquivo não encontrado: {$file}\n";
            return false;
        }
        
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        
        if ($startLine < 0 || $endLine >= count($lines)) {
            echo "    ⚠️  Linhas inválidas em {$file}\n";
            return false;
        }
        
        // Verificar se o bloco ainda existe (pode ter sido modificado)
        $currentBlock = array_slice($lines, $startLine, $endLine - $startLine + 1);
        $expectedBlock = $block['code'];
        
        if (!$this->blocksMatch($currentBlock, $expectedBlock)) {
            echo "    ⚠️  Bloco modificado desde a análise em {$file}\n";
            return false;
        }
        
        // Remover as linhas
        array_splice($lines, $startLine, $endLine - $startLine + 1);
        
        // Salvar arquivo modificado
        file_put_contents($file, implode("\n", $lines));
        
        return true;
    }

    private function blocksMatch($current, $expected)
    {
        if (count($current) !== count($expected)) {
            return false;
        }
        
        for ($i = 0; $i < count($current); $i++) {
            if (trim($current[$i]) !== trim($expected[$i])) {
                return false;
            }
        }
        
        return true;
    }

    private function generateRemovalReport()
    {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'summary' => [
                'removed' => $this->removedCount,
                'preserved' => $this->preservedCount,
                'backup' => $this->backupDir
            ],
            'actions' => $this->log
        ];
        
        $reportFile = 'duplicate-removal-report.json';
        file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
        
        echo "\n📄 Relatório de remoção salvo: {$reportFile}\n";
    }

    public function restoreBackup($backupPath = null)
    {
        $backupPath = $backupPath ?: $this->backupDir;
        
        if (!is_dir($backupPath)) {
            throw new Exception("Backup não encontrado: {$backupPath}");
        }
        
        echo "🔄 Restaurando backup de: {$backupPath}\n";
        
        $files = glob($backupPath . '/*');
        $restored = 0;
        
        foreach ($files as $backupFile) {
            $originalFile = basename($backupFile);
            
            // Encontrar o arquivo original no projeto
            $found = $this->findOriginalFile($originalFile);
            
            if ($found) {
                copy($backupFile, $found);
                echo "  ✅ Restaurado: {$found}\n";
                $restored++;
            } else {
                echo "  ⚠️  Arquivo original não encontrado: {$originalFile}\n";
            }
        }
        
        echo "\n✅ Restauração concluída. Arquivos restaurados: {$restored}\n";
        return $restored;
    }

    private function findOriginalFile($filename)
    {
        // Buscar o arquivo no projeto
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator('.', RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->getFilename() === $filename) {
                return $file->getPathname();
            }
        }
        
        return null;
    }
}