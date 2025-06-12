<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin principal
        User::create([
            'user_login' => 'pierre_dev',
            'user_email' => 'pierre@cosmicrowd.com',
            'user_password' => Hash::make('password123'),
            'user_active' => true,
            'user_role' => 'admin',
            'user_date_inscription' => now(),
        ]);

        // Second admin
        User::create([
            'user_login' => 'admin_cosmic',
            'user_email' => 'admin@cosmicrowd.com',
            'user_password' => Hash::make('admin123'),
            'user_active' => true,
            'user_role' => 'admin',
            'user_date_inscription' => now(),
        ]);

        // Membres normaux
        for ($i = 1; $i <= 15; $i++) {
            User::create([
                'user_login' => 'explorer_' . $i,
                'user_email' => "explorer{$i}@cosmicrowd.com",
                'user_password' => Hash::make('password123'),
                'user_active' => true,
                'user_role' => 'member',
                'user_date_inscription' => now()->subDays(rand(1, 30)),
                'user_last_login' => rand(0, 1) ? now()->subDays(rand(0, 7)) : null,
            ]);
        }

        $this->command->info('17 utilisateurs créés (2 admins + 15 membres) !');
    }
}
