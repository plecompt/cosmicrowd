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

    /**
     * Main seeder method.
     * Creates the galaxy and initiates the generation of solar systems, planets and moons.
     * @return void
     */
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

    /**
     * Generates all the solar systems for the given galaxy.
     * The solar systems are positioned in a spiral pattern to form the galaxy's arms.
     *
     * @param int $galaxyId The ID of the galaxy to populate.
     */
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

    /**
     * Generates a random number of planets (0-8) for a given solar system.
     *
     * @param SolarSystem $solarSystem The solar system to add planets to.
     */
    private function generatePlanets($solarSystem)
    {
        $numPlanets = rand(0, 8);
        
        for($i = 0; $i < $numPlanets; $i++) {
            $planet = Planet::factory()->create([
                'solar_system_id' => $solarSystem->solar_system_id,
                'user_id' => null,
            ]);

            $this->generateMoons($planet);
        }
    }

    /**
     * Generates a random number (0-3) of moons for a given planet.
     *
     * @param Planet $planet The planet to add moons to.
     */
    private function generateMoons($planet)
    {
        $numMoons = rand(0, 3);
        
        for($i = 0; $i < $numMoons; $i++) {
            Moon::factory()->create([
                'planet_id' => $planet->planet_id,
                'user_id' => null,
            ]);
        }
    }

    /**
     * Generates a random number with a Gaussian (normal) distribution.
     *
     * @param float $center The mean of the distribution.
     * @param float $deviation The standard deviation of the distribution.
     * @return float A random number following a Gaussian distribution.
     */
    private function gaussianRandom(float $center = 0.0, float $deviation = 1.0): float 
    {
        $u = mt_rand() / mt_getrandmax();
        $v = mt_rand() / mt_getrandmax();
        
        $z = sqrt(-2.0 * log($u)) * cos(2.0 * M_PI * $v);
        
        return $z * $deviation + $center;
    }

    /**
     * Calculates a new position for a celestial body to form a spiral arm.
     * It applies a spiral transformation to a given 3D position.
     *
     * @param float $x The initial x-coordinate.
     * @param float $y The initial y-coordinate.
     * @param float $z The initial z-coordinate.
     * @param float $offset The angular offset for the spiral arm.
     * @return \App\Utils\Vector3 The new position in the spiral arm.
     */
    private function spiral(float $x, float $y, float $z, float $offset)
    {
        $r = sqrt($x**2 + $y**2);
        $theta = $offset;
        $theta += $x > 0 ? atan($y/$x) : atan($y/$x) + M_PI;
        $theta += ($r / self::CONFIG['ARM_X_DIST']) * self::CONFIG['SPIRAL_FORCE'];

        return new Vector3($r * cos($theta), $r * sin($theta), $z);
    }
}