#!/bin/bash
set -euo pipefail

# ========== Cores ==========
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Iniciando MyWorkProfile em modo produção...${NC}"

# ========== Helpers ==========
log()   { echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $*${NC}"; }
warn()  { echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] ⚠️  $*${NC}"; }
error() { echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ❌ $*${NC}"; }

# Garante diretório do app
cd /var/www/html || { error "Diretório /var/www/html não encontrado"; exit 1; }

# ========== Sanidade básica ==========
log "Verificando variáveis essenciais..."
: "${APP_URL:?APP_URL não está definida}"
# APP_KEY pode ser gerada; não falhar aqui

# ========== Espera ativa por MySQL (se configurado) ==========
if [ -n "${DB_HOST:-}" ]; then
  log "Aguardando MySQL em ${DB_HOST}:${DB_PORT:-3306}..."
  if command -v mysqladmin >/dev/null 2>&1; then
    timeout=120; c=0
    until mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT:-3306}" --silent --connect-timeout=5; do
      if [ $c -ge $timeout ]; then
        error "Timeout: Não conectou ao MySQL após ${timeout}s"
        exit 1
      fi
      sleep 2; c=$((c+2))
    done
    log "✅ MySQL disponível"
  else
    warn "mysqladmin não está instalado; seguindo sem wait ativo de MySQL"
  fi
fi

# ========== Espera ativa por Redis (se selecionado) ==========
if [ "${CACHE_STORE:-database}" = "redis" ] && [ -n "${REDIS_HOST:-}" ]; then
  log "Aguardando Redis em ${REDIS_HOST}:${REDIS_PORT:-6379}..."
  if command -v redis-cli >/dev/null 2>&1; then
    timeout=30; c=0
    until redis-cli -h "${REDIS_HOST}" -p "${REDIS_PORT:-6379}" ping >/dev/null 2>&1; do
      if [ $c -ge $timeout ]; then
        warn "Timeout Redis: alternando para cache/session em database"
        export CACHE_STORE=database
        export SESSION_DRIVER=database
        break
      fi
      sleep 1; c=$((c+1))
    done
    [ "${CACHE_STORE:-database}" = "redis" ] && log "✅ Redis disponível"
  else
    warn "redis-cli não está instalado; seguindo sem wait ativo de Redis"
  fi
fi

# ========== Criar .env otimizado ==========
log "Gerando .env de produção..."
cat > .env << EOF
# Aplicação
APP_NAME=${APP_NAME:-MyWorkProfile}
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_KEY=${APP_KEY:-}
APP_URL=${APP_URL}
APP_LOCALE=${APP_LOCALE:-pt}
APP_FALLBACK_LOCALE=${APP_FALLBACK_LOCALE:-en}
APP_FAKER_LOCALE=${APP_FAKER_LOCALE:-pt_BR}

# Logs
LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_STACK=${LOG_STACK:-daily}
LOG_LEVEL=${LOG_LEVEL:-error}
LOG_DEPRECATIONS_CHANNEL=${LOG_DEPRECATIONS_CHANNEL:-null}

# Segurança / Proxy
TRUSTED_PROXIES=${TRUSTED_PROXIES:-*}
FORCE_HTTPS=${FORCE_HTTPS:-true}
BCRYPT_ROUNDS=${BCRYPT_ROUNDS:-12}

# Sessão
SESSION_DRIVER=${SESSION_DRIVER:-database}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}
SESSION_ENCRYPT=${SESSION_ENCRYPT:-true}
SESSION_PATH=${SESSION_PATH:-/}
SESSION_DOMAIN=${SESSION_DOMAIN:-}
SESSION_SECURE_COOKIE=${SESSION_SECURE_COOKIE:-true}
SESSION_HTTP_ONLY=${SESSION_HTTP_ONLY:-true}
SESSION_SAME_SITE=${SESSION_SAME_SITE:-lax}

# Database
DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-}
DB_USERNAME=${DB_USERNAME:-}
DB_PASSWORD=${DB_PASSWORD:-}

# Cache / Queue
CACHE_STORE=${CACHE_STORE:-database}
CACHE_PREFIX=${CACHE_PREFIX:-MyWorkProfile_cache}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-database}

# Redis
REDIS_CLIENT=${REDIS_CLIENT:-phpredis}
REDIS_HOST=${REDIS_HOST:-}
REDIS_PASSWORD=${REDIS_PASSWORD:-null}
REDIS_PORT=${REDIS_PORT:-6379}
REDIS_DB=${REDIS_DB:-0}

# Filesystem
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}

# Broadcast
BROADCAST_CONNECTION=${BROADCAST_CONNECTION:-log}

# Email
MAIL_MAILER=${MAIL_MAILER:-log}
MAIL_HOST=${MAIL_HOST:-}
MAIL_PORT=${MAIL_PORT:-587}
MAIL_USERNAME=${MAIL_USERNAME:-}
MAIL_PASSWORD=${MAIL_PASSWORD:-}
MAIL_ENCRYPTION=${MAIL_ENCRYPTION:-tls}
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS:-noreply@MyWorkProfile.ddns.net}
MAIL_FROM_NAME=${MAIL_FROM_NAME:-MyWorkProfile}

# Frontend
VITE_APP_NAME=${VITE_APP_NAME:-MyWorkProfile}
VITE_APP_URL=${VITE_APP_URL:-${APP_URL}}
EOF
log "✅ .env criado"

# ========== APP_KEY ==========
if ! grep -q '^APP_KEY=base64:' .env || [ -z "${APP_KEY:-}" ] || [ "${APP_KEY:-}" = "base64:" ]; then
  warn "APP_KEY ausente ou inválida; gerando uma nova..."
  php artisan key:generate --force || { error "Falha ao gerar APP_KEY"; exit 1; }
  log "✅ APP_KEY gerada"
fi

# ========== Link de storage ==========
php artisan storage:link >/dev/null 2>&1 || true

# ========== Migrações / Seeders ==========
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  log "Checando migrações..."
  if php artisan migrate:status | grep -q "Pending" ; then
    log "Executando migrações pendentes..."
    php artisan migrate --force
    log "✅ Migrações ok"
  else
    log "✅ Sem migrações pendentes"
  fi
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
  log "Executando seeders..."
  php artisan db:seed --force || warn "Seeders falharam (seguindo)"
fi

# ========== Otimizações ==========
if [ "${APP_ENV:-production}" = "production" ] || [ "${OPTIMIZE_FOR_PRODUCTION:-true}" = "true" ]; then
  log "Aplicando otimizações..."
  php artisan cache:clear || true
  php artisan config:clear || true
  php artisan route:clear || true
  php artisan view:clear || true

  php artisan config:cache || warn "config:cache falhou"
  php artisan route:cache  || warn "route:cache falhou (rotas com closures?)"
  php artisan view:cache   || warn "view:cache falhou"
  php artisan event:cache  || true

  if command -v composer >/dev/null 2>&1; then
    composer dump-autoload --optimize || true
  else
    warn "composer não encontrado para dump-autoload (ok)"
  fi
  log "✅ Otimizações concluídas"
fi

# ========== Clear on demand ==========
if [ "${CLEAR_CACHE:-false}" = "true" ]; then
  log "Limpando todos os caches sob demanda..."
  php artisan cache:clear || true
  php artisan config:clear || true
  php artisan route:clear || true
  php artisan view:clear  || true
  php artisan event:clear || true
fi

# ========== Permissões ==========
log "Ajustando permissões..."
chown -R www-data:www-data /var/www/html
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
log "✅ Permissões ok"

# ========== Queue Worker opcional ==========
if [ "${RUN_QUEUE_WORKER:-false}" = "true" ]; then
  log "Iniciando queue worker em background..."
  cat >/usr/local/bin/queue-worker.sh << 'WORKER_EOF'
#!/bin/bash
set -euo pipefail
cd /var/www/html
while true; do
  php artisan queue:work --daemon --tries=3 --timeout=60 --sleep=3 --max-jobs=1000 --max-time=3600 || true
  echo "Queue worker parou. Reiniciando em 5s..."
  sleep 5
done
WORKER_EOF
  chmod +x /usr/local/bin/queue-worker.sh
  nohup /usr/local/bin/queue-worker.sh >/var/log/queue-worker.log 2>&1 &
  log "✅ Queue worker iniciado"
fi

# ========== Scheduler opcional ==========
if [ "${RUN_SCHEDULER:-false}" = "true" ]; then
  warn "Scheduler deve ser executado como um serviço separado no Coolify"
  warn "Configure um job/cronjob separado para: php artisan schedule:run"
fi

# ========== Health Check ==========
log "Criando health check em /public/health.php..."
cat > /var/www/html/public/health.php << 'HEALTH_EOF'
<?php
header('Content-Type: application/json');

$checks = [
  'status' => 'ok',
  'timestamp' => date('c'),
  'version' => '1.0.0'
];

try {
  $driver = getenv('DB_CONNECTION') === 'pgsql' ? 'pgsql' : 'mysql';
  $dsn = sprintf('%s:host=%s;port=%s;dbname=%s',
    $driver,
    getenv('DB_HOST'),
    getenv('DB_PORT') ?: ($driver === 'pgsql' ? '5432' : '3306'),
    getenv('DB_DATABASE')
  );
  new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
  $checks['database'] = 'connected';
} catch (Throwable $e) {
  $checks['database'] = 'error';
  $checks['status'] = 'error';
}

if (function_exists('apcu_enabled') && apcu_enabled()) {
  $checks['cache'] = 'available';
} else {
  $checks['cache'] = 'unavailable';
}

echo json_encode($checks, JSON_PRETTY_PRINT);
HEALTH_EOF
log "✅ Health check pronto em /health.php"

# ========== Preparação de logs ==========
mkdir -p /var/log/MyWorkProfile && chown -R www-data:www-data /var/log/MyWorkProfile
log "✅ Diretórios de log preparados"

# ========== Verificações finais ==========
log "Executando verificações finais..."
php -v >/dev/null 2>&1 || { error "PHP não está funcionando"; exit 1; }
php artisan --version >/dev/null 2>&1 || { error "Laravel não está funcionando"; exit 1; }

# Checagem leve de DB sem tinker
if [ -n "${DB_HOST:-}" ] && [ -n "${DB_DATABASE:-}" ]; then
  if ! php -r "try{
    \$driver=getenv('DB_CONNECTION')==='pgsql'?'pgsql':'mysql';
    \$dsn=sprintf('%s:host=%s;port=%s;dbname=%s',\$driver,getenv('DB_HOST'),getenv('DB_PORT')?:((\$driver==='pgsql')?'5432':'3306'),getenv('DB_DATABASE'));
    new PDO(\$dsn,getenv('DB_USERNAME'),getenv('DB_PASSWORD')); exit(0);
  }catch(Throwable \$e){ exit(1);}"; then
    error "Não foi possível conectar ao banco de dados!"
    exit 1
  fi
fi

log "✅ Todas as verificações passaram!"
echo -e "${BLUE}   - PHP Version: $(php -r 'echo PHP_VERSION;')${NC}"
echo -e "${BLUE}   - Laravel Version: $(php artisan --version | awk '{print $3}')${NC}"
echo -e "${BLUE}   - Environment: ${APP_ENV:-production}${NC}"
echo -e "${BLUE}   - Debug Mode: ${APP_DEBUG:-false}${NC}"
echo -e "${BLUE}   - Cache Driver: ${CACHE_STORE:-database}${NC}"
echo -e "${BLUE}   - Session Driver: ${SESSION_DRIVER:-database}${NC}"
echo -e "${BLUE}   - Queue Driver: ${QUEUE_CONNECTION:-database}${NC}"

log "🚀 MyWorkProfile pronto para produção!"
log "🌐 ${APP_URL}"
log "❤️  Health: ${APP_URL%/}/health.php"

# ========== Iniciar processo principal ==========
exec "$@"
