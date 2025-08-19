<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class ManageRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MyWorkProfile:roles 
                            {action : Ação a ser executada (assign, remove, list, create-admin)}
                            {--user= : ID ou email do usuário}
                            {--role= : Slug do role}
                            {--email= : Email do usuário (alternativa ao --user)}
                            {--name= : Nome do role (para criação)}
                            {--level= : Nível do role (para criação)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gerenciar roles e permissões dos usuários';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'assign':
                return $this->assignRole();
            case 'remove':
                return $this->removeRole();
            case 'list':
                return $this->listUserRoles();
            case 'create-admin':
                return $this->createAdmin();
            default:
                $this->error('Ação inválida. Use: assign, remove, list, create-admin');
                return 1;
        }
    }

    private function assignRole()
    {
        $user = $this->getUser();
        if (!$user) return 1;

        $roleSlug = $this->option('role');
        if (!$roleSlug) {
            $this->error('Role é obrigatório. Use --role=slug-do-role');
            return 1;
        }

        $role = Role::where('slug', $roleSlug)->first();
        if (!$role) {
            $this->error("Role '{$roleSlug}' não encontrado.");
            return 1;
        }

        if ($user->hasRole($roleSlug)) {
            $this->warn("Usuário já possui o role '{$role->name}'.");
            return 0;
        }

        $user->assignRole($role);
        $this->info("Role '{$role->name}' atribuído com sucesso ao usuário {$user->email}.");
        return 0;
    }

    private function removeRole()
    {
        $user = $this->getUser();
        if (!$user) return 1;

        $roleSlug = $this->option('role');
        if (!$roleSlug) {
            $this->error('Role é obrigatório. Use --role=slug-do-role');
            return 1;
        }

        $role = Role::where('slug', $roleSlug)->first();
        if (!$role) {
            $this->error("Role '{$roleSlug}' não encontrado.");
            return 1;
        }

        if (!$user->hasRole($roleSlug)) {
            $this->warn("Usuário não possui o role '{$role->name}'.");
            return 0;
        }

        $user->removeRole($role);
        $this->info("Role '{$role->name}' removido com sucesso do usuário {$user->email}.");
        return 0;
    }

    private function listUserRoles()
    {
        $user = $this->getUser();
        if (!$user) return 1;

        $roles = $user->roles;
        
        if ($roles->isEmpty()) {
            $this->info("Usuário {$user->email} não possui roles atribuídos.");
            return 0;
        }

        $this->info("Roles do usuário {$user->email}:");
        $this->table(
            ['ID', 'Nome', 'Slug', 'Nível', 'Ativo'],
            $roles->map(function ($role) {
                return [
                    $role->id,
                    $role->name,
                    $role->slug,
                    $role->level,
                    $role->is_active ? 'Sim' : 'Não'
                ];
            })
        );

        return 0;
    }

    private function createAdmin()
    {
        $email = $this->option('email') ?? $this->option('user');
        if (!$email) {
            $email = $this->ask('Email do usuário que será admin:');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("Usuário com email '{$email}' não encontrado.");
            return 1;
        }

        $adminRole = Role::where('slug', 'admin')->first();
        if (!$adminRole) {
            $this->error('Role admin não encontrado. Execute o seeder primeiro.');
            return 1;
        }

        if ($user->hasRole('admin')) {
            $this->warn("Usuário {$user->email} já é admin.");
            return 0;
        }

        $user->assignRole($adminRole);
        $this->info("Usuário {$user->email} agora é admin!");
        return 0;
    }

    private function getUser(): ?User
    {
        $userIdentifier = $this->option('user') ?? $this->option('email');
        
        if (!$userIdentifier) {
            $this->error('Usuário é obrigatório. Use --user=id ou --email=email');
            return null;
        }

        // Tentar encontrar por ID primeiro
        if (is_numeric($userIdentifier)) {
            $user = User::find($userIdentifier);
            if ($user) return $user;
        }

        // Tentar encontrar por email
        $user = User::where('email', $userIdentifier)->first();
        if (!$user) {
            $this->error("Usuário '{$userIdentifier}' não encontrado.");
            return null;
        }

        return $user;
    }
}