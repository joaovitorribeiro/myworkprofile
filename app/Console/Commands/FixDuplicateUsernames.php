<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FixDuplicateUsernames extends Command
{
    protected $signature = 'users:fix-duplicate-usernames';
    protected $description = 'Fix duplicate usernames by making them unique';

    public function handle()
    {
        $this->info('Fixing duplicate usernames...');

        // Find all duplicate usernames
        $duplicates = User::select('username')
            ->whereNotNull('username')
            ->groupBy('username')
            ->havingRaw('count(*) > 1')
            ->pluck('username');

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate usernames found.');
            return;
        }

        foreach ($duplicates as $username) {
            $this->info("Fixing duplicate username: {$username}");
            
            $users = User::where('username', $username)->get();
            
            // Keep the first user with the original username
            // Update the rest with unique usernames
            foreach ($users as $index => $user) {
                if ($index > 0) {
                    $newUsername = $username . '_' . $user->id;
                    $user->username = $newUsername;
                    $user->save();
                    $this->info("Updated user {$user->id} to username: {$newUsername}");
                }
            }
        }

        $this->info('Duplicate usernames fixed successfully!');
    }
}