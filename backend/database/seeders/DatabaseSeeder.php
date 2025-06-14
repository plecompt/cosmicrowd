<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            GalaxySeeder::class,
            SolarSystemSeeder::class,
            PlanetSeeder::class,
            MoonSeeder::class,
            LikerSolarSystemSeeder::class,
            LikerPlanetSeeder::class,
            LikerMoonSeeder::class,
            UserSolarSystemOwnershipSeeder::class,
        ]);
    }
}