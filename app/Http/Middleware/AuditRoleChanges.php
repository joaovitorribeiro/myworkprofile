<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AuditRoleChanges
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log apenas para rotas de administração de roles/permissões
        if ($request->is('admin/*') && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->logRolePermissionChange($request, $response);
        }

        return $response;
    }

    /**
     * Log mudanças de roles e permissões
     */
    private function logRolePermissionChange(Request $request, $response)
    {
        $user = Auth::user();
        $action = $this->getActionFromRoute($request);
        
        if (!$action) {
            return;
        }

        $logData = [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'action' => $action,
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $this->sanitizeRequestData($request->all()),
            'response_status' => $response->getStatusCode(),
            'timestamp' => now()->toISOString(),
        ];

        // Log com nível apropriado baseado no status da resposta
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            Log::info('Role/Permission Change', $logData);
        } else {
            Log::warning('Failed Role/Permission Change Attempt', $logData);
        }
    }

    /**
     * Determina a ação baseada na rota
     */
    private function getActionFromRoute(Request $request): ?string
    {
        $routeName = $request->route()?->getName();
        
        return match($routeName) {
            'admin.assign-role' => 'assign_role',
            'admin.remove-role' => 'remove_role',
            'admin.assign-permission' => 'assign_permission',
            'admin.remove-permission' => 'remove_permission',
            default => null,
        };
    }

    /**
     * Remove dados sensíveis do log
     */
    private function sanitizeRequestData(array $data): array
    {
        // Remove campos sensíveis que não devem ser logados
        $sensitiveFields = ['password', 'password_confirmation', '_token', 'api_token'];
        
        foreach ($sensitiveFields as $field) {
            unset($data[$field]);
        }
        
        return $data;
    }
}