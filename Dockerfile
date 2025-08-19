# Build otimizado para produção com ordem correta
FROM php:8.2-apache

ARG DEBIAN_FRONTEND=noninteractive
ARG BUILD_DATE
ARG VCS_REF
ARG VERSION

LABEL maintainer="MyWorkProfile Team" \
      org.label-schema.build-date=$BUILD_DATE \
      org.label-schema.name="MyWorkProfile" \
      org.label-schema.description="MyWorkProfile " \
      org.label-schema.version=$VERSION \
      org.label-schema.vcs-ref=$VCS_REF \
      org.label-schema.schema-version="1.0"

# Variáveis de ambiente para produção
ENV APP_ENV=production \
    APP_DEBUG=false \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# ---- Sistema + Node + ferramentas úteis ----
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get update && apt-get install -y \
        nodejs \
        git curl wget unzip zip nano htop \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
        libonig-dev libxml2-dev \
        libzip-dev zlib1g-dev \
        default-mysql-client \
        redis-tools \
        libmemcached-dev \
        libicu-dev \
        logrotate \
    && rm -rf /var/lib/apt/lists/* && apt-get clean

# ---- PHP extensions ----
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" gd pdo pdo_mysql mbstring exif pcntl bcmath zip opcache intl

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# ---- OPcache (produção) ----
RUN { \
  echo 'opcache.enable=1'; \
  echo 'opcache.enable_cli=0'; \
  echo 'opcache.memory_consumption=256'; \
  echo 'opcache.interned_strings_buffer=16'; \
  echo 'opcache.max_accelerated_files=20000'; \
  echo 'opcache.max_wasted_percentage=5'; \
  echo 'opcache.use_cwd=1'; \
  echo 'opcache.validate_timestamps=0'; \
  echo 'opcache.revalidate_freq=0'; \
  echo 'opcache.save_comments=0'; \
  echo 'opcache.fast_shutdown=1'; \
  echo 'opcache.enable_file_override=1'; \
  echo 'opcache.optimization_level=0x7FFFBFFF'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# ---- PHP ini (produção) ----
RUN { \
  echo 'memory_limit=512M'; \
  echo 'max_execution_time=300'; \
  echo 'max_input_vars=3000'; \
  echo 'upload_max_filesize=50M'; \
  echo 'post_max_size=50M'; \
  echo 'max_file_uploads=20'; \
  echo 'expose_php=Off'; \
  echo 'display_errors=Off'; \
  echo 'display_startup_errors=Off'; \
  echo 'log_errors=On'; \
  echo 'error_log=/var/log/php_errors.log'; \
  echo 'date.timezone=America/Sao_Paulo'; \
  echo 'session.cookie_httponly=1'; \
  echo 'session.cookie_secure=1'; \
  echo 'session.use_strict_mode=1'; \
  echo 'session.cookie_samesite=Lax'; \
  echo 'realpath_cache_size=4096K'; \
  echo 'realpath_cache_ttl=600'; \
} > /usr/local/etc/php/conf.d/production.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---- Apache ----
RUN a2enmod rewrite headers deflate expires ssl \
 && a2dismod -f autoindex \
 && a2disconf serve-cgi-bin

# Segurança no Apache
RUN { \
  echo 'ServerTokens Prod'; \
  echo 'ServerSignature Off'; \
  echo 'TraceEnable Off'; \
  echo 'ServerName localhost'; \
  echo 'Header always set X-Content-Type-Options nosniff'; \
  echo 'Header always set X-Frame-Options DENY'; \
  echo 'Header always set X-XSS-Protection "1; mode=block"'; \
  echo 'Header always set Referrer-Policy "strict-origin-when-cross-origin"'; \
  echo 'Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"'; \
} > /etc/apache2/conf-available/security.conf \
 && a2enconf security

# Compressão e cache HTTP
RUN { \
  echo '<IfModule mod_deflate.c>'; \
  echo '  AddOutputFilterByType DEFLATE text/plain text/html text/xml text/css text/javascript application/xml application/xhtml+xml application/rss+xml application/javascript application/x-javascript application/json'; \
  echo '</IfModule>'; \
} > /etc/apache2/conf-available/compression.conf \
 && a2enconf compression

RUN { \
  echo '<IfModule mod_expires.c>'; \
  echo '  ExpiresActive On'; \
  echo '  ExpiresByType text/css "access plus 1 year"'; \
  echo '  ExpiresByType application/javascript "access plus 1 year"'; \
  echo '  ExpiresByType application/x-javascript "access plus 1 year"'; \
  echo '  ExpiresByType text/javascript "access plus 1 year"'; \
  echo '  ExpiresByType image/png "access plus 1 year"'; \
  echo '  ExpiresByType image/jpg "access plus 1 year"'; \
  echo '  ExpiresByType image/jpeg "access plus 1 year"'; \
  echo '  ExpiresByType image/gif "access plus 1 year"'; \
  echo '  ExpiresByType image/svg+xml "access plus 1 year"'; \
  echo '  ExpiresByType image/webp "access plus 1 year"'; \
  echo '  ExpiresByType font/woff "access plus 1 year"'; \
  echo '  ExpiresByType font/woff2 "access plus 1 year"'; \
  echo '</IfModule>'; \
} > /etc/apache2/conf-available/expires.conf \
 && a2enconf expires

# ---- App ----
WORKDIR /var/www/html

# Camada de cache para Composer (tolerante ao lock)
COPY composer.json ./ 
COPY composer.lock ./ 
RUN if [ -f composer.lock ]; then \
      composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader --no-interaction --prefer-dist; \
    else \
      echo "composer.lock ausente — instalando sem lock (versões podem variar)"; \
      composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader --no-interaction --prefer-dist; \
    fi && composer clear-cache

# Camada de cache para Node (se existirem)
COPY package*.json ./ 
RUN if [ -f package.json ]; then \
      npm install --no-audit --no-fund && npm cache clean --force && rm -rf /root/.npm; \
    else \
      echo "package.json ausente — pulando npm install"; \
    fi

# Copiar código
COPY . .

# Autoload final
RUN composer dump-autoload --optimize --classmap-authoritative

# Invalidar cache para garantir uso do script atualizado
COPY .docker-cache-bust /tmp/cache-bust

# Build do frontend (se houver TS/Vite no projeto)
RUN if [ -f package.json ]; then \
      node scripts/build-with-type-ignore.cjs && npm cache clean --force && rm -rf /root/.npm; \
    else \
      echo "Sem package.json — pulando build do frontend"; \
    fi

# Estrutura de diretórios
RUN mkdir -p storage/logs \
             storage/framework/cache \
             storage/framework/sessions \
             storage/framework/views \
             storage/app/public \
             bootstrap/cache \
             /var/log/MyWorkProfile

# Permissões
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 && chmod -R 775 /var/www/html/storage \
 && chmod -R 775 /var/www/html/bootstrap/cache \
 && chmod -R 755 /var/log/MyWorkProfile \
 && chown -R www-data:www-data /var/log/MyWorkProfile

# DocumentRoot → /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/../!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# .htaccess (mantido)
RUN { \
  echo '<IfModule mod_rewrite.c>'; \
  echo '  <IfModule mod_negotiation.c>'; \
  echo '    Options -MultiViews -Indexes'; \
  echo '  </IfModule>'; \
  echo '  RewriteEngine On'; \
  echo '  RewriteCond %{HTTP:Authorization} .'; \
  echo '  RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]'; \
  echo '  RewriteCond %{REQUEST_FILENAME} !-d'; \
  echo '  RewriteCond %{REQUEST_URI} (.+)/$'; \
  echo '  RewriteRule ^ %1 [L,R=301]'; \
  echo '  RewriteCond %{REQUEST_FILENAME} !-d'; \
  echo '  RewriteCond %{REQUEST_FILENAME} !-f'; \
  echo '  RewriteRule ^ index.php [L]'; \
  echo '</IfModule>'; \
  echo '<IfModule mod_headers.c>'; \
  echo '  Header always set X-Content-Type-Options nosniff'; \
  echo '  Header always set X-Frame-Options DENY'; \
  echo '  Header always set X-XSS-Protection "1; mode=block"'; \
  echo '  Header always set Referrer-Policy "strict-origin-when-cross-origin"'; \
  echo '  Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"'; \
  echo '</IfModule>'; \
  echo '<Files ~ "\.(env|log|ini)$">'; \
  echo '  Require all denied'; \
  echo '</Files>'; \
} > /var/www/html/public/.htaccess

# Health endpoint
RUN { \
  echo '<?php'; \
  echo 'header("Content-Type: application/json");'; \
  echo '$checks=["status"=>"ok","timestamp"=>date("c"),"version"=>"1.0.0","environment"=>($_ENV["APP_ENV"]??"unknown")];'; \
  echo 'try {'; \
  echo '  if (!empty($_ENV["DB_HOST"])) {'; \
  echo '    $pdo=new PDO("mysql:host=".$_ENV["DB_HOST"].";port=".($_ENV["DB_PORT"]??"3306").";dbname=".$_ENV["DB_DATABASE"], $_ENV["DB_USERNAME"], $_ENV["DB_PASSWORD"], [PDO::ATTR_TIMEOUT=>5]);'; \
  echo '    $checks["database"]="connected";'; \
  echo '  }'; \
  echo '} catch (Exception $e) {'; \
  echo '  $checks["database"]="error"; $checks["status"]="error";'; \
  echo '}'; \
  echo '$free=disk_free_space("/var/www/html"); $tot=disk_total_space("/var/www/html");'; \
  echo '$checks["disk_usage"]= $tot ? round((($tot-$free)/$tot)*100,2)."%" : "n/a";'; \
  echo '$mem=memory_get_usage(true); $checks["memory_usage"]=round($mem/1024/1024,2)."MB";'; \
  echo 'http_response_code($checks["status"]==="ok"?200:503);'; \
  echo 'echo json_encode($checks, JSON_PRETTY_PRINT);'; \
} > /var/www/html/public/health.php

# logrotate (sem cron - será gerenciado externamente)
RUN { \
  echo '/var/log/MyWorkProfile/*.log {'; \
  echo '  daily'; \
  echo '  missingok'; \
  echo '  rotate 30'; \
  echo '  compress'; \
  echo '  delaycompress'; \
  echo '  notifempty'; \
  echo '  create 644 www-data www-data'; \
  echo '}'; \
} > /etc/logrotate.d/MyWorkProfile

# Expor porta interna (Traefik usará esta)
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=10s --start-period=90s --retries=3 \
  CMD curl -fsS http://localhost/health.php || exit 1

# Entrypoint (usa o arquivo da raiz do repo)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh && sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
