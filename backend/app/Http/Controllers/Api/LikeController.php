<?php

namespace App\Http\Controllers\Api;

use App\Models\LikeMoon;
use App\Models\LikePlanet;
use App\Models\LikeSolarSystem;
use App\Models\Moon;
use App\Models\Planet;
use App\Models\SolarSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{


    // Add or Delete a like for given SolarSystemId
    public function toggleSolarSystem($galaxyId, $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
                ->findOrFail($solarSystemId);
            
            $userId = Auth::id();
            
            $existingLike = LikeSolarSystem::where('solar_system_id', $solarSystemId)
                ->where('user_id', $userId)
                ->first();
                
            if ($existingLike) {
                $existingLike->delete();
                return response()->json(['message' => 'Like deleted', 'liked' => false]);
            } else {
                LikeSolarSystem::create([
                    'solar_system_id' => $solarSystemId,
                    'user_id' => $userId
                ]);
                return response()->json(['message' => 'Like added', 'liked' => true]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while adding/deleting like'], 500);
        }
    }

    // Add or Delete a like for given Planet
    public function togglePlanet($galaxyId, $solarSystemId, $planetId): JsonResponse
    {
        try {
            $planet = Planet::whereHas('solarSystem', function($query) use ($galaxyId, $solarSystemId) {
                $query->where('galaxy_id', $galaxyId)
                    ->where('solar_system_id', $solarSystemId);
            })->findOrFail($planetId);
            
            $userId = Auth::id();
            
            $existingLike = LikePlanet::where('planet_id', $planetId)
                ->where('user_id', $userId)
                ->first();
                
            if ($existingLike) {
                $existingLike->delete();
                return response()->json(['message' => 'Like added', 'liked' => false]);
            } else {
                LikePlanet::create([
                    'planet_id' => $planetId,
                    'user_id' => $userId
                ]);
                return response()->json(['message' => 'Like deleted', 'liked' => true]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while adding/deleting like'], 500);
        }
    }

    // Add or Delete a like for given moon
    public function toggleMoon($galaxyId, $solarSystemId, $planetId, $moonId): JsonResponse
    {
        try {
            $moon = Moon::whereHas('planet.solarSystem', function($query) use ($galaxyId, $solarSystemId) {
                $query->where('galaxy_id', $galaxyId)
                    ->where('solar_system_id', $solarSystemId);
            })->where('planet_id', $planetId)
            ->findOrFail($moonId);
            
            $userId = Auth::id();
            
            $existingLike = LikeMoon::where('moon_id', $moonId)
                ->where('user_id', $userId)
                ->first();
                
            if ($existingLike) {
                $existingLike->delete();
                return response()->json(['message' => 'Like added', 'liked' => false]);
            } else {
                LikeMoon::create([
                    'moon_id' => $moonId,
                    'user_id' => $userId
                ]);
                return response()->json(['message' => 'Like deleted', 'liked' => true]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while adding/deleting like'], 500);
        }
    }

    // Count the number of like for given SolarSystem
    public function countSolarSystemLikes($galaxyId, $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
                ->findOrFail($solarSystemId);

            $likesCount = LikeSolarSystem::where('solar_system_id', $solarSystemId)->count();

            return response()->json([
                'likes_count' => $likesCount
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while getting likes count'], 500);
        }
    }

    // Count the number of like for given Planet
    public function countPlanetLikes($galaxyId, $solarSystemId, $planetId): JsonResponse
    {
        try {
            $planet = Planet::whereHas('solarSystem', function($query) use ($galaxyId, $solarSystemId) {
                $query->where('galaxy_id', $galaxyId)
                    ->where('solar_system_id', $solarSystemId);
            })->findOrFail($planetId);

            $likesCount = LikePlanet::where('planet_id', $planetId)->count();

            return response()->json([
                'likes_count' => $likesCount
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while getting likes count'], 500);
        }
    }

    // Count the number of like for given Moon
    public function countMoonLikes($galaxyId, $solarSystemId, $planetId, $moonId): JsonResponse
    {
        try {
            $moon = Moon::whereHas('planet.solarSystem', function($query) use ($galaxyId, $solarSystemId) {
                $query->where('galaxy_id', $galaxyId)
                    ->where('solar_system_id', $solarSystemId);
            })->where('planet_id', $planetId)
            ->findOrFail($moonId);

            $likesCount = LikeMoon::where('moon_id', $moonId)->count();

            return response()->json([
                'likes_count' => $likesCount
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while getting likes count'], 500);
        }
    }

    // Return the list of like for given SolarSystem
    public function getSolarSystemLikesStats($galaxyId, $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
                ->findOrFail($solarSystemId);

            $likes = LikeSolarSystem::with(['user:user_id,user_login'])
                ->where('solar_system_id', $solarSystemId)
                ->select('user_id', 'like_solar_system_date')
                ->get()
                ->map(function($like) {
                    return [
                        'user_name' => $like->user->user_login,
                        'liked_at' => $like->like_solar_system_date
                    ];
                });

            return response()->json([
                'likes' => $likes
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while getting likes counts'], 500);
        }
    }

    // Return the list of like for given Planet
    public function getPlanetLikesStats($galaxyId, $solarSystemId, $planetId): JsonResponse
    {
        try {
            $planet = Planet::whereHas('solarSystem', function($query) use ($galaxyId, $solarSystemId) {
                $query->where('galaxy_id', $galaxyId)
                    ->where('solar_system_id', $solarSystemId);
            })->findOrFail($planetId);

            $likes = LikePlanet::with(['user:user_id,user_login'])
                ->where('planet_id', $planetId)
                ->select('user_id', 'like_planet_date')
                ->get()
                ->map(function($like) {
                    return [
                        'user_name' => $like->user->user_login,
                        'liked_at' => $like->like_planet_date
                    ];
                });

            return response()->json([
                'likes' => $likes
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while getting likes count'], 500);
        }
    }

    // Return the list of like for given Moon
    public function getMoonLikesStats($galaxyId, $solarSystemId, $planetId, $moonId): JsonResponse
    {
        try {
            $moon = Moon::whereHas('planet.solarSystem', function($query) use ($galaxyId, $solarSystemId) {
                $query->where('galaxy_id', $galaxyId)
                    ->where('solar_system_id', $solarSystemId);
            })->where('planet_id', $planetId)
            ->findOrFail($moonId);

            $likes = LikeMoon::with(['user:user_id,user_login'])
                ->where('moon_id', $moonId)
                ->select('user_id', 'like_moon_date')
                ->get()
                ->map(function($like) {
                    return [
                        'user_name' => $like->user->user_login,
                        'liked_at' => $like->like_moon_date
                    ];
                });

            return response()->json([
                'likes' => $likes
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while getting likes count'], 500);
        }
    }
} 