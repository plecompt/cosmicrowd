<?php

namespace Database\Seeders;

use App\Models\Star;
use App\Models\User;
use App\Models\Galaxy;
use Illuminate\Database\Seeder;

class StarSeeder extends Seeder
{
    private $starTypeData = [
        'brown_dwarf' => [
            'gravity' => [10, 100], 'temp' => [500, 2000], 'luminosity' => [0.00001, 0.0001],
            'mass' => [0.01, 0.08], 'radius' => [0.7, 1.0]
        ],
        'red_dwarf' => [
            'gravity' => [50, 300], 'temp' => [2300, 3800], 'luminosity' => [0.0001, 0.08],
            'mass' => [0.08, 0.6], 'radius' => [0.1, 0.7]
        ],
        'yellow_dwarf' => [
            'gravity' => [200, 400], 'temp' => [5200, 6000], 'luminosity' => [0.6, 1.5],
            'mass' => [0.8, 1.04], 'radius' => [0.96, 1.15]
        ],
        'white_dwarf' => [
            'gravity' => [100000, 1000000], 'temp' => [8000, 40000], 'luminosity' => [0.0001, 0.1],
            'mass' => [0.17, 1.33], 'radius' => [0.008, 0.02]
        ],
        'red_giant' => [
            'gravity' => [0.5, 5], 'temp' => [3000, 4000], 'luminosity' => [10, 1000],
            'mass' => [0.3, 8], 'radius' => [10, 100]
        ],
        'blue_giant' => [
            'gravity' => [50, 200], 'temp' => [20000, 50000], 'luminosity' => [10000, 1000000],
            'mass' => [10, 50], 'radius' => [7, 15]
        ],
        'red_supergiant' => [
            'gravity' => [0.1, 1], 'temp' => [3200, 4500], 'luminosity' => [1000, 500000],
            'mass' => [10, 40], 'radius' => [200, 1500]
        ],
        'blue_supergiant' => [
            'gravity' => [10, 100], 'temp' => [20000, 50000], 'luminosity' => [10000, 1000000],
            'mass' => [20, 90], 'radius' => [15, 25]
        ],
        'hypergiant' => [
            'gravity' => [0.01, 0.1], 'temp' => [3000, 35000], 'luminosity' => [100000, 5000000],
            'mass' => [100, 300], 'radius' => [1000, 2000]
        ],
        'neutron_star' => [
            'gravity' => [1e11, 1e12], 'temp' => [600000, 1000000], 'luminosity' => [0.001, 0.1],
            'mass' => [1.17, 2.16], 'radius' => [0.00001, 0.00002]
        ],
        'pulsar' => [
            'gravity' => [1e11, 1e12], 'temp' => [1000000, 3000000], 'luminosity' => [0.001, 0.1],
            'mass' => [1.17, 2.16], 'radius' => [0.00001, 0.00002]
        ],
        'variable' => [
            'gravity' => [1, 1000], 'temp' => [3000, 30000], 'luminosity' => [0.1, 10000],
            'mass' => [0.5, 20], 'radius' => [1, 200]
        ],
        'binary' => [
            'gravity' => [10, 500], 'temp' => [3000, 40000], 'luminosity' => [0.1, 1000],
            'mass' => [0.5, 10], 'radius' => [0.5, 20]
        ],
        'ternary' => [
            'gravity' => [10, 500], 'temp' => [3000, 40000], 'luminosity' => [0.1, 1000],
            'mass' => [0.5, 15], 'radius' => [0.5, 25]
        ]
    ];

    private $starNames = [
        'Proxima', 'Kepler', 'Gliese', 'Vega', 'Altair', 'Rigel', 'Betelgeuse', 'Sirius',
        'Canopus', 'Arcturus', 'Capella', 'Aldebaran', 'Spica', 'Antares', 'Fomalhaut',
        'Deneb', 'Regulus', 'Adhara', 'Castor', 'Procyon', 'Achernar', 'Bellatrix',
        'Elnath', 'Miaplacidus', 'Alnilam', 'Alnair', 'Alioth', 'Dubhe', 'Mirfak'
    ];

     /**
     * 🎯 Comportement par défaut : 1 à 3 étoiles par utilisateur
     */
    public function run()
    {
        $users = User::all();
        $galaxy = Galaxy::first();
        
        if (!$galaxy) {
            $this->command->error("❌ Aucune galaxie trouvée ! Exécutez d'abord GalaxySeeder.");
            return;
        }

        $starsCreated = 0;

        foreach ($users as $user) {
            $numberOfStars = rand(1, 3); // 1 à 3 étoiles par utilisateur
            
            for ($i = 0; $i < $numberOfStars; $i++) {
                $this->createSingleStar($user->user_id, $galaxy);
                $starsCreated++;
            }
        }

        $this->command->info("✅ {$starsCreated} étoiles créées avec le comportement par défaut !");
    }

    /**
     * 🚀 Méthode pour générer x étoiles (utilisée par la commande personnalisée)
     */
    public function generateStars(int $count, ?int $userId = null): int
    {
        $users = User::all();
        $galaxy = Galaxy::first();
        
        if (!$galaxy) {
            throw new \Exception("❌ Aucune galaxie trouvée !");
        }

        $starsCreated = 0;

        for ($i = 0; $i < $count; $i++) {
            // Si userId fourni : utiliser cet utilisateur, sinon utilisateur aléatoire
            $assignedUserId = $userId ?? $users->random()->user_id;
            
            $this->createSingleStar($assignedUserId, $galaxy);
            $starsCreated++;
        }

        return $starsCreated;
    }

    /**
     * 🛠️ Crée une seule étoile
     */
    private function createSingleStar(int $userId, Galaxy $galaxy)
    {
        // Type d'étoile aléatoire
        $starType = array_rand($this->starTypeData);
        $typeData = $this->starTypeData[$starType];
        
        // Nom aléatoire unique
        $starName = $this->starNames[array_rand($this->starNames)] . '-' . uniqid();
        
        Star::create([
            'star_name' => $starName,
            'star_desc' => "Étoile de type {$starType}",
            'star_type' => $starType,
            'star_gravity' => $this->randomFloat($typeData['gravity'][0], $typeData['gravity'][1]),
            'star_surface_temp' => $this->randomFloat($typeData['temp'][0], $typeData['temp'][1]),
            'star_luminosity' => $this->randomFloat($typeData['luminosity'][0], $typeData['luminosity'][1]),
            'star_mass' => $this->randomFloat($typeData['mass'][0], $typeData['mass'][1]),
            'star_diameter' => $this->randomFloat($typeData['radius'][0], $typeData['radius'][1]) * 2 * 6371,
            'star_initial_x' => rand(-1000, 1000),
            'star_initial_y' => rand(-1000, 1000),
            'star_initial_z' => rand(-1000, 1000),
            'user_id' => $userId,
            'galaxy_id' => $galaxy->galaxy_id,
        ]);
    }

    private function randomFloat($min, $max)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}