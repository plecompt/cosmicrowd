<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Galaxy;
use App\Models\SolarSystem;
use App\Utils\Vector3;
use App\Utils\StellarRanges;

class GalaxySeeder extends Seeder
{
    // ✅ TOUS LES TYPES avec probabilités réalistes
    private const STAR_TYPES = [
        // 🔴 NAINES (75% de toutes les étoiles)
        'red_dwarf' => 47,      // Les + communes
        'brown_dwarf' => 15,    // Assez communes
        'yellow_dwarf' => 5,    // Comme le Soleil (rares !)
        'white_dwarf' => 8,     // Restes d'étoiles mortes
        
        // 🔥 GÉANTES (10% - Étoiles évoluées)
        'red_giant' => 6,       // Étoiles vieillissantes
        'blue_giant' => 4,      // Chaudes et massives
        
        // 💥 SUPERGÉANTES (6% - Très rares)
        'red_supergiant' => 3,  // Bételgeuse, Antarès
        'blue_supergiant' => 3, // Rigel, Deneb
        
        // 🌟 EXTRÊMES (6% - Ultra rares)
        'hypergiant' => 2,      // Les + grandes étoiles
        'neutron_star' =>1,    // Résidus supernovae
        'pulsar' => 1,          // Étoiles à neutrons rotatives
        'variable' => 2,        // Luminosité variable
        
        // 🌠 SYSTÈMES (2.3% - Très spéciaux)
        'binary' => 1.3,        // Systèmes doubles
        'ternary' => 1,       // Systèmes triples
        
        // ⚫ BLACK HOLES (0.6% - Extrêmement rares)
        'black_hole' => 0.7,    // Trous noirs
    ];

    // Config galaxie simple

    private const CONFIG = [
        'NUM_SYSTEMS' => 2000, // per ARMS!
        'NUM_ARMS' => 4,
        'GALAXY_THICKNESS' => 5,
        'CORE_X_DIST' => 33,
        'CORE_Y_DIST' => 33,
        'GALAXY_RADIUS' => 1000,//200 * galaxy_thickness ?
        'ARM_X_DIST' => 100,
        'ARM_Y_DIST' => 50,
        'ARM_X_MEAN' => 50, //where the arm is centered
        'ARM_Y_MEAN' => 25, //where the arm is centered
        'SPIRAL_FORCE' => 2.0, //how strong the arms must be
    ];

    public function run()
    {
        echo "🌌 Génération galaxie CosmiCrowd...\n";
        
        // 1. Créer la galaxie
        $galaxy = $this->createGalaxy();
        echo "✅ Galaxie créée : {$galaxy->galaxy_name}\n";
        
        // 2. Générer les systèmes solaires
        $this->generateSolarSystems($galaxy->galaxy_id);
        echo "✅ " . self::CONFIG['NUM_SYSTEMS'] * self::CONFIG['NUM_ARMS'] . " systèmes générés !\n";
        
        // 3. Statistiques
        $this->showStatistics();
    }

    private function createGalaxy(): Galaxy
    {
        return Galaxy::create([
            'galaxy_name' => 'CosmiCrowd Galaxy',
            'galaxy_desc' => 'Galaxie spirale collaborative générée pour CosmiCrowd',
            'galaxy_size' => self::CONFIG['GALAXY_RADIUS'],
            'galaxy_age' => rand(8, 14) // Milliards d'années
        ]);
    }

    /**
     * Generate random number following Gaussian/Normal distribution
     * Uses Box-Muller transformation
     */
    private function gaussianRandom(float $center = 0.0, float $deviation = 1.0): float {
        $u = mt_rand() / mt_getrandmax();
        $v = mt_rand() / mt_getrandmax();
        
        $z = sqrt(-2.0 * log($u)) * cos(2.0 * M_PI * $v);
        
        return $z * $deviation + $center;
    }


    private function generateSolarSystems(int $galaxyId){

        for($i=0; $i < self::CONFIG['NUM_ARMS']; $i++){
            for($j=0; $j < self::CONFIG['NUM_SYSTEMS']; $j++){
                $position = $this->spiral($this->gaussianRandom(self::CONFIG['ARM_X_MEAN'], self::CONFIG['ARM_X_DIST']), $this->gaussianRandom(self::CONFIG['ARM_Y_MEAN'], self::CONFIG['ARM_Y_DIST']), $this->gaussianRandom(0, self::CONFIG['GALAXY_THICKNESS']), $i * 2 * M_PI / self::CONFIG['NUM_ARMS']);

                // Type d'étoile avec probabilité
                $starType = $this->getRandomStarType();
                
                // Propriétés selon le type
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
                    'galaxy_id' => $galaxyId
                ]);
            }
        }
    }

    private function spiral(float $x, float $y, float $z, float $offset){
        $r = sqrt($x**2 + $y**2);
        $theta = $offset;
        $theta += $x > 0 ? atan($y/$x) : atan($y/$x) + M_PI;
        $theta += ($r / self::CONFIG['ARM_X_DIST']) * self::CONFIG['SPIRAL_FORCE'];

        return new Vector3($r * cos($theta), $r * sin($theta), $z);
    }

    private function getRandomStarType(): string
    {
        $random = rand(1, 1000) / 10; // Précision 0.1%
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
        
        // ⚫ Noms spéciaux pour certains types
        $specialNames = match($type) {
            'black_hole' => ['Sagittarius A*', 'Cygnus X-1', 'V404 Cygni', 'GRO J1655-40'],
            'pulsar' => ['PSR J', 'B1919+21', 'Vela', 'Crab Pulsar'],
            'neutron_star' => ['RX J', 'PSR', 'SGR', 'AXP'],
            'hypergiant' => ['VY Canis', 'UY Scuti', 'Stephenson', 'Westerlund'],
            default => null
        };
        
        if ($specialNames && rand(1, 3) === 1) {
            return $specialNames[array_rand($specialNames)] . ' ' . rand(1000, 9999);
        }
        
        return $prefixes[array_rand($prefixes)] . ' ' . 
               $suffixes[array_rand($suffixes)] . ' ' . 
               rand(100, 9999);
    }

    private function generateDescription(string $type): string
    {
        return match($type) {
            'red_dwarf' => 'Naine rouge, étoile froide et de longue durée de vie',
            'brown_dwarf' => 'Naine brune, "étoile ratée" qui ne peut fusionner l\'hydrogène',
            'yellow_dwarf' => 'Naine jaune similaire à notre Soleil',
            'white_dwarf' => 'Naine blanche, résidu dense d\'une étoile morte',
            'red_giant' => 'Géante rouge en fin de vie, atmosphère étendue',
            'blue_giant' => 'Géante bleue chaude et massive',
            'red_supergiant' => 'Supergéante rouge, parmi les plus grandes étoiles',
            'blue_supergiant' => 'Supergéante bleue extrêmement chaude et lumineuse',
            'hypergiant' => 'Hypergéante, étoile de taille extraordinaire',
            'neutron_star' => 'Étoile à neutrons ultra-dense, résidu de supernova',
            'pulsar' => 'Pulsar, étoile à neutrons émettant des faisceaux radio',
            'variable' => 'Étoile variable à luminosité changeante',
            'binary' => 'Système binaire de deux étoiles en orbite',
            'ternary' => 'Système ternaire de trois étoiles liées',
            'black_hole' => 'Trou noir, région où rien ne peut s\'échapper',
            default => 'Système stellaire généré automatiquement'
        };
    }

    private function showStatistics(): void
    {
        echo "\n📊 STATISTIQUES DE LA GALAXIE :\n";
        
        $stats = [];
        foreach (self::STAR_TYPES as $type => $probability) {
            $count = SolarSystem::where('solar_system_type', $type)->count();
            if ($count > 0) {
                $stats[$type] = $count;
            }
        }
        
        arsort($stats);
        
        foreach ($stats as $type => $count) {
            $percentage = round(($count / self::CONFIG['NUM_SYSTEMS']) * 100, 1);
            echo "  🌟 {$type}: {$count} ({$percentage}%)\n";
        }
        
        echo "\n🎯 Galaxie CosmiCrowd générée avec succès !\n";
    }
}
