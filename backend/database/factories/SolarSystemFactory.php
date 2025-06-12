<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SolarSystemFactory extends Factory
{
    protected static $starTypes = [
        ['name' => 'Naine rouge', 'color' => '#FF6B6B'],
        ['name' => 'Naine jaune', 'color' => '#FFD93D'],
        ['name' => 'Géante bleue', 'color' => '#74C0FC'],
        ['name' => 'Géante rouge', 'color' => '#FF8787'],
        ['name' => 'Naine blanche', 'color' => '#F8F9FA'],
        ['name' => 'Pulsar', 'color' => '#9775FA'],
    ];

    protected static $starNames = [
        'Kepler', 'Vega', 'Altair', 'Rigel', 'Betelgeuse', 'Sirius', 'Proxima', 'Alpha',
        'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Theta', 'Polaris', 'Canopus',
        'Arcturus', 'Capella', 'Aldebaran', 'Spica', 'Antares', 'Fomalhaut', 'Deneb',
    ];

    public function definition()
    {
        $type = fake()->randomElement(self::$starTypes);
        
        return [
            'name' => fake()->randomElement(self::$starNames) . '-' . fake()->randomNumber(3),
            'type' => $type['name'],
            'color' => $type['color'],
            'size' => fake()->numberBetween(50, 200),
            'temperature' => fake()->numberBetween(2000, 50000),
            'position_x' => fake()->numberBetween(-1000, 1000),
            'position_y' => fake()->numberBetween(-1000, 1000),
            'position_z' => fake()->numberBetween(-100, 100),
            'description' => fake()->optional()->sentence(10)
        ];
    }
}
