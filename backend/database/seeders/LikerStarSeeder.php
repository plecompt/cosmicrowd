<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Star;
use App\Models\LikerStar;

class LikerStarSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $stars = Star::all();

        if ($users->isEmpty() || $stars->isEmpty()) {
            $this->command->warn('⚠️  Aucun utilisateur ou étoile trouvé. Exécutez UserSeeder et StarSeeder d\'abord.');
            return;
        }

        $likesData = [];
        $totalLikes = 0;

        foreach ($users as $user) {
            // Chaque utilisateur like en moyenne 2-8 systèmes d'autres utilisateurs
            $likesCount = rand(0, 12);
            
            // Récupérer les étoiles des AUTRES utilisateurs
            $otherUsersStars = $stars->where('user_id', '!=', $user->user_id);
            
            if ($otherUsersStars->isEmpty()) continue;

            // Sélectionner aléatoirement des étoiles à liker
            $starsToLike = $otherUsersStars->random(min($likesCount, $otherUsersStars->count()));

            foreach ($starsToLike as $star) {
                // Éviter les doublons
                $alreadyExists = collect($likesData)->contains(function ($like) use ($user, $star) {
                    return $like['user_id'] == $user->user_id && $like['star_id'] == $star->star_id;
                });

                if (!$alreadyExists) {
                    $likesData[] = [
                        'user_id' => $user->user_id,
                        'star_id' => $star->star_id,
                        'liker_star_date' => $this->randomDateTime()
                    ];
                    $totalLikes++;
                }
            }
        }

        // Insérer par chunks pour éviter les problèmes de mémoire
        collect($likesData)->chunk(500)->each(function ($chunk) {
            LikerStar::insert($chunk->toArray());
        });

        $this->command->info("✅ $totalLikes likes de systèmes stellaires générés avec succès !");
        
        // Statistiques
        $popularSystems = LikerStar::getPopularSystems(5);
        $this->command->info("🌟 Top 5 des systèmes les plus likés :");
        foreach ($popularSystems as $system) {
            $this->command->line("   • {$system->star->star_name} ({$system->likes_count} likes)");
        }
    }

    private function randomDateTime()
    {
        // Dates aléatoires dans les 6 derniers mois
        $start = now()->subMonths(6);
        $end = now();
        
        return $start->addSeconds(rand(0, $end->diffInSeconds($start)))->format('Y-m-d H:i:s');
    }
}
