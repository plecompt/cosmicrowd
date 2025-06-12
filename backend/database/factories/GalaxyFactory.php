<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GalaxyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'galaxy_name' => $this->faker->unique()->words(3, true),
            'galaxy_desc' => $this->faker->sentence(),
            'galaxy_size' => $this->faker->numberBetween(1000, 100000),
            'galaxy_age' => $this->faker->numberBetween(1000000000, 14000000000),
        ];
    }
} 