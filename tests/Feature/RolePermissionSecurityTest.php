<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolePermissionSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_cannot_access_admin_routes_without_permission()
    {
        // Criar permissões necessárias
        Permission::create(['name' => 'roles.view']);
        
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get('/admin/roles');
            
        // Deve retornar 403 (Forbidden) pois não tem permissão
        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function authenticated_user_with_permission_can_access_admin_routes()
    {
        // Criar permissões necessárias
        Permission::create(['name' => 'roles.view']);
        
        $user = User::factory()->create();
        $user->givePermissionTo('roles.view');
        
        $response = $this->actingAs($user)
            ->get('/admin/roles');
            
        // Deve retornar 200 (OK) pois tem permissão
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function unauthenticated_user_cannot_access_admin_routes()
    {
        $response = $this->get('/admin/roles');
        
        // Deve redirecionar para login
        $this->assertEquals(302, $response->getStatusCode());
    }

    /** @test */
    public function spatie_permission_integration_works()
    {
        // Criar role e permissão
        $role = Role::create(['name' => 'admin']);
        $permission = Permission::create(['name' => 'users.manage']);
        
        // Atribuir permissão à role
        $role->givePermissionTo($permission);
        
        // Criar usuário e atribuir role
        $user = User::factory()->create();
        $user->assignRole($role);
        
        // Verificar se o usuário tem a permissão
        $this->assertTrue($user->hasPermissionTo('users.manage'));
        $this->assertTrue($user->hasRole('admin'));
    }

    /** @test */
    public function user_can_have_multiple_roles_and_permissions()
    {
        // Criar roles e permissões
        $adminRole = Role::create(['name' => 'admin']);
        $moderatorRole = Role::create(['name' => 'moderator']);
        
        $manageUsersPermission = Permission::create(['name' => 'users.manage']);
        $moderatePostsPermission = Permission::create(['name' => 'posts.moderate']);
        
        // Atribuir permissões às roles
        $adminRole->givePermissionTo($manageUsersPermission);
        $moderatorRole->givePermissionTo($moderatePostsPermission);
        
        // Criar usuário e atribuir múltiplas roles
        $user = User::factory()->create();
        $user->assignRole([$adminRole, $moderatorRole]);
        
        // Verificar se o usuário tem todas as permissões
        $this->assertTrue($user->hasPermissionTo('users.manage'));
        $this->assertTrue($user->hasPermissionTo('posts.moderate'));
        $this->assertTrue($user->hasRole(['admin', 'moderator']));
    }

    /** @test */
    public function middleware_permission_check_works()
    {
        // Criar permissão necessária
        Permission::create(['name' => 'roles.manage']);
        
        // Usuário sem permissão
        $userWithoutPermission = User::factory()->create();
        
        $response = $this->actingAs($userWithoutPermission)
            ->post('/admin/assign-role', [
                'user_id' => 1,
                'role' => 'admin'
            ]);
            
        $this->assertEquals(403, $response->getStatusCode());
        
        // Usuário com permissão
        $userWithPermission = User::factory()->create();
        $userWithPermission->givePermissionTo('roles.manage');
        
        $response = $this->actingAs($userWithPermission)
            ->post('/admin/assign-role', [
                'user_id' => 1,
                'role' => 'admin'
            ]);
            
        // Deve passar do middleware (pode falhar na lógica do controller, mas não no middleware)
        $this->assertNotEquals(403, $response->getStatusCode());
    }
}