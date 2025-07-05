<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LikePlanetSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('like_planet')->insert([
            [
                'user_id' => 1,
                'planet_id' => 1,
                'like_planet_date' => now(),
            ],
            [
                'user_id' => 2,
                'planet_id' => 1,
                'like_planet_date' => now(),
            ],
        ]);
    }
}
