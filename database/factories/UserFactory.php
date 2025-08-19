<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\City;
use App\Models\Country;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Buscar uma cidade brasileira aleatória com coordenadas
        $brazil = Country::where('iso2', 'BR')->first();
        $city = null;
        
        if ($brazil) {
            $city = City::where('country_id', $brazil->id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->inRandomOrder()
                ->first();
        }
        
        // Se não encontrar cidade brasileira, usar dados padrão
        if (!$city) {
            return [
                'name' => fake()->firstName(),
                'sobrenome' => fake()->lastName(),
                'idade' => fake()->numberBetween(18, 80),
                'data_nascimento' => fake()->date('Y-m-d', '-18 years'),
                'email' => fake()->unique()->safeEmail(),
                'email_verified_at' => now(),
                'password' => static::$password ??= Hash::make('password'),
                'remember_token' => Str::random(10),
                'publications_count' => fake()->numberBetween(0, 50),
                'followers_count' => fake()->numberBetween(0, 1000),
                'following_count' => fake()->numberBetween(0, 500),
                'friends_count' => fake()->numberBetween(0, 200),
                'country_id' => null,
                'state_id' => null,
                'city_id' => null,
                'latitude' => fake()->latitude(-90, 90),
                'longitude' => fake()->longitude(-180, 180),
            ];
        }
        
        // Gerar nome brasileiro baseado na região
        $nomesBrasileiros = [
            'Ana', 'Carlos', 'Maria', 'João', 'Fernanda', 'Pedro', 'Juliana', 'Rafael',
            'Camila', 'Lucas', 'Beatriz', 'Gabriel', 'Larissa', 'Thiago', 'Amanda', 'Diego',
            'Priscila', 'Rodrigo', 'Vanessa', 'Bruno', 'Tatiana', 'Marcelo', 'Renata', 'André',
            'Cristina', 'Felipe', 'Patrícia', 'Gustavo', 'Mônica', 'Leandro', 'Simone', 'Fábio'
        ];
        
        $sobrenomesBrasileiros = [
            'Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Alves', 'Pereira',
            'Lima', 'Gomes', 'Costa', 'Ribeiro', 'Martins', 'Carvalho', 'Almeida', 'Lopes',
            'Soares', 'Fernandes', 'Vieira', 'Barbosa', 'Rocha', 'Dias', 'Monteiro', 'Cardoso',
            'Reis', 'Araújo', 'Nascimento', 'Freitas', 'Correia', 'Mendes', 'Castro', 'Pinto'
        ];
        
        // Adicionar pequena variação nas coordenadas para simular usuários próximos
        $latVariation = fake()->randomFloat(4, -0.05, 0.05); // Variação de ~5km
        $lngVariation = fake()->randomFloat(4, -0.05, 0.05);
        
        return [
            'name' => fake()->randomElement($nomesBrasileiros),
            'sobrenome' => fake()->randomElement($sobrenomesBrasileiros),
            'idade' => fake()->numberBetween(18, 80),
            'data_nascimento' => fake()->date('Y-m-d', '-18 years'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'publications_count' => fake()->numberBetween(0, 50),
            'followers_count' => fake()->numberBetween(0, 1000),
            'following_count' => fake()->numberBetween(0, 500),
            'friends_count' => fake()->numberBetween(0, 200),
            'country_id' => $city->country_id,
            'state_id' => $city->state_id,
            'city_id' => $city->id,
            'latitude' => $city->latitude + $latVariation,
            'longitude' => $city->longitude + $lngVariation,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
