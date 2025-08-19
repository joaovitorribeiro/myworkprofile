<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Baixando dados de países...');
        
        try {
            // URL do arquivo JSON de países do repositório dr5hn/countries-states-cities-database
            $url = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/json/countries.json';
            
            $response = Http::timeout(60)->get($url);
            
            if ($response->successful()) {
                $countries = $response->json();
                
                $this->command->info('Inserindo ' . count($countries) . ' países...');
                
                foreach ($countries as $country) {
                    Country::updateOrCreate(
                        ['iso2' => $country['iso2']],
                        [
                            'name' => $country['name'],
                            'code' => $country['iso3'] ?? null,
                            'iso2' => $country['iso2'],
                            'phone_code' => $country['phone_code'] ?? null,
                            'capital' => $country['capital'] ?? null,
                            'currency' => $country['currency'] ?? null,
                            'currency_symbol' => $country['currency_symbol'] ?? null,
                            'region' => $country['region'] ?? null,
                            'subregion' => $country['subregion'] ?? null,
                            'latitude' => $country['latitude'] ?? null,
                            'longitude' => $country['longitude'] ?? null,
                            'active' => true
                        ]
                    );
                }
                
                $this->command->info('Países inseridos com sucesso!');
            } else {
                $this->command->error('Erro ao baixar dados de países: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->command->error('Erro ao processar países: ' . $e->getMessage());
            Log::error('Erro no CountriesSeeder: ' . $e->getMessage());
        }
    }
}
