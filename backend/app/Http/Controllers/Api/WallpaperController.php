<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\SolarSystem;
use App\Models\Wallpaper;
use Illuminate\Http\JsonResponse;

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
}
