<?php

namespace App\Http\Controllers;

use App\Models\Star;
use App\Models\User;
use App\Models\Galaxy;
use Illuminate\Http\Request;

class GalaxyController extends Controller
{
    // Page d'accueil avec l'animation de la galaxie
    public function index()
    {
        // Récupérer toutes les galaxies avec leurs étoiles
        $galaxies = Galaxy::with(['stars' => function($query) {
            $query->with(['user', 'planets.moons']);
        }])->get();

        // Ajouter les statistiques pour chaque galaxie
        $galaxies->each(function($galaxy) {
            $galaxy->stats = [
                'total_stars' => $galaxy->starsCount(),
                'active_stars' => $galaxy->activeStarsCount(),
                'total_planets' => $galaxy->planets()->count(),
                'total_moons' => $galaxy->moons()->count(),
                'total_objects' => $galaxy->getTotalObjectsCount(),
                'active_users' => User::whereHas('stars', function($query) use ($galaxy) {
                    $query->where('galaxy_id', $galaxy->galaxy_id);
                })->where('user_active', true)->count()
            ];
        });

        // Statistiques globales pour l'affichage
        $stats = getStats();
    }

    // Nouvelle méthode pour les statistiques (correspond à la route galaxy/stats)
    public function getStats()
    {
        $stats = [
            'total_galaxies' => Galaxy::count(),
            'total_stars' => Star::count(),
            'total_users' => User::count(),
            'total_planets' => \App\Models\Planet::count(),
            'total_moons' => \App\Models\Moon::count(),
            'total_objects' => Galaxy::count() + Star::count() + \App\Models\Planet::count() + \App\Models\Moon::count(),
            'active_users' => User::where('user_active', true)->count()
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    // Récupérer les étoiles avec pagination pour l'animation
    public function getStarsForAnimation(Request $request)
    {
        $limit = $request->get('limit', 50);
        $offset = $request->get('offset', 0);

        $stars = Star::with(['user'])
                    ->select([
                        'star_id',
                        'star_name',
                        'star_type',
                        'star_gravity',
                        'star_surface_temp',
                        'star_diameter',
                        'star_mass',
                        'star_luminosity',
                        'star_initial_x',
                        'star_initial_y',
                        'star_initial_z',
                        'galaxy_id',
                        'user_id'
                    ])
                    ->offset($offset)
                    ->limit($limit)
                    ->get();

        return response()->json([
            'success' => true,
            'stars' => $stars
        ]);
    }

    // Récupérer les étoiles les plus likées
    public function getMostLikedStars(Request $request)
    {
        $limit = $request->get('limit', 10);

        $stars = Star::with(['user', 'likes'])
                    ->withCount('likes')
                    ->orderBy('likes_count', 'desc')
                    ->limit($limit)
                    ->get();

        return response()->json([
            'success' => true,
            'most_liked_stars' => $stars
        ]);
    }

    // Récupérer les étoiles récentes
    public function getRecentStars(Request $request)
    {
        $limit = $request->get('limit', 10);

        $stars = Star::with(['user', 'planets'])
                    ->orderBy('star_id', 'desc')
                    ->limit($limit)
                    ->get();

        return response()->json([
            'success' => true,
            'recent_stars' => $stars
        ]);
    }
}
