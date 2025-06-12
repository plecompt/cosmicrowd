<?php

namespace Database\Factories;

use App\Models\Planet;
use Illuminate\Database\Eloquent\Factories\Factory;

class MoonFactory extends Factory
{
    protected static $moonNames = [
        'Luna', 'Selene', 'Diana', 'Artemis', 'Cynthia', 'Phoebe', 'Titania',
        'Oberon', 'Europa', 'Ganymede', 'Io', 'Callisto', 'Enceladus', 'Titan'
    ];

    public function definition()
    {
        return [
            'name' => fake()->randomElement(self::$moonNames) . '-' . fake()->randomNumber(2),
            'size' => fake()->numberBetween(5, 30),
            'color' => fake()->hexColor(),
            'distance_from_planet' => fake()->numberBetween(20, 100),
            'orbital_period' => fake()->numberBetween(1, 30),
            'description' => fake()->optional()->sentence(6),
            'planet_id' => Planet::factory(),
        ];
    }
}
