<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait HasPermissions
{
    /**
     * Verificar se o usuário autenticado tem uma permissão específica
     */
    protected function checkPermission(Request $request, string $permission): bool
    {
        return $request->user() && $request->user()->hasPermissionTo($permission);
    }

    /**
     * Verificar se o usuário autenticado tem qualquer uma das permissões
     */
    protected function checkAnyPermission(Request $request, array $permissions): bool
    {
        return $request->user() && $request->user()->hasAnyPermission($permissions);
    }

    /**
     * Verificar se o usuário autenticado tem todas as permissões
     */
    protected function checkAllPermissions(Request $request, array $permissions): bool
    {
        return $request->user() && $request->user()->hasAllPermissions($permissions);
    }

    /**
     * Verificar se o usuário autenticado tem um role específico
     */
    protected function checkRole(Request $request, string $role): bool
    {
        return $request->user() && $request->user()->hasRole($role);
    }

    /**
     * Verificar se o usuário autenticado tem qualquer um dos roles
     */
    protected function checkAnyRole(Request $request, array $roles): bool
    {
        return $request->user() && $request->user()->hasAnyRole($roles);
    }

    /**
     * Retornar erro de permissão negada
     */
    protected function permissionDenied(string $message = 'Você não tem permissão para realizar esta ação.'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => 'Forbidden'
        ], 403);
    }

    /**
     * Verificar permissão e retornar erro se não tiver
     */
    protected function requirePermission(Request $request, string $permission, string $message = null): ?JsonResponse
    {
        if (!$this->checkPermission($request, $permission)) {
            return $this->permissionDenied($message ?? "Você precisa da permissão '{$permission}' para realizar esta ação.");
        }
        return null;
    }

    /**
     * Verificar role e retornar erro se não tiver
     */
    protected function requireRole(Request $request, string $role, string $message = null): ?JsonResponse
    {
        if (!$this->checkRole($request, $role)) {
            return $this->permissionDenied($message ?? "Você precisa do role '{$role}' para acessar este recurso.");
        }
        return null;
    }

    /**
     * Verificar se o usuário pode gerenciar outro usuário
     */
    protected function canManageUser(Request $request, \App\Models\User $targetUser): bool
    {
        return $request->user() && $request->user()->canManageUser($targetUser);
    }

    /**
     * Verificar se o usuário é admin
     */
    protected function isAdmin(Request $request): bool
    {
        return $request->user() && $request->user()->isAdmin();
    }

    /**
     * Verificar se o usuário é super admin
     */
    protected function isSuperAdmin(Request $request): bool
    {
        return $request->user() && $request->user()->isSuperAdmin();
    }

    /**
     * Verificar se o usuário é moderador
     */
    protected function isModerator(Request $request): bool
    {
        return $request->user() && $request->user()->isModerator();
    }

    /**
     * Verificar se o usuário é premium
     */
    protected function isPremium(Request $request): bool
    {
        return $request->user() && $request->user()->isPremium();
    }

    /**
     * Verificar se o usuário está verificado
     */
    protected function isVerified(Request $request): bool
    {
        return $request->user() && $request->user()->isVerified();
    }

    /**
     * Obter o nível hierárquico mais alto do usuário
     */
    protected function getUserHighestLevel(Request $request): int
    {
        if (!$request->user()) {
            return 0;
        }

        $highestRole = $request->user()->roles()->orderBy('level', 'desc')->first();
        return $highestRole ? $highestRole->level : 0;
    }

    /**
     * Verificar se o usuário tem nível hierárquico suficiente
     */
    protected function hasMinimumLevel(Request $request, int $minimumLevel): bool
    {
        return $this->getUserHighestLevel($request) >= $minimumLevel;
    }
}