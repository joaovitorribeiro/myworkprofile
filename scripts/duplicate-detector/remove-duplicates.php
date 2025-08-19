#!/usr/bin/env php
<?php

require_once __DIR__ . '/DuplicateRemover.php';

function showHelp()
{
    echo "🧹 Ferramenta de Remoção Automática de Duplicatas\n";
    echo "================================================\n\n";
    echo "Uso: php remove-duplicates.php [opções]\n\n";
    echo "Opções:\n";
    echo "  --report <arquivo>     Arquivo de relatório de duplicatas (obrigatório)\n";
    echo "  --config <arquivo>     Arquivo de configuração personalizado\n";
    echo "  --dry-run             Simular remoção sem modificar arquivos\n";
    echo "  --backup-dir <dir>    Diretório personalizado para backup\n";
    echo "  --restore <dir>       Restaurar backup do diretório especificado\n";
    echo "  --interactive         Modo interativo (confirmar cada remoção)\n";
    echo "  --force               Forçar remoção sem confirmação\n";
    echo "  --help                Mostrar esta ajuda\n\n";
    echo "Exemplos:\n";
    echo "  php remove-duplicates.php --report duplicate-report.json\n";
    echo "  php remove-duplicates.php --report duplicate-report.json --dry-run\n";
    echo "  php remove-duplicates.php --restore ./backups/2024-01-15_10-30-45\n";
    echo "  php remove-duplicates.php --report duplicate-report.json --interactive\n\n";
}

function parseArguments($argv)
{
    $options = [
        'report' => null,
        'config' => null,
        'dry-run' => false,
        'backup-dir' => null,
        'restore' => null,
        'interactive' => false,
        'force' => false,
        'help' => false
    ];
    
    for ($i = 1; $i < count($argv); $i++) {
        switch ($argv[$i]) {
            case '--report':
                $options['report'] = $argv[++$i] ?? null;
                break;
            case '--config':
                $options['config'] = $argv[++$i] ?? null;
                break;
            case '--dry-run':
                $options['dry-run'] = true;
                break;
            case '--backup-dir':
                $options['backup-dir'] = $argv[++$i] ?? null;
                break;
            case '--restore':
                $options['restore'] = $argv[++$i] ?? null;
                break;
            case '--interactive':
                $options['interactive'] = true;
                break;
            case '--force':
                $options['force'] = true;
                break;
            case '--help':
            case '-h':
                $options['help'] = true;
                break;
            default:
                echo "❌ Opção desconhecida: {$argv[$i]}\n";
                exit(1);
        }
    }
    
    return $options;
}

function confirmAction($message)
{
    echo "$message (s/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    return strtolower(trim($line)) === 's';
}

function simulateRemoval($reportPath)
{
    echo "🔍 Simulando remoção de duplicatas...\n\n";
    
    if (!file_exists($reportPath)) {
        echo "❌ Arquivo de relatório não encontrado: {$reportPath}\n";
        exit(1);
    }
    
    $report = json_decode(file_get_contents($reportPath), true);
    
    if (empty($report['duplicates'])) {
        echo "✅ Nenhuma duplicata encontrada para remover!\n";
        return;
    }
    
    $totalBlocks = 0;
    $totalGroups = count($report['duplicates']);
    
    foreach ($report['duplicates'] as $index => $duplicate) {
        $blocks = $duplicate['blocks'];
        $totalBlocks += count($blocks);
        
        echo "📋 Grupo #" . ($index + 1) . " - {$duplicate['count']} duplicatas:\n";
        
        foreach ($blocks as $blockIndex => $block) {
            $status = $blockIndex === 0 ? "💾 [PRESERVAR]" : "🗑️  [REMOVER]";
            echo "  $status {$block['file']} (linhas {$block['startLine']}-{$block['endLine']})\n";
        }
        echo "\n";
    }
    
    $toRemove = $totalBlocks - $totalGroups;
    echo "📊 Resumo da simulação:\n";
    echo "  • Total de grupos: $totalGroups\n";
    echo "  • Total de blocos: $totalBlocks\n";
    echo "  • Blocos a preservar: $totalGroups\n";
    echo "  • Blocos a remover: $toRemove\n\n";
    echo "💡 Use sem --dry-run para executar a remoção real.\n";
}

function main($argv)
{
    $options = parseArguments($argv);
    
    if ($options['help']) {
        showHelp();
        exit(0);
    }
    
    // Modo de restauração
    if ($options['restore']) {
        echo "🔄 Iniciando restauração de backup...\n\n";
        
        if (!$options['force'] && !confirmAction("⚠️  Isso irá sobrescrever os arquivos atuais. Continuar?")) {
            echo "❌ Operação cancelada.\n";
            exit(0);
        }
        
        try {
            $remover = new DuplicateRemover($options['config']);
            $restored = $remover->restoreBackup($options['restore']);
            echo "\n✅ Restauração concluída! Arquivos restaurados: $restored\n";
        } catch (Exception $e) {
            echo "❌ Erro na restauração: " . $e->getMessage() . "\n";
            exit(1);
        }
        
        exit(0);
    }
    
    // Verificar se o relatório foi fornecido
    if (!$options['report']) {
        echo "❌ Erro: Arquivo de relatório é obrigatório.\n";
        echo "Use --help para ver as opções disponíveis.\n";
        exit(1);
    }
    
    // Modo dry-run
    if ($options['dry-run']) {
        simulateRemoval($options['report']);
        exit(0);
    }
    
    // Remoção real
    echo "🧹 Iniciando remoção de duplicatas...\n\n";
    
    if (!$options['force'] && !$options['interactive']) {
        if (!confirmAction("⚠️  Isso irá modificar seus arquivos. Um backup será criado. Continuar?")) {
            echo "❌ Operação cancelada.\n";
            exit(0);
        }
    }
    
    try {
        $remover = new DuplicateRemover($options['config']);
        
        if ($options['interactive']) {
            echo "🤝 Modo interativo ativado. Você será consultado para cada grupo.\n\n";
            // TODO: Implementar modo interativo
            echo "⚠️  Modo interativo ainda não implementado. Use --force para continuar.\n";
            exit(1);
        }
        
        $result = $remover->removeIntelligent($options['report']);
        
        echo "\n🎉 Processo concluído com sucesso!\n";
        echo "📊 Estatísticas finais:\n";
        echo "  • Blocos removidos: {$result['removed']}\n";
        echo "  • Blocos preservados: {$result['preserved']}\n";
        echo "  • Backup salvo em: {$result['backup']}\n\n";
        echo "💡 Para desfazer as alterações, use:\n";
        echo "   php remove-duplicates.php --restore {$result['backup']}\n";
        
    } catch (Exception $e) {
        echo "❌ Erro durante a remoção: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Verificar se o PHP está sendo executado via CLI
if (php_sapi_name() !== 'cli') {
    echo "❌ Este script deve ser executado via linha de comando.\n";
    exit(1);
}

// Executar função principal
main($argv);