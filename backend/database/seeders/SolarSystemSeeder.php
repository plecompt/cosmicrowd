<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SolarSystem;

class SolarSystemSeeder extends Seeder
{
    public function run(): void
    {
        // // Empty system
        // SolarSystem::create([
        //     'solar_system_name' => 'Center of the Galaxy',
        //     'solar_system_desc' => 'A lonely black_hole in the center of the galaxy',
        //     'solar_system_type' => 'black_hole',
        //     'solar_system_gravity' => 525000.0,
        //     'solar_system_surface_temp' => 0,
        //     'solar_system_diameter' => 972600,
        //     'solar_system_mass' => 99758773490,
        //     'solar_system_luminosity' => 0,
        //     'solar_system_initial_x' => 0,
        //     'solar_system_initial_y' => 0,
        //     'solar_system_initial_z' => 0,
        //     'galaxy_id' => 1,
        //     'user_id' => 2,
        // ]);
    }
}