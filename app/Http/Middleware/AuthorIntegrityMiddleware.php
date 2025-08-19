<?php

namespace App\Http\Middleware;

use App\Helpers\AuthorCrypto;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthorIntegrityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar integridade das configurações de autoria
        if (!$this->verifyAuthorIntegrity()) {
            Log::critical('VIOLAÇÃO DE INTEGRIDADE: Configurações de autoria do projeto foram alteradas', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'timestamp' => now()->toISOString(),
                'project' => 'MyWorkProfile'
            ]);
            
            // Em produção, você pode querer retornar um erro 403 ou redirecionar
            if (app()->environment('production')) {
                abort(403, 'Acesso negado: Integridade do sistema comprometida.');
            }
        }
        
        return $next($request);
    }
    
    /**
     * Verificar integridade das configurações de autoria
     */
    private function verifyAuthorIntegrity(): bool
    {
        try {
            $authorData = AuthorCrypto::getAuthorData();
            
            if (!$authorData) {
                Log::warning('Dados do autor não encontrados ou corrompidos');
                return false;
            }
            
            // Validar estrutura dos dados
            if (!AuthorCrypto::validateAuthorData($authorData)) {
                Log::warning('Estrutura dos dados do autor inválida');
                return false;
            }
            
            // Verificar integridade
            if (!AuthorCrypto::verifyIntegrity($authorData)) {
                Log::critical('Hash de integridade não confere');
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao verificar integridade das configurações de autoria', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
}