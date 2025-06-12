<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSolarSystemOwnershipSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_solar_system_ownership')->insert([
            [
                'user_id' => 1,
                'solar_system_id' => 1,
                'ownership_type' => 'claimed',
                'owned_at' => now(),
            ],
        ]);
    }
} 