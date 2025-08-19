<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\User\ProfileCompletionController;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Se não estiver autenticado, deixa o middleware de auth lidar
        if (!$user) {
            return $next($request);
        }

        // Rotas que não precisam de perfil completo
        $exemptRoutes = [
            'validation',
            'validation.store',
            'validation.skip',
            'logout',
            'logout.get',
            'verification.notice',
            'verification.verify',
            'verification.send',
        ];

        // Se a rota atual está na lista de exceções, permite acesso
        if (in_array($request->route()->getName(), $exemptRoutes)) {
            return $next($request);
        }

        // Se está acessando rotas de API, permite (para não quebrar funcionalidades)
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Verifica se o perfil está completo
        if (!ProfileCompletionController::isProfileComplete($user)) {
            // Se for uma requisição AJAX/API, retorna JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Perfil incompleto. Complete seu perfil para continuar.',
                    'redirect' => route('validation')
                ], 403);
            }

            // Redireciona para a página de validação de perfil
            return redirect()->route('validation')
                ->with('message', 'Complete seu perfil para acessar esta funcionalidade.');
        }

        return $next($request);
    }
}