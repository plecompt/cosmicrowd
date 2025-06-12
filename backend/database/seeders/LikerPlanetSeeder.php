<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LikerPlanetSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('liker_planet')->insert([
            [
                'user_id' => 1,
                'planet_id' => 1,
                'liker_planet_date' => now(),
            ],
            [
                'user_id' => 2,
                'planet_id' => 1,
                'liker_planet_date' => now(),
            ],
        ]);
    }
}
