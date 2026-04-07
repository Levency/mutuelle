<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crée l'admin par défaut
        User::updateOrCreate(
            ['email' => 'admin@mutulle.ht'],
            [
                'name' => 'Administrateur Mutulle',
                'email' => 'admin@mutulle.ht',
                'password' => Hash::make('Admin@2024!'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Paramètres par défaut
        $this->call(SettingsSeeder::class);
    }
}
