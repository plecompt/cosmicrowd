<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\LikeSolarSystem;
use App\Models\LikePlanet;
use App\Models\LikeMoon;
use App\Models\LikeWallpaper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LikeController
{
    use ApiResponse;

    // WIP: Count likes for a solar system
    public function countSolarSystemLikes($solarSystemId): JsonResponse
    {
        $count = LikeSolarSystem::where('solar_system_id', $solarSystemId)->count();
        return $this->success(['count' => $count], 'Solar system like count retrieved');
    }

    // WIP: Count likes for a planet
    public function countPlanetLikes($planetId): JsonResponse
    {
        $count = LikePlanet::where('planet_id', $planetId)->count();
        return $this->success(['count' => $count], 'Planet like count retrieved');
    }

    // WIP: Count likes for a moon
    public function countMoonLikes($moonId): JsonResponse
    {
        $count = LikeMoon::where('moon_id', $moonId)->count();
        return $this->success(['count' => $count], 'Moon like count retrieved');
    }

    // WIP: Count likes for a wallpaper
    public function countWallpaperLikes($wallpaperId): JsonResponse
    {
        $count = LikeWallpaper::where('wallpaper_id', $wallpaperId)->count();
        return $this->success(['count' => $count], 'Wallpaper like count retrieved');
    }
}
