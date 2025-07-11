<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SolarSystem;

class SolarSystemSeeder extends Seeder
{
    public function run(): void
    {
        // Empty system
        SolarSystem::create([
            'solar_system_name' => 'Center of the Galaxy',
            'solar_system_desc' => 'A lonely black_hole in the center of the galaxy',
            'solar_system_type' => 'black_hole',
            'solar_system_gravity' => 525000.0,
            'solar_system_surface_temp' => 0,
            'solar_system_diameter' => 972600,
            'solar_system_mass' => 99758773490,
            'solar_system_luminosity' => 0,
            'solar_system_initial_x' => 0,
            'solar_system_initial_y' => 0,
            'solar_system_initial_z' => 0,
            'galaxy_id' => 1,
            'user_id' => 2,
        ]);

        // Sol system
        SolarSystem::create([
            'solar_system_name' => 'Sol System',
            'solar_system_desc' => 'Our solar system replica',
            'solar_system_type' => 'yellow_dwarf',
            'solar_system_gravity' => 274.0,
            'solar_system_surface_temp' => 5778000,
            'solar_system_diameter' => 139270000,
            'solar_system_mass' => 198900000,
            'solar_system_luminosity' => 100,
            'solar_system_initial_x' => 0,
            'solar_system_initial_y' => 0,
            'solar_system_initial_z' => 100,
            'galaxy_id' => 1,
            'user_id' => 2,
        ]);

        // Kepler system
        SolarSystem::create([
            'solar_system_name' => 'Kepler System',
            'solar_system_desc' => 'A rich system with many worlds',
            'solar_system_type' => 'red_giant',
            'solar_system_gravity' => 219.0,
            'solar_system_surface_temp' => 4200,
            'solar_system_diameter' => 1254000,
            'solar_system_mass' => 1591200,
            'solar_system_luminosity' => 60,
            'solar_system_initial_x' => -150,
            'solar_system_initial_y' => -100,
            'solar_system_initial_z' => 75,
            'galaxy_id' => 1,
            'user_id' => 2,
        ]);
    }
}