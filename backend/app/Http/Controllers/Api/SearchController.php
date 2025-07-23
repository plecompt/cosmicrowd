<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\SolarSystem;
use App\Models\Planet;
use App\Models\Moon;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController
{
    use ApiResponse;

    // Global search in names or login
    public function globalSearch(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);
        $filters = $request->get('filters', []);

        if (empty($query)) {
            return $this->error('Empty query', 400);
        }

        $results = [];
        $totalResults = 0;

        // Check if users filter is enabled
        if (isset($filters['users']) && $filters['users']) {
            $users = User::where('user_login', 'LIKE', '%' . $query . '%')
                        ->limit($limit)
                        ->get();
            $results['users'] = $users;
            $totalResults += $users->count();
        }

        // Check if systems filter is enabled
        if (isset($filters['systems']) && $filters['systems']) {
            $solarSystems = SolarSystem::where('solar_system_name', 'LIKE', '%' . $query . '%')
                            ->limit($limit)
                            ->get();
            $results['solar_systems'] = $solarSystems;
            $totalResults += $solarSystems->count();
        }

        // Check if planets filter is enabled
        if (isset($filters['planets']) && $filters['planets']) {
            $planets = Planet::with('solarSystem')
                            ->where('planet_name', 'LIKE', '%' . $query . '%')
                            ->limit($limit)
                            ->get()
                            ->map(function ($planet) {
                                $planet->solar_system_id = $planet->solarSystem->solar_system_id ?? null;
                                return $planet;
                            });
            $results['planets'] = $planets;
            $totalResults += $planets->count();
        }

        // Check if moons filter is enabled
        if (isset($filters['moons']) && $filters['moons']) {
            $moons = Moon::with('solarSystem')
                        ->where('moon_name', 'LIKE', '%' . $query . '%')
                        ->limit($limit)
                        ->get()
                        ->map(function ($moon) {
                            $moon->solar_system_id = $moon->solarSystem->solar_system_id ?? null;
                            return $moon;
                        });
            $results['moons'] = $moons;
            $totalResults += $moons->count();
        }

        return $this->success([
            'results' => $results,
            'total_results' => $totalResults
        ]);
    }
}