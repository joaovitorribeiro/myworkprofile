<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Baixando dados de cidades...');
        
        try {
            // URL do arquivo JSON de cidades do repositório dr5hn/countries-states-cities-database
            $url = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/json/cities.json';
            
            $response = Http::timeout(300)->get($url);
            
            if ($response->successful()) {
                $cities = $response->json();
                
                $this->command->info('Inserindo ' . count($cities) . ' cidades...');
                
                // Cache apenas dos países para economizar memória
                $countries = Country::pluck('id', 'iso2')->toArray();
                
                $batchSize = 500; // Reduzindo o tamanho do lote
                $batches = array_chunk($cities, $batchSize);
                
                foreach ($batches as $index => $batch) {
                    $this->command->info('Processando lote ' . ($index + 1) . ' de ' . count($batches));
                    
                    $cityData = [];
                    
                    foreach ($batch as $city) {
                        $countryId = $countries[$city['country_code']] ?? null;
                        
                        // Buscar estado apenas quando necessário para economizar memória
                        $stateId = null;
                        if (isset($city['state_name']) && $city['state_name'] && $countryId) {
                            $state = State::where('name', $city['state_name'])
                                         ->where('country_id', $countryId)
                                         ->first();
                            $stateId = $state?->id;
                        }
                        
                        if ($countryId) {
                            $cityData[] = [
                                'name' => $city['name'],
                                'state_id' => $stateId,
                                'country_id' => $countryId,
                                'latitude' => $city['latitude'] ?? null,
                                'longitude' => $city['longitude'] ?? null,
                                'active' => true,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }
                    }
                    
                    if (!empty($cityData)) {
                        DB::table('cities')->insertOrIgnore($cityData);
                    }
                    
                    // Limpar variáveis para liberar memória
                    unset($cityData);
                    
                    // Forçar garbage collection a cada 10 lotes
                    if (($index + 1) % 10 === 0) {
                        gc_collect_cycles();
                    }
                }
                
                $this->command->info('Cidades inseridas com sucesso!');
            } else {
                $this->command->error('Erro ao baixar dados de cidades: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->command->error('Erro ao processar cidades: ' . $e->getMessage());
            Log::error('Erro no CitiesSeeder: ' . $e->getMessage());
        }
    }
}
