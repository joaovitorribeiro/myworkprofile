#!/bin/bash

# Script para iniciar o ambiente de desenvolvimento local

# Verificar se o .env existe, caso contrário, criar a partir do .env.example
if [ ! -f ".env" ]; then
    echo "Arquivo .env não encontrado. Criando a partir do .env.example..."
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "Arquivo .env criado. Por favor, verifique as configurações."
    else
        echo "Arquivo .env.example não encontrado. Por favor, crie um arquivo .env manualmente."
        exit 1
    fi
fi

# Verificar se o Node.js está instalado
if ! command -v node &> /dev/null; then
    echo "Node.js não encontrado. Por favor, instale o Node.js para continuar."
    exit 1
fi

NODE_VERSION=$(node -v)
echo "Node.js encontrado: $NODE_VERSION"

# Verificar se o PHP está instalado
if ! command -v php &> /dev/null; then
    echo "PHP não encontrado. Por favor, instale o PHP 8.2 ou superior para continuar."
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "PHP encontrado: $PHP_VERSION"

# Verificar versão do PHP
PHP_VERSION_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
PHP_VERSION_MINOR=$(php -r "echo PHP_MINOR_VERSION;")

if [ "$PHP_VERSION_MAJOR" -lt 8 ] || ([ "$PHP_VERSION_MAJOR" -eq 8 ] && [ "$PHP_VERSION_MINOR" -lt 2 ]); then
    echo "Versão do PHP é menor que 8.2. Este projeto requer PHP 8.2 ou superior."
    exit 1
fi

# Verificar se o Composer está instalado
if ! command -v composer &> /dev/null; then
    echo "Composer não encontrado. Por favor, instale o Composer para continuar."
    exit 1
fi

COMPOSER_VERSION=$(composer --version)
echo "Composer encontrado: $COMPOSER_VERSION"

# Instalar dependências se necessário
if [ ! -d "vendor" ]; then
    echo "Instalando dependências do Composer..."
    composer install
fi

if [ ! -d "node_modules" ]; then
    echo "Instalando dependências do Node.js..."
    npm install
fi

# Gerar chave da aplicação se necessário
APP_KEY=$(grep -E "^APP_KEY=" .env | cut -d '=' -f2)
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "Gerando chave da aplicação..."
    php artisan key:generate
else
    echo "Chave da aplicação já configurada."
fi

# Iniciar o servidor de desenvolvimento
echo "Iniciando o servidor de desenvolvimento..."
echo "Pressione Ctrl+C para encerrar."
npm run dev