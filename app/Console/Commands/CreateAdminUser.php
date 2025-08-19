<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MyWorkProfile:create-admin {email} {name} {sobrenome} {idade} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criar um usuário administrador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name');
        $sobrenome = $this->argument('sobrenome');
        $idade = $this->argument('idade');
        $password = $this->argument('password');

        // Verificar se o usuário já existe
        if (User::where('email', $email)->exists()) {
            $this->error("Usuário com email '{$email}' já existe.");
            return 1;
        }

        // Criar o usuário
        $user = User::create([
            'name' => $name,
            'sobrenome' => $sobrenome,
            'email' => $email,
            'idade' => (int) $idade,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->info("Usuário '{$name}' criado com sucesso!");
        $this->info("Email: {$email}");
        $this->info("ID: {$user->id}");

        return 0;
    }
}
