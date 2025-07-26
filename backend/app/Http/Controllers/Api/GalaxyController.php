<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\Galaxy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GalaxyController
{
    use ApiResponse;

    // Return the list of all galaxies with their solarSystems, planets and moons count
    public function index(): JsonResponse
    {
        $galaxies = Galaxy::withCount(['solarSystems', 'planets', 'moons'])->get();
        return $this->success($galaxies, 'All galaxies retrieved');
    }

    // Return a specific galaxy with solarSystems, planets and moons count
    public function show($id): JsonResponse
    {
        $galaxy = Galaxy::withCount(['solarSystems', 'planets', 'moons'])
            ->findOrFail($id);
        return $this->success($galaxy, 'Galaxy retrieved');
    }

    // Return the list of solarSystems in the given galaxy, used for main animation
    public function getSolarSystemsForAnimation($id): JsonResponse
    {
        $galaxy = Galaxy::findOrFail($id);
        $solarSystems = $galaxy->solarSystems()
            ->leftJoin('user', 'solar_system.user_id', '=', 'user.user_id')
            ->select('solar_system.*', 'user.user_login')
            ->get();
        return $this->success($solarSystems, 'Solar systems for animation retrieved');
    }

    // Return the most liked solarSystems
    public function getMostLikedSolarSystems(Request $request, $id): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);

        $galaxy = Galaxy::findOrFail($id);

        $solarSystems = $galaxy->solarSystems()
            ->with('planets')
            ->withCount('likes')
            ->orderByDesc('likes_count')
            ->limit($limit)
            ->get();

        return $this->success($solarSystems, 'Most liked solar systems retrieved');
    }

    // Return the most recent solarSystems
    public function getMostRecentSolarSystems(Request $request, $id): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);

        $galaxy = Galaxy::findOrFail($id);

        $solarSystems = $galaxy->solarSystems()
            ->with('planets')
            ->withCount('likes')
            ->orderByDesc('solar_system_id') //using id to orderby
            ->limit($limit)
            ->get();

        return $this->success($solarSystems, 'Most recent solar systems retrieved');
    }
}