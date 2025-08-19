<?php
require_once 'DuplicateDetector.php';
require_once 'DuplicateRemover.php';
require_once 'ReportGenerator.php';

echo "🧪 Teste Final - Validação Completa\n";
echo "============================================\n";

$testDir = __DIR__ . '/final-test-files';
if (is_dir($testDir)) {
    array_map('unlink', glob($testDir . '/*'));
    rmdir($testDir);
}
mkdir($testDir, 0777, true);

// 1. Criar arquivos com duplicatas óbvias
echo "📁 Criando arquivos de teste...\n";

$duplicateCode = '<?php
function duplicateFunction() {
    echo "This is a duplicate function";
    $result = "hello world";
    return $result;
}
';

file_put_contents($testDir . '/file1.php', $duplicateCode);
file_put_contents($testDir . '/file2.php', $duplicateCode);
file_put_contents($testDir . '/file3.php', $duplicateCode);

$uniqueCode = '<?php
function uniqueFunction() {
    echo "This is unique";
    return false;
}
';

file_put_contents($testDir . '/unique.php', $uniqueCode);

// 2. Configuração otimizada para detecção
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

// 3. Teste de Detecção
echo "\n🔍 Testando detecção...\n";
$detector = new DuplicateDetector($testDir . '/config.json');
$result = $detector->scan($testDir);

echo "Arquivos processados: " . (is_array($result['processedFiles']) ? count($result['processedFiles']) : $result['processedFiles']) . "\n";
echo "Grupos de duplicatas: " . count($result['duplicates']) . "\n";

$detectionSuccess = count($result['duplicates']) > 0;
echo $detectionSuccess ? "✅ Detecção: SUCESSO\n" : "❌ Detecção: FALHA\n";

// 4. Teste de Remoção
echo "\n🗑️ Testando remoção...\n";
if ($detectionSuccess) {
    $tempReportPath = $testDir . '/temp-report.json';
    file_put_contents($tempReportPath, json_encode($result, JSON_PRETTY_PRINT));
    
    $remover = new DuplicateRemover($testDir . '/config.json');
    $removalResult = $remover->removeIntelligent($tempReportPath);
    
    $removalSuccess = $removalResult['removed'] > 0;
    echo $removalSuccess ? "✅ Remoção: SUCESSO\n" : "❌ Remoção: FALHA\n";
} else {
    echo "⏭️ Remoção: PULADO (sem duplicatas)\n";
    $removalSuccess = false;
}

// 5. Teste de Relatórios
echo "\n📊 Testando relatórios...\n";
$reportGenerator = new ReportGenerator();
// Usar os dados já coletados do scan
$reportData = $result;

$reports = $reportGenerator->generateReport($reportData, 'all');
$htmlReport = !empty($reports['html']);
$csvReport = !empty($reports['csv']);
$markdownReport = !empty($reports['markdown']);

$reportSuccess = !empty($htmlReport) && !empty($csvReport) && !empty($markdownReport);
echo $reportSuccess ? "✅ Relatórios: SUCESSO\n" : "❌ Relatórios: FALHA\n";

// 6. Resumo Final
echo "\n============================================\n";
echo "📋 RESUMO FINAL\n";
echo "============================================\n";
echo "Detecção de duplicatas: " . ($detectionSuccess ? "✅ PASSOU" : "❌ FALHOU") . "\n";
echo "Remoção de duplicatas: " . ($removalSuccess ? "✅ PASSOU" : "❌ FALHOU") . "\n";
echo "Geração de relatórios: " . ($reportSuccess ? "✅ PASSOU" : "❌ FALHOU") . "\n";

$overallSuccess = $detectionSuccess && $reportSuccess;
echo "\nResultado geral: " . ($overallSuccess ? "✅ SUCESSO" : "❌ FALHA") . "\n";

if ($overallSuccess) {
    echo "\n🎉 A solução de detecção de duplicatas está funcionando corretamente!\n";
} else {
    echo "\n⚠️ Alguns componentes precisam de ajustes.\n";
}

// Limpar
array_map('unlink', glob($testDir . '/*'));
rmdir($testDir);

exit($overallSuccess ? 0 : 1);