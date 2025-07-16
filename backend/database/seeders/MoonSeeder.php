<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Moon;

class MoonSeeder extends Seeder
{
    public function run(): void
    {
        // Earth's Moon (Planet ID 3 - Earth in Sol System)
        // Moon::create([
        //     'moon_name' => 'Moon',
        //     'moon_desc' => 'Earth only satellite',
        //     'moon_type' => 'rocky',
        //     'moon_gravity' => 1.62,
        //     'moon_surface_temp' => 290,
        //     'moon_orbital_longitude' => 0.0,
        //     'moon_eccentricity' => 0.0549,
        //     'moon_apogee' => 405,
        //     'moon_perigee' => 363,
        //     'moon_orbital_inclination' => 5.145,
        //     'moon_average_distance' => 384,
        //     'moon_orbital_period' => 27.3,
        //     'moon_inclination_angle' => 6.687,
        //     'moon_rotation_period' => 27.3,
        //     'moon_mass' => 73,
        //     'moon_diameter' => 3474,
        //     'moon_rings' => 0,
        //     'moon_initial_x' => 0,
        //     'moon_initial_y' => 0,
        //     'moon_initial_z' => 0,
        //     'planet_id' => 3,
        //     'user_id' => 2,
        // ]);
    }
}