<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\SolarSystem;
use App\Models\Planet;
use App\Models\Moon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController
{
    use ApiResponse;

    /**
     * Perform global search across multiple entities
     * 
     * Searches through users, solar systems, planets, and moons based on query string
     * and optional filters. Returns categorized results with total count.
     *
     * @param Request $request Contains search query, limit, and filters
     * @return JsonResponse Search results grouped by entity type
     */
    public function globalSearch(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            $limit = (int) $request->get('limit', 10);
            $filters = $request->get('filters', []);

            if (empty($query)) {
                return $this->error('Search query is required', 400);
            }

            $results = [];
            $totalResults = 0;

            // Search users if filter is enabled
            if (isset($filters['users']) && $filters['users']) {
                $users = User::where('user_login', 'LIKE', '%' . $query . '%')
                            ->select('user_id', 'user_login', 'user_email', 'user_role', 'user_date_inscription')
                            ->limit($limit)
                            ->get();
                $results['users'] = $users;
                $totalResults += $users->count();
            }

            // Search solar systems if filter is enabled
            if (isset($filters['systems']) && $filters['systems']) {
                $solarSystems = SolarSystem::where('solar_system_name', 'LIKE', '%' . $query . '%')
                                ->limit($limit)
                                ->get();
                $results['solar_systems'] = $solarSystems;
                $totalResults += $solarSystems->count();
            }

            // Search planets if filter is enabled
            if (isset($filters['planets']) && $filters['planets']) {
                $planets = Planet::where('planet_name', 'LIKE', '%' . $query . '%')
                                ->limit($limit)
                                ->get();
                $results['planets'] = $planets;
                $totalResults += $planets->count();
            }

            // Search moons if filter is enabled
            if (isset($filters['moons']) && $filters['moons']) {
                $moons = Moon::where('moon_name', 'LIKE', '%' . $query . '%')
                            ->limit($limit)
                            ->get();
                $results['moons'] = $moons;
                $totalResults += $moons->count();
            }

            return $this->success([
                'results' => $results,
                'total_results' => $totalResults,
                'query' => $query
            ], 'Search completed successfully');

        } catch (\Exception $e) {
            return $this->error('Error performing search', 500);
        }
    }
}
