<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;

class DatabaseProtectionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar comandos
        $this->commands([
            \App\Console\Commands\SafeMigrateCommand::class,
            \App\Console\Commands\DatabaseProtectionCommand::class,
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Interceptar comandos perigosos
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            $this->interceptDangerousCommands($event);
        });
    }

    /**
     * Interceptar comandos perigosos em produção
     */
    private function interceptDangerousCommands(CommandStarting $event): void
    {
        $environment = app()->environment();
        $commandName = $event->command;
        
        // Verificar se é ambiente de produção
        if ($environment !== 'production' && $environment !== 'prod') {
            return;
        }

        // Carregar configuração de proteção
        $configPath = config_path('database_protection.php');
        if (!File::exists($configPath)) {
            return;
        }

        $config = include $configPath;
        if (!$config['enabled']) {
            return;
        }

        // Lista de comandos perigosos
        $dangerousCommands = $config['blocked_commands'] ?? [
            'migrate:fresh',
            'migrate:reset', 
            'db:wipe',
            'migrate:rollback'
        ];

        // Verificar se o comando é perigoso
        foreach ($dangerousCommands as $dangerous) {
            if (str_starts_with($commandName, $dangerous)) {
                $this->blockDangerousCommand($commandName, $dangerous);
                exit(1);
            }
        }
    }

    /**
     * Bloquear comando perigoso
     */
    private function blockDangerousCommand(string $commandName, string $dangerousCommand): void
    {
        echo "\n";
        echo "\033[41m\033[97m 🚨 COMANDO BLOQUEADO EM PRODUÇÃO! 🚨 \033[0m\n";
        echo "\n";
        echo "\033[91mComando: {$commandName}\033[0m\n";
        echo "\033[91mMotivo: Comando perigoso '{$dangerousCommand}' detectado\033[0m\n";
        echo "\n";
        echo "\033[93m⚠️  Este comando pode APAGAR TODOS OS DADOS do banco!\033[0m\n";
        echo "\n";
        echo "\033[96mAlternativas seguras:\033[0m\n";
        echo "\033[96m• php artisan migrate:safe --fresh --force --backup\033[0m\n";
        echo "\033[96m• php artisan db:protect --status\033[0m\n";
        echo "\n";
        echo "\033[93mPara emergências, desabilite temporariamente:\033[0m\n";
        echo "\033[93m• php artisan db:protect --disable (apenas em dev!)\033[0m\n";
        echo "\n";
    }
}