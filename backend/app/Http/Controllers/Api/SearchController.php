<?php

namespace App\Http\Controllers\Api;

use App\Models\SolarSystem;
use App\Models\Planet;
use App\Models\Moon;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController
{
    // Recherche globale
    public function globalSearch(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez entrer un terme de recherche'
            ], 400);
        }

        // Rechercher dans les étoiles
        $solarSystems = SolarSystem::where('solar_system_name', 'LIKE', '%' . $query . '%')
                    ->limit($limit)
                    ->get();

        // Rechercher dans les planètes
        $planets = Planet::where('planet_name', 'LIKE', '%' . $query . '%')
                        ->limit($limit)
                        ->get();

        // Rechercher dans les lunes
        $moons = Moon::where('moon_name', 'LIKE', '%' . $query . '%')
                    ->limit($limit)
                    ->get();

        // Rechercher dans les lunes
        $users = User::where('user_login', 'LIKE', '%' . $query . '%')
                    ->limit($limit)
                    ->get();

        return response()->json([
            'success' => true,
            'query' => $query,
            'results' => [
                'solar_systems' => $solarSystems,
                'planets' => $planets,
                'moons' => $moons,
                'users' => $users
            ],
            'total_results' => $solarSystems->count() + $planets->count() + $moons->count() + $users->count()
        ]);
    }

    // Recherche spécifique aux étoiles
    public function searchStars(Request $request)
    {
        $query = $request->get('q', '');
        $typeFilter = $request->get('type');
        $gravityMin = $request->get('gravity_min');
        $gravityMax = $request->get('gravity_max');
        $tempMin = $request->get('temp_min');
        $tempMax = $request->get('temp_max');
        $limit = $request->get('limit', 15);

        $starsQuery = Star::with(['user', 'planets', 'likes']);

        // Recherche par nom
        if (!empty($query)) {
            $starsQuery->where('star_name', 'LIKE', '%' . $query . '%');
        }

        // Filtres
        if ($typeFilter) {
            $starsQuery->where('star_type', $typeFilter);
        }

        if ($gravityMin) {
            $starsQuery->where('star_gravity', '>=', $gravityMin);
        }

        if ($gravityMax) {
            $starsQuery->where('star_gravity', '<=', $gravityMax);
        }

        if ($tempMin) {
            $starsQuery->where('star_surface_temp', '>=', $tempMin);
        }

        if ($tempMax) {
            $starsQuery->where('star_surface_temp', '<=', $tempMax);
        }

        $stars = $starsQuery->orderBy('star_id', 'desc')
                          ->paginate($limit);

        return response()->json([
            'success' => true,
            'stars' => $stars
        ]);
    }

    // Recherche spécifique aux planètes
    public function searchPlanets(Request $request)
    {
        $query = $request->get('q', '');
        $typeFilter = $request->get('type');
        $gravityMin = $request->get('gravity_min');
        $gravityMax = $request->get('gravity_max');
        $distanceMin = $request->get('distance_min');
        $distanceMax = $request->get('distance_max');
        $limit = $request->get('limit', 15);

        $planetsQuery = Planet::with(['star.user', 'moons', 'likes']);

        // Recherche par nom
        if (!empty($query)) {
            $planetsQuery->where('planet_name', 'LIKE', '%' . $query . '%');
        }

        // Filtres
        if ($typeFilter) {
            $planetsQuery->where('planet_type', $typeFilter);
        }

        if ($gravityMin) {
            $planetsQuery->where('planet_gravity', '>=', $gravityMin);
        }

        if ($gravityMax) {
            $planetsQuery->where('planet_gravity', '<=', $gravityMax);
        }

        if ($distanceMin) {
            $planetsQuery->where('planet_average_distance', '>=', $distanceMin);
        }

        if ($distanceMax) {
            $planetsQuery->where('planet_average_distance', '<=', $distanceMax);
        }

        $planets = $planetsQuery->orderBy('planet_id', 'desc')
                              ->paginate($limit);

        return response()->json([
            'success' => true,
            'planets' => $planets
        ]);
    }

    // Recherche par utilisateur
    public function searchByUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        $stars = Star::with(['planets.moons', 'likes'])
                    ->where('user_id', $userId)
                    ->orderBy('star_id', 'desc')
                    ->get();

        return response()->json([
            'success' => true,
            'user' => $user,
            'stars' => $stars,
            'total_stars' => $stars->count(),
            'total_planets' => $stars->sum(function($star) { return $star->planets->count(); }),
            'total_moons' => $stars->sum(function($star) { 
                return $star->planets->sum(function($planet) { 
                    return $planet->moons->count(); 
                }); 
            })
        ]);
    }

    // Suggestions de recherche
    public function searchSuggestions(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 5);

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'suggestions' => []
            ]);
        }

        $starSuggestions = Star::where('star_name', 'LIKE', $query . '%')
                              ->limit($limit)
                              ->pluck('star_name');

        $planetSuggestions = Planet::where('planet_name', 'LIKE', $query . '%')
                                  ->limit($limit)
                                  ->pluck('planet_name');

        $userSuggestions = User::where('user_pseudo', 'LIKE', $query . '%')
                              ->limit($limit)
                              ->pluck('user_pseudo');

        $suggestions = collect()
            ->merge($starSuggestions->map(function($name) { return ['type' => 'star', 'name' => $name]; }))
            ->merge($planetSuggestions->map(function($name) { return ['type' => 'planet', 'name' => $name]; }))
            ->merge($userSuggestions->map(function($name) { return ['type' => 'user', 'name' => $name]; }))
            ->take($limit * 3);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }
}
