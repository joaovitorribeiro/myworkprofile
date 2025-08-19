<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Post::class => PostPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gates personalizados para verificações adicionais
        Gate::define('admin-access', function (User $user) {
            return $user->hasAnyRole(['admin', 'super-admin']);
        });

        Gate::define('moderator-access', function (User $user) {
            return $user->hasAnyRole(['admin', 'super-admin', 'moderador', 'super-moderador']);
        });

        Gate::define('premium-access', function (User $user) {
            return $user->hasRole('premium') || $user->hasAnyRole(['admin', 'super-admin']);
        });

        Gate::define('verified-access', function (User $user) {
            return $user->hasRole('verificado') || $user->hasAnyRole(['admin', 'super-admin', 'premium']);
        });

        // Gate para prevenir auto-elevação
        Gate::define('can-manage-higher-roles', function (User $user, User $targetUser) {
            $userLevel = $this->getUserLevel($user);
            $targetLevel = $this->getUserLevel($targetUser);
            
            return $userLevel > $targetLevel;
        });

        // Gate para verificar se pode atribuir um role específico
        Gate::define('can-assign-role', function (User $user, string $roleName) {
            $userLevel = $this->getUserLevel($user);
            $roleLevel = $this->getRoleLevel($roleName);
            
            return $userLevel > $roleLevel;
        });
    }

    /**
     * Obter o nível hierárquico do usuário
     */
    private function getUserLevel(User $user): int
    {
        $highestRole = $user->roles()->orderBy('level', 'desc')->first();
        return $highestRole ? $highestRole->level : 0;
    }

    /**
     * Obter o nível hierárquico de um role pelo nome
     */
    private function getRoleLevel(string $roleName): int
    {
        $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
        return $role ? $role->level ?? 0 : 0;
    }
}