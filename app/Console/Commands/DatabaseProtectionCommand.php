<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class DatabaseProtectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:protect 
                            {--enable : Enable database protection}
                            {--disable : Disable database protection}
                            {--status : Show protection status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage database protection settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $enable = $this->option('enable');
        $disable = $this->option('disable');
        $status = $this->option('status');

        if ($enable) {
            $this->enableProtection();
        } elseif ($disable) {
            $this->disableProtection();
        } elseif ($status) {
            $this->showStatus();
        } else {
            $this->showHelp();
        }

        return 0;
    }

    /**
     * Habilitar proteção do banco de dados
     */
    private function enableProtection(): void
    {
        $configPath = config_path('database_protection.php');
        
        $config = [
            'enabled' => true,
            'production_protection' => true,
            'blocked_commands' => [
                'migrate:fresh',
                'migrate:reset',
                'db:wipe',
                'migrate:rollback'
            ],
            'require_confirmation' => true,
            'auto_backup' => true,
            'backup_retention_days' => 30
        ];

        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        
        File::put($configPath, $content);
        
        $this->info('🛡️  Proteção do banco de dados HABILITADA');
        $this->info('📁 Configuração salva em: ' . $configPath);
        
        // Criar diretório de backups
        $backupDir = storage_path('backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
            $this->info('📂 Diretório de backups criado: ' . $backupDir);
        }
    }

    /**
     * Desabilitar proteção do banco de dados
     */
    private function disableProtection(): void
    {
        $environment = app()->environment();
        
        if ($environment === 'production' || $environment === 'prod') {
            $this->error('❌ ERRO: Não é possível desabilitar proteção em PRODUÇÃO!');
            $this->error('Esta é uma medida de segurança obrigatória.');
            return;
        }

        $configPath = config_path('database_protection.php');
        
        if (File::exists($configPath)) {
            $config = include $configPath;
            $config['enabled'] = false;
            
            $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
            File::put($configPath, $content);
        }
        
        $this->warn('⚠️  Proteção do banco de dados DESABILITADA');
        $this->warn('Use apenas em ambiente de desenvolvimento!');
    }

    /**
     * Mostrar status da proteção
     */
    private function showStatus(): void
    {
        $environment = app()->environment();
        $configPath = config_path('database_protection.php');
        
        $this->info('📊 STATUS DA PROTEÇÃO DO BANCO DE DADOS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        $this->info("🌍 Ambiente: {$environment}");
        
        if (File::exists($configPath)) {
            $config = include $configPath;
            
            $status = $config['enabled'] ? '🟢 HABILITADA' : '🔴 DESABILITADA';
            $this->line("🛡️  Proteção: {$status}");
            
            if ($config['enabled']) {
                $this->line('📋 Comandos bloqueados:');
                foreach ($config['blocked_commands'] as $command) {
                    $this->line("   • {$command}");
                }
                
                $backup = $config['auto_backup'] ? '🟢 SIM' : '🔴 NÃO';
                $this->line("💾 Backup automático: {$backup}");
                
                if ($config['auto_backup']) {
                    $this->line("📅 Retenção: {$config['backup_retention_days']} dias");
                }
            }
        } else {
            $this->warn('⚠️  Arquivo de configuração não encontrado');
            $this->info('Execute: php artisan db:protect --enable');
        }
        
        // Verificar diretório de backups
        $backupDir = storage_path('backups');
        if (File::exists($backupDir)) {
            $backups = File::files($backupDir);
            $this->line("📂 Backups disponíveis: " . count($backups));
        } else {
            $this->warn('📂 Diretório de backups não existe');
        }
    }

    /**
     * Mostrar ajuda
     */
    private function showHelp(): void
    {
        $this->info('🛡️  PROTEÇÃO DO BANCO DE DADOS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');
        $this->info('Comandos disponíveis:');
        $this->line('  --enable   Habilitar proteção');
        $this->line('  --disable  Desabilitar proteção (apenas dev)');
        $this->line('  --status   Mostrar status atual');
        $this->line('');
        $this->info('Uso recomendado:');
        $this->line('  php artisan db:protect --enable');
        $this->line('  php artisan migrate:safe --fresh --backup');
        $this->line('');
        $this->warn('⚠️  Em produção, sempre use migrate:safe ao invés de migrate:fresh');
    }
}