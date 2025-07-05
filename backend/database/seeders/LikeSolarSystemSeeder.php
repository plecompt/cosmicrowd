<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LikeSolarSystemSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('like_solar_system')->insert([
            [
                'user_id' => 1,
                'solar_system_id' => 1,
                'like_solar_system_date' => now(),
            ],
            [
                'user_id' => 2,
                'solar_system_id' => 1,
                'like_solar_system_date' => now(),
            ],
        ]);
    }
} 