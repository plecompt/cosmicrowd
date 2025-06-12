<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Moon;
use App\Models\LikerMoon;

class LikerMoonSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $moons = Moon::with(['planet.star'])->get();

        if ($users->isEmpty() || $moons->isEmpty()) {
            $this->command->warn('⚠️  Aucun utilisateur ou lune trouvé. Exécutez UserSeeder et MoonSeeder d\'abord.');
            return;
        }

        $likesData = [];
        $totalLikes = 0;

        foreach ($users as $user) {
            // Chaque utilisateur like en moyenne 2-10 lunes d'autres utilisateurs
            // Les lunes sont moins souvent likées que les planètes
            $likesCount = rand(0, 15);
            
            // Récupérer les lunes des AUTRES utilisateurs
            $otherUsersMoons = $moons->where('user_id', '!=', $user->user_id);
            
            if ($otherUsersMoons->isEmpty()) continue;

            // Probabilité de liker selon le type de lune
            $moonTypePreferences = [
                'natural' => 30,      // Très populaires (notre Lune)
                'captured_asteroid' => 15, // Moyennement populaires
                'shepherd' => 20,     // Populaires (gardiens d'anneaux)
                'trojan' => 12,       // Moins populaires
                'irregular' => 8,     // Rares
                'volcanic' => 25,     // Très fascinantes (Io)
                'icy' => 28,         // Très populaires (Europa, Encelade)
                'tidally_locked' => 18, // Moyennement populaires
                'binary' => 10,       // Rares mais intéressantes
                'ring_moon' => 15     // Moyennement populaires
            ];

            // Sélectionner des lunes selon les préférences
            $selectedMoons = collect();
            foreach ($otherUsersMoons as $moon) {
                $preference = $moonTypePreferences[$moon->moon_type] ?? 15;
                
                // Bonus pour les lunes avec des caractéristiques spéciales
                if ($moon->moon_temperature < -100) $preference += 5; // Lunes glacées
                if ($moon->moon_temperature > 100) $preference += 3;  // Lunes chaudes
                if ($moon->moon_mass > 0.1) $preference += 3;         // Grosses lunes
                
                if (rand(1, 100) <= $preference) {
                    $selectedMoons->push($moon);
                }
            }

            // Limiter au nombre de likes souhaité
            if ($selectedMoons->count() > 0) {
                $moonsToLike = $selectedMoons->random(min($likesCount, $selectedMoons->count()));

                foreach ($moonsToLike as $moon) {
                    // Éviter les doublons
                    $alreadyExists = collect($likesData)->contains(function ($like) use ($user, $moon) {
                        return $like['user_id'] == $user->user_id && $like['moon_id'] == $moon->moon_id;
                    });

                    if (!$alreadyExists) {
                        $likesData[] = [
                            'user_id' => $user->user_id,
                            'moon_id' => $moon->moon_id,
                            'liker_moon_date' => $this->randomDateTime()
                        ];
                        $totalLikes++;
                    }
                }
            }
        }

        // Insérer par chunks pour éviter les problèmes de mémoire
        collect($likesData)->chunk(500)->each(function ($chunk) {
            LikerMoon::insert($chunk->toArray());
        });

        $this->command->info("✅ $totalLikes likes de lunes générés avec succès !");
        
        // Statistiques
        $this->displayStats();
    }

    private function displayStats()
    {
        $popularMoons = LikerMoon::getPopularMoons(5);
        $this->command->info("🌙 Top 5 des lunes les plus likées :");
        foreach ($popularMoons as $moonLike) {
            $moon = $moonLike->moon;
            $planet = $moon->planet;
            $this->command->line("   • {$moon->moon_name} ({$moon->moon_type}) orbite {$planet->planet_name} - {$moonLike->likes_count} likes");
        }

        // Stats par type de lune
        $typeStats = LikerMoon::select('moon.moon_type', \DB::raw('COUNT(*) as likes_count'))
                              ->join('moon', 'liker_moon.moon_id', '=', 'moon.moon_id')
                              ->groupBy('moon.moon_type')
                              ->orderBy('likes_count', 'desc')
                              ->get();

        $this->command->info("📊 Likes par type de lune :");
        foreach ($typeStats as $stat) {
            $this->command->line("   • " . ucfirst(str_replace('_', ' ', $stat->moon_type)) . " : {$stat->likes_count} likes");
        }

        // Lunes les plus fascinantes (volcanique, glacée)
        $specialMoons = LikerMoon::whereHas('moon', function ($q) {
                                    $q->whereIn('moon_type', ['volcanic', 'icy']);
                                })
                                ->select('moon_id', \DB::raw('COUNT(*) as likes_count'))
                                ->groupBy('moon_id')
                                ->orderBy('likes_count', 'desc')
                                ->limit(3)
                                ->with(['moon.planet.star'])
                                ->get();

        if ($specialMoons->count() > 0) {
            $this->command->info("🔥❄️ Lunes spéciales les plus appréciées :");
            foreach ($specialMoons as $moonLike) {
                $moon = $moonLike->moon;
                $emoji = $moon->moon_type === 'volcanic' ? '🌋' : '❄️';
                $this->command->line("   $emoji {$moon->moon_name} ({$moon->moon_type}) - {$moonLike->likes_count} likes");
            }
        }
    }

    private function randomDateTime()
    {
        // Dates aléatoires dans les 3 derniers mois
        $start = now()->subMonths(3);
        $end = now();
        
        return $start->addSeconds(rand(0, $end->diffInSeconds($start)))->format('Y-m-d H:i:s');
    }
}
