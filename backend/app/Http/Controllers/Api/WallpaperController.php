<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use App\Models\SolarSystem;
use App\Models\Wallpaper;
use App\Models\Galaxy;

class WallpaperController
{
    use ApiResponse;

    // Return the wallpaper for a specific solar system
    public function show($galaxyId, $solarSystemId): JsonResponse
    {
        $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
            ->findOrFail($solarSystemId);
        
        $wallpaper = Wallpaper::where('solar_system_id', $solarSystemId)
            ->firstOrFail();
        
        return $this->success($wallpaper, 'Wallpaper retrieved');
    }

    // Check if a wallpaper exists for a specific solar system
    public function exists($galaxyId, $solarSystemId): JsonResponse
    {
        $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
            ->findOrFail($solarSystemId);
        
        $exists = Wallpaper::where('solar_system_id', $solarSystemId)
            ->exists();
        
        return $this->success(['exists' => $exists], 'Wallpaper existence checked');
    }

    // Return the most liked wallpapers, 10 by default
    public function getMostLikedWallpapers(Request $request, $galaxyId): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);

        $galaxy = Galaxy::findOrFail($galaxyId);

        $wallpapers = Wallpaper::with(['user:user_id,user_login', 'solarSystem:solar_system_id,solar_system_name'])
            ->withCount('likes')
            ->where('galaxy_id', $galaxyId)
            ->orderByDesc('likes_count')
            ->limit($limit)
            ->get();

        return $this->success($wallpapers, 'Most liked wallpapers retrieved');
    }

    // Return the most recent wallpapers, 10 by default
    public function getMostRecentWallpapers(Request $request, $galaxyId): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);

        $galaxy = Galaxy::findOrFail($galaxyId);

        $wallpapers = Wallpaper::with(['user:user_id,user_login', 'solarSystem:solar_system_id,solar_system_name'])
            ->withCount('likes')
            ->where('galaxy_id', $galaxyId)
            ->orderByDesc('wallpaper_created_at')
            ->limit($limit)
            ->get();

        return $this->success($wallpapers, 'Most recent wallpapers retrieved');
    }
}
