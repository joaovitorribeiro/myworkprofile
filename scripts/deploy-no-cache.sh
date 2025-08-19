#!/bin/bash

# Script para deploy forçando rebuild sem cache do Docker
# Útil quando há mudanças no Dockerfile ou scripts de build

echo "🚀 Iniciando deploy com rebuild forçado (sem cache)..."

# Atualizar timestamp do cache-bust
echo "# Este arquivo força o rebuild do Docker invalidando o cache" > .docker-cache-bust
echo "# Timestamp: $(date '+%Y-%m-%d %H:%M:%S')" >> .docker-cache-bust
echo "# Motivo: Deploy forçado sem cache" >> .docker-cache-bust
echo "CACHE_BUST=$(date '+%Y%m%d%H%M%S')" >> .docker-cache-bust

echo "📝 Cache-bust atualizado com timestamp: $(date '+%Y%m%d%H%M%S')"

# Instruções para diferentes plataformas de deploy
echo ""
echo "📋 Para fazer deploy sem cache, use um dos comandos abaixo:"
echo ""
echo "🐳 Docker Compose:"
echo "   docker-compose build --no-cache"
echo "   docker-compose up -d"
echo ""
echo "☁️  Coolify:"
echo "   No painel do Coolify, vá em Settings > Build e ative 'No Cache'"
echo "   Ou adicione --no-cache nas Build Args"
echo ""
echo "🔧 Docker direto:"
echo "   docker build --no-cache -t MyWorkProfile ."
echo ""
echo "✅ Cache-bust preparado! Agora faça o deploy na sua plataforma."