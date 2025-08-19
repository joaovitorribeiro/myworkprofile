<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\User\DiscoveryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TestDiscoveryCommand extends Command
{
    protected $signature = 'test:discovery';
    protected $description = 'Testa a API de descoberta e verifica os logs';

    public function handle()
    {
        $this->info('Testando API de descoberta...');
        
        // Pegar o primeiro usuário
        $user = User::first();
        if (!$user) {
            $this->error('Nenhum usuário encontrado no banco de dados.');
            return 1;
        }

        $this->info("Testando com usuário: {$user->email} (ID: {$user->id})");
        
        // Simular autenticação
        Auth::login($user);
        
        // Criar uma requisição simulada
        $request = Request::create('/api/discovery/nearby', 'GET', [
            'page' => 1,
            'per_page' => 10,
            'filter_gender' => 'all',
            'filter_location_type' => 'city'
        ]);
        
        // Definir o usuário autenticado na requisição
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        // Instanciar o controller
        $controller = new DiscoveryController();
        
        try {
            // Primeiro, verificar quantos usuários existem no total
            $totalUsers = \App\Models\User::count();
            $this->info("Total de usuários no banco: {$totalUsers}");
            
            // Verificar usuários por gênero
            $maleUsers = \App\Models\User::where('gender', 'male')->count();
            $femaleUsers = \App\Models\User::where('gender', 'female')->count();
            $this->info("Usuários masculinos: {$maleUsers}, femininos: {$femaleUsers}");
            
            // Testar sem filtros
            $this->info('\n=== Teste 1: Sem filtros ===');
            $request = new \Illuminate\Http\Request();
            $request->merge(['user_id' => $user->id]);
            
            $response = $controller->getNearbyUsers($request);
            $responseData = $response->getData(true);
            $this->info('Usuários sugeridos: ' . count($responseData['suggested_users'] ?? []));
            $this->info('Usuários próximos: ' . count($responseData['nearby_users'] ?? []));
            
            // Testar com filtro de gênero
            $this->info('\n=== Teste 2: Filtro de gênero (female) ===');
            $request = new \Illuminate\Http\Request();
            $request->merge([
                'user_id' => $user->id,
                'filter_gender' => 'female',
                'filter_location_type' => 'all',
                'age_min' => 18,
                'age_max' => 50
            ]);
            
            $response = $controller->getNearbyUsers($request);
            $responseData = $response->getData(true);
            $this->info('Usuários sugeridos (filtro female): ' . count($responseData['suggested_users'] ?? []));
            
            // Testar com filtro de localização
            $this->info('\n=== Teste 3: Filtro de localização (city) ===');
            $request = new \Illuminate\Http\Request();
            $request->merge([
                'user_id' => $user->id,
                'filter_gender' => 'all',
                'filter_location_type' => 'city',
                'age_min' => 18,
                'age_max' => 50
            ]);
            
            $response = $controller->getNearbyUsers($request);
            $responseData = $response->getData(true);
            $this->info('Usuários sugeridos (filtro city): ' . count($responseData['suggested_users'] ?? []));
            
            // Testar com filtros 'all' para ambos
            $this->info('\n=== Teste 4: Filtros "Todos" (all) ===');
            $request = new \Illuminate\Http\Request();
            $request->merge([
                'user_id' => $user->id,
                'filter_gender' => 'all',
                'filter_location_type' => 'all',
                'per_page' => 50, // Aumentar limite para ver todos os usuários
                'age_min' => 18,
                'age_max' => 50
            ]);
            
            $response = $controller->getNearbyUsers($request);
            $responseData = $response->getData(true);
            $this->info('Usuários sugeridos (filtro ALL): ' . count($responseData['suggested_users'] ?? []));
            $this->info('Usuários próximos (filtro ALL): ' . count($responseData['nearby_users'] ?? []));
            
            // Verificar usuários ativos vs inativos
            $activeUsers = \App\Models\User::where('is_active', true)->count();
            $inactiveUsers = \App\Models\User::where('is_active', false)->count();
            $this->info("Usuários ativos: {$activeUsers}, inativos: {$inactiveUsers}");
            
            Log::info('TESTE: API de descoberta executada com sucesso', [
                'user_id' => $user->id,
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            $this->error('Erro durante o teste: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}