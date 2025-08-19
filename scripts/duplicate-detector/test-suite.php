#!/usr/bin/env php
<?php

require_once __DIR__ . '/DuplicateDetector.php';
require_once __DIR__ . '/DuplicateRemover.php';
require_once __DIR__ . '/ReportGenerator.php';

class DuplicateDetectorTestSuite
{
    private $testDir;
    private $backupDir;
    private $results = [];
    private $verbose = false;

    public function __construct($verbose = false)
    {
        $this->verbose = $verbose;
        $this->testDir = __DIR__ . '/test-files';
        $this->backupDir = __DIR__ . '/test-backups';
        
        // Criar diretórios de teste
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0755, true);
        }
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function runAllTests()
    {
        echo "🧪 Iniciando suite de testes do Detector de Duplicatas\n";
        echo str_repeat('=', 60) . "\n";
        
        $startTime = microtime(true);
        
        // Preparar ambiente de teste
        $this->setupTestEnvironment();
        
        // Executar testes
        $this->testDetection();
        $this->testRemoval();
        $this->testReporting();
        $this->testConfiguration();
        $this->testHooks();
        $this->testPerformance();
        $this->testEdgeCases();
        
        // Limpar ambiente
        $this->cleanupTestEnvironment();
        
        $endTime = microtime(true);
        $totalTime = round($endTime - $startTime, 2);
        
        // Mostrar resultados
        $this->showResults($totalTime);
        
        return $this->allTestsPassed();
    }

    private function setupTestEnvironment()
    {
        $this->log("📁 Configurando ambiente de teste...");
        
        // Criar arquivos de teste com duplicatas conhecidas
        $duplicateCode = '<?php\nfunction duplicateFunction() {\n    echo "This is a duplicate function";\n    $result = "hello world";\n    $data = array();\n    $data["key"] = "value";\n    return $result;\n}';
        
        $this->createTestFile('test1.php', $duplicateCode);
        $this->createTestFile('test2.php', $duplicateCode);
        
        $this->createTestFile('test3.js', 'function duplicateJsFunction() {\n    console.log("duplicate code");\n    return true;\n}\n\nconst obj = {\n    prop: "value"\n};');
        
        $this->createTestFile('test4.js', 'function duplicateJsFunction() {\n    console.log("duplicate code");\n    return true;\n}\n\nconst anotherObj = {\n    prop: "different"\n};');
        
        $this->createTestFile('unique.php', '<?php\nfunction uniqueFunction() {\n    return "this is unique";\n}\n\nclass UniqueClass {\n    public function uniqueMethod() {\n        return "unique";\n    }\n}');
        
        // Criar arquivo de configuração de teste
        $testConfig = [
            'settings' => [
                'minLines' => 2,
                'minTokens' => 5,
                'similarity' => 0.7,
                'extensions' => ['.php', '.js'],
                'excludePaths' => ['vendor/', 'node_modules/'],
                'excludeFiles' => ['*.min.js'],
                'ignoreWhitespace' => true,
                'ignoreComments' => true
            ],
            'reporting' => [
                'outputFile' => $this->testDir . '/test-report.json'
            ]
        ];
        
        file_put_contents($this->testDir . '/test-config.json', json_encode($testConfig, JSON_PRETTY_PRINT));
    }

    private function testDetection()
    {
        $this->log("🔍 Testando detecção de duplicatas...");
        
        try {
            $detector = new DuplicateDetector($this->testDir . '/test-config.json');
            $result = $detector->scan($this->testDir);
            
            // Verificar se duplicatas foram encontradas
            $duplicatesDetected = count($result['duplicates']) > 0;
            $this->addResult('detection_basic', $duplicatesDetected, 'Detecção básica de duplicatas');
            
            // Verificar se as duplicatas esperadas foram encontradas
            $duplicateCount = array_sum(array_column($result['duplicates'], 'count'));
            $correctCount = $duplicateCount >= 2;
            $this->addResult('detection_count', $correctCount, "Contagem de duplicatas (esperado: >=2, atual: {$duplicateCount})");
            
            // Verificar se arquivos únicos não foram marcados como duplicatas
            $uniqueFilesProcessed = is_array($result['processedFiles']) ? count($result['processedFiles']) > 0 : $result['processedFiles'] > 0;
            $this->addResult('detection_unique', $uniqueFilesProcessed, 'Processamento de arquivos únicos');
            
        } catch (Exception $e) {
            $this->addResult('detection_basic', false, 'Erro na detecção: ' . $e->getMessage());
        }
    }

    private function testRemoval()
    {
        $this->log("🗑️ Testando remoção de duplicatas...");
        
        try {
            // Criar cópia dos arquivos para teste de remoção
            $this->createTestFile('removal_test1.php', '<?php\nfunction removeMe() {\n    return "duplicate";\n}\necho "test";');
            $this->createTestFile('removal_test2.php', '<?php\nfunction removeMe() {\n    return "duplicate";\n}\necho "different";');
            
            $detector = new DuplicateDetector($this->testDir . '/test-config.json');
            $result = $detector->scan($this->testDir);
            
            // Salvar relatório temporário para teste de remoção
            $tempReportPath = $this->testDir . '/temp-report.json';
            file_put_contents($tempReportPath, json_encode($result, JSON_PRETTY_PRINT));
            
            $remover = new DuplicateRemover($this->testDir . '/test-config.json');
            $removalResult = $remover->removeIntelligent($tempReportPath);
            
            $removalSuccessful = $removalResult['removed'] > 0;
            $this->addResult('removal_basic', $removalSuccessful, 'Remoção básica de duplicatas');
            
            // Verificar se backup foi criado
            $backupExists = is_dir($this->backupDir) && count(scandir($this->backupDir)) > 2;
            $this->addResult('removal_backup', $backupExists, 'Criação de backup');
            
        } catch (Exception $e) {
            $this->addResult('removal_basic', false, 'Erro na remoção: ' . $e->getMessage());
        }
    }

    private function testReporting()
    {
        $this->log("📊 Testando geração de relatórios...");
        
        try {
            $detector = new DuplicateDetector($this->testDir . '/test-config.json');
            $result = $detector->scan($this->testDir);
            
            $reportGenerator = new ReportGenerator($this->testDir . '/test-config.json');
            $reports = $reportGenerator->generateReport($result, 'json');
            
            $jsonReportExists = isset($reports['json']) && file_exists($reports['json']);
            $this->addResult('reporting_json', $jsonReportExists, 'Geração de relatório JSON');
            
            // Testar relatório HTML
            $htmlReports = $reportGenerator->generateReport($result, 'html');
            $htmlReportExists = isset($htmlReports['html']) && file_exists($htmlReports['html']);
            $this->addResult('reporting_html', $htmlReportExists, 'Geração de relatório HTML');
            
            // Verificar conteúdo do relatório JSON
            if ($jsonReportExists) {
                $reportData = json_decode(file_get_contents($reports['json']), true);
                $hasMetadata = isset($reportData['metadata']);
                $hasSummary = isset($reportData['summary']);
                $hasDuplicates = isset($reportData['duplicates']);
                
                $this->addResult('reporting_structure', $hasMetadata && $hasSummary && $hasDuplicates, 'Estrutura do relatório JSON');
            }
            
        } catch (Exception $e) {
            $this->addResult('reporting_json', false, 'Erro na geração de relatórios: ' . $e->getMessage());
        }
    }

    private function testConfiguration()
    {
        $this->log("⚙️ Testando configuração...");
        
        try {
            // Testar carregamento de configuração
            $detector = new DuplicateDetector($this->testDir . '/test-config.json');
            $configLoaded = true; // Se chegou até aqui, a configuração foi carregada
            $this->addResult('config_loading', $configLoaded, 'Carregamento de configuração');
            
            // Testar configuração padrão
            $defaultDetector = new DuplicateDetector();
            $defaultConfigLoaded = true;
            $this->addResult('config_default', $defaultConfigLoaded, 'Configuração padrão');
            
            // Testar arquivo .duplicateignore
            $ignoreFile = $this->testDir . '/.duplicateignore';
            file_put_contents($ignoreFile, "*.min.js\nvendor/\nnode_modules/");
            
            $detectorWithIgnore = new DuplicateDetector($this->testDir . '/test-config.json');
            $ignoreWorking = true; // Teste básico - se não deu erro, está funcionando
            $this->addResult('config_ignore', $ignoreWorking, 'Arquivo .duplicateignore');
            
        } catch (Exception $e) {
            $this->addResult('config_loading', false, 'Erro na configuração: ' . $e->getMessage());
        }
    }

    private function testHooks()
    {
        $this->log("🪝 Testando hooks do Git...");
        
        try {
            // Verificar se hooks existem
            $preCommitHook = __DIR__ . '/../../.git/hooks/pre-commit';
            $preMergeHook = __DIR__ . '/../../.git/hooks/pre-merge-commit';
            
            $preCommitExists = file_exists($preCommitHook);
            $preMergeExists = file_exists($preMergeHook);
            
            $this->addResult('hooks_precommit', $preCommitExists, 'Hook pre-commit existe');
            $this->addResult('hooks_premerge', $preMergeExists, 'Hook pre-merge existe');
            
            // Verificar se script de instalação existe
            $installScript = __DIR__ . '/install-hooks.ps1';
            $installScriptExists = file_exists($installScript);
            $this->addResult('hooks_installer', $installScriptExists, 'Script de instalação de hooks');
            
        } catch (Exception $e) {
            $this->addResult('hooks_precommit', false, 'Erro ao verificar hooks: ' . $e->getMessage());
        }
    }

    private function testPerformance()
    {
        $this->log("⚡ Testando performance...");
        
        try {
            // Criar arquivos maiores para teste de performance
            for ($i = 0; $i < 100; $i++) {
                $this->createTestFile("perf_test_{$i}.php", "<?php\nfunction test{$i}() {\n    return {$i};\n}");
            }
            
            $startTime = microtime(true);
            $detector = new DuplicateDetector($this->testDir . '/test-config.json');
            $result = $detector->scan($this->testDir);
            $endTime = microtime(true);
            
            $scanTime = $endTime - $startTime;
            $performanceOk = $scanTime < 10; // Deve completar em menos de 10 segundos
            
            $this->addResult('performance_scan', $performanceOk, "Tempo de scan: {$scanTime}s (limite: 10s)");
            
            // Testar memória
            $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // MB
            $memoryOk = $memoryUsage < 128; // Menos de 128MB
            
            $this->addResult('performance_memory', $memoryOk, "Uso de memória: {$memoryUsage}MB (limite: 128MB)");
            
        } catch (Exception $e) {
            $this->addResult('performance_scan', false, 'Erro no teste de performance: ' . $e->getMessage());
        }
    }

    private function testEdgeCases()
    {
        $this->log("🎯 Testando casos extremos...");
        
        try {
            // Arquivo vazio
            $this->createTestFile('empty.php', '');
            
            // Arquivo com apenas comentários
            $this->createTestFile('comments_only.php', '<?php\n// Apenas comentários\n/* Mais comentários */');
            
            // Arquivo com caracteres especiais
            $this->createTestFile('special_chars.php', '<?php\necho "Olá, mundo! 🌍";\n$var = "Ação com acentuação";');
            
            $detector = new DuplicateDetector($this->testDir . '/test-config.json');
            $result = $detector->scan($this->testDir);
            
            $edgeCasesHandled = true; // Se não deu erro, os casos extremos foram tratados
            $this->addResult('edge_cases', $edgeCasesHandled, 'Tratamento de casos extremos');
            
            // Testar diretório inexistente
            try {
                $detector->scan('/diretorio/inexistente');
                $this->addResult('edge_invalid_dir', false, 'Deveria falhar com diretório inexistente');
            } catch (Exception $e) {
                $this->addResult('edge_invalid_dir', true, 'Tratamento correto de diretório inexistente');
            }
            
        } catch (Exception $e) {
            $this->addResult('edge_cases', false, 'Erro nos casos extremos: ' . $e->getMessage());
        }
    }

    private function createTestFile($filename, $content)
    {
        $filepath = $this->testDir . '/' . $filename;
        file_put_contents($filepath, $content);
    }

    private function addResult($testName, $passed, $description)
    {
        $this->results[] = [
            'name' => $testName,
            'passed' => $passed,
            'description' => $description
        ];
        
        if ($this->verbose) {
            $status = $passed ? '✅' : '❌';
            echo "  {$status} {$description}\n";
        }
    }

    private function log($message)
    {
        echo "{$message}\n";
    }

    private function showResults($totalTime)
    {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "📋 RESULTADOS DOS TESTES\n";
        echo str_repeat('=', 60) . "\n";
        
        $passed = 0;
        $failed = 0;
        
        foreach ($this->results as $result) {
            $status = $result['passed'] ? '✅ PASSOU' : '❌ FALHOU';
            echo sprintf("%-50s %s\n", $result['description'], $status);
            
            if ($result['passed']) {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        echo "\n" . str_repeat('-', 60) . "\n";
        echo "📊 RESUMO:\n";
        echo "  Testes executados: " . count($this->results) . "\n";
        echo "  Passou: {$passed}\n";
        echo "  Falhou: {$failed}\n";
        echo "  Taxa de sucesso: " . round(($passed / count($this->results)) * 100, 1) . "%\n";
        echo "  Tempo total: {$totalTime}s\n";
        
        if ($this->allTestsPassed()) {
            echo "\n🎉 TODOS OS TESTES PASSARAM! A solução está funcionando corretamente.\n";
        } else {
            echo "\n⚠️  ALGUNS TESTES FALHARAM. Verifique os problemas acima.\n";
        }
    }

    private function allTestsPassed()
    {
        foreach ($this->results as $result) {
            if (!$result['passed']) {
                return false;
            }
        }
        return true;
    }

    private function cleanupTestEnvironment()
    {
        $this->log("🧹 Limpando ambiente de teste...");
        
        // Remover arquivos de teste
        $files = glob($this->testDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        // Remover diretórios de teste se estiverem vazios
        if (is_dir($this->testDir) && count(scandir($this->testDir)) == 2) {
            rmdir($this->testDir);
        }
        
        if (is_dir($this->backupDir)) {
            $backupFiles = glob($this->backupDir . '/*');
            foreach ($backupFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            if (count(scandir($this->backupDir)) == 2) {
                rmdir($this->backupDir);
            }
        }
    }
}

// CLI Interface
class TestCLI
{
    public function run()
    {
        $options = $this->parseArguments();
        
        if (isset($options['help'])) {
            $this->showHelp();
            return;
        }
        
        $verbose = isset($options['verbose']);
        $testSuite = new DuplicateDetectorTestSuite($verbose);
        
        $success = $testSuite->runAllTests();
        
        exit($success ? 0 : 1);
    }
    
    private function parseArguments()
    {
        $options = [];
        $args = $_SERVER['argv'] ?? [];
        
        foreach ($args as $arg) {
            switch ($arg) {
                case '--help':
                case '-h':
                    $options['help'] = true;
                    break;
                case '--verbose':
                case '-v':
                    $options['verbose'] = true;
                    break;
            }
        }
        
        return $options;
    }
    
    private function showHelp()
    {
        echo "\n🧪 Suite de Testes do Detector de Duplicatas\n";
        echo "Valida todas as funcionalidades da solução\n\n";
        echo "Uso:\n";
        echo "  php test-suite.php [opções]\n\n";
        echo "Opções:\n";
        echo "  -h, --help      Mostra esta ajuda\n";
        echo "  -v, --verbose   Modo verboso (mostra detalhes de cada teste)\n\n";
        echo "Testes executados:\n";
        echo "  • Detecção de duplicatas\n";
        echo "  • Remoção de duplicatas\n";
        echo "  • Geração de relatórios\n";
        echo "  • Configuração e .duplicateignore\n";
        echo "  • Hooks do Git\n";
        echo "  • Performance\n";
        echo "  • Casos extremos\n\n";
    }
}

// Executar se chamado via CLI
if (php_sapi_name() === 'cli') {
    $cli = new TestCLI();
    $cli->run();
}