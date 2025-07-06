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
        $perigee = fake()->numberBetween(1000, 50000);
        $apogee = fake()->numberBetween($perigee, 100000);
        $averageDistance = ($perigee + $apogee) / 2;
        
        return [
            'moon_name' => fake()->randomElement(self::$moonNames) . '-' . fake()->randomNumber(2),
            'moon_desc' => fake()->optional()->sentence(6),
            'moon_type' => fake()->randomElement(self::$moonTypes),
            'moon_gravity' => fake()->randomFloat(2, 0.1, 10.0),
            'moon_surface_temp' => fake()->randomFloat(2, 50, 400),
            'moon_orbital_longitude' => fake()->randomFloat(2, 0, 360),
            'moon_eccentricity' => fake()->randomFloat(3, 0, 0.9),
            'moon_apogee' => $apogee,
            'moon_perigee' => $perigee,
            'moon_orbital_inclination' => fake()->numberBetween(0, 360),
            'moon_average_distance' => $averageDistance,
            'moon_orbital_period' => fake()->numberBetween(1, 365),
            'moon_inclination_angle' => fake()->numberBetween(0, 360),
            'moon_rotation_period' => fake()->numberBetween(1, 100),
            'moon_mass' => fake()->numberBetween(1e20, 1e24),
            'moon_diameter' => fake()->numberBetween(100, 5000),
            'moon_rings' => fake()->numberBetween(0, 3),
            'moon_initial_x' => fake()->numberBetween(-1000, 1000),
            'moon_initial_y' => fake()->numberBetween(-1000, 1000),
            'moon_initial_z' => fake()->numberBetween(-100, 100),
            'planet_id' => Planet::factory(),
            'user_id' => User::factory(),
        ];
    }
}
