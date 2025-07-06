<?php

namespace Database\Factories;

use App\Models\Galaxy;
use Illuminate\Database\Eloquent\Factories\Factory;

class SolarSystemFactory extends Factory
{
    protected static $starTypes = [
        ['name' => 'brown_dwarf', 'color' => '#8B4513', 'gravity' => [10, 100], 'temp' => [500, 2000], 'luminosity' => [1, 10], 'mass' => [1e28, 1e29], 'diameter' => [50000, 100000]],
        ['name' => 'red_dwarf', 'color' => '#FF6B6B', 'gravity' => [50, 300], 'temp' => [2300, 3800], 'luminosity' => [10, 100], 'mass' => [1e29, 8e29], 'diameter' => [100000, 500000]],
        ['name' => 'yellow_dwarf', 'color' => '#FFD93D', 'gravity' => [200, 400], 'temp' => [5200, 6000], 'luminosity' => [600, 1500], 'mass' => [1e30, 2e30], 'diameter' => [1200000, 1500000]],
        ['name' => 'white_dwarf', 'color' => '#F8F9FA', 'gravity' => [100000, 1000000], 'temp' => [8000, 40000], 'luminosity' => [1, 100], 'mass' => [3e29, 2e30], 'diameter' => [10000, 20000]],
        ['name' => 'red_giant', 'color' => '#FF8787', 'gravity' => [1, 5], 'temp' => [3000, 4000], 'luminosity' => [1000, 10000], 'mass' => [5e29, 1e31], 'diameter' => [50000000, 200000000]],
        ['name' => 'blue_giant', 'color' => '#74C0FC', 'gravity' => [50, 200], 'temp' => [20000, 50000], 'luminosity' => [10000, 1000000], 'mass' => [1e31, 8e31], 'diameter' => [5000000, 15000000]],
        ['name' => 'neutron_star', 'color' => '#9775FA', 'gravity' => [1e11, 1e12], 'temp' => [600000, 1000000], 'luminosity' => [1, 100], 'mass' => [2e30, 4e30], 'diameter' => [20, 40]],
        ['name' => 'black_hole', 'color' => '#000000', 'gravity' => [1e15, 1e16], 'temp' => [0, 100], 'luminosity' => [0, 0], 'mass' => [5e30, 1e32], 'diameter' => [10, 1000]],
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
            'solar_system_name' => fake()->randomElement(self::$starNames) . '-' . fake()->randomNumber(3),
            'solar_system_desc' => fake()->optional()->sentence(10),
            'solar_system_type' => $type['name'],
            'solar_system_gravity' => fake()->randomFloat(2, $type['gravity'][0], $type['gravity'][1]),
            'solar_system_surface_temp' => fake()->randomFloat(2, $type['temp'][0], $type['temp'][1]),
            'solar_system_diameter' => fake()->numberBetween($type['diameter'][0], $type['diameter'][1]),
            'solar_system_mass' => fake()->numberBetween($type['mass'][0], $type['mass'][1]),
            'solar_system_luminosity' => fake()->numberBetween($type['luminosity'][0], $type['luminosity'][1]),
            'solar_system_initial_x' => fake()->numberBetween(-1000, 1000),
            'solar_system_initial_y' => fake()->numberBetween(-1000, 1000),
            'solar_system_initial_z' => fake()->numberBetween(-100, 100),
            'galaxy_id' => Galaxy::factory(),
        ];
    }
}
