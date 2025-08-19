#!/usr/bin/env php
<?php

function showHelp()
{
    echo "⚙️  Configurador de Detecção de Duplicatas\n";
    echo "=========================================\n\n";
    echo "Uso: php configure.php [opções]\n\n";
    echo "Opções:\n";
    echo "  --project-type <tipo>     Tipo do projeto (laravel, react, vue, generic)\n";
    echo "  --create-config           Criar nova configuração interativa\n";
    echo "  --update-ignore           Atualizar arquivo .duplicateignore\n";
    echo "  --validate                Validar configuração atual\n";
    echo "  --reset                   Resetar para configuração padrão\n";
    echo "  --show-current            Mostrar configuração atual\n";
    echo "  --help                    Mostrar esta ajuda\n\n";
    echo "Exemplos:\n";
    echo "  php configure.php --project-type laravel\n";
    echo "  php configure.php --create-config\n";
    echo "  php configure.php --validate\n";
}

function getProjectTemplates()
{
    return [
        'laravel' => [
            'description' => 'Projeto Laravel/PHP',
            'fileExtensions' => ['php', 'blade.php', 'js', 'vue', 'css', 'scss'],
            'ignorePaths' => ['vendor', 'storage', 'bootstrap/cache', 'node_modules'],
            'ignoreFiles' => ['composer.lock', 'package-lock.json'],
            'specialRules' => [
                'ignoreMigrations' => true,
                'ignoreSeeds' => true,
                'preserveControllers' => true
            ]
        ],
        'react' => [
            'description' => 'Projeto React/JavaScript',
            'fileExtensions' => ['js', 'jsx', 'ts', 'tsx', 'css', 'scss', 'less'],
            'ignorePaths' => ['node_modules', 'build', 'dist', 'coverage'],
            'ignoreFiles' => ['package-lock.json', 'yarn.lock'],
            'specialRules' => [
                'ignoreTests' => false,
                'preserveHooks' => true
            ]
        ],
        'vue' => [
            'description' => 'Projeto Vue.js',
            'fileExtensions' => ['vue', 'js', 'ts', 'css', 'scss', 'less'],
            'ignorePaths' => ['node_modules', 'dist', 'coverage'],
            'ignoreFiles' => ['package-lock.json', 'yarn.lock'],
            'specialRules' => [
                'ignoreComponents' => false,
                'preserveComposables' => true
            ]
        ],
        'generic' => [
            'description' => 'Projeto genérico',
            'fileExtensions' => ['php', 'js', 'py', 'java', 'c', 'cpp', 'cs'],
            'ignorePaths' => ['node_modules', 'vendor', 'build', 'dist'],
            'ignoreFiles' => [],
            'specialRules' => []
        ]
    ];
}

function createConfigForProject($projectType)
{
    $templates = getProjectTemplates();
    
    if (!isset($templates[$projectType])) {
        echo "❌ Tipo de projeto inválido: $projectType\n";
        echo "Tipos disponíveis: " . implode(', ', array_keys($templates)) . "\n";
        return false;
    }
    
    $template = $templates[$projectType];
    
    echo "🔧 Criando configuração para: {$template['description']}\n";
    
    $config = [
        'projectType' => $projectType,
        'description' => "Configuração para {$template['description']}",
        'version' => '2.0',
        'analysis' => [
            'minLines' => 3,
            'minTokens' => 50,
            'similarity' => 0.85,
            'fileExtensions' => $template['fileExtensions']
        ],
        'ignore' => [
            'paths' => $template['ignorePaths'],
            'files' => $template['ignoreFiles'],
            'patterns' => []
        ],
        'removal' => [
            'strategy' => 'intelligent',
            'backup' => [
                'enabled' => true,
                'directory' => './backups'
            ]
        ],
        'reporting' => [
            'format' => 'json',
            'outputFile' => 'duplicate-report.json',
            'verbosity' => 'detailed'
        ]
    ];
    
    // Adicionar regras específicas do projeto
    if (!empty($template['specialRules'])) {
        $config['projectSpecific'][$projectType] = $template['specialRules'];
    }
    
    $configFile = __DIR__ . '/config.json';
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
    
    echo "✅ Configuração criada: $configFile\n";
    
    // Criar .duplicateignore específico se não existir
    createIgnoreFile($projectType, $template);
    
    return true;
}

function createIgnoreFile($projectType, $template)
{
    $ignoreFile = __DIR__ . '/.duplicateignore';
    
    if (file_exists($ignoreFile)) {
        echo "⚠️  Arquivo .duplicateignore já existe. Use --update-ignore para atualizar.\n";
        return;
    }
    
    $content = "# Configuração de ignore para projeto $projectType\n";
    $content .= "# Gerado automaticamente em " . date('Y-m-d H:i:s') . "\n\n";
    
    // Adicionar padrões específicos do tipo de projeto
    switch ($projectType) {
        case 'laravel':
            $content .= "# Laravel específico\n";
            $content .= "vendor/\n";
            $content .= "storage/\n";
            $content .= "bootstrap/cache/\n";
            $content .= "database/migrations/*.php\n";
            $content .= "database/seeders/*.php\n";
            $content .= "*.blade.php\n";
            break;
            
        case 'react':
            $content .= "# React específico\n";
            $content .= "node_modules/\n";
            $content .= "build/\n";
            $content .= "dist/\n";
            $content .= "coverage/\n";
            $content .= "src/serviceWorker.js\n";
            $content .= "src/setupTests.js\n";
            break;
            
        case 'vue':
            $content .= "# Vue específico\n";
            $content .= "node_modules/\n";
            $content .= "dist/\n";
            $content .= "coverage/\n";
            break;
    }
    
    $content .= "\n# Arquivos comuns\n";
    $content .= "*.min.js\n";
    $content .= "*.min.css\n";
    $content .= "*.log\n";
    $content .= ".DS_Store\n";
    $content .= "Thumbs.db\n";
    
    file_put_contents($ignoreFile, $content);
    echo "✅ Arquivo .duplicateignore criado\n";
}

function createInteractiveConfig()
{
    echo "🎯 Configuração Interativa\n";
    echo "=========================\n\n";
    
    // Tipo de projeto
    echo "Selecione o tipo de projeto:\n";
    $templates = getProjectTemplates();
    $options = array_keys($templates);
    
    foreach ($options as $index => $type) {
        echo "  " . ($index + 1) . ". $type - {$templates[$type]['description']}\n";
    }
    
    echo "\nEscolha (1-" . count($options) . "): ";
    $choice = (int)trim(fgets(STDIN)) - 1;
    
    if ($choice < 0 || $choice >= count($options)) {
        echo "❌ Escolha inválida\n";
        return false;
    }
    
    $projectType = $options[$choice];
    
    // Configurações de análise
    echo "\n📊 Configurações de Análise\n";
    echo "Linhas mínimas para considerar duplicata (padrão: 3): ";
    $minLines = trim(fgets(STDIN));
    $minLines = $minLines ?: 3;
    
    echo "Similaridade mínima 0-1 (padrão: 0.85): ";
    $similarity = trim(fgets(STDIN));
    $similarity = $similarity ?: 0.85;
    
    // Estratégia de remoção
    echo "\n🗑️  Estratégia de Remoção\n";
    echo "1. Inteligente (recomendado)\n";
    echo "2. Simples\n";
    echo "Escolha (1-2): ";
    $strategyChoice = (int)trim(fgets(STDIN));
    $strategy = $strategyChoice === 2 ? 'simple' : 'intelligent';
    
    // Criar configuração
    $template = $templates[$projectType];
    $config = [
        'projectType' => $projectType,
        'description' => "Configuração personalizada para {$template['description']}",
        'version' => '2.0',
        'analysis' => [
            'minLines' => (int)$minLines,
            'minTokens' => 50,
            'similarity' => (float)$similarity,
            'fileExtensions' => $template['fileExtensions']
        ],
        'ignore' => [
            'paths' => $template['ignorePaths'],
            'files' => $template['ignoreFiles'],
            'patterns' => []
        ],
        'removal' => [
            'strategy' => $strategy,
            'backup' => [
                'enabled' => true,
                'directory' => './backups'
            ]
        ],
        'reporting' => [
            'format' => 'json',
            'outputFile' => 'duplicate-report.json',
            'verbosity' => 'detailed'
        ]
    ];
    
    $configFile = __DIR__ . '/config.json';
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
    
    echo "\n✅ Configuração personalizada criada!\n";
    createIgnoreFile($projectType, $template);
    
    return true;
}

function validateConfig()
{
    echo "🔍 Validando configuração...\n";
    
    $configFile = __DIR__ . '/config.json';
    
    if (!file_exists($configFile)) {
        echo "❌ Arquivo config.json não encontrado\n";
        return false;
    }
    
    $config = json_decode(file_get_contents($configFile), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ Erro no JSON: " . json_last_error_msg() . "\n";
        return false;
    }
    
    $errors = [];
    
    // Validar campos obrigatórios
    $required = ['analysis', 'ignore', 'removal', 'reporting'];
    foreach ($required as $field) {
        if (!isset($config[$field])) {
            $errors[] = "Campo obrigatório ausente: $field";
        }
    }
    
    // Validar extensões de arquivo
    if (empty($config['analysis']['fileExtensions'])) {
        $errors[] = "Nenhuma extensão de arquivo configurada";
    }
    
    // Validar similaridade
    $similarity = $config['analysis']['similarity'] ?? 0;
    if ($similarity < 0 || $similarity > 1) {
        $errors[] = "Similaridade deve estar entre 0 e 1";
    }
    
    if (empty($errors)) {
        echo "✅ Configuração válida!\n";
        echo "📊 Projeto: " . ($config['projectType'] ?? 'não especificado') . "\n";
        echo "📁 Extensões: " . implode(', ', $config['analysis']['fileExtensions']) . "\n";
        echo "🎯 Similaridade: " . $config['analysis']['similarity'] . "\n";
        return true;
    } else {
        echo "❌ Erros encontrados:\n";
        foreach ($errors as $error) {
            echo "  • $error\n";
        }
        return false;
    }
}

function showCurrentConfig()
{
    $configFile = __DIR__ . '/config.json';
    
    if (!file_exists($configFile)) {
        echo "❌ Arquivo config.json não encontrado\n";
        return false;
    }
    
    echo "📋 Configuração Atual\n";
    echo "====================\n\n";
    
    $config = json_decode(file_get_contents($configFile), true);
    
    echo "Tipo do Projeto: " . ($config['projectType'] ?? 'não especificado') . "\n";
    echo "Versão: " . ($config['version'] ?? 'não especificada') . "\n";
    echo "Descrição: " . ($config['description'] ?? 'não especificada') . "\n\n";
    
    echo "📊 Análise:\n";
    echo "  • Linhas mínimas: " . ($config['analysis']['minLines'] ?? 'não especificado') . "\n";
    echo "  • Similaridade: " . ($config['analysis']['similarity'] ?? 'não especificado') . "\n";
    echo "  • Extensões: " . implode(', ', $config['analysis']['fileExtensions'] ?? []) . "\n\n";
    
    echo "🚫 Ignorar:\n";
    echo "  • Caminhos: " . implode(', ', $config['ignore']['paths'] ?? []) . "\n";
    echo "  • Arquivos: " . implode(', ', $config['ignore']['files'] ?? []) . "\n\n";
    
    echo "🗑️  Remoção:\n";
    echo "  • Estratégia: " . ($config['removal']['strategy'] ?? 'não especificada') . "\n";
    echo "  • Backup: " . ($config['removal']['backup']['enabled'] ? 'habilitado' : 'desabilitado') . "\n";
    
    return true;
}

function resetConfig()
{
    echo "⚠️  Isso irá resetar a configuração para o padrão. Continuar? (s/N): ";
    $confirm = trim(fgets(STDIN));
    
    if (strtolower($confirm) !== 's') {
        echo "❌ Operação cancelada\n";
        return false;
    }
    
    $defaultConfig = __DIR__ . '/config.json';
    $backupConfig = __DIR__ . '/config.json.backup';
    
    // Fazer backup da configuração atual
    if (file_exists($defaultConfig)) {
        copy($defaultConfig, $backupConfig);
        echo "💾 Backup criado: config.json.backup\n";
    }
    
    // Copiar configuração padrão
    $advancedConfig = __DIR__ . '/advanced-config.json';
    if (file_exists($advancedConfig)) {
        copy($advancedConfig, $defaultConfig);
        echo "✅ Configuração resetada para o padrão\n";
    } else {
        echo "❌ Arquivo de configuração padrão não encontrado\n";
        return false;
    }
    
    return true;
}

function main($argv)
{
    if (count($argv) < 2) {
        showHelp();
        exit(1);
    }
    
    $option = $argv[1];
    
    switch ($option) {
        case '--project-type':
            if (!isset($argv[2])) {
                echo "❌ Especifique o tipo do projeto\n";
                exit(1);
            }
            createConfigForProject($argv[2]);
            break;
            
        case '--create-config':
            createInteractiveConfig();
            break;
            
        case '--validate':
            validateConfig();
            break;
            
        case '--show-current':
            showCurrentConfig();
            break;
            
        case '--reset':
            resetConfig();
            break;
            
        case '--help':
        case '-h':
            showHelp();
            break;
            
        default:
            echo "❌ Opção desconhecida: $option\n";
            showHelp();
            exit(1);
    }
}

// Verificar se está sendo executado via CLI
if (php_sapi_name() !== 'cli') {
    echo "❌ Este script deve ser executado via linha de comando.\n";
    exit(1);
}

main($argv);