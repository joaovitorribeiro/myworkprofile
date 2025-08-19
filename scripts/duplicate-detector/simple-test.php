<?php
require_once 'DuplicateDetector.php';

// Criar diretório de teste simples
$testDir = __DIR__ . '/simple-test';
if (!is_dir($testDir)) {
    mkdir($testDir, 0777, true);
}

// Criar dois arquivos idênticos
$duplicateCode = '<?php
function testFunction() {
    echo "Hello World";
    return true;
}
';

file_put_contents($testDir . '/file1.php', $duplicateCode);
file_put_contents($testDir . '/file2.php', $duplicateCode);

// Configuração simples
$config = [
    'settings' => [
        'minLines' => 2,
        'minTokens' => 3,
        'similarity' => 0.5,
        'extensions' => ['.php'],
        'excludePaths' => [],
        'excludeFiles' => [],
        'ignoreWhitespace' => false,
        'ignoreComments' => false
    ],
    'reporting' => [
        'outputFile' => $testDir . '/report.json'
    ]
];

file_put_contents($testDir . '/config.json', json_encode($config, JSON_PRETTY_PRINT));

// Executar detecção
echo "🧪 Teste simples de detecção de duplicatas\n";
echo "📁 Diretório: $testDir\n";

$detector = new DuplicateDetector($testDir . '/config.json');
$result = $detector->scan($testDir);

echo "\n📊 Resultado:\n";
echo "Arquivos processados: " . $result['processedFiles'] . "\n";
echo "Grupos de duplicatas: " . count($result['duplicates']) . "\n";
echo "Total de duplicatas: " . $result['totalDuplicates'] . "\n";

if (!empty($result['duplicates'])) {
    echo "\n✅ SUCESSO: Duplicatas detectadas!\n";
    foreach ($result['duplicates'] as $i => $duplicate) {
        echo "Grupo " . ($i + 1) . ": " . $duplicate['count'] . " duplicatas\n";
    }
} else {
    echo "\n❌ FALHA: Nenhuma duplicata detectada\n";
    echo "\nDebug - Conteúdo dos arquivos:\n";
    echo "File1: " . file_get_contents($testDir . '/file1.php') . "\n";
    echo "File2: " . file_get_contents($testDir . '/file2.php') . "\n";
}

// Limpar
array_map('unlink', glob($testDir . '/*'));
rmdir($testDir);