#!/bin/bash

# MyWorkProfile Monitoring Script
# Script para monitoramento contínuo do MyWorkProfile em produção

set -e

# Configurações
LOG_FILE="/var/log/MyWorkProfile-monitor.log"
ALERT_EMAIL="admin@MyWorkProfile.com"
DISCORD_WEBHOOK=""  # Opcional: webhook do Discord
SLACK_WEBHOOK=""    # Opcional: webhook do Slack
CHECK_INTERVAL=300  # 5 minutos
MAX_RETRIES=3

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Função para log
log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] ⚠️  $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ❌ $1${NC}" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] ℹ️  $1${NC}" | tee -a "$LOG_FILE"
}

success() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] ✅ $1${NC}" | tee -a "$LOG_FILE"
}

# Função para enviar alertas
send_alert() {
    local message="$1"
    local severity="${2:-warning}"
    
    # Email
    if [ -n "$ALERT_EMAIL" ]; then
        local subject="MyWorkProfile Alert - $(hostname)"
        echo "$message" | mail -s "$subject" "$ALERT_EMAIL" 2>/dev/null || true
    fi
    
    # Discord
    if [ -n "$DISCORD_WEBHOOK" ]; then
        local color=16776960  # Amarelo
        [ "$severity" = "error" ] && color=16711680    # Vermelho
        [ "$severity" = "success" ] && color=65280     # Verde
        
        curl -H "Content-Type: application/json" \
             -X POST \
             -d "{
                 \"embeds\": [{
                     \"title\": \"MyWorkProfile Monitor\",
                     \"description\": \"$message\",
                     \"color\": $color,
                     \"timestamp\": \"$(date -u +%Y-%m-%dT%H:%M:%S.000Z)\",
                     \"footer\": {
                         \"text\": \"MyWorkProfile Monitor\"
                     }
                 }]
             }" \
             "$DISCORD_WEBHOOK" >/dev/null 2>&1 || true
    fi
    
    # Slack
    if [ -n "$SLACK_WEBHOOK" ]; then
        local emoji=":warning:"
        [ "$severity" = "error" ] && emoji=":x:"
        [ "$severity" = "success" ] && emoji=":white_check_mark:"
        
        curl -X POST \
             -H 'Content-type: application/json' \
             --data "{
                 \"text\": \"$emoji $message\",
                 \"username\": \"MyWorkProfile Monitor\",
                 \"icon_emoji\": \":computer:\"
             }" \
             "$SLACK_WEBHOOK" >/dev/null 2>&1 || true
    fi
}

# Verificar containers Docker
check_containers() {
    info "Verificando containers Docker..."
    
    local containers=("MyWorkProfile-app" "MyWorkProfile-mysql" "MyWorkProfile-redis")
    local failed_containers=()
    
    for container in "${containers[@]}"; do
        if ! docker ps --filter "name=$container" --filter "status=running" --quiet | grep -q .; then
            failed_containers+=("$container")
        fi
    done
    
    if [ ${#failed_containers[@]} -gt 0 ]; then
        local message="Containers não estão rodando: ${failed_containers[*]}"
        error "$message"
        send_alert "$message" "error"
        return 1
    else
        success "Todos os containers estão rodando"
        return 0
    fi
}

# Verificar conectividade com banco de dados
check_database() {
    info "Verificando conectividade com banco de dados..."
    
    if ! docker exec MyWorkProfile-mysql mysqladmin ping -h localhost -u MyWorkProfile_user -pMyWorkProfile_password_segura >/dev/null 2>&1; then
        local message="Banco de dados MySQL não está respondendo"
        error "$message"
        send_alert "$message" "error"
        return 1
    fi
    
    if ! docker exec MyWorkProfile-redis redis-cli -a redis_password_segura ping >/dev/null 2>&1; then
        local message="Redis não está respondendo"
        error "$message"
        send_alert "$message" "error"
        return 1
    fi
    
    success "Banco de dados e Redis estão funcionando"
    return 0
}

# ... rest of the file content ...