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
            'brown_dwarf' => fake()->numberBetween(20000, 159000),
            'red_dwarf' => fake()->numberBetween(159000, 1193000),
            'yellow_dwarf' => fake()->numberBetween(1591000, 2387000),
            'white_dwarf' => fake()->numberBetween(597000, 2784000),
            'red_giant' => fake()->numberBetween(1591000, 15912000),
            'blue_giant' => fake()->numberBetween(15912000, 49725000),
            'red_supergiant' => fake()->numberBetween(15912000, 79560000),
            'blue_supergiant' => fake()->numberBetween(29835000, 179010000),
            'hypergiant' => fake()->numberBetween(49725000, 626535000),
            'neutron_star' => fake()->numberBetween(2188000, 4315000),
            'pulsar' => fake()->numberBetween(2188000, 4315000),
            'variable' => fake()->numberBetween(1591000, 15912000),
            'binary' => fake()->numberBetween(3182000, 59670000),
            'ternary' => fake()->numberBetween(1989000, 29835000),
            'black_hole' => fake()->numberBetween(5967000, 25000000000), 
            default => 1989000,
        };
    }

    private function generateDiameter(string $starType): int
    {
        return match($starType) {
            'brown_dwarf' => fake()->numberBetween(70000000, 210000000),
            'red_dwarf' => fake()->numberBetween(140000000, 840000000),
            'yellow_dwarf' => fake()->numberBetween(1120000000, 1680000000),
            'white_dwarf' => fake()->numberBetween(14000000, 70000000),
            'red_giant' => fake()->numberBetween(70000000000, 600000000000),
            'blue_giant' => fake()->numberBetween(7000000000, 70000000000),
            'red_supergiant' => fake()->numberBetween(140000000000, 600000000000),
            'blue_supergiant' => fake()->numberBetween(14000000000, 140000000000),
            'hypergiant' => fake()->numberBetween(140000000000, 600000000000),
            'neutron_star' => fake()->numberBetween(20000, 40000),
            'pulsar' => fake()->numberBetween(20000, 40000),
            'variable' => fake()->numberBetween(1000000000, 100000000000),
            'binary' => fake()->numberBetween(1000000000, 100000000000),
            'ternary' => fake()->numberBetween(1000000000, 50000000000),
            'black_hole' => fake()->numberBetween(60000, 600000000000),
            default => fake()->numberBetween(1120000000, 1680000000),
        };
    }

     private function generateGravity(string $starType): float
    {
        return match($starType) {
            'brown_dwarf' => fake()->randomFloat(2, 0, 50),
            'red_dwarf' => fake()->randomFloat(2, 10, 200),
            'yellow_dwarf' => fake()->randomFloat(2, 200, 400),
            'white_dwarf' => fake()->randomFloat(2, 50000, 500000),
            'red_giant' => fake()->randomFloat(2, 5, 100),
            'blue_giant' => fake()->randomFloat(2, 100, 1000),
            'red_supergiant' => fake()->randomFloat(2, 1, 50),
            'blue_supergiant' => fake()->randomFloat(2, 50, 500),
            'hypergiant' => fake()->randomFloat(2, 1, 100),
            'neutron_star' => fake()->randomFloat(2, 100000000, 1000000000000),
            'pulsar' => fake()->randomFloat(2, 100000000, 1000000000000),
            'variable' => fake()->randomFloat(2, 10, 400),
            'binary' => fake()->randomFloat(2, 50, 800),
            'ternary' => fake()->randomFloat(2, 100, 600),
            'black_hole' => fake()->randomFloat(2, 1000000000, 1000000000000),
            default => fake()->randomFloat(2, 200, 400),
        };
    }

    private function generateSurfaceTemp(string $starType): float
    {
        return match($starType) {
            'brown_dwarf' => fake()->randomFloat(2, 800, 2500),
            'red_dwarf' => fake()->randomFloat(2, 2500, 4000),
            'yellow_dwarf' => fake()->randomFloat(2, 5000, 6000),
            'white_dwarf' => fake()->randomFloat(2, 8000, 40000),
            'red_giant' => fake()->randomFloat(2, 3000, 5000),
            'blue_giant' => fake()->randomFloat(2, 20000, 50000),
            'red_supergiant' => fake()->randomFloat(2, 3000, 4500),
            'blue_supergiant' => fake()->randomFloat(2, 30000, 50000),
            'hypergiant' => fake()->randomFloat(2, 3000, 50000),
            'neutron_star' => fake()->randomFloat(2, 100000, 200000),
            'pulsar' => fake()->randomFloat(2, 100000, 200000),
            'variable' => fake()->randomFloat(2, 3000, 10000),
            'binary' => fake()->randomFloat(2, 3000, 30000),
            'ternary' => fake()->randomFloat(2, 3000, 20000),
            'black_hole' => fake()->randomFloat(2, 0, 100000),
            default => fake()->randomFloat(2, 5000, 6000),
        };
    }

    private function generateLuminosity(string $starType): int
    {
        return match($starType) {
            'brown_dwarf' => fake()->numberBetween(1, 10),
            'red_dwarf' => fake()->numberBetween(1, 1000),
            'yellow_dwarf' => fake()->numberBetween(800, 1200),
            'white_dwarf' => fake()->numberBetween(1, 100),
            'red_giant' => fake()->numberBetween(100, 10000),
            'blue_giant' => fake()->numberBetween(10000, 1000000),
            'red_supergiant' => fake()->numberBetween(10000, 500000),
            'blue_supergiant' => fake()->numberBetween(100000, 1000000),
            'hypergiant' => fake()->numberBetween(500000, 10000000),
            'neutron_star' => fake()->numberBetween(1, 1000),
            'pulsar' => fake()->numberBetween(1000, 100000),
            'variable' => fake()->numberBetween(100, 100000),
            'binary' => fake()->numberBetween(1000, 2000000),
            'ternary' => fake()->numberBetween(1500, 3000000),
            'black_hole' => fake()->numberBetween(1, 1000),
            default => 1000,
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
