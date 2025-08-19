<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Criar permissões
            $permissions = $this->createPermissions();
            
            // Criar roles
            $roles = $this->createRoles();
            
            // Atribuir permissões aos roles
            $this->assignPermissionsToRoles($roles, $permissions);
        });
    }

    private function createPermissions(): array
    {
        $permissionsData = [
            // Usuários
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.ban',
            'users.verify',

            // Conteúdo
            'posts.view',
            'posts.create',
            'posts.edit',
            'posts.delete',
            'posts.moderate',

            // Comentários
            'comments.view',
            'comments.create',
            'comments.edit',
            'comments.delete',
            'comments.moderate',

            // Transmissões
            'lives.view',
            'lives.create',
            'lives.moderate',
            'lives.end',

            // Mensagens
            'messages.send',
            'messages.view',
            'messages.moderate',

            // Administração
            'admin.dashboard',
            'admin.reports',
            'admin.system',

            // Roles e Permissões
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'roles.assign',

            // Premium
            'premium.access',
            'premium.unlimited-upload',
            'premium.advanced-filters',
        ];

        $permissions = [];
        foreach ($permissionsData as $permissionName) {
            $permissions[$permissionName] = Permission::create(['name' => $permissionName]);
        }

        return $permissions;
    }

    private function createRoles(): array
    {
        $rolesData = [
            'super-admin',
            'admin',
            'super-moderator',
            'moderator',
            'verified',
            'premium',
            'user',
            'banned'
        ];

        $roles = [];
        foreach ($rolesData as $roleName) {
            $roles[$roleName] = Role::create(['name' => $roleName]);
        }

        return $roles;
    }

    private function assignPermissionsToRoles(array $roles, array $permissions): void
    {
        // Super Admin - todas as permissões
        $roles['super-admin']->givePermissionTo(array_keys($permissions));

        // Admin - quase todas as permissões (exceto gerenciar sistema)
        $adminPermissions = array_filter(array_keys($permissions), fn($slug) => !in_array($slug, ['admin.system']));
        $roles['admin']->givePermissionTo($adminPermissions);

        // Super Moderador
        $superModeratorPermissions = [
            'users.view', 'users.ban', 'users.verify',
            'posts.view', 'posts.moderate', 'posts.delete',
            'comments.view', 'comments.moderate', 'comments.delete',
            'lives.view', 'lives.moderate', 'lives.end',
            'messages.view', 'messages.moderate',
            'admin.dashboard', 'admin.reports'
        ];
        $roles['super-moderator']->givePermissionTo($superModeratorPermissions);

        // Moderador
        $moderatorPermissions = [
            'users.view',
            'posts.view', 'posts.moderate',
            'comments.view', 'comments.moderate',
            'lives.view', 'lives.moderate',
            'messages.moderate',
            'admin.dashboard'
        ];
        $roles['moderator']->givePermissionTo($moderatorPermissions);

        // Verificado
        $verifiedPermissions = [
            'posts.create', 'posts.view',
            'comments.create', 'comments.view',
            'lives.create', 'lives.view',
            'messages.send', 'messages.view'
        ];
        $roles['verified']->givePermissionTo($verifiedPermissions);

        // Premium
        $premiumPermissions = [
            'posts.create', 'posts.view',
            'comments.create', 'comments.view',
            'lives.create', 'lives.view',
            'messages.send', 'messages.view',
            'premium.access', 'premium.unlimited-upload', 'premium.advanced-filters'
        ];
        $roles['premium']->givePermissionTo($premiumPermissions);

        // Usuário padrão
        $userPermissions = [
            'posts.create', 'posts.view',
            'comments.create', 'comments.view',
            'lives.view',
            'messages.send', 'messages.view'
        ];
        $roles['user']->givePermissionTo($userPermissions);

        // Banido - nenhuma permissão
    }
}