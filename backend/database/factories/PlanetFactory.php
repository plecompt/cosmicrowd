<?php

namespace Database\Factories;

use App\Models\SolarSystem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanetFactory extends Factory
{
    protected static $planetTypes = [
        'Tellurique', 'Gazeuse', 'Glacée', 'Océanique', 'Désertique', 'Volcanique'
    ];

    protected static $planetNames = [
        'Terra', 'Aqua', 'Ignis', 'Ventus', 'Glacies', 'Petra', 'Silva', 'Umbra',
        'Lux', 'Ferrum', 'Aurum', 'Crystalline', 'Nebula', 'Cosmos', 'Stella'
    ];

    public function definition()
    {
        return [
            'name' => fake()->randomElement(self::$planetNames) . '-' . fake()->randomLetter() . fake()->randomNumber(2),
            'type' => fake()->randomElement(self::$planetTypes),
            'size' => fake()->numberBetween(20, 100),
            'color' => fake()->hexColor(),
            'distance_from_star' => fake()->numberBetween(100, 800),
            'orbital_period' => fake()->numberBetween(10, 365),
            'description' => fake()->optional()->sentence(8),
            'solar_system_id' => SolarSystem::factory(),
        ];
    }
}
