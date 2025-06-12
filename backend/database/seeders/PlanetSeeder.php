<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanetSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('planet')->insert([
            [
                'planet_name' => 'Mercure',
                'planet_desc' => 'La plus petite planète du système solaire.',
                'planet_type' => 'terrestrial',
                'planet_gravity' => 3.7,
                'planet_surface_temp' => 167.0,
                'planet_orbital_longitude' => 0.0,
                'planet_eccentricity' => 0.206,
                'planet_apogee' => 69,
                'planet_perigee' => 46,
                'planet_orbital_inclination' => 7.0,
                'planet_average_distance' => 58,
                'planet_orbital_period' => 88,
                'planet_inclination_angle' => 0.034,
                'planet_rotation_period' => 1407,
                'planet_mass' => 330,
                'planet_diameter' => 4879,
                'planet_rings' => 0,
                'planet_initial_x' => 0,
                'planet_initial_y' => 0,
                'planet_initial_z' => 0,
                'solar_system_id' => 1,
                'user_id' => 1,
            ],
            [
                'planet_name' => 'Vénus',
                'planet_desc' => 'La planète la plus chaude du système solaire, avec une rotation rétrograde.',
                'planet_type' => 'terrestrial',
                'planet_gravity' => 8.87,
                'planet_surface_temp' => 462.0,
                'planet_orbital_longitude' => 0.0,
                'planet_eccentricity' => 0.007,
                'planet_apogee' => 109,
                'planet_perigee' => 107,
                'planet_orbital_inclination' => 3.4,
                'planet_average_distance' => 108,
                'planet_orbital_period' => 225,
                'planet_inclination_angle' => 177.4,
                'planet_rotation_period' => 5832,
                'planet_mass' => 4867,
                'planet_diameter' => 12104,
                'planet_rings' => 0,
                'planet_initial_x' => 0,
                'planet_initial_y' => 0,
                'planet_initial_z' => 0,
                'solar_system_id' => 1,
                'user_id' => 1,
            ],
        ]);
    }
}
