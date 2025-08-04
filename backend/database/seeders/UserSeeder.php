<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'user_login' => 'admin',
            'user_password' => Hash::make('@Admin123456'),
            'user_email' => 'admin@cosmicrowd.com',
            'user_active' => true,
            'user_role' => 'admin',
            'user_date_inscription' => now(),
        ]);

        User::create([
            'user_login' => 'user',
            'user_password' => Hash::make('@User1234567'),
            'user_email' => 'user@cosmicrowd.com',
            'user_active' => true,
            'user_role' => 'member',
            'user_date_inscription' => now(),
        ]);

        User::create([
            'user_login' => 'user2',
            'user_password' => Hash::make('@User1234567'),
            'user_email' => 'user2@cosmicrowd.com',
            'user_active' => true,
            'user_role' => 'member',
            'user_date_inscription' => now(),
        ]);

        User::create([
            'user_login' => 'user3',
            'user_password' => Hash::make('@User1234567'),
            'user_email' => 'user3@cosmicrowd.com',
            'user_active' => true,
            'user_role' => 'member',
            'user_date_inscription' => now(),
        ]);
    }
}
