<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GalaxyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'galaxy_name' => fake()->words(2, true) . ' Galaxy',
            'galaxy_desc' => fake()->sentence(10),
            'galaxy_size' => fake()->numberBetween(500, 2000),
            'galaxy_age' => fake()->numberBetween(8, 14),
        ];
    }
}
