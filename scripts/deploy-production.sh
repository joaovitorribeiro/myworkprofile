#!/bin/bash

# MyWorkProfile Production Deploy Script
# Este script automatiza o processo de deploy em produção

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configurações
PROJECT_NAME="MyWorkProfile"
DOMAIN="MyWorkProfile.com"
REPO_URL="https://github.com/seu-usuario/MyWorkProfile.git"
BRANCH="main"
COOLIFY_URL="http://localhost:8000"

# Função para log
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] ⚠️  $1${NC}"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ❌ $1${NC}"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] ℹ️  $1${NC}"
}

success() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] ✅ $1${NC}"
}

# Banner
echo -e "${PURPLE}"
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║                    MyWorkProfile Deploy Script                     ║"
echo "║                  Produção Automatizada                      ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Verificar se está rodando como root
if [ "$EUID" -eq 0 ]; then
    error "Este script não deve ser executado como root!"
    exit 1
fi

# Verificar dependências
log "Verificando dependências..."

command -v docker >/dev/null 2>&1 || { error "Docker não está instalado!"; exit 1; }
command -v curl >/dev/null 2>&1 || { error "curl não está instalado!"; exit 1; }
command -v git >/dev/null 2>&1 || { error "git não está instalado!"; exit 1; }

success "Todas as dependências estão instaladas!"

# Verificar se Docker está rodando
if ! docker info >/dev/null 2>&1; then
    error "Docker não está rodando!"
    exit 1
fi

# Verificar se Coolify está rodando
if ! curl -f $COOLIFY_URL/health >/dev/null 2>&1; then
    warn "Coolify não está respondendo em $COOLIFY_URL"
    read -p "Continuar mesmo assim? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Menu principal
echo -e "${CYAN}"
echo "Escolha uma opção:"
echo "1) Deploy completo (primeira vez)"
echo "2) Deploy de atualização"
echo "3) Rollback para versão anterior"
echo "4) Verificar status"
echo "5) Backup manual"
echo "6) Limpar cache"
echo "7) Ver logs"
echo "8) Sair"
echo -e "${NC}"

read -p "Digite sua escolha (1-8): " choice

case $choice in
    1)
        log "Iniciando deploy completo..."
        
        # Verificar se já existe deploy
        if docker ps | grep -q MyWorkProfile; then
            warn "Já existe uma instância do MyWorkProfile rodando!"
            read -p "Deseja parar e recriar? (y/N): " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                log "Parando containers existentes..."
                docker stop $(docker ps -q --filter "name=MyWorkProfile") 2>/dev/null || true
                docker rm $(docker ps -aq --filter "name=MyWorkProfile") 2>/dev/null || true
            else
                exit 1
            fi
        fi
        
        # Clonar repositório se não existir
        if [ ! -d "/tmp/MyWorkProfile-deploy" ]; then
            log "Clonando repositório..."
            git clone $REPO_URL /tmp/MyWorkProfile-deploy
        else
            log "Atualizando repositório..."
            cd /tmp/MyWorkProfile-deploy
            git pull origin $BRANCH
        fi
        
        cd /tmp/MyWorkProfile-deploy
        
        # Build da imagem
        log "Fazendo build da imagem Docker..."
        docker build -f Dockerfile -t MyWorkProfile:latest .
        
        # Criar rede se não existir
        docker network create MyWorkProfile-network 2>/dev/null || true
        
        # Iniciar MySQL
        log "Iniciando MySQL..."
        docker run -d \
            --name MyWorkProfile-mysql \
            --network MyWorkProfile-network \
            -e MYSQL_ROOT_PASSWORD=root_password_segura \
            -e MYSQL_DATABASE=MyWorkProfile_production \
            -e MYSQL_USER=MyWorkProfile_user \
            -e MYSQL_PASSWORD=MyWorkProfile_password_segura \
            -v MyWorkProfile_mysql_data:/var/lib/mysql \
            mysql:8.0
        
        # Aguardar MySQL inicializar
        log "Aguardando MySQL inicializar..."
        sleep 30
        
        # Iniciar Redis (opcional)
        log "Iniciando Redis..."
        docker run -d \
            --name MyWorkProfile-redis \
            --network MyWorkProfile-network \
            -v MyWorkProfile_redis_data:/data \
            redis:7-alpine redis-server --requirepass redis_password_segura
        
        # Iniciar aplicação
        log "Iniciando aplicação MyWorkProfile..."
        docker run -d \
            --name MyWorkProfile-app \
            --network MyWorkProfile-network \
            -p 80:80 \
            -p 443:443 \
            -e APP_NAME="MyWorkProfile" \
            -e APP_ENV="production" \
            -e APP_DEBUG="false" \
            -e APP_KEY="base64:$(openssl rand -base64 32)" \
            -e APP_URL="https://$DOMAIN" \
            -e DB_CONNECTION="mysql" \
            -e DB_HOST="MyWorkProfile-mysql" \
            -e DB_PORT="3306" \
            -e DB_DATABASE="MyWorkProfile_production" \
            -e DB_USERNAME="MyWorkProfile_user" \
            -e DB_PASSWORD="MyWorkProfile_password_segura" \
            -e CACHE_STORE="redis" \
            -e SESSION_DRIVER="redis" \
            -e QUEUE_CONNECTION="redis" \
            -e REDIS_HOST="MyWorkProfile-redis" \
            -e REDIS_PASSWORD="redis_password_segura" \
            -e REDIS_PORT="6379" \
            -e RUN_MIGRATIONS="true" \
            -e OPTIMIZE_FOR_PRODUCTION="true" \
            -e RUN_QUEUE_WORKER="true" \
            -v MyWorkProfile_storage:/var/www/html/storage \
            MyWorkProfile:latest
        
        success "Deploy completo finalizado!"
        ;;
        
    2)
        log "Iniciando deploy de atualização..."
        
        # Fazer backup antes da atualização
        log "Fazendo backup antes da atualização..."
        /usr/local/bin/backup-MyWorkProfile.sh 2>/dev/null || warn "Script de backup não encontrado"
        
        # Atualizar código
        cd /tmp/MyWorkProfile-deploy 2>/dev/null || {
            log "Clonando repositório..."
            git clone $REPO_URL /tmp/MyWorkProfile-deploy
            cd /tmp/MyWorkProfile-deploy
        }
        
        git pull origin $BRANCH
        
        # Build nova imagem
        log "Fazendo build da nova versão..."
        docker build -f Dockerfile -t MyWorkProfile:$(date +%Y%m%d_%H%M%S) .
        docker tag MyWorkProfile:$(date +%Y%m%d_%H%M%S) MyWorkProfile:latest
        
        # Rolling update
        log "Fazendo rolling update..."
        docker stop MyWorkProfile-app
        docker rm MyWorkProfile-app
        
        # Reiniciar com nova imagem
        docker run -d \
            --name MyWorkProfile-app \
            --network MyWorkProfile-network \
            -p 80:80 \
            -p 443:443 \
            --env-file /tmp/MyWorkProfile.env \
            -v MyWorkProfile_storage:/var/www/html/storage \
            MyWorkProfile:latest
        
        success "Atualização concluída!"
        ;;
        
    3)
        log "Iniciando rollback..."
        
        # Listar imagens disponíveis
        echo "Imagens disponíveis:"
        docker images MyWorkProfile --format "table {{.Tag}}\t{{.CreatedAt}}\t{{.Size}}"
        
        read -p "Digite a tag da versão para rollback: " tag
        
        if docker images MyWorkProfile:$tag --format "{{.ID}}" | grep -q .; then
            log "Fazendo rollback para versão $tag..."
            
            docker stop MyWorkProfile-app
            docker rm MyWorkProfile-app
            
            docker run -d \
                --name MyWorkProfile-app \
                --network MyWorkProfile-network \
                -p 80:80 \
                -p 443:443 \
                --env-file /tmp/MyWorkProfile.env \
                -v MyWorkProfile_storage:/var/www/html/storage \
                MyWorkProfile:$tag
            
            success "Rollback para versão $tag concluído!"
        else
            error "Versão $tag não encontrada!"
        fi
        ;;
        
    4)
        log "Verificando status do sistema..."
        
        echo -e "${CYAN}=== Status dos Containers ===${NC}"
        docker ps --filter "name=MyWorkProfile" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
        
        echo -e "\n${CYAN}=== Uso de Recursos ===${NC}"
        docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}\t{{.BlockIO}}"
        
        echo -e "\n${CYAN}=== Health Check ===${NC}"
        if curl -f http://localhost/health.php >/dev/null 2>&1; then
            success "Aplicação está respondendo!"
        else
            error "Aplicação não está respondendo!"
        fi
        
        echo -e "\n${CYAN}=== Logs Recentes ===${NC}"
        docker logs --tail 10 MyWorkProfile-app 2>/dev/null || warn "Container MyWorkProfile-app não encontrado"
        ;;
        
    5)
        log "Iniciando backup manual..."
        
        if [ -f "/usr/local/bin/backup-MyWorkProfile.sh" ]; then
        /usr/local/bin/backup-MyWorkProfile.sh
            success "Backup concluído!"
        else
            warn "Script de backup não encontrado. Fazendo backup básico..."
            
            BACKUP_DIR="/tmp/MyWorkProfile-backup-$(date +%Y%m%d_%H%M%S)"
            mkdir -p $BACKUP_DIR
            
            # Backup do banco
            docker exec MyWorkProfile-mysql mysqldump -u MyWorkProfile_user -pMyWorkProfile_password_segura MyWorkProfile_production > $BACKUP_DIR/database.sql
            
            # Backup dos arquivos
            docker cp MyWorkProfile-app:/var/www/html/storage $BACKUP_DIR/
            
            # Compactar
            tar -czf $BACKUP_DIR.tar.gz -C /tmp $(basename $BACKUP_DIR)
            rm -rf $BACKUP_DIR
            
            success "Backup salvo em: $BACKUP_DIR.tar.gz"
        fi
        ;;
        
    6)
        log "Limpando cache..."
        
        docker exec MyWorkProfile-app php artisan cache:clear
        docker exec MyWorkProfile-app php artisan cache:clear
    docker exec MyWorkProfile-app php artisan config:clear
    docker exec MyWorkProfile-app php artisan route:clear
        
        # Recriar cache otimizado
        docker exec MyWorkProfile-app php artisan config:cache
    docker exec MyWorkProfile-app php artisan route:cache
    docker exec MyWorkProfile-app php artisan view:cache
        
        success "Cache limpo e recriado!"
        ;;
        
    7)
        log "Mostrando logs..."
        
        echo "Escolha o container:"
        echo "1) MyWorkProfile-app"
    echo "2) MyWorkProfile-mysql"
    echo "3) MyWorkProfile-redis"
        echo "4) Todos"
        
        read -p "Digite sua escolha (1-4): " log_choice
        
        case $log_choice in
            1) docker logs -f MyWorkProfile-app ;;
        2) docker logs -f MyWorkProfile-mysql ;;
        3) docker logs -f MyWorkProfile-redis ;;
            4) 
                echo -e "${CYAN}=== Logs MyWorkProfile-app ===${NC}"
                docker logs --tail 20 MyWorkProfile-app
                echo -e "\n${CYAN}=== Logs MyWorkProfile-mysql ===${NC}"
                docker logs --tail 10 MyWorkProfile-mysql
                echo -e "\n${CYAN}=== Logs MyWorkProfile-redis ===${NC}"
                docker logs --tail 10 MyWorkProfile-redis
                ;;
            *) error "Opção inválida!" ;;
        esac
        ;;
        
    8)
        log "Saindo..."
        exit 0
        ;;
        
    *)
        error "Opção inválida!"
        exit 1
        ;;
esac

# Verificação final
if [ $choice -eq 1 ] || [ $choice -eq 2 ]; then
    log "Executando verificações finais..."
    
    sleep 10
    
    # Verificar se a aplicação está respondendo
    if curl -f http://localhost/health.php >/dev/null 2>&1; then
        success "✅ Aplicação está funcionando!"
        info "🌐 Acesse: https://$DOMAIN"
        info "❤️  Health Check: https://$DOMAIN/health.php"
    else
        error "❌ Aplicação não está respondendo!"
        warn "Verifique os logs: docker logs MyWorkProfile-app"
    fi
    
    # Mostrar informações úteis
    echo -e "\n${PURPLE}=== Informações Úteis ===${NC}"
    echo -e "\n${BLUE}Containers rodando:${NC}"
    docker ps --filter "name=MyWorkProfile" --format "  - {{.Names}}: {{.Status}}"
    
    echo -e "\n${BLUE}Comandos úteis:${NC}"
    echo "  - Ver logs: docker logs -f MyWorkProfile-app"
    echo "  - Acessar container: docker exec -it MyWorkProfile-app bash"
    echo "  - Reiniciar app: docker restart MyWorkProfile-app"
    echo "  - Status: docker ps"
    echo "  - Backup: /usr/local/bin/backup-MyWorkProfile.sh"
fi

echo -e "\n${GREEN}Deploy script finalizado!${NC}"