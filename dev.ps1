# Script PowerShell para iniciar o ambiente de desenvolvimento local

# Verificar se o .env existe, caso contrário, criar a partir do .env.example
if (-not (Test-Path -Path ".env")) {
    Write-Host "Arquivo .env não encontrado. Criando a partir do .env.example..."
    if (Test-Path -Path ".env.example") {
        Copy-Item -Path ".env.example" -Destination ".env"
        Write-Host "Arquivo .env criado. Por favor, verifique as configurações."
    } else {
        Write-Host "Arquivo .env.example não encontrado. Por favor, crie um arquivo .env manualmente."
        exit 1
    }
}

# Verificar se o Node.js está instalado
try {
    $nodeVersion = node -v
    Write-Host "Node.js encontrado: $nodeVersion"
} catch {
    Write-Host "Node.js não encontrado. Por favor, instale o Node.js para continuar."
    exit 1
}

# Verificar se o PHP está instalado
try {
    $phpVersion = php -v
    if ($phpVersion -match "PHP ([0-9]+\.[0-9]+)") {
        $version = $matches[1]
        Write-Host "PHP encontrado: $version"
        if ([double]$version -lt 8.2) {
            Write-Host "Versão do PHP é menor que 8.2. Este projeto requer PHP 8.2 ou superior."
            exit 1
        }
    }
} catch {
    Write-Host "PHP não encontrado. Por favor, instale o PHP 8.2 ou superior para continuar."
    exit 1
}

# Verificar se o Composer está instalado
try {
    $composerVersion = composer -V
    Write-Host "Composer encontrado: $composerVersion"
} catch {
    Write-Host "Composer não encontrado. Por favor, instale o Composer para continuar."
    exit 1
}

# Instalar dependências se necessário
if (-not (Test-Path -Path "vendor")) {
    Write-Host "Instalando dependências do Composer..."
    composer install
}

if (-not (Test-Path -Path "node_modules")) {
    Write-Host "Instalando dependências do Node.js..."
    npm install
}

# Gerar chave da aplicação se necessário
$envContent = Get-Content -Path ".env" -Raw
if ($envContent -match "APP_KEY=") {
    if ($envContent -match "APP_KEY=base64:[A-Za-z0-9+/=]+") {
        Write-Host "Chave da aplicação já configurada."
    } else {
        Write-Host "Gerando chave da aplicação..."
        php artisan key:generate
    }
} else {
    Write-Host "Gerando chave da aplicação..."
    php artisan key:generate
}

# Iniciar o servidor de desenvolvimento
Write-Host "Iniciando o servidor de desenvolvimento..."
Write-Host "Pressione Ctrl+C para encerrar."
npm run dev