<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): ResponseAlias
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Não autenticado.',
                'error' => 'Unauthenticated'
            ], 401);
        }

        if (!$request->user()->hasAnyRole($roles)) {
            return response()->json([
                'message' => 'Você não tem o role necessário para acessar este recurso.',
                'error' => 'Forbidden',
                'required_roles' => $roles
            ], 403);
        }

        return $next($request);
    }
}