# Script de instalação dos hooks do Git para detecção de duplicatas
# Execute como: powershell -ExecutionPolicy Bypass -File install-hooks.ps1

Write-Host "🔧 Instalando hooks do Git para detecção de duplicatas..." -ForegroundColor Cyan

# Verificar se estamos em um repositório Git
if (-not (Test-Path ".git")) {
    Write-Host "❌ Este não é um repositório Git válido" -ForegroundColor Red
    exit 1
}

# Verificar se PHP está disponível
try {
    $phpVersion = php -v 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "PHP não encontrado"
    }
    Write-Host "✅ PHP encontrado" -ForegroundColor Green
} catch {
    Write-Host "❌ PHP não encontrado. Instale PHP para usar a detecção de duplicatas." -ForegroundColor Red
    exit 1
}

# Verificar se os scripts de detecção existem
$detectorPath = "./scripts/duplicate-detector/detect-duplicates.php"
if (-not (Test-Path $detectorPath)) {
    Write-Host "❌ Script detector não encontrado: $detectorPath" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Scripts de detecção encontrados" -ForegroundColor Green

# Criar diretório de hooks se não existir
$hooksDir = ".git/hooks"
if (-not (Test-Path $hooksDir)) {
    New-Item -ItemType Directory -Path $hooksDir -Force | Out-Null
}

# Configurar Git para usar os hooks
Write-Host "🔧 Configurando Git hooks..." -ForegroundColor Cyan

# Configurar core.hooksPath
git config core.hooksPath .git/hooks
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ core.hooksPath configurado" -ForegroundColor Green
} else {
    Write-Host "⚠️  Aviso: Não foi possível configurar core.hooksPath" -ForegroundColor Yellow
}

# Verificar se os hooks foram criados
$preCommitHook = ".git/hooks/pre-commit"
$preMergeHook = ".git/hooks/pre-merge-commit"

if (Test-Path $preCommitHook) {
    Write-Host "✅ Hook pre-commit instalado" -ForegroundColor Green
} else {
    Write-Host "❌ Hook pre-commit não encontrado" -ForegroundColor Red
}

if (Test-Path $preMergeHook) {
    Write-Host "✅ Hook pre-merge-commit instalado" -ForegroundColor Green
} else {
    Write-Host "❌ Hook pre-merge-commit não encontrado" -ForegroundColor Red
}

# Testar a detecção de duplicatas
Write-Host "\n🧪 Testando detecção de duplicatas..." -ForegroundColor Cyan
try {
    php $detectorPath --scan . | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Teste de detecção bem-sucedido" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Teste de detecção com avisos" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Erro no teste de detecção: $_" -ForegroundColor Red
}

# Mostrar configurações disponíveis
Write-Host "\n📋 CONFIGURAÇÕES DISPONÍVEIS:" -ForegroundColor Cyan
Write-Host "\n🔧 Variáveis de ambiente para controle:" -ForegroundColor White
Write-Host "  ALLOW_DUPLICATE_MERGE=true    - Permite merge mesmo com duplicatas" -ForegroundColor Gray
Write-Host "  STRICT_DUPLICATE_CHECK=true   - Bloqueia commits com duplicatas" -ForegroundColor Gray

Write-Host "\n📊 Comandos úteis:" -ForegroundColor White
Write-Host "  php scripts/duplicate-detector/detect-duplicates.php --scan     - Escanear duplicatas" -ForegroundColor Gray
Write-Host "  php scripts/duplicate-detector/detect-duplicates.php --report   - Ver relatório" -ForegroundColor Gray
Write-Host "  php scripts/duplicate-detector/detect-duplicates.php --remove   - Remover duplicatas" -ForegroundColor Gray

Write-Host "\n🎉 Instalação concluída!" -ForegroundColor Green
Write-Host "Os hooks agora verificarão duplicatas automaticamente em commits e merges." -ForegroundColor White