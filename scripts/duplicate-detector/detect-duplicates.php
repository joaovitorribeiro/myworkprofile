#!/usr/bin/env php
<?php

require_once __DIR__ . '/DuplicateDetector.php';

function showHelp() {
    echo "\n🔍 Detector de Duplicatas de Código\n";
    echo "==================================\n\n";
    echo "Uso: php detect-duplicates.php [opções] [diretório]\n\n";
    echo "Opções:\n";
    echo "  --scan <dir>          Escanear diretório específico\n";
    echo "  --remove              Remover duplicatas encontradas (CUIDADO!)\n";
    echo "  --remove-smart        Remoção inteligente com backup automático\n";
    echo "  --report              Mostrar relatório detalhado\n";
    echo "  --config <arquivo>    Usar arquivo de configuração personalizado\n";
    echo "  --output <arquivo>    Salvar relatório em arquivo JSON\n";
    echo "  --dry-run             Simular operações sem modificar arquivos\n";
    echo "  --help                Mostrar esta ajuda\n\n";
    echo "Exemplos:\n";
    echo "  php detect-duplicates.php --scan ./src\n";
    echo "  php detect-duplicates.php --scan ./app --remove-smart\n";
    echo "  php detect-duplicates.php --report\n";
    echo "  php detect-duplicates.php --scan ./src --output report.json\n";
    echo "\n⚠️  IMPORTANTE: Sempre faça backup antes de usar --remove ou --remove-smart!\n";
}

function parseArguments($argv) {
    $options = [
        'scan' => null,
        'remove' => false,
        'remove-smart' => false,
        'report' => false,
        'config' => null,
        'output' => null,
        'dry-run' => false,
        'help' => false
    ];
    
    for ($i = 1; $i < count($argv); $i++) {
        switch ($argv[$i]) {
            case '--scan':
                $options['scan'] = $argv[++$i] ?? null;
                break;
            case '--remove':
                $options['remove'] = true;
                break;
            case '--remove-smart':
                $options['remove-smart'] = true;
                break;
            case '--report':
                $options['report'] = true;
                break;
            case '--config':
                $options['config'] = $argv[++$i] ?? null;
                break;
            case '--output':
                $options['output'] = $argv[++$i] ?? null;
                break;
            case '--dry-run':
                $options['dry-run'] = true;
                break;
            case '--help':
            case '-h':
                $options['help'] = true;
                break;
            default:
                if (!isset($options['scan']) && !str_starts_with($argv[$i], '--')) {
                    $options['scan'] = $argv[$i];
                } else {
                    echo "❌ Opção desconhecida: {$argv[$i]}\n";
                    exit(1);
                }
        }
    }
    
    return $options;
}

function showReport($reportFile) {
    if (!file_exists($reportFile)) {
        echo "❌ Arquivo de relatório não encontrado: {$reportFile}\n";
        echo "Execute primeiro: php detect-duplicates.php --scan\n";
        return;
    }
    
    $report = json_decode(file_get_contents($reportFile), true);
    
    echo "\n📊 RELATÓRIO DE DUPLICATAS\n";
    echo "=========================\n\n";
    echo "📅 Data: {$report['timestamp']}\n";
    echo "📁 Arquivos processados: {$report['summary']['filesProcessed']}\n";
    echo "🔍 Grupos de duplicatas: {$report['summary']['duplicateGroups']}\n";
    echo "📋 Total de duplicatas: {$report['summary']['totalDuplicates']}\n\n";
    
    if (empty($report['duplicates'])) {
        echo "✅ Nenhuma duplicata encontrada!\n";
        return;
    }
    
    echo "🔍 DUPLICATAS ENCONTRADAS:\n";
    echo "-------------------------\n\n";
    
    foreach ($report['duplicates'] as $index => $duplicate) {
        echo "Grupo #" . ($index + 1) . " (" . $duplicate['count'] . " duplicatas, similaridade: " . 
             round($duplicate['similarity'] * 100, 1) . "%)\n";
        
        foreach ($duplicate['blocks'] as $blockIndex => $block) {
            $relativePath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $block['file']);
            echo "  " . ($blockIndex + 1) . ". {$relativePath} (linhas {$block['startLine']}-{$block['endLine']})\n";
        }
        
        echo "  Código:\n";
        $firstBlock = $duplicate['blocks'][0];
        foreach (array_slice($firstBlock['code'], 0, 3) as $line) {
            echo "    " . trim($line) . "\n";
        }
        if (count($firstBlock['code']) > 3) {
            echo "    ...\n";
        }
        echo "\n";
    }
}

function confirmAction($message) {
    echo $message . " (s/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    return strtolower(trim($line)) === 's';
}

function main($argv) {
    $options = parseArguments($argv);
    
    if ($options['help']) {
        showHelp();
        exit(0);
    }
    
    try {
        $detector = new DuplicateDetector($options['config']);
        
        if ($options['report']) {
            showReport('duplicate-report.json');
        } elseif ($options['scan']) {
            echo "🔍 Escaneando: {$options['scan']}\n";
            
            if ($options['dry-run']) {
                echo "🔍 Modo simulação ativado - nenhum arquivo será modificado\n";
            }
            
            $result = $detector->scan($options['scan']);
            
            // Salvar relatório se especificado
            if ($options['output']) {
                file_put_contents($options['output'], json_encode($result, JSON_PRETTY_PRINT));
                echo "📄 Relatório salvo em: {$options['output']}\n";
            }
            
            if (!empty($result['duplicates'])) {
                if ($options['remove-smart']) {
                    if ($options['dry-run']) {
                        echo "\n🔍 Simulação de remoção inteligente:\n";
                        simulateSmartRemoval($result);
                    } else {
                        echo "\n🧹 Iniciando remoção inteligente...\n";
                        performSmartRemoval($result, $options['config']);
                    }
                } elseif ($options['remove']) {
                    if ($options['dry-run']) {
                        echo "\n🔍 Simulação de remoção simples:\n";
                        simulateSimpleRemoval($result);
                    } else {
                        echo "\n🗑️  Removendo duplicatas (método simples)...\n";
                        $removed = $detector->removeDuplicates();
                        echo "✅ Removidas {$removed} duplicatas!\n";
                    }
                }
            } else {
                echo "✅ Nenhuma duplicata encontrada!\n";
            }
        } else {
            echo "❌ Erro: Especifique um diretório para escanear ou use --report\n";
            echo "Use --help para ver as opções disponíveis.\n";
            exit(1);
        }
        
    } catch (Exception $e) {
        echo "❌ Erro: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function simulateSmartRemoval($result) {
    echo "📊 Grupos de duplicatas encontrados: " . count($result['duplicates']) . "\n";
    
    foreach ($result['duplicates'] as $index => $duplicate) {
        echo "\n📋 Grupo #" . ($index + 1) . " ({$duplicate['count']} duplicatas):\n";
        
        foreach ($duplicate['blocks'] as $blockIndex => $block) {
            $status = $blockIndex === 0 ? "💾 [PRESERVAR]" : "🗑️  [REMOVER]";
            echo "  $status {$block['file']} (linhas {$block['startLine']}-{$block['endLine']})\n";
        }
    }
    
    echo "\n💡 Use sem --dry-run para executar a remoção real.\n";
}

function simulateSimpleRemoval($result) {
    $totalToRemove = 0;
    
    foreach ($result['duplicates'] as $duplicate) {
        $totalToRemove += $duplicate['count'] - 1; // Manter apenas o primeiro
    }
    
    echo "📊 Total de blocos a remover: $totalToRemove\n";
    echo "💡 Use sem --dry-run para executar a remoção real.\n";
}

function performSmartRemoval($result, $configPath) {
    require_once __DIR__ . '/DuplicateRemover.php';
    
    // Salvar relatório temporário
    $tempReport = tempnam(sys_get_temp_dir(), 'duplicate_report_') . '.json';
    file_put_contents($tempReport, json_encode($result, JSON_PRETTY_PRINT));
    
    try {
        $remover = new DuplicateRemover($configPath);
        $removalResult = $remover->removeIntelligent($tempReport);
        
        echo "\n🎉 Remoção inteligente concluída!\n";
        echo "📊 Blocos removidos: {$removalResult['removed']}\n";
        echo "📊 Blocos preservados: {$removalResult['preserved']}\n";
        echo "💾 Backup criado em: {$removalResult['backup']}\n";
        
    } finally {
        // Limpar arquivo temporário
        if (file_exists($tempReport)) {
            unlink($tempReport);
        }
    }
}

// Script principal
main($argv);