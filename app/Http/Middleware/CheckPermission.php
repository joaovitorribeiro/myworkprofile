<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): ResponseAlias
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Não autenticado.',
                'error' => 'Unauthenticated'
            ], 401);
        }

        if (!$request->user()->hasPermissionTo($permission)) {
            return response()->json([
                'message' => 'Você não tem permissão para realizar esta ação.',
                'error' => 'Forbidden',
                'required_permission' => $permission
            ], 403);
        }

        return $next($request);
    }
}