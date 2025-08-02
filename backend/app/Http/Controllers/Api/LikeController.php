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

    /**
     * Check user likes for multiple items
     * 
     * Returns array of IDs that the authenticated user has liked
     * for the specified type (solar_system, planet, moon, wallpaper)
     *
     * @param Request $request Contains comma-separated IDs and type
     * @return JsonResponse Array of liked IDs
     */
    public function checkUserLikes(Request $request): JsonResponse
    {
        try {
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
        } catch (\Exception $e) {
            return $this->error('Error retrieving user likes', 500);
        }
    }

    /**
     * Count likes for a solar system
     *
     * @param string $solarSystemId Solar system identifier
     * @return JsonResponse Like count for the solar system
     */
    public function countSolarSystemLikes(string $solarSystemId): JsonResponse
    {
        try {
            $count = LikeSolarSystem::where('solar_system_id', $solarSystemId)->count();
            return $this->success(['count' => $count], 'Solar system like count retrieved');
        } catch (\Exception $e) {
            return $this->error('Error counting solar system likes', 500);
        }
    }

    /**
     * Count likes for a planet
     *
     * @param string $planetId Planet identifier
     * @return JsonResponse Like count for the planet
     */
    public function countPlanetLikes(string $planetId): JsonResponse
    {
        try {
            $count = LikePlanet::where('planet_id', $planetId)->count();
            return $this->success(['count' => $count], 'Planet like count retrieved');
        } catch (\Exception $e) {
            return $this->error('Error counting planet likes', 500);
        }
    }

    /**
     * Count likes for a moon
     *
     * @param string $moonId Moon identifier
     * @return JsonResponse Like count for the moon
     */
    public function countMoonLikes(string $moonId): JsonResponse
    {
        try {
            $count = LikeMoon::where('moon_id', $moonId)->count();
            return $this->success(['count' => $count], 'Moon like count retrieved');
        } catch (\Exception $e) {
            return $this->error('Error counting moon likes', 500);
        }
    }

    /**
     * Count likes for a wallpaper
     *
     * @param string $wallpaperId Wallpaper identifier
     * @return JsonResponse Like count for the wallpaper
     */
    public function countWallpaperLikes(string $wallpaperId): JsonResponse
    {
        try {
            $count = LikeWallpaper::where('wallpaper_id', $wallpaperId)->count();
            return $this->success(['count' => $count], 'Wallpaper like count retrieved');
        } catch (\Exception $e) {
            return $this->error('Error counting wallpaper likes', 500);
        }
    }

    /**
     * Toggle like status for a solar system
     * 
     * Adds or removes like for authenticated user on specified solar system
     *
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier
     * @return JsonResponse Like action result with liked ID and action
     */
    public function toggleSolarSystem(string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
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
            }

            LikeSolarSystem::create([
                'user_id' => $userId,
                'solar_system_id' => $solarSystemId
            ]);

            return $this->success([
                'likedId' => $solarSystemId,
                'action' => 'liked'
            ], 'Solar system liked successfully');
        } catch (\Exception $e) {
            return $this->error('Error toggling solar system like', 500);
        }
    }

    /**
     * Toggle like status for a planet
     * 
     * Adds or removes like for authenticated user on specified planet
     *
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier (not used but required for routing)
     * @param string $planetId Planet identifier
     * @return JsonResponse Like action result with liked ID and action
     */
    public function togglePlanet(string $galaxyId, string $solarSystemId, string $planetId): JsonResponse
    {
        try {
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
            }

            LikePlanet::create([
                'user_id' => $userId,
                'planet_id' => $planetId
            ]);

            return $this->success([
                'likedId' => $planetId,
                'action' => 'liked'
            ], 'Planet liked successfully');
        } catch (\Exception $e) {
            return $this->error('Error toggling planet like', 500);
        }
    }

    /**
     * Toggle like status for a moon
     * 
     * Adds or removes like for authenticated user on specified moon
     *
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier (not used but required for routing)
     * @param string $planetId Planet identifier (not used but required for routing)
     * @param string $moonId Moon identifier
     * @return JsonResponse Like action result with liked ID and action
     */
    public function toggleMoon(string $galaxyId, string $solarSystemId, string $planetId, string $moonId): JsonResponse
    {
        try {
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
            }

            LikeMoon::create([
                'user_id' => $userId,
                'moon_id' => $moonId
            ]);

            return $this->success([
                'likedId' => $moonId,
                'action' => 'liked'
            ], 'Moon liked successfully');
        } catch (\Exception $e) {
            return $this->error('Error toggling moon like', 500);
        }
    }

    /**
     * Toggle like status for a wallpaper
     * 
     * Adds or removes like for authenticated user on specified wallpaper
     *
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier (not used but required for routing)
     * @param string $wallpaperId Wallpaper identifier
     * @return JsonResponse Like action result with liked ID and action
     */
    public function toggleWallpaper(string $galaxyId, string $solarSystemId, string $wallpaperId): JsonResponse
    {
        try {
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
            }

            LikeWallpaper::create([
                'user_id' => $userId,
                'wallpaper_id' => $wallpaperId
            ]);

            return $this->success([
                'likedId' => $wallpaperId,
                'action' => 'liked'
            ], 'Wallpaper liked successfully');
        } catch (\Exception $e) {
            return $this->error('Error toggling wallpaper like', 500);
        }
    }
}
