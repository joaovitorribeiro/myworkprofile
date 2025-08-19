# Deploy no Coolify - MyWorkProfile

## Configurações Necessárias no Coolify

### 1. Variáveis de Ambiente Configuradas

✅ **Suas variáveis estão corretas! Use exatamente como fornecido:**

```bash
# Aplicação
APP_NAME=MyWorkProfile
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:WQvEGMiEGbzhx5a9aPAsCrB3b73TtCgQ++HYiV6P01o=
APP_URL=https://MyWorkProfile.ddns.net
APP_LOCALE=pt
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=pt_BR

# Segurança / Proxy
TRUSTED_PROXIES=*
FORCE_HTTPS=true
BCRYPT_ROUNDS=12

# Sessão
SESSION_DRIVER=database
SESSION_CONNECTION=default
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=.MyWorkProfile.ddns.net
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Sanctum
SANCTUM_STATEFUL_DOMAINS=MyWorkProfile.ddns.net

# Database
DB_CONNECTION=mysql
DB_HOST=asoogwcskcoog8gwc0kokg8k
DB_PORT=3306
DB_DATABASE=default
DB_USERNAME=mysql
DB_PASSWORD=ix7MzOWtSSQfusBi0qwyYeEkXwQl6NpsXCKjgHOL3LQUIpLpcpCw5yGtUGjpJO86
MYSQL_ROOT_PASSWORD=Ggvg5xGr6ZdlTuX0TOQFuM0HAnC7zZ8AMdB2lQOFXkL9ZkYRHFDaDBqxlDtPpw7y

# Logs
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=debug
LOG_DEPRECATIONS_CHANNEL=null

# Mail
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@MyWorkProfile.ddns.net
MAIL_FROM_NAME=MyWorkProfile

# Outros
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

# Frontend
VITE_APP_NAME=MyWorkProfile
VITE_APP_URL=https://MyWorkProfile.ddns.net

# Deploy flags
RUN_MIGRATIONS=true
CLEAR_CACHE=true
RUN_QUEUE_WORKER=false
```

### 2. Configurações de Build

- **Build Context**: `.`
- **Dockerfile**: `Dockerfile`
- **Port**: `80` (interno)

### 3. Health Check

O container já possui health check configurado em `/health.php`
- Interval: 30s
- Timeout: 10s
- Start Period: 90s
- Retries: 3

### 4. Volumes Persistentes

Configure os seguintes volumes no Coolify:
- `storage`: `/var/www/html/storage`
- `logs`: `/var/log/MyWorkProfile`

### 5. Recursos Recomendados

- **Memory Limit**: 1GB
- **Memory Reservation**: 512MB
- **CPU**: 0.5 cores mínimo

### 6. Configurações de Segurança

O container já inclui:
- `no-new-privileges:true`
- Headers de segurança configurados
- PHP configurado para produção
- Apache com configurações de segurança

### 7. Scheduler (Opcional)

Se precisar do Laravel Scheduler, configure um job separado no Coolify:
```bash
# Comando: php artisan schedule:run
# Frequência: * * * * * (a cada minuto)
```

### 8. Queue Worker (Opcional)

Para processar filas, configure um serviço separado:
```bash
# Comando: php artisan queue:work --daemon --tries=3 --timeout=60
```

### 9. Logs

Os logs são direcionados para:
- Laravel: `/var/www/html/storage/logs/`
- PHP: `/var/log/php_errors.log`
- Apache: `/var/log/apache2/`
- Aplicação: `/var/log/MyWorkProfile/`

### 10. Troubleshooting

#### Container não inicia
- Verifique se `APP_KEY` está definida
- Verifique se `DB_HOST` e `DB_PASSWORD` estão corretos
- Verifique os logs do container

#### Problemas de conexão com banco
- Aguarde até 2 minutos para conexão inicial
- Verifique se o MySQL está acessível
- Verifique as credenciais

#### Performance
- Monitore uso de memória (limite: 1GB)
- OPcache está habilitado para melhor performance
- Cache de configuração é aplicado automaticamente

## Comandos Úteis

```bash
# Ver logs do container
docker logs MyWorkProfile_app

# Executar comandos Laravel
docker exec MyWorkProfile_app php artisan migrate
docker exec MyWorkProfile_app php artisan cache:clear

# Verificar health
curl https://seudominio.com/health.php
```