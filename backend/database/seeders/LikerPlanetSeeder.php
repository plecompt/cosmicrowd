<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Planet;
use App\Models\LikerPlanet;

class LikerPlanetSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $planets = Planet::with('star')->get();

        if ($users->isEmpty() || $planets->isEmpty()) {
            $this->command->warn('⚠️  Aucun utilisateur ou planète trouvé. Exécutez UserSeeder et PlanetSeeder d\'abord.');
            return;
        }

        $likesData = [];
        $totalLikes = 0;

        foreach ($users as $user) {
            // Chaque utilisateur like en moyenne 3-15 planètes d'autres utilisateurs
            $likesCount = rand(1, 20);
            
            // Récupérer les planètes des AUTRES utilisateurs
            $otherUsersPlanets = $planets->where('user_id', '!=', $user->user_id);
            
            if ($otherUsersPlanets->isEmpty()) continue;

            // Probabilité de liker selon le type de planète
            $planetTypePreferences = [
                'terrestrial' => 25,  // Très populaires
                'gas_giant' => 20,    // Populaires
                'ice_giant' => 15,    // Moyennement populaires
                'super_earth' => 20,  // Populaires
                'hot_jupiter' => 10,  // Moins populaires
                'mini_neptune' => 8,  // Moins populaires
                'lava_world' => 2     // Rares mais fascinantes
            ];

            // Sélectionner des planètes selon les préférences
            $selectedPlanets = collect();
            foreach ($otherUsersPlanets as $planet) {
                $preference = $planetTypePreferences[$planet->planet_type] ?? 10;
                if (rand(1, 100) <= $preference) {
                    $selectedPlanets->push($planet);
                }
            }

            // Limiter au nombre de likes souhaité
            $planetsToLike = $selectedPlanets->random(min($likesCount, $selectedPlanets->count()));

            foreach ($planetsToLike as $planet) {
                // Éviter les doublons
                $alreadyExists = collect($likesData)->contains(function ($like) use ($user, $planet) {
                    return $like['user_id'] == $user->user_id && $like['planet_id'] == $planet->planet_id;
                });

                if (!$alreadyExists) {
                    $likesData[] = [
                        'user_id' => $user->user_id,
                        'planet_id' => $planet->planet_id,
                        'liker_planet_date' => $this->randomDateTime()
                    ];
                    $totalLikes++;
                }
            }
        }

        // Insérer par chunks pour éviter les problèmes de mémoire
        collect($likesData)->chunk(500)->each(function ($chunk) {
            LikerPlanet::insert($chunk->toArray());
        });

        $this->command->info("✅ $totalLikes likes de planètes générés avec succès !");
        
        // Statistiques
        $this->displayStats();
    }

    private function displayStats()
    {
        $popularPlanets = LikerPlanet::getPopularPlanets(5);
        $this->command->info("🪐 Top 5 des planètes les plus likées :");
        foreach ($popularPlanets as $planetLike) {
            $planet = $planetLike->planet;
            $this->command->line("   • {$planet->planet_name} ({$planet->planet_type}) - {$planetLike->likes_count} likes");
        }

        // Stats par type de planète
        $typeStats = LikerPlanet::select('planet.planet_type', \DB::raw('COUNT(*) as likes_count'))
                                ->join('planet', 'liker_planet.planet_id', '=', 'planet.planet_id')
                                ->groupBy('planet.planet_type')
                                ->orderBy('likes_count', 'desc')
                                ->get();

        $this->command->info("📊 Likes par type de planète :");
        foreach ($typeStats as $stat) {
            $this->command->line("   • " . ucfirst(str_replace('_', ' ', $stat->planet_type)) . " : {$stat->likes_count} likes");
        }
    }

    private function randomDateTime()
    {
        // Dates aléatoires dans les 4 derniers mois
        $start = now()->subMonths(4);
        $end = now();
        
        return $start->addSeconds(rand(0, $end->diffInSeconds($start)))->format('Y-m-d H:i:s');
    }
}
