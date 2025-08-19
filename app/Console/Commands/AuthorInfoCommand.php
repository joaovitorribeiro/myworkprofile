<?php

namespace App\Console\Commands;

use App\Helpers\AuthorCrypto;
use Illuminate\Console\Command;

class AuthorInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'author:info {--verify : Verificar integridade das informações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exibir informações do projeto e verificar integridade';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== INFORMAÇÕES DO PROJETO ===');
        $this->newLine();
        
        try {
            $authorInfo = AuthorCrypto::getAuthorData();
            
            if (!$authorInfo) {
                $this->error('❌ Erro: Não foi possível acessar as informações do projeto.');
                return Command::FAILURE;
            }
            
            // Validar estrutura dos dados
            if (!AuthorCrypto::validateAuthorData($authorInfo)) {
                $this->error('❌ Erro: Estrutura dos dados do projeto inválida.');
                return Command::FAILURE;
            }
            
            $this->line('<fg=cyan>Projeto:</> ' . $authorInfo['project_name']);
            $this->line('<fg=cyan>Versão:</> ' . $authorInfo['version']);
            $this->line('<fg=cyan>Criado em:</> ' . $authorInfo['created_at']);
            
            $this->newLine();
            
            // Verificar integridade se solicitado ou sempre
            if ($this->option('verify') || true) {
                if (!AuthorCrypto::verifyIntegrity($authorInfo)) {
                    $this->error('❌ VIOLAÇÃO DE INTEGRIDADE: As configurações do projeto foram alteradas!');
                    return Command::FAILURE;
                } else {
                    $this->info('✅ Integridade verificada: Configurações do projeto estão íntegras.');
                }
            }
            
            $this->newLine();
            $this->warn('⚠️  AVISO: Este projeto possui direitos autorais protegidos.');
            $this->warn('⚠️  Qualquer alteração não autorizada das configurações pode violar os termos de uso.');
            
        } catch (\Exception $e) {
            $this->error('❌ Erro ao acessar dados do projeto: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }

}