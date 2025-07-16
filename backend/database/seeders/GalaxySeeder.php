<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Galaxy;
use App\Models\SolarSystem;
use App\Utils\Vector3;
use App\Models\Planet;
use App\Models\Moon;

class GalaxySeeder extends Seeder
{
    // Galaxy configuration
    private const CONFIG = [
        'NUM_SYSTEMS' => 2000,
        'NUM_ARMS' => 4,
        'GALAXY_THICKNESS' => 5,
        'ARM_X_DIST' => 100,
        'ARM_Y_DIST' => 50,
        'ARM_X_MEAN' => 50,
        'ARM_Y_MEAN' => 25,
        'SPIRAL_FORCE' => 2.0,
        'GALAXY_RADIUS' => 1000,
    ];

    // Planet types by distance zones
    private const PLANET_ZONES = [
        'inner' => ['terrestrial', 'lava', 'super_earth'], // Rocky planets close to star
        'habitable' => ['terrestrial', 'ocean', 'super_earth'], // Goldilocks zone
        'outer' => ['gas', 'ice', 'sub_neptune'], // Gas giants far from star
        'kuiper' => ['ice', 'dwarf'] // Frozen objects at edge
    ];

    public function run()
    {      
        $galaxy = Galaxy::factory()->create([
            'galaxy_name' => 'CosmiCrowd Galaxy',
            'galaxy_desc' => 'Collaborative spiral galaxy generated for CosmiCrowd',
            'galaxy_size' => self::CONFIG['GALAXY_RADIUS'],
            'galaxy_age' => rand(8, 14)
        ]);
        
        $this->generateSolarSystems($galaxy->galaxy_id);
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

                $solarSystem = SolarSystem::factory()->create([
                    'solar_system_initial_x' => $position->x,
                    'solar_system_initial_y' => $position->y,
                    'solar_system_initial_z' => $position->z,
                    'galaxy_id' => $galaxyId,
                ]);

                $this->generatePlanets($solarSystem);
            }
        }
    }

    private function generatePlanets($solarSystem)
    {
        $numPlanets = rand(0, 8);
        $starDiameter = $solarSystem->solar_system_diameter;
        
        for($i = 0; $i < $numPlanets; $i++) {
            // Calculate orbital distance (closer planets first)
            $baseDistance = 50000000 + ($i * 200000000); // 50M km base + 200M km per planet
            $orbitalDistance = $baseDistance + rand(-20000000, 20000000); // Add variation
            
            // Determine planet zone and type
            $zone = $this->getPlanetZone($i, $numPlanets);
            $planetType = $this->getRandomElement(self::PLANET_ZONES[$zone]);
            
            // Planet size based on zone and star size
            $planetDiameter = $this->calculatePlanetDiameter($zone, $starDiameter);
            
            // Calculate orbital position for Three.js with orbital inclination
            $orbitalInclination = rand(-15, 15) * M_PI / 180; // Small inclination
            $orbitalAngle = rand(0, 360) * M_PI / 180;
            $orbitalRadius = $orbitalDistance / 50000000; // Better scale for Three.js (1 unit = 50M km)
            
            $planet = Planet::factory()->create([
                'solar_system_id' => $solarSystem->solar_system_id,
                'user_id' => null,
                'planet_type' => $planetType,
                'planet_diameter' => $planetDiameter,
                'planet_average_distance' => $orbitalDistance,
                'planet_perigee' => $orbitalDistance * 0.98,
                'planet_apogee' => $orbitalDistance * 1.02,
                'planet_initial_x' => cos($orbitalAngle) * $orbitalRadius,
                'planet_initial_y' => sin($orbitalInclination) * $orbitalRadius,
                'planet_initial_z' => sin($orbitalAngle) * $orbitalRadius,
                'planet_mass' => $this->calculatePlanetMass($planetDiameter, $planetType),
                'planet_surface_temp' => $this->calculatePlanetTemperature($orbitalDistance),
                // 'planet_orbital_inclination' => $orbitalInclination * 180 / M_PI,
            ]);
            
            $this->generateMoons($planet);
        }
    }

    private function generateMoons($planet)
    {
        // Gas giants have more moons
        $maxMoons = in_array($planet->planet_type, ['gas', 'sub_neptune']) ? 5 : 2;
        $numMoons = rand(0, $maxMoons);
        
        for($i = 0; $i < $numMoons; $i++) {
            $moonDistance = 500000 + ($i * 300000); // 500k km base + 300k km per moon
            $moonInclination = rand(-30, 30) * M_PI / 180; // More varied inclination for moons
            $moonAngle = rand(0, 360) * M_PI / 180;
            $orbitalRadius = $moonDistance / 10000000; // Scale for Three.js (1 unit = 10M km)
            
            // Calculate moon position relative to planet
            $moonRelativeX = cos($moonAngle) * $orbitalRadius;
            $moonRelativeY = sin($moonInclination) * $orbitalRadius;
            $moonRelativeZ = sin($moonAngle) * $orbitalRadius;
            
            Moon::factory()->create([
                'planet_id' => $planet->planet_id,
                'user_id' => null,
                'moon_diameter' => $planet->planet_diameter * rand(10, 30) / 100,
                'moon_average_distance' => $moonDistance,
                'moon_perigee' => $moonDistance * 0.95,
                'moon_apogee' => $moonDistance * 1.05,
                'moon_initial_x' => $planet->planet_initial_x + $moonRelativeX,
                'moon_initial_y' => $planet->planet_initial_y + $moonRelativeY,
                'moon_initial_z' => $planet->planet_initial_z + $moonRelativeZ,
                'moon_mass' => rand(10, 100),
                // 'moon_orbital_inclination' => $moonInclination * 180 / M_PI,
            ]);
        }
    }

    private function getPlanetZone(int $planetIndex, int $totalPlanets): string
    {
        $ratio = $planetIndex / max($totalPlanets - 1, 1);
        
        if ($ratio < 0.2) return 'inner';
        if ($ratio < 0.4) return 'habitable';
        if ($ratio < 0.8) return 'outer';
        return 'kuiper';
    }

    private function calculatePlanetDiameter(string $zone, int $starDiameter): int
    {
        $maxDiameter = $starDiameter * 0.1; // Max 10% of star size
        
        return match($zone) {
            'inner' => rand(2000000, min(15000000, $maxDiameter)), // Small rocky
            'habitable' => rand(8000000, min(20000000, $maxDiameter)), // Earth-like
            'outer' => rand(40000000, min(140000000, $maxDiameter)), // Gas giants
            'kuiper' => rand(1000000, min(5000000, $maxDiameter)), // Dwarf planets
        };
    }

    private function calculatePlanetMass(int $diameter, string $type): int
    {
        $baseMass = ($diameter / 1000000) * 10; // Base calculation
        
        return match($type) {
            'gas', 'sub_neptune' => $baseMass * rand(80, 120) / 100, // Less dense
            'terrestrial', 'super_earth' => $baseMass * rand(120, 150) / 100, // Dense
            default => $baseMass
        };
    }

    private function calculatePlanetTemperature(int $distance): float
    {
        // Simplified temperature calculation based on distance
        $solarConstant = 1361; // W/m²
        $temperature = 278 * pow($distance / 149597870700, -0.5); // Kelvin
        
        return max(0, min(773, $temperature));
    }

    private function getRandomElement(array $array): mixed
    {
        return $array[array_rand($array)];
    }

    private function gaussianRandom(float $center = 0.0, float $deviation = 1.0): float 
    {
        $u = mt_rand() / mt_getrandmax();
        $v = mt_rand() / mt_getrandmax();
        
        $z = sqrt(-2.0 * log($u)) * cos(2.0 * M_PI * $v);
        
        return $z * $deviation + $center;
    }

    private function spiral(float $x, float $y, float $z, float $offset)
    {
        $r = sqrt($x**2 + $y**2);
        $theta = $offset;
        $theta += $x > 0 ? atan($y/$x) : atan($y/$x) + M_PI;
        $theta += ($r / self::CONFIG['ARM_X_DIST']) * self::CONFIG['SPIRAL_FORCE'];

        return new Vector3($r * cos($theta), $r * sin($theta), $z);
    }
}