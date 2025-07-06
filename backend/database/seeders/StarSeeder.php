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
        'neutron_star' => [
            'gravity' => [1e11, 1e12], 'temp' => [600000, 1000000], 'luminosity' => [0.001, 0.1],
            'mass' => [1.17, 2.16], 'radius' => [0.00001, 0.00002]
        ],
        'black_hole' => [
            'gravity' => [1e15, 1e16], 'temp' => [0, 100], 'luminosity' => [0, 0],
            'mass' => [3, 100], 'radius' => [0.00001, 0.001]
        ]
    ];

    private $starNames = [
        'Proxima', 'Kepler', 'Gliese', 'Vega', 'Altair', 'Rigel', 'Betelgeuse', 'Sirius',
        'Canopus', 'Arcturus', 'Capella', 'Aldebaran', 'Spica', 'Antares', 'Fomalhaut',
        'Deneb', 'Regulus', 'Adhara', 'Castor', 'Procyon', 'Achernar', 'Bellatrix'
    ];

    public function run()
    {
        $users = User::all();
        $galaxy = Galaxy::first();
        
        if (!$galaxy) {
            $this->command->error("No galaxy found! Run GalaxySeeder first.");
            return;
        }

        $starsCreated = 0;

        foreach ($users as $user) {
            $numberOfStars = rand(1, 3); // 1 to 3 stars per user
            
            for ($i = 0; $i < $numberOfStars; $i++) {
                $this->createSingleStar($user->user_id, $galaxy);
                $starsCreated++;
            }
        }

        $this->command->info("{$starsCreated} stars created successfully!");
    }

    public function generateStars(int $count, ?int $userId = null): int
    {
        $users = User::all();
        $galaxy = Galaxy::first();
        
        if (!$galaxy) {
            throw new \Exception("No galaxy found!");
        }

        $starsCreated = 0;

        for ($i = 0; $i < $count; $i++) {
            $assignedUserId = $userId ?? $users->random()->user_id;
            $this->createSingleStar($assignedUserId, $galaxy);
            $starsCreated++;
        }

        return $starsCreated;
    }

    private function createSingleStar(int $userId, Galaxy $galaxy)
    {
        $starType = array_rand($this->starTypeData);
        $typeData = $this->starTypeData[$starType];
        $starName = $this->starNames[array_rand($this->starNames)] . '-' . uniqid();
        
        // Random position in galaxy
        $coordinates = $this->generateGalaxyPosition($galaxy);
        
        Star::create([
            'star_name' => $starName,
            'star_desc' => "Star of type {$starType}",
            'star_type' => $starType,
            'star_gravity' => $this->randomFloat($typeData['gravity'][0], $typeData['gravity'][1]),
            'star_surface_temp' => $this->randomFloat($typeData['temp'][0], $typeData['temp'][1]),
            'star_luminosity' => $this->randomFloat($typeData['luminosity'][0], $typeData['luminosity'][1]),
            'star_mass' => $this->randomFloat($typeData['mass'][0], $typeData['mass'][1]),
            'star_diameter' => $this->randomFloat($typeData['radius'][0], $typeData['radius'][1]) * 2 * 6371,
            'star_initial_x' => $coordinates['x'],
            'star_initial_y' => $coordinates['y'],
            'star_initial_z' => $coordinates['z'],
            'user_id' => $userId,
            'galaxy_id' => $galaxy->galaxy_id,
        ]);
    }

    private function generateGalaxyPosition(Galaxy $galaxy): array
    {
        $radius = $galaxy->galaxy_size / 10; // Scale for Three.js
        
        // Simple spiral galaxy distribution
        $distance = sqrt($this->randomFloat(0, 1)) * $radius;
        $angle = $this->randomFloat(0, 2 * M_PI);
        $height = $this->randomFloat(-100, 100);
        
        return [
            'x' => round($distance * cos($angle)),
            'y' => round($height),
            'z' => round($distance * sin($angle))
        ];
    }
    
    private function randomFloat($min, $max)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}