<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\Galaxy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GalaxyController
{
    use ApiResponse;

    /**
     * Get all galaxies with their counts
     * 
     * Returns all galaxies with solar systems, planets and moons count
     *
     * @return JsonResponse List of galaxies with counts
     */
    public function index(): JsonResponse
    {
        try {
            $galaxies = Galaxy::withCount(['solarSystems', 'planets', 'moons'])->get();
            return $this->success($galaxies, 'All galaxies retrieved');
        } catch (\Exception $e) {
            return $this->error('Error retrieving galaxies', 500);
        }
    }

    /**
     * Get specific galaxy with counts
     * 
     * Returns a specific galaxy with solar systems, planets and moons count
     *
     * @param string $id Galaxy identifier
     * @return JsonResponse Galaxy data with counts
     */
    public function show(string $id): JsonResponse
    {
        try {
            $galaxy = Galaxy::withCount(['solarSystems', 'planets', 'moons'])
                ->findOrFail($id);
            return $this->success($galaxy, 'Galaxy retrieved');
        } catch (\Exception $e) {
            return $this->error('Galaxy not found', 404);
        }
    }

    /**
     * Get solar systems for galaxy animation
     * 
     * Returns solar systems list with user login for main galaxy animation
     *
     * @param string $id Galaxy identifier
     * @return JsonResponse Solar systems with user data for animation
     */
    public function getSolarSystemsForAnimation(string $id): JsonResponse
    {
        try {
            $galaxy = Galaxy::findOrFail($id);
            $solarSystems = $galaxy->solarSystems()
                ->leftJoin('user', 'solar_system.user_id', '=', 'user.user_id')
                ->select('solar_system.*', 'user.user_login')
                ->get();
            return $this->success($solarSystems, 'Solar systems for animation retrieved');
        } catch (\Exception $e) {
            return $this->error('Error retrieving solar systems for animation', 500);
        }
    }

    /**
     * Get most liked solar systems in galaxy
     * 
     * Returns the most liked solar systems with planets and like counts
     *
     * @param Request $request Contains optional limit parameter
     * @param string $id Galaxy identifier
     * @return JsonResponse Most liked solar systems with data
     */
    public function getMostLikedSolarSystems(Request $request, string $id): JsonResponse
    {
        try {
            $limit = (int) $request->query('limit', 10);

            $galaxy = Galaxy::findOrFail($id);

            $solarSystems = $galaxy->solarSystems()
                ->with('planets')
                ->withCount('likes')
                ->orderByDesc('likes_count')
                ->limit($limit)
                ->get();

            return $this->success($solarSystems, 'Most liked solar systems retrieved');
        } catch (\Exception $e) {
            return $this->error('Error retrieving most liked solar systems', 500);
        }
    }

    /**
     * Get most recent solar systems in galaxy
     * 
     * Returns the most recently created solar systems with planets and like counts
     *
     * @param Request $request Contains optional limit parameter
     * @param string $id Galaxy identifier
     * @return JsonResponse Most recent solar systems with data
     */
    public function getMostRecentSolarSystems(Request $request, string $id): JsonResponse
    {
        try {
            $limit = (int) $request->query('limit', 10);

            $galaxy = Galaxy::findOrFail($id);

            $solarSystems = $galaxy->solarSystems()
                ->with('planets')
                ->withCount('likes')
                ->orderByDesc('solar_system_id') // Using ID to order by creation
                ->limit($limit)
                ->get();

            return $this->success($solarSystems, 'Most recent solar systems retrieved');
        } catch (\Exception $e) {
            return $this->error('Error retrieving most recent solar systems', 500);
        }
    }
}
