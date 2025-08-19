<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Baixando dados de estados...');
        
        try {
            // URL do arquivo JSON de estados do repositório dr5hn/countries-states-cities-database
            $url = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/json/states.json';
            
            $response = Http::timeout(120)->get($url);
            
            if ($response->successful()) {
                $states = $response->json();
                
                $this->command->info('Inserindo ' . count($states) . ' estados...');
                
                // Cache dos países para melhor performance
                $countries = Country::pluck('id', 'iso2')->toArray();
                
                foreach ($states as $state) {
                    // Busca o país pelo código ISO2
                    $countryId = $countries[$state['country_code']] ?? null;
                    
                    if ($countryId) {
                        State::updateOrCreate(
                            [
                                'name' => $state['name'],
                                'country_id' => $countryId
                            ],
                            [
                                'code' => $state['state_code'] ?? null,
                                'latitude' => $state['latitude'] ?? null,
                                'longitude' => $state['longitude'] ?? null,
                                'active' => true
                            ]
                        );
                    }
                }
                
                $this->command->info('Estados inseridos com sucesso!');
            } else {
                $this->command->error('Erro ao baixar dados de estados: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->command->error('Erro ao processar estados: ' . $e->getMessage());
            Log::error('Erro no StatesSeeder: ' . $e->getMessage());
        }
    }
}
