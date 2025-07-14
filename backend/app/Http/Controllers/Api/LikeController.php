<?php

namespace App\Http\Controllers\Api;

use App\Models\LikeSolarSystem;
use App\Models\LikePlanet;
use App\Models\LikeMoon;
use App\Models\LikeWallpaper;
use App\Models\Moon;
use App\Models\Planet;
use App\Models\SolarSystem;
use App\Models\Wallpaper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class LikeController
{
    // WIP
    function countSolarSystemLikes($solarSystemId) {
        return LikeSolarSystem::where('solar_system_id', $solarSystemId)->count();
    }

    function countPlanetLikes($planetId) {
        return LikePlanet::where('planet_id', $planetId)->count();
    }

    function countMoonLikes($moonId) {
        return LikeMoon::where('moon_id', $moonId)->count();
    }

    function countWallpaperLikes($wallpaperId) {
        return LikeWallpaper::where('wallpaper_id', $wallpaperId)->count();
    }
} 