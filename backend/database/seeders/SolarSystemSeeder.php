<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolarSystemSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('solar_system')->insert([
            [
                'solar_system_name' => 'Système Solaire',
                'solar_system_desc' => 'Notre système solaire, abritant la Terre et 7 autres planètes.',
                'solar_system_type' => 'yellow_dwarf',
                'solar_system_gravity' => 274.0,
                'solar_system_surface_temp' => 5778.0,
                'solar_system_diameter' => 1392684,
                'solar_system_mass' => 1,
                'solar_system_luminosity' => 1,
                'solar_system_initial_x' => 0,
                'solar_system_initial_y' => 0,
                'solar_system_initial_z' => 0,
                'galaxy_id' => 1,
            ],
            [
                'solar_system_name' => 'Alpha Centauri',
                'solar_system_desc' => 'Le système stellaire le plus proche de notre système solaire.',
                'solar_system_type' => 'binary',
                'solar_system_gravity' => 250.0,
                'solar_system_surface_temp' => 5790.0,
                'solar_system_diameter' => 1392000,
                'solar_system_mass' => 2,
                'solar_system_luminosity' => 1.5,
                'solar_system_initial_x' => 100,
                'solar_system_initial_y' => 100,
                'solar_system_initial_z' => 100,
                'galaxy_id' => 1,
            ],
        ]);
    }
} 