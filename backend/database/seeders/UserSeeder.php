<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user')->insert([
            [
                'user_login' => 'admin',
                'user_password' => Hash::make('admin1234'),
                'user_email' => 'admin@cosmicrowd.com',
                'user_active' => true,
                'user_role' => 'admin',
                'user_date_inscription' => now(),
            ],
            [
                'user_login' => 'user',
                'user_password' => Hash::make('user1234'),
                'user_email' => 'user@cosmicrowd.com',
                'user_active' => true,
                'user_role' => 'member',
                'user_date_inscription' => now(),
            ],
        ]);
    }
}
