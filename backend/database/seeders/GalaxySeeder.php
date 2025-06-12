<?php

namespace Database\Seeders;

use App\Models\Galaxy;
use Illuminate\Database\Seeder;

class GalaxySeeder extends Seeder
{
    public function run()
    {
        $galaxies = [
            [
                'galaxy_name' => 'CosmiCrowd Alpha',
                'galaxy_desc' => 'La galaxie principale de CosmiCrowd où tous les explorateurs créent leurs systèmes stellaires.',
                'galaxy_size' => 100000, // années-lumière de diamètre
                'galaxy_age' => 13500, // millions d\'années
            ],
            [
                'galaxy_name' => 'Nébuleuse d\'Andromède',
                'galaxy_desc' => 'Une galaxie spirale majestueuse, zone d\'expansion pour les explorateurs expérimentés.',
                'galaxy_size' => 220000,
                'galaxy_age' => 10000,
            ],
            [
                'galaxy_name' => 'Voie Lactée Beta',
                'galaxy_desc' => 'Réplique de notre galaxie natale, parfaite pour les débutants.',
                'galaxy_size' => 105000,
                'galaxy_age' => 13600,
            ],
            [
                'galaxy_name' => 'Galaxie du Tourbillon',
                'galaxy_desc' => 'Une galaxie aux bras spiraux parfaits, idéale pour les créations artistiques.',
                'galaxy_size' => 76000,
                'galaxy_age' => 8500,
            ],
            [
                'galaxy_name' => 'Centaurus A',
                'galaxy_desc' => 'Une galaxie elliptique unique aux propriétés gravitationnelles particulières.',
                'galaxy_size' => 60000,
                'galaxy_age' => 12000,
            ]
        ];

        foreach ($galaxies as $galaxy) {
            Galaxy::create($galaxy);
        }

        $this->command->info('5 galaxies créées avec succès !');
        $this->command->info('Galaxie principale : CosmiCrowd Alpha (ID: 1)');
    }
}
