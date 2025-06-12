<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GalaxySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('galaxy')->insert([
            [
                'galaxy_name' => 'Voie Lactée',
                'galaxy_desc' => 'Notre galaxie spirale, abritant notre système solaire.',
                'galaxy_size' => 100000,
                'galaxy_age' => 13600,
            ],
            [
                'galaxy_name' => 'Andromède',
                'galaxy_desc' => 'La plus grande galaxie du Groupe Local, en collision avec la Voie Lactée.',
                'galaxy_size' => 220000,
                'galaxy_age' => 10000,
            ],
            [
                'galaxy_name' => 'Grand Nuage de Magellan',
                'galaxy_desc' => 'Une galaxie naine irrégulière, satellite de la Voie Lactée.',
                'galaxy_size' => 14000,
                'galaxy_age' => 13000,
            ],
        ]);
    }
}
