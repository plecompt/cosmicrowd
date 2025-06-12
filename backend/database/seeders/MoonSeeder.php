<?php

namespace Database\Seeders;

use App\Models\Moon;
use App\Models\Planet;
use Illuminate\Database\Seeder;

class MoonSeeder extends Seeder
{
    public function run()
    {
        $planets = Planet::with('star')->get();
        
        // Données réalistes pour chaque type de lune
        $moonTypeData = [
            'rocky' => [
                'mass' => [0.001, 0.2], // Masses terrestres
                'radius' => [0.1, 0.6], // Rayons terrestres
                'temp_modifier' => [-10, 10], // Modification température planète
            ],
            'icy' => [
                'mass' => [0.0005, 0.15],
                'radius' => [0.08, 0.5],
                'temp_modifier' => [-30, -10],
            ],
            'mixed' => [
                'mass' => [0.001, 0.3],
                'radius' => [0.15, 0.8],
                'temp_modifier' => [-20, 20],
            ],
            'primitive' => [
                'mass' => [0.00001, 0.01],
                'radius' => [0.01, 0.15],
                'temp_modifier' => [-20, 5],
            ],
            'regular' => [
                'mass' => [0.001, 0.4],
                'radius' => [0.2, 1.0],
                'temp_modifier' => [-15, 15],
            ],
            'irregular' => [
                'mass' => [0.0001, 0.05],
                'radius' => [0.05, 0.3],
                'temp_modifier' => [-25, 25],
            ],
            'trojan' => [
                'mass' => [0.0001, 0.1],
                'radius' => [0.1, 0.4],
                'temp_modifier' => [-15, 15],
            ],
            'coorbital' => [
                'mass' => [0.0005, 0.2],
                'radius' => [0.15, 0.5],
                'temp_modifier' => [-10, 10],
            ]
        ];

        $moonNames = [
            'Luna', 'Selene', 'Cynthia', 'Diana', 'Artemis', 'Hecate', 'Phoebe', 'Titania',
            'Oberon', 'Miranda', 'Ariel', 'Umbriel', 'Callisto', 'Ganymede', 'Io', 'Europa',
            'Titan', 'Enceladus', 'Mimas', 'Iapetus', 'Rhea', 'Dione', 'Tethys', 'Hyperion',
            'Triton', 'Nereid', 'Proteus', 'Charon', 'Nix', 'Hydra', 'Styx', 'Kerberos',
            'Phobos', 'Deimos', 'Amalthea', 'Thebe', 'Adrastea', 'Metis', 'Lysithea'
        ];

        $moonsCreated = 0;

        foreach ($planets as $planet) {
            $numberOfMoons = $this->getMoonCountByPlanetType($planet->planet_type, $planet->planet_mass);
            
            if ($numberOfMoons == 0) continue;
            
            $usedDistances = [];
            
            for ($i = 0; $i < $numberOfMoons; $i++) {
                $moonType = $this->selectMoonType($planet->planet_type, $i);
                $typeData = $moonTypeData[$moonType];
                
                // Générer distance orbitale (en rayons planétaires)
                $maxAttempts = 10;
                $attempts = 0;
                do {
                    $distanceInRadii = $this->generateMoonDistance($i, $planet, $moonType);
                    $distance = $distanceInRadii * $planet->planet_radius * 6371; // km
                    $attempts++;
                } while (in_array(round($distance), $usedDistances) && $attempts < $maxAttempts);
                
                // Si on n'a pas trouvé de distance unique après maxAttempts, on utilise la dernière générée
                $usedDistances[] = round($distance);
                
                // Calculer période orbitale (lois de Kepler pour satellite)
                $planetMass = max($planet->planet_mass * 5.972e24, 1); // kg, minimum 1 kg pour éviter division par zéro
                $orbitalPeriod = 2 * pi() * sqrt(pow($distance * 1000, 3) / 
                               (6.674e-11 * $planetMass)) / 3600; // heures
                
                // Rotation (souvent verrouillée par effet de marée)
                $rotationPeriod = $orbitalPeriod;
                if ($moonType === 'captured_asteroid' || $moonType === 'debris') {
                    $rotationPeriod = rand(1, 50); // Rotation chaotique
                }
                
                // Calculer periapsis/apoapsis
                $eccentricity = $this->getMoonEccentricity($moonType);
                $semiMajorAxis = $distance;
                $periapsis = $semiMajorAxis * (1 - $eccentricity);
                $apoapsis = $semiMajorAxis * (1 + $eccentricity);
                
                // Température basée sur celle de la planète
                $baseTemp = $planet->planet_temperature ?? -50;
                $tempModifier = rand($typeData['temp_modifier'][0], $typeData['temp_modifier'][1]);
                $temperature = $baseTemp + $tempModifier;
                
                $moonName = $moonNames[array_rand($moonNames)] . ($numberOfMoons > 1 ? ' ' . chr(65 + $i) : '');
                
                Moon::create([
                    'moon_name' => $moonName,
                    'moon_desc' => "Lune de type {$moonType} orbitant autour de {$planet->planet_name}",
                    'moon_type' => $moonType,
                    'moon_mass' => $this->randomFloat($typeData['mass'][0], $typeData['mass'][1]),
                    'moon_diameter' => $this->randomFloat($typeData['radius'][0], $typeData['radius'][1]) * 2 * 6371, // Conversion en km
                    'moon_average_distance' => $distance,
                    'moon_orbital_period' => $orbitalPeriod,
                    'moon_rotation_period' => $rotationPeriod,
                    'moon_surface_temp' => $temperature,
                    'moon_gravity' => $this->calculateGravity($this->randomFloat($typeData['mass'][0], $typeData['mass'][1]), $this->randomFloat($typeData['radius'][0], $typeData['radius'][1])),
                    'moon_orbital_longitude' => rand(0, 360),
                    'moon_eccentricity' => $eccentricity,
                    'moon_perigee' => $periapsis,
                    'moon_apogee' => $apoapsis,
                    'moon_orbital_inclination' => rand(0, 180),
                    'moon_inclination_angle' => rand(0, 180),
                    'moon_rings' => rand(0, 5),
                    'moon_initial_x' => rand(-1000, 1000),
                    'moon_initial_y' => rand(-1000, 1000),
                    'moon_initial_z' => rand(-1000, 1000),
                    'user_id' => $planet->user_id,
                    'planet_id' => $planet->planet_id,
                ]);

                $moonsCreated++;
            }
        }

        $this->command->info("{$moonsCreated} lunes créées avec succès !");
    }

    private function getMoonCountByPlanetType($planetType, $planetMass)
    {
        // Nombre de lunes selon le type et masse de la planète
        $moonCounts = [
            'terrestrial' => $planetMass > 1 ? [0, 2] : [0, 1],
            'gas' => [2, 15], // Les géantes gazeuses ont beaucoup de lunes
            'ice' => [0, 3],
            'super_earth' => [0, 3],
            'sub_neptune' => [0, 4],
            'dwarf' => [0, 1],
            'lava' => [0, 1],
            'carbon' => [0, 2],
            'ocean' => [0, 2]
        ];

        $range = $moonCounts[$planetType] ?? [0, 1];
        return rand($range[0], $range[1]);
    }

    private function selectMoonType($planetType, $moonIndex)
    {
        // Distribution réaliste selon le type de planète
        $distributions = [
            'terrestrial' => ['rocky' => 70, 'primitive' => 30],
            'gas' => ['icy' => 40, 'rocky' => 30, 'mixed' => 15, 'primitive' => 10, 'irregular' => 5],
            'ice' => ['icy' => 80, 'rocky' => 15, 'irregular' => 5],
            'super_earth' => ['rocky' => 50, 'mixed' => 30, 'regular' => 20],
            'sub_neptune' => ['icy' => 40, 'mixed' => 30, 'regular' => 30],
            'dwarf' => ['primitive' => 60, 'irregular' => 40],
            'lava' => ['rocky' => 50, 'mixed' => 40, 'irregular' => 10],
            'carbon' => ['rocky' => 60, 'mixed' => 25, 'primitive' => 15],
            'ocean' => ['icy' => 50, 'mixed' => 30, 'regular' => 20]
        ];

        $dist = $distributions[$planetType] ?? ['rocky' => 100];
        return $this->weightedRandom($dist);
    }

    private function generateMoonDistance($moonIndex, $planet, $moonType)
    {
        // Distance en rayons planétaires
        $baseDistances = [
            'rocky' => [5, 25],
            'icy' => [10, 50],
            'mixed' => [8, 35],
            'primitive' => [30, 200],
            'regular' => [15, 40],
            'irregular' => [20, 100],
            'trojan' => [50, 150],
            'coorbital' => [40, 120]
        ];

        $range = $baseDistances[$moonType] ?? [5, 30];
        return rand($range[0], $range[1]) * (1 + $moonIndex * 0.5);
    }

    private function getMoonEccentricity($moonType)
    {
        $eccentricities = [
            'rocky' => 0.05,
            'icy' => 0.1,
            'mixed' => 0.08,
            'primitive' => 0.4,
            'regular' => 0.02,
            'irregular' => 0.3,
            'trojan' => 0.15,
            'coorbital' => 0.1
        ];

        return $eccentricities[$moonType] ?? 0.1;
    }

    private function weightedRandom($weights)
    {
        $total = array_sum($weights);
        $random = rand(1, $total);
        
        foreach ($weights as $type => $weight) {
            $random -= $weight;
            if ($random <= 0) {
                return $type;
            }
        }
        
        return array_key_first($weights);
    }

    private function randomFloat($min, $max)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    private function calculateGravity($mass, $radius)
    {
        // Calcul de la gravité basée sur la masse et le rayon
        // Éviter la division par zéro en s'assurant que le rayon est au moins 1 km
        $radiusKm = max($radius * 1000, 1);
        return 6.674e-11 * $mass / pow($radiusKm, 2); // m/s²
    }
}
