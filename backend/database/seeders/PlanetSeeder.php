<?php

namespace Database\Seeders;

use App\Models\Planet;
use App\Models\Star;
use Illuminate\Database\Seeder;

class PlanetSeeder extends Seeder
{
    public function run()
    {
        $stars = Star::all();
        
        // Données réalistes pour chaque type de planète
        $planetTypeData = [
            'terrestrial' => [
                'mass' => [0.055, 1.5], // Masses terrestres
                'radius' => [0.38, 1.2], // Rayons terrestres
                'temp_range' => [-60, 60], // Celsius
                'rotation' => [0.5, 100], // heures
            ],
            'gas' => [
                'mass' => [10, 500], // Masses terrestres
                'radius' => [3, 12], // Rayons terrestres
                'temp_range' => [-180, -80],
                'rotation' => [8, 20], // heures
            ],
            'ice' => [
                'mass' => [0.02, 0.8],
                'radius' => [0.2, 0.9],
                'temp_range' => [-220, -120],
                'rotation' => [12, 200], // heures
            ],
            'super_earth' => [
                'mass' => [1.5, 10],
                'radius' => [1.2, 2.5],
                'temp_range' => [-50, 100],
                'rotation' => [10, 40], // heures
            ],
            'sub_neptune' => [
                'mass' => [5, 20],
                'radius' => [2, 4],
                'temp_range' => [-100, 50],
                'rotation' => [15, 30], // heures
            ],
            'dwarf' => [
                'mass' => [0.01, 0.1],
                'radius' => [0.1, 0.4],
                'temp_range' => [-200, -100],
                'rotation' => [20, 100], // heures
            ],
            'lava' => [
                'mass' => [0.5, 2],
                'radius' => [0.8, 1.5],
                'temp_range' => [100, 500],
                'rotation' => [5, 30], // heures
            ],
            'carbon' => [
                'mass' => [0.5, 3],
                'radius' => [0.7, 1.8],
                'temp_range' => [-50, 200],
                'rotation' => [10, 50], // heures
            ],
            'ocean' => [
                'mass' => [0.8, 3.0],
                'radius' => [0.9, 1.8],
                'temp_range' => [-10, 40],
                'rotation' => [20, 48], // heures
            ]
        ];

        $planetNames = [
            'Kepler', 'Gliese', 'TRAPPIST', 'Proxima', 'Terra', 'Aqua', 'Ignis', 'Glacies',
            'Ventus', 'Umbra', 'Lux', 'Petra', 'Aureus', 'Viridis', 'Rubeus', 'Caelum',
            'Oceanus', 'Montanus', 'Crystallum', 'Metalum', 'Gaseous', 'Frigidus', 'Calidus'
        ];

        $planetsCreated = 0;

        foreach ($stars as $star) {
            $numberOfPlanets = $this->getPlanetCountByStarType($star->star_type);
            $usedDistances = [];
            
            for ($i = 0; $i < $numberOfPlanets; $i++) {
                $planetType = $this->selectPlanetType($i, $numberOfPlanets);
                $typeData = $planetTypeData[$planetType];
                
                // Générer une distance orbitale unique
                do {
                    $distance = $this->generateOrbitDistance($i, $star);
                } while (in_array($distance, $usedDistances));
                $usedDistances[] = $distance;
                
                // Calculer période orbitale (3ème loi de Kepler simplifiée)
                $orbitalPeriod = pow($distance, 1.5) * 365.25; // jours
                
                // Calculer periapsis/apoapsis avec excentricité réaliste
                $eccentricity = rand(0, 30) / 100; // 0 à 0.3
                $semiMajorAxis = $distance;
                $periapsis = $semiMajorAxis * (1 - $eccentricity);
                $apoapsis = $semiMajorAxis * (1 + $eccentricity);
                
                // Calculer température basée sur distance et type d'étoile
                $baseTemp = $this->calculatePlanetTemperature($distance, $star->star_luminosity, $star->star_surface_temp);
                $temperature = $baseTemp + rand($typeData['temp_range'][0], $typeData['temp_range'][1]);
                
                $planetName = $planetNames[array_rand($planetNames)] . ' ' . chr(65 + $i); // A, B, C...
                
                Planet::create([
                    'planet_name' => $planetName,
                    'planet_desc' => "Planète {$planetType} orbitant autour de {$star->star_name}",
                    'planet_type' => $planetType,
                    'planet_mass' => $this->randomFloat($typeData['mass'][0], $typeData['mass'][1]),
                    'planet_diameter' => $this->randomFloat($typeData['radius'][0], $typeData['radius'][1]) * 2 * 6371, // Conversion en km
                    'planet_average_distance' => $distance,
                    'planet_orbital_period' => $orbitalPeriod,
                    'planet_rotation_period' => $this->randomFloat($typeData['rotation'][0], $typeData['rotation'][1]),
                    'planet_surface_temp' => $temperature,
                    'planet_gravity' => $this->calculateGravity($this->randomFloat($typeData['mass'][0], $typeData['mass'][1]), $this->randomFloat($typeData['radius'][0], $typeData['radius'][1])),
                    'planet_orbital_longitude' => rand(0, 360),
                    'planet_eccentricity' => $eccentricity,
                    'planet_perigee' => $periapsis,
                    'planet_apogee' => $apoapsis,
                    'planet_orbital_inclination' => rand(0, 180),
                    'planet_inclination_angle' => rand(0, 180),
                    'planet_rings' => rand(0, 10),
                    'planet_initial_x' => rand(-1000, 1000),
                    'planet_initial_y' => rand(-1000, 1000),
                    'planet_initial_z' => rand(-1000, 1000),
                    'user_id' => $star->user_id,
                    'star_id' => $star->star_id,
                ]);

                $planetsCreated++;
            }
        }

        $this->command->info("{$planetsCreated} planètes créées avec succès !");
    }

    private function getPlanetCountByStarType($starType)
    {
        $planetCounts = [
            'brown_dwarf' => [0, 2],
            'red_dwarf' => [1, 4],
            'yellow_dwarf' => [2, 8],
            'white_dwarf' => [0, 1],
            'red_giant' => [0, 3],
            'blue_giant' => [1, 5],
            'red_supergiant' => [0, 2],
            'blue_supergiant' => [0, 3],
            'hypergiant' => [0, 1],
            'neutron_star' => [0, 0],
            'pulsar' => [0, 1],
            'variable' => [1, 6],
            'binary' => [2, 10],
            'ternary' => [3, 12]
        ];

        $range = $planetCounts[$starType] ?? [1, 5];
        return rand($range[0], $range[1]);
    }

    private function selectPlanetType($planetIndex, $totalPlanets)
    {
        // Logique réaliste : planètes rocheuses plus proches, gazeuses plus loin
        $ratio = $planetIndex / max($totalPlanets - 1, 1);
        
        if ($ratio < 0.4) {
            // Planètes internes
            return ['terrestrial', 'lava', 'dwarf'][array_rand(['terrestrial', 'lava', 'dwarf'])];
        } elseif ($ratio < 0.7) {
            // Zone médiane
            return ['super_earth', 'sub_neptune', 'carbon'][array_rand(['super_earth', 'sub_neptune', 'carbon'])];
        } else {
            // Planètes externes
            return ['gas', 'ice', 'ocean'][array_rand(['gas', 'ice', 'ocean'])];
        }
    }

    private function generateOrbitDistance($planetIndex, $star)
    {
        // Distance basée sur la séquence de Titius-Bode modifiée
        $baseDistance = 0.3; // UA
        $factor = 1.6; // Facteur de progression
        
        return $baseDistance * pow($factor, $planetIndex) * rand(80, 120) / 100;
    }

    private function calculatePlanetTemperature($distance, $starLuminosity, $starTemp)
    {
        // Température d'équilibre simplifiée
        $solarConstant = 1361; // W/m²
        $receivedEnergy = $solarConstant * $starLuminosity / pow($distance, 2);
        return pow($receivedEnergy / (4 * 5.67e-8), 0.25) - 273.15; // Celsius
    }

    private function randomFloat($min, $max)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    private function calculateGravity($mass, $radius)
    {
        // Calcul de la gravité basée sur la masse et le rayon
        $gravitationalConstant = 6.67430e-11; // Constante gravitationnelle (m^3 kg^-1 s^-2)
        $gravity = $gravitationalConstant * $mass / pow($radius, 2);
        return $gravity; // m/s²
    }
}
