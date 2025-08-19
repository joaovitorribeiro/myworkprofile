# Script para deploy forcando rebuild sem cache do Docker
# Util quando ha mudancas no Dockerfile ou scripts de build

Write-Host "Iniciando deploy com rebuild forcado (sem cache)..." -ForegroundColor Green

# Atualizar timestamp do cache-bust
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
$cacheBust = Get-Date -Format "yyyyMMddHHmmss"

$content = "# Este arquivo forca o rebuild do Docker invalidando o cache`n# Timestamp: $timestamp`n# Motivo: Deploy forcado sem cache`nCACHE_BUST=$cacheBust"

$content | Out-File -FilePath ".docker-cache-bust" -Encoding UTF8

Write-Host "Cache-bust atualizado com timestamp: $cacheBust" -ForegroundColor Yellow

Write-Host ""
Write-Host "Para fazer deploy sem cache, use um dos comandos abaixo:" -ForegroundColor Cyan
Write-Host ""
Write-Host "Docker Compose:" -ForegroundColor Blue
Write-Host "   docker-compose build --no-cache"
Write-Host "   docker-compose up -d"
Write-Host ""
Write-Host "Coolify:" -ForegroundColor Blue
Write-Host "   No painel do Coolify, va em Settings > Build e ative 'No Cache'"
Write-Host "   Ou adicione --no-cache nas Build Args"
Write-Host ""
Write-Host "Docker direto:" -ForegroundColor Blue
Write-Host "   docker build --no-cache -t MyWorkProfile ."
Write-Host ""
Write-Host "Cache-bust preparado! Agora faca o deploy na sua plataforma." -ForegroundColor Green