<?php

namespace Database\Factories;

use App\Models\SolarSystem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanetFactory extends Factory
{
    protected static $planetTypes = [
        'terrestrial', 'gas', 'ice', 'super_earth', 'sub_neptune', 'dwarf', 'lava', 'carbon', 'ocean'
    ];

    protected static $planetNames = [
        'Terra', 'Aqua', 'Ignis', 'Ventus', 'Glacies', 'Petra', 'Silva', 'Umbra',
        'Lux', 'Ferrum', 'Aurum', 'Crystalline', 'Nebula', 'Cosmos', 'Stella'
    ];

    public function definition()
    {
        //apogee must be greater than perigee and distance must be the average
        $perigee = fake()->numberBetween(50000000, 8000000000); // 50 million to 8 billion meters
        $apogee = fake()->numberBetween($perigee, 15000000000); // up to 15 billion meters
        $averageDistance = ($perigee + $apogee) / 2;
        
        return [
            'planet_name' => fake()->randomElement(self::$planetNames) . '-' . fake()->randomLetter() . fake()->randomNumber(2),
            'planet_desc' => fake()->sentence(8),
            'planet_type' => fake()->randomElement(self::$planetTypes),
            'planet_gravity' => fake()->randomFloat(2, 0.5, 25.0), // m.s²
            'planet_surface_temp' => fake()->randomFloat(2, 0, 1000), // kelvin
            'planet_orbital_longitude' => fake()->randomFloat(2, 0, 360), // degrees
            'planet_eccentricity' => fake()->randomFloat(3, 0, 0.9), // 0 - 1 (0 => perfect circle, 0.99 very eliptic)
            'planet_apogee' => $apogee, // meters
            'planet_perigee' => $perigee, // meters 
            'planet_orbital_inclination' => fake()->numberBetween(0, 360), // degress
            'planet_average_distance' => $averageDistance, // meters
            'planet_orbital_period' => fake()->numberBetween(1, 365000), // days
            'planet_inclination_angle' => fake()->numberBetween(0, 360), // degrees
            'planet_rotation_period' => fake()->numberBetween(1, 24000), // days
            'planet_mass' => fake()->numberBetween(1, 100000), // x 10^24 kg
            'planet_diameter' => fake()->numberBetween(1, 200000), // kilometers
            'planet_rings' => fake()->numberBetween(0, 10),
            'planet_initial_x' => fake()->numberBetween(-5000, 5000), // unit in three.js
            'planet_initial_y' => fake()->numberBetween(-5000, 5000), // unit in three.js
            'planet_initial_z' => fake()->numberBetween(-5000, 5000), // unit in three.js
            'solar_system_id' => SolarSystem::factory(),
            'user_id' => null,
        ];
    }

}
