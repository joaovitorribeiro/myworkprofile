#!/usr/bin/env php
<?php

require_once __DIR__ . '/ReportGenerator.php';
require_once __DIR__ . '/DuplicateDetector.php';

class ReportCLI
{
    private $options = [];
    private $reportGenerator;
    private $detector;

    public function __construct()
    {
        $this->parseArguments();
        $this->reportGenerator = new ReportGenerator($this->options['config'] ?? null);
        $this->detector = new DuplicateDetector($this->options['config'] ?? null);
    }

    public function run()
    {
        if (isset($this->options['help'])) {
            $this->showHelp();
            return;
        }

        if (isset($this->options['version'])) {
            echo "MyWorkProfile Duplicate Report Generator v2.0\n";
            return;
        }

        try {
            if (isset($this->options['from-file'])) {
                $this->generateFromFile();
            } else {
                $this->generateFromScan();
            }
        } catch (Exception $e) {
            echo "❌ Erro: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function generateFromFile()
    {
        $reportFile = $this->options['from-file'];
        
        if (!file_exists($reportFile)) {
            throw new Exception("Arquivo de relatório não encontrado: {$reportFile}");
        }

        echo "📖 Carregando dados do arquivo: {$reportFile}\n";
        $data = json_decode(file_get_contents($reportFile), true);
        
        if (!$data) {
            throw new Exception("Erro ao decodificar arquivo JSON");
        }

        $this->generateReports($data);
    }

    private function generateFromScan()
    {
        $directory = $this->options['directory'] ?? getcwd();
        
        if (!is_dir($directory)) {
            throw new Exception("Diretório não encontrado: {$directory}");
        }

        echo "🔍 Escaneando diretório: {$directory}\n";
        
        $startTime = microtime(true);
$result = $this->detector->scan($directory) ?? throw new Exception("Scan failed to return results");
        $endTime = microtime(true);
        
        $result['performance'] = [
            'scanTime' => round($endTime - $startTime, 2),
            'filesPerSecond' => round(count($result['processedFiles']) / ($endTime - $startTime), 2)
        ];

        $this->generateReports($result);
    }

    private function generateReports($data)
    {
        $format = $this->options['format'] ?? 'all';
        $validFormats = ['all', 'json', 'html', 'csv', 'markdown'];
        
        if (!in_array($format, $validFormats)) {
            throw new Exception("Formato inválido. Use: " . implode(', ', $validFormats));
        }

        echo "\n📊 Gerando relatórios no formato: {$format}\n";
        echo str_repeat('=', 50) . "\n";

        $reports = $this->reportGenerator->generateReport($data, $format);
        
        // Mostrar resumo
        $this->showSummary($data);
        
        // Mostrar arquivos gerados
        echo "\n📁 Arquivos gerados:\n";
        foreach ($reports as $type => $filepath) {
            echo "  {$type}: " . basename($filepath) . "\n";
        }

        // Gerar dashboard se solicitado
        if (isset($this->options['dashboard'])) {
            echo "\n🎯 Gerando dashboard interativo...\n";
            $dashboardPath = $this->reportGenerator->generateDashboard($data);
            echo "Dashboard: " . basename($dashboardPath) . "\n";
        }

        // Abrir relatório automaticamente
        if (isset($this->options['open']) && isset($reports['html'])) {
            $this->openReport($reports['html']);
        }

        echo "\n✅ Relatórios gerados com sucesso!\n";
        
        // Salvar dados para uso futuro
        if (isset($this->options['save-data'])) {
            $this->saveReportData($data);
        }
    }

    private function showSummary($data)
    {
        $duplicates = $data['duplicates'] ?? [];
        $processedFiles = $data['processedFiles'] ?? [];
        
        $totalDuplicates = 0;
        $duplicatedLines = 0;
        
        foreach ($duplicates as $duplicate) {
            $totalDuplicates += $duplicate['count'];
            foreach ($duplicate['blocks'] as $block) {
                $duplicatedLines += ($block['endLine'] - $block['startLine'] + 1);
            }
        }
        
        echo "\n📈 Resumo da Análise:\n";
        echo "  Arquivos processados: " . count($processedFiles) . "\n";
        echo "  Grupos de duplicatas: " . count($duplicates) . "\n";
        echo "  Total de duplicatas: {$totalDuplicates}\n";
        echo "  Linhas duplicadas: {$duplicatedLines}\n";
        
        if (isset($data['performance'])) {
            echo "  Tempo de scan: {$data['performance']['scanTime']}s\n";
            echo "  Arquivos/segundo: {$data['performance']['filesPerSecond']}\n";
        }
    }

    private function saveReportData($data)
    {
        $filename = 'report-data-' . date('Y-m-d_H-i-s') . '.json';
        $filepath = __DIR__ . '/reports/' . $filename;
        
        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
        echo "💾 Dados salvos: {$filename}\n";
    }

    private function openReport($filepath)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            exec("start \"\" \"$filepath\"");
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            exec("open \"$filepath\"");
        } else {
            exec("xdg-open \"$filepath\"");
        }
        echo "🌐 Abrindo relatório no navegador...\n";
    }

    private function parseArguments()
    {
        $args = $_SERVER['argv'] ?? [];
        array_shift($args); // Remove script name
        
        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];
            
            switch ($arg) {
                case '--help':
                case '-h':
                    $this->options['help'] = true;
                    break;
                    
                case '--version':
                case '-v':
                    $this->options['version'] = true;
                    break;
                    
                case '--directory':
                case '-d':
                    $this->options['directory'] = $args[++$i] ?? null;
                    break;
                    
                case '--format':
                case '-f':
                    $this->options['format'] = $args[++$i] ?? null;
                    break;
                    
                case '--config':
                case '-c':
                    $this->options['config'] = $args[++$i] ?? null;
                    break;
                    
                case '--from-file':
                    $this->options['from-file'] = $args[++$i] ?? null;
                    break;
                    
                case '--dashboard':
                    $this->options['dashboard'] = true;
                    break;
                    
                case '--open':
                case '-o':
                    $this->options['open'] = true;
                    break;
                    
                case '--save-data':
                    $this->options['save-data'] = true;
                    break;
                    
                default:
                    if (strpos($arg, '--') === 0) {
                        echo "⚠️  Opção desconhecida: {$arg}\n";
                    } else {
                        $this->options['directory'] = $arg;
                    }
                    break;
            }
        }
    }

    private function showHelp()
    {
        echo "\n🔍 MyWorkProfile Duplicate Report Generator v2.0\n";
        echo "Gera relatórios detalhados sobre duplicatas de código\n\n";
        
        echo "Uso:\n";
        echo "  php generate-report.php [opções] [diretório]\n\n";
        
        echo "Opções:\n";
        echo "  -h, --help              Mostra esta ajuda\n";
        echo "  -v, --version           Mostra a versão\n";
        echo "  -d, --directory DIR     Diretório para escanear (padrão: atual)\n";
        echo "  -f, --format FORMAT     Formato do relatório (all|json|html|csv|markdown)\n";
        echo "  -c, --config FILE       Arquivo de configuração personalizado\n";
        echo "  --from-file FILE        Gerar relatório a partir de dados salvos\n";
        echo "  --dashboard             Gerar dashboard interativo\n";
        echo "  -o, --open              Abrir relatório HTML automaticamente\n";
        echo "  --save-data             Salvar dados para uso futuro\n\n";
        
        echo "Formatos disponíveis:\n";
        echo "  all                     Gera todos os formatos\n";
        echo "  json                    Relatório em JSON\n";
        echo "  html                    Relatório em HTML (visual)\n";
        echo "  csv                     Relatório em CSV (planilha)\n";
        echo "  markdown                Relatório em Markdown\n\n";
        
        echo "Exemplos:\n";
        echo "  php generate-report.php                    # Escanear diretório atual\n";
        echo "  php generate-report.php -f html -o         # HTML e abrir no navegador\n";
        echo "  php generate-report.php -d /path/to/code   # Escanear diretório específico\n";
        echo "  php generate-report.php --dashboard        # Gerar com dashboard\n";
        echo "  php generate-report.php --from-file data.json  # A partir de dados salvos\n\n";
        
        echo "Arquivos gerados em: scripts/duplicate-detector/reports/\n";
    }
}

// Executar CLI
if (php_sapi_name() === 'cli') {
    $cli = new ReportCLI();
    $cli->run();
} else {
    echo "Este script deve ser executado via linha de comando.\n";
    exit(1);
}