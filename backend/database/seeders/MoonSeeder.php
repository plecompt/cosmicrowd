<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoonSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('moon')->insert([
            [
                'moon_name' => 'Lune',
                'moon_desc' => 'Le seul satellite naturel de la Terre.',
                'moon_type' => 'rocky',
                'moon_gravity' => 1.62,
                'moon_surface_temp' => 20,
                'moon_orbital_longitude' => 0.0,
                'moon_eccentricity' => 0.0549,
                'moon_apogee' => 405,
                'moon_perigee' => 363,
                'moon_orbital_inclination' => 5.145,
                'moon_average_distance' => 384,
                'moon_orbital_period' => 27.3,
                'moon_inclination_angle' => 6.687,
                'moon_rotation_period' => 27.3,
                'moon_mass' => 73,
                'moon_diameter' => 3474,
                'moon_rings' => 0,
                'moon_initial_x' => 0,
                'moon_initial_y' => 0,
                'moon_initial_z' => 0,
                'planet_id' => 1,
                'user_id' => 1,
            ],
        ]);
    }
}
