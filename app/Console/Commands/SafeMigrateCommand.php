<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

class SafeMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:safe 
                            {--fresh : Drop all tables and re-run all migrations}
                            {--seed : Seed the database after migration}
                            {--force : Force the operation to run when in production}
                            {--backup : Create database backup before migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run database migrations with production safety checks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $environment = app()->environment();
        $isFresh = $this->option('fresh');
        $isForced = $this->option('force');
        $shouldBackup = $this->option('backup');
        $shouldSeed = $this->option('seed');

        // Verificar se é ambiente de produção
        if ($environment === 'production' || $environment === 'prod') {
            $this->error('🚨 AMBIENTE DE PRODUÇÃO DETECTADO!');
            $this->line('');
            
            if ($isFresh && !$isForced) {
                $this->error('❌ OPERAÇÃO BLOQUEADA: migrate:fresh em produção!');
                $this->error('Esta operação APAGARÁ TODOS OS DADOS do banco de dados.');
                $this->line('');
                $this->info('Para executar em produção, use:');
                $this->info('php artisan migrate:safe --fresh --force --backup');
                $this->line('');
                $this->warn('⚠️  ATENÇÃO: Sempre faça backup antes de executar!');
                return 1;
            }

            if ($isFresh && $isForced) {
                $this->warn('⚠️  ATENÇÃO: Você está prestes a APAGAR TODOS OS DADOS!');
                $this->line('');
                
                // Verificar se há dados importantes
                $userCount = DB::table('users')->count();
                $postCount = DB::table('posts')->count() ?? 0;
                
                if ($userCount > 0 || $postCount > 0) {
                    $this->error("📊 DADOS ENCONTRADOS:");
                    $this->error("- Usuários: {$userCount}");
                    $this->error("- Posts: {$postCount}");
                    $this->line('');
                }

                // Solicitar confirmação tripla
                if (!$this->confirmTriple()) {
                    $this->info('Operação cancelada.');
                    return 1;
                }

                // Criar backup se solicitado
                if ($shouldBackup) {
                    $this->createBackup();
                }
            }
        }

        // Executar migração
        $this->info('🚀 Executando migração...');
        
        try {
            if ($isFresh) {
                $this->call('migrate:fresh', [
                    '--force' => true
                ]);
            } else {
                $this->call('migrate', [
                    '--force' => true
                ]);
            }

            if ($shouldSeed) {
                $this->info('🌱 Executando seeders...');
                $this->call('db:seed', [
                    '--force' => true
                ]);
            }

            $this->info('✅ Migração concluída com sucesso!');
            
        } catch (\Exception $e) {
            $this->error('❌ Erro durante a migração:');
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Solicitar confirmação tripla para operações perigosas
     */
    private function confirmTriple(): bool
    {
        $this->warn('Esta operação é IRREVERSÍVEL!');
        $this->line('');
        
        // Primeira confirmação
        if (!$this->confirm('Você tem certeza que deseja continuar?')) {
            return false;
        }

        // Segunda confirmação
        $this->warn('ÚLTIMA CHANCE! Todos os dados serão perdidos.');
        if (!$this->confirm('Você REALMENTE tem certeza?')) {
            return false;
        }

        // Terceira confirmação - digite o nome do ambiente
        $environment = app()->environment();
        $typed = $this->ask("Digite '{$environment}' para confirmar:");
        
        if ($typed !== $environment) {
            $this->error('Confirmação incorreta. Operação cancelada.');
            return false;
        }

        return true;
    }

    /**
     * Criar backup do banco de dados
     */
    private function createBackup(): void
    {
        $this->info('💾 Criando backup do banco de dados...');
        
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = storage_path("backups/database_backup_{$timestamp}.sql");
        
        // Criar diretório se não existir
        if (!file_exists(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }

        try {
            // Configurações do banco
            $host = Config::get('database.connections.mysql.host');
            $database = Config::get('database.connections.mysql.database');
            $username = Config::get('database.connections.mysql.username');
            $password = Config::get('database.connections.mysql.password');

            // Comando mysqldump
            $command = sprintf(
                'mysqldump -h%s -u%s -p%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($backupPath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                $this->info("✅ Backup criado: {$backupPath}");
            } else {
                $this->warn("⚠️  Falha ao criar backup automático.");
                $this->warn("Certifique-se de ter um backup manual antes de continuar.");
                
                if (!$this->confirm('Continuar sem backup automático?')) {
                    exit(1);
                }
            }
        } catch (\Exception $e) {
            $this->warn("⚠️  Erro ao criar backup: {$e->getMessage()}");
            
            if (!$this->confirm('Continuar sem backup?')) {
                exit(1);
            }
        }
    }
}