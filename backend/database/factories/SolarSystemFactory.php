<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Galaxy;

class SolarSystemFactory extends Factory
{
    public function definition(): array
    {
        $starType = $this->getRandomStarType();
        
        return [
            'solar_system_name' => $this->generateSolarSystemName($starType),
            'solar_system_desc' => $this->generateSolarSystemDescription($starType),
            'solar_system_type' => $starType,
            'solar_system_gravity' => $this->generateGravity($starType),
            'solar_system_surface_temp' => $this->generateSurfaceTemp($starType),
            'solar_system_diameter' => $this->generateDiameter($starType),
            'solar_system_mass' => $this->generateMass($starType),
            'solar_system_luminosity' => $this->generateLuminosity($starType),
            'galaxy_id' => Galaxy::factory(),
            'user_id' => null,
        ];
    }

    private function getRandomStarType(): string
    {
        $starTypes = [
            'red_dwarf' => 76,
            'brown_dwarf' => 10,
            'yellow_dwarf' => 7,
            'white_dwarf' => 3,
            'red_giant' => 2,
            'blue_giant' => 1,
            'red_supergiant' => 0.5,
            'blue_supergiant' => 0.3,
            'hypergiant' => 0.1,
            'neutron_star' => 0.08,
            'pulsar' => 0.05,
            'variable' => 0.02,
            'binary' => 1,
            'ternary' => 0.5,
            'black_hole' => 0.001,
        ];

        $rand = fake()->randomFloat(3, 0, 100);
        $cumulative = 0;

        foreach ($starTypes as $type => $probability) {
            $cumulative += $probability;
            if ($rand <= $cumulative) {
                return $type;
            }
        }

        return 'yellow_dwarf';
    }

    private function generateMass(string $starType): int
    {
        return match($starType) {
            'brown_dwarf' => fake()->numberBetween(20000, 159000), // 0.01-0.08 solar masses
            'red_dwarf' => fake()->numberBetween(159000, 1193000), // 0.08-0.6 solar masses
            'yellow_dwarf' => fake()->numberBetween(1591000, 2387000), // 0.8-1.2 solar masses
            'white_dwarf' => fake()->numberBetween(597000, 2784000), // 0.3-1.4 solar masses
            'red_giant' => fake()->numberBetween(1591000, 15912000), // 0.8-8 solar masses
            'blue_giant' => fake()->numberBetween(15912000, 49725000), // 8-25 solar masses
            'red_supergiant' => fake()->numberBetween(15912000, 79560000), // 8-40 solar masses
            'blue_supergiant' => fake()->numberBetween(29835000, 179010000), // 15-90 solar masses
            'hypergiant' => fake()->numberBetween(49725000, 626535000), // 25-315 solar masses
            'neutron_star' => fake()->numberBetween(2188000, 4315000), // 1.1-2.17 solar masses
            'pulsar' => fake()->numberBetween(2188000, 4315000), // 1.1-2.17 solar masses
            'variable' => fake()->numberBetween(1591000, 15912000), // 0.8-8 solar masses
            'binary' => fake()->numberBetween(3182000, 59670000), // 1.6-30 solar masses
            'ternary' => fake()->numberBetween(1989000, 29835000), // 1-15 solar masses
            'black_hole' => fake()->numberBetween(5967000, 1989000000), // 3-1000 solar masses
            default => 1989000, // 1 solar mass
        };
    }

    private function generateDiameter(string $starType): int
    {
        return match($starType) {
            'brown_dwarf' => fake()->numberBetween(70000000, 210000000), // 0.05-0.15 solar diameters
            'red_dwarf' => fake()->numberBetween(140000000, 840000000), // 0.1-0.6 solar diameters
            'yellow_dwarf' => fake()->numberBetween(1120000000, 1680000000), // 0.8-1.2 solar diameters
            'white_dwarf' => fake()->numberBetween(14000000, 70000000), // 0.01-0.05 solar diameters
            'red_giant' => fake()->numberBetween(14000000000, 140000000000), // 10-100 solar diameters
            'blue_giant' => fake()->numberBetween(7000000000, 35000000000), // 5-25 solar diameters
            'red_supergiant' => fake()->numberBetween(280000000000, 2800000000000), // 200-2000 solar diameters
            'blue_supergiant' => fake()->numberBetween(28000000000, 70000000000), // 20-50 solar diameters
            'hypergiant' => fake()->numberBetween(560000000000, 4200000000000), // 400-3000 solar diameters
            'neutron_star' => fake()->numberBetween(28000000, 56000000), // 20-40 km
            'pulsar' => fake()->numberBetween(28000000, 56000000), // 20-40 km
            'variable' => fake()->numberBetween(7000000000, 70000000000), // 5-50 solar diameters
            'binary' => fake()->numberBetween(2800000000, 14000000000), // 2-10 solar diameters
            'ternary' => fake()->numberBetween(2100000000, 11200000000), // 1.5-8 solar diameters
            'black_hole' => fake()->numberBetween(1400000, 14000000), // Event horizon
            default => 1400000000, // 1 solar diameter
        };
    }

    private function generateGravity(string $starType): float
    {
        return match($starType) {
            'brown_dwarf' => fake()->randomFloat(2, 10, 300),
            'red_dwarf' => fake()->randomFloat(2, 20, 100),
            'yellow_dwarf' => fake()->randomFloat(2, 200, 350),
            'white_dwarf' => fake()->randomFloat(2, 100000, 10000000),
            'red_giant' => fake()->randomFloat(2, 0.1, 10),
            'blue_giant' => fake()->randomFloat(2, 50, 200),
            'red_supergiant' => fake()->randomFloat(2, 0.01, 1),
            'blue_supergiant' => fake()->randomFloat(2, 1, 50),
            'hypergiant' => fake()->randomFloat(2, 0.01, 10),
            'neutron_star' => fake()->randomFloat(2, 100000000000, 1000000000000),
            'pulsar' => fake()->randomFloat(2, 100000000000, 1000000000000),
            'variable' => fake()->randomFloat(2, 0.1, 300),
            'binary' => fake()->randomFloat(2, 10, 500),
            'ternary' => fake()->randomFloat(2, 5, 400),
            'black_hole' => PHP_FLOAT_MAX,
            default => 274.0,
        };
    }

    private function generateSurfaceTemp(string $starType): int
    {
        return match($starType) {
            'brown_dwarf' => fake()->numberBetween(500, 2500),
            'red_dwarf' => fake()->numberBetween(2300, 3800),
            'yellow_dwarf' => fake()->numberBetween(5200, 6000),
            'white_dwarf' => fake()->numberBetween(4000, 150000),
            'red_giant' => fake()->numberBetween(2500, 4500),
            'blue_giant' => fake()->numberBetween(10000, 30000),
            'red_supergiant' => fake()->numberBetween(2500, 4500),
            'blue_supergiant' => fake()->numberBetween(20000, 50000),
            'hypergiant' => fake()->numberBetween(3000, 50000),
            'neutron_star' => fake()->numberBetween(100000, 10000000),
            'pulsar' => fake()->numberBetween(100000, 10000000),
            'variable' => fake()->numberBetween(2500, 30000),
            'binary' => fake()->numberBetween(3000, 25000),
            'ternary' => fake()->numberBetween(3000, 20000),
            'black_hole' => 0,
            default => 5778,
        };
    }

    private function generateLuminosity(string $starType): int
    {
        return match($starType) {
            'brown_dwarf' => fake()->numberBetween(1, 100),
            'red_dwarf' => fake()->numberBetween(100, 100000),
            'yellow_dwarf' => fake()->numberBetween(500000, 1500000),
            'white_dwarf' => fake()->numberBetween(100, 100000),
            'red_giant' => fake()->numberBetween(10000000, 5000000000),
            'blue_giant' => fake()->numberBetween(1000000000, 100000000000),
            'red_supergiant' => fake()->numberBetween(1000000000, 1000000000000),
            'blue_supergiant' => fake()->numberBetween(10000000000, 2000000000000),
            'hypergiant' => fake()->numberBetween(50000000000, 2147483647), // Max int
            'neutron_star' => fake()->numberBetween(1000, 10000000),
            'pulsar' => fake()->numberBetween(1000, 10000000),
            'variable' => fake()->numberBetween(1000000, 1000000000),
            'binary' => fake()->numberBetween(2000000, 5000000000),
            'ternary' => fake()->numberBetween(1000000, 2000000000),
            'black_hole' => 0,
            default => 1000000,
        };
    }

    private function generateSolarSystemName(string $starType): string
    {
        $prefixes = ['Alpha', 'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta'];
        $suffixes = ['Centauri', 'Draconis', 'Orionis', 'Andromedae', 'Cassiopeiae', 'Lyrae'];
        
        return fake()->randomElement($prefixes) . ' ' . fake()->randomElement($suffixes);
    }

    private function generateSolarSystemDescription(string $starType): string
    {
        $descriptions = [
            'brown_dwarf' => 'A cool, dim brown dwarf system',
            'red_dwarf' => 'A small, long-lived red dwarf star',
            'yellow_dwarf' => 'A stable, sun-like yellow dwarf',
            'white_dwarf' => 'A dense white dwarf remnant',
            'red_giant' => 'An expanding red giant star',
            'blue_giant' => 'A massive, hot blue giant',
            'red_supergiant' => 'An enormous red supergiant',
            'blue_supergiant' => 'A brilliant blue supergiant',
            'hypergiant' => 'An extremely rare hypergiant star',
            'neutron_star' => 'An ultra-dense neutron star',
            'pulsar' => 'A rapidly spinning pulsar',
            'variable' => 'A variable brightness star',
            'binary' => 'A binary star system',
            'ternary' => 'A triple star system',
            'black_hole' => 'A mysterious black hole',
        ];

        return $descriptions[$starType] ?? 'A stellar system';
    }
}
