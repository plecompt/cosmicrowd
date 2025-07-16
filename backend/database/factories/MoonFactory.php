<?php

namespace Database\Factories;

use App\Models\Planet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MoonFactory extends Factory
{
    protected static $moonTypes = [
        'rocky', 'icy', 'mixed', 'primitive', 'regular', 'irregular', 'trojan', 'coorbital'
    ];

    protected static $moonNames = [
        'Luna', 'Selene', 'Diana', 'Artemis', 'Cynthia', 'Phoebe', 'Titania',
        'Oberon', 'Europa', 'Ganymede', 'Io', 'Callisto', 'Enceladus', 'Titan'
    ];

    public function definition()
    {
        //perigee must be greater than apogee
        $perigee = fake()->numberBetween(100000, 80000000);
        $apogee = fake()->numberBetween($perigee, 100000000);
        $averageDistance = ($perigee + $apogee) / 2;
        
        return [
            'moon_name' => fake()->randomElement(self::$moonNames) . '-' . fake()->randomNumber(2),
            'moon_desc' => fake()->sentence(6),
            'moon_type' => fake()->randomElement(self::$moonTypes),
            'moon_gravity' => fake()->randomFloat(2, 0.1, 3.7), // m.s²
            'moon_surface_temp' => fake()->randomFloat(2, 0, 400), // kelvin
            'moon_orbital_longitude' => fake()->randomFloat(2, 0, 360), // degrees
            'moon_eccentricity' => fake()->randomFloat(3, 0, 0.9), // 0 - 1 (0 perfect circle, 0.99 very eleptic)
            'moon_apogee' => $apogee, // meters
            'moon_perigee' => $perigee, // meters
            'moon_orbital_inclination' => fake()->numberBetween(0, 360), // degrees
            'moon_average_distance' => $averageDistance, // meters
            'moon_orbital_period' => fake()->numberBetween(1, 1000), // days
            'moon_inclination_angle' => fake()->numberBetween(0, 360), // degrees
            'moon_rotation_period' => fake()->numberBetween(1, 100), // days
            'moon_mass' => fake()->numberBetween(10, 500), // x 10^24kg
            'moon_diameter' => fake()->numberBetween(100000, 5000000), // meters
            'moon_rings' => fake()->numberBetween(0, 1),
            'moon_initial_x' => fake()->numberBetween(-100, 100), // unit in three.js
            'moon_initial_y' => fake()->numberBetween(-100, 100), // unit in three.js
            'moon_initial_z' => fake()->numberBetween(-100, 100), // unit in three.js
            'planet_id' => Planet::factory(),
            'user_id' => null,
        ];
    }
}
