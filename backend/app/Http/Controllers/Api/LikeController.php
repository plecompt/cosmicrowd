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

    public function checkUserLikes(Request $request)
    {
        $request->validate([
            'ids' => 'required|string',
            'type' => 'required|string|in:solar_system,planet,moon,wallpaper'
        ]);

        $ids = explode(',', $request->ids);
        $userId = auth()->id();

        $likedIds = [];

        switch ($request->type) {
            case 'solar_system':
                $likedIds = LikeSolarSystem::where('user_id', $userId)
                    ->whereIn('solar_system_id', $ids)
                    ->pluck('solar_system_id')
                    ->toArray();
                break;
                
            case 'planet':
                $likedIds = LikePlanet::where('user_id', $userId)
                    ->whereIn('planet_id', $ids)
                    ->pluck('planet_id')
                    ->toArray();
                break;
                
            case 'moon':
                $likedIds = LikeMoon::where('user_id', $userId)
                    ->whereIn('moon_id', $ids)
                    ->pluck('moon_id')
                    ->toArray();
                break;

            case 'wallpaper':
                $likedIds = LikeWallpaper::where('user_id', $userId)
                    ->whereIn('wallpaper_id', $ids)
                    ->pluck('wallpaper_id')
                    ->toArray();
                break;
        }

        return $this->success($likedIds, 'User likes retrieved successfully');
    }

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

    public function toggleSolarSystem($galaxyId, $solarSystemId): JsonResponse
    {
        $userId = auth()->id();
        
        $like = LikeSolarSystem::where('user_id', $userId)
            ->where('solar_system_id', $solarSystemId)
            ->first();
        
        if ($like) {
            $like->delete();
            return $this->success([
                'likedId' => $solarSystemId,
                'action' => 'unliked'
            ], 'Solar system unliked successfully');
        } else {
            LikeSolarSystem::create([
                'user_id' => $userId,
                'solar_system_id' => $solarSystemId
            ]);
            return $this->success([
                'likedId' => $solarSystemId,
                'action' => 'liked'
            ], 'Solar system liked successfully');
        }
    }

    public function togglePlanet($galaxyId, $solarSystemId, $planetId): JsonResponse
    {
        $userId = auth()->id();
        
        $like = LikePlanet::where('user_id', $userId)
            ->where('planet_id', $planetId)
            ->first();
        
        if ($like) {
            $like->delete();
            return $this->success([
                'likedId' => $planetId,
                'action' => 'unliked'
            ], 'Planet unliked successfully');
        } else {
            LikePlanet::create([
                'user_id' => $userId,
                'planet_id' => $planetId
            ]);
            return $this->success([
                'likedId' => $planetId,
                'action' => 'liked'
            ], 'Planet liked successfully');
        }
    }

    public function toggleMoon($galaxyId, $solarSystemId, $planetId, $moonId): JsonResponse
    {
        $userId = auth()->id();
        
        $like = LikeMoon::where('user_id', $userId)
            ->where('moon_id', $moonId)
            ->first();
        
        if ($like) {
            $like->delete();
            return $this->success([
                'likedId' => $moonId,
                'action' => 'unliked'
            ], 'Moon unliked successfully');
        } else {
            LikeMoon::create([
                'user_id' => $userId,
                'moon_id' => $moonId
            ]);
            return $this->success([
                'likedId' => $moonId,
                'action' => 'liked'
            ], 'Moon liked successfully');
        }
    }

    public function toggleWallpaper($galaxyId, $solarSystemId, $wallpaperId): JsonResponse
    {
        $userId = auth()->id();
        
        $like = LikeWallpaper::where('user_id', $userId)
            ->where('wallpaper_id', $wallpaperId)
            ->first();
        
        if ($like) {
            $like->delete();
            return $this->success([
                'likedId' => $wallpaperId,
                'action' => 'unliked'
            ], 'Wallpaper unliked successfully');
        } else {
            LikeWallpaper::create([
                'user_id' => $userId,
                'wallpaper_id' => $wallpaperId
            ]);
            return $this->success([
                'likedId' => $wallpaperId,
                'action' => 'liked'
            ], 'Wallpaper liked successfully');
        }
    }
}
