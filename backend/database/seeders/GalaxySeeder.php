<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Galaxy;
use App\Models\SolarSystem;
use App\Utils\Vector3;
use App\Utils\StellarRanges;

class GalaxySeeder extends Seeder
{
    // Realistic star type probabilities
    private const STAR_TYPES = [
        // Red dwarfs (75% of all stars)
        'red_dwarf' => 47,      // Most common
        'brown_dwarf' => 15,    // Quite common
        'yellow_dwarf' => 5,    // Like our Sun (rare!)
        'white_dwarf' => 8,     // Stellar remnants
        
        // Giants (10% - Evolved stars)
        'red_giant' => 6,       // Aging stars
        'blue_giant' => 4,      // Hot and massive
        
        // Supergiants (6% - Very rare)
        'red_supergiant' => 3,  // Betelgeuse, Antares
        'blue_supergiant' => 3, // Rigel, Deneb
        
        // Extreme objects (6% - Ultra rare)
        'hypergiant' => 2,      // Largest stars
        'neutron_star' => 1,    // Supernova remnants
        'pulsar' => 1,          // Rotating neutron stars
        'variable' => 2,        // Variable brightness
        
        // Multiple systems (2.3% - Special)
        'binary' => 1.3,        // Binary systems
        'ternary' => 1,         // Triple systems
        
        // Black holes (0.6% - Extremely rare)
        'black_hole' => 0.7,    // Black holes
    ];

    // Galaxy configuration
    private const CONFIG = [
        'NUM_SYSTEMS' => 2000, // per arm
        'NUM_ARMS' => 4,
        'GALAXY_THICKNESS' => 5,
        'CORE_X_DIST' => 33,
        'CORE_Y_DIST' => 33,
        'GALAXY_RADIUS' => 1000,
        'ARM_X_DIST' => 100,
        'ARM_Y_DIST' => 50,
        'ARM_X_MEAN' => 50, // arm center
        'ARM_Y_MEAN' => 25, // arm center
        'SPIRAL_FORCE' => 2.0, // spiral strength
    ];

    public function run()
    {      
        // Create galaxy
        $galaxy = $this->createGalaxy();
        // Generate solar systems
        $this->generateSolarSystems($galaxy->galaxy_id);
    }

    private function createGalaxy(): Galaxy
    {
        return Galaxy::create([
            'galaxy_name' => 'CosmiCrowd Galaxy',
            'galaxy_desc' => 'Collaborative spiral galaxy generated for CosmiCrowd',
            'galaxy_size' => self::CONFIG['GALAXY_RADIUS'],
            'galaxy_age' => rand(8, 14) // Billions of years
        ]);
    }

    /**
     * Generate random number following Gaussian/Normal distribution
     * Uses Box-Muller transformation
     */
    private function gaussianRandom(float $center = 0.0, float $deviation = 1.0): float 
    {
        $u = mt_rand() / mt_getrandmax();
        $v = mt_rand() / mt_getrandmax();
        
        $z = sqrt(-2.0 * log($u)) * cos(2.0 * M_PI * $v);
        
        return $z * $deviation + $center;
    }

    private function generateSolarSystems(int $galaxyId)
    {
        for($i = 0; $i < self::CONFIG['NUM_ARMS']; $i++){
            for($j = 0; $j < self::CONFIG['NUM_SYSTEMS']; $j++){
                $position = $this->spiral(
                    $this->gaussianRandom(self::CONFIG['ARM_X_MEAN'], self::CONFIG['ARM_X_DIST']), 
                    $this->gaussianRandom(self::CONFIG['ARM_Y_MEAN'], self::CONFIG['ARM_Y_DIST']), 
                    $this->gaussianRandom(0, self::CONFIG['GALAXY_THICKNESS']), 
                    $i * 2 * M_PI / self::CONFIG['NUM_ARMS']
                );
                // Star type with probability
                $starType = $this->getRandomStarType();
                
                // Properties based on type
                $properties = StellarRanges::generateRandomStar($starType);

                SolarSystem::create([
                    'solar_system_name' => $this->generateStarName($starType),
                    'solar_system_desc' => $this->generateDescription($starType),
                    'solar_system_type' => $starType,
                    'solar_system_gravity' => $properties['gravity'],
                    'solar_system_surface_temp' => $properties['surface_temp'],
                    'solar_system_diameter' => $properties['diameter'],
                    'solar_system_mass' => $properties['mass'],
                    'solar_system_luminosity' => $properties['luminosity'],    
                    'solar_system_initial_x' => $position->x,
                    'solar_system_initial_y' => $position->y,
                    'solar_system_initial_z' => $position->z,
                    'galaxy_id' => $galaxyId,
                ]);
            }
        }
    }

    private function spiral(float $x, float $y, float $z, float $offset)
    {
        $r = sqrt($x**2 + $y**2);
        $theta = $offset;
        $theta += $x > 0 ? atan($y/$x) : atan($y/$x) + M_PI;
        $theta += ($r / self::CONFIG['ARM_X_DIST']) * self::CONFIG['SPIRAL_FORCE'];

        return new Vector3($r * cos($theta), $r * sin($theta), $z);
    }

    private function getRandomStarType(): string
    {
        $random = rand(1, 100);
        $cumulative = 0;
        
        foreach (self::STAR_TYPES as $type => $probability) {
            $cumulative += $probability;
            if ($random <= $cumulative) {
                return $type;
            }
        }
        
        return 'red_dwarf';
    }

    private function generateStarName(string $type): string
    {
        $prefixes = ['Alpha', 'Beta', 'Gamma', 'Delta', 'Zeta', 'Omicron', 'Theta', 'Sigma', 'Tau', 'Phi'];
        $suffixes = ['Centauri', 'Draconis', 'Orionis', 'Cygni', 'Lyrae', 'Vega', 'Sirius', 'Rigel', 'Deneb'];
        
        // Special names for certain types
        $specialNames = match($type) {
            'black_hole' => ['Sagittarius A*', 'Cygnus X-1', 'V404 Cygni', 'GRO J1655-40'],
            'pulsar' => ['PSR J', 'B1919+21', 'Vela', 'Crab Pulsar'],
            'neutron_star' => ['RX J', 'PSR', 'SGR', 'AXP'],
            'hypergiant' => ['VY Canis', 'UY Scuti', 'Stephenson', 'Westerlund'],
            default => null
        };
        
        if ($specialNames && rand(1, 3) === 1) {
            return $specialNames[array_rand($specialNames)] . ' ' . rand(1, 9999);
        }
        
        return $prefixes[array_rand($prefixes)] . ' ' . 
               $suffixes[array_rand($suffixes)] . ' ' . 
               rand(1, 9999);
    }

    private function generateDescription(string $type): string
    {
        return match($type) {
            'red_dwarf' => 'Red dwarf star, cool and long-lived',
            'brown_dwarf' => 'Brown dwarf, "failed star" that cannot fuse hydrogen',
            'yellow_dwarf' => 'Yellow dwarf similar to our Sun',
            'white_dwarf' => 'White dwarf, dense remnant of a dead star',
            'red_giant' => 'Red giant in its final stages, extended atmosphere',
            'blue_giant' => 'Hot and massive blue giant',
            'red_supergiant' => 'Red supergiant, among the largest stars',
            'blue_supergiant' => 'Blue supergiant, extremely hot and luminous',
            'hypergiant' => 'Hypergiant, star of extraordinary size',
            'neutron_star' => 'Ultra-dense neutron star, supernova remnant',
            'pulsar' => 'Pulsar, neutron star emitting radio beams',
            'variable' => 'Variable star with changing luminosity',
            'binary' => 'Binary system of two orbiting stars',
            'ternary' => 'Ternary system of three bound stars',
            'black_hole' => 'Black hole, region where nothing can escape',
            default => 'Automatically generated stellar system'
        };
    }
}
