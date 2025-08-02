<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use App\Models\SolarSystem;
use App\Models\Wallpaper;
use App\Models\Galaxy;
use Illuminate\Validation\ValidationException;

class WallpaperController
{
    use ApiResponse;

    /**
     * Get wallpaper for a specific solar system
     * 
     * Returns the wallpaper associated with the specified solar system
     *
     * @param string $galaxyId Galaxy identifier
     * @param string $solarSystemId Solar system identifier
     * @return JsonResponse Wallpaper data
     */
    public function show(string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
                ->findOrFail($solarSystemId);
            
            $wallpaper = Wallpaper::where('solar_system_id', $solarSystemId)
                ->firstOrFail();
            
            return $this->success($wallpaper, 'Wallpaper retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Solar system or wallpaper not found', 404);
        } catch (\Exception $e) {
            return $this->error('Error retrieving wallpaper', 500);
        }
    }

    /**
     * Check if wallpaper exists for a solar system
     * 
     * Verifies whether the specified solar system has an associated wallpaper
     *
     * @param string $galaxyId Galaxy identifier
     * @param string $solarSystemId Solar system identifier
     * @return JsonResponse Boolean indicating wallpaper existence
     */
    public function exists(string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
                ->findOrFail($solarSystemId);
            
            $exists = Wallpaper::where('solar_system_id', $solarSystemId)
                ->exists();
            
            return $this->success(['exists' => $exists], 'Wallpaper existence checked');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Solar system not found', 404);
        } catch (\Exception $e) {
            return $this->error('Error checking wallpaper existence', 500);
        }
    }

    /**
     * Create or update wallpaper for a solar system
     * 
     * Creates a new wallpaper or updates existing one for the specified solar system
     *
     * @param Request $request Contains wallpaper settings data
     * @param string $galaxyId Galaxy identifier
     * @param string $solarSystemId Solar system identifier
     * @return JsonResponse Created or updated wallpaper data
     */
    public function store(Request $request, string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->error('Unauthorized', 401);
            }

            $validated = $request->validate([
                'wallpaper_settings' => 'required|string'
            ]);

            $galaxy = Galaxy::findOrFail($galaxyId);
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)->findOrFail($solarSystemId);

            $existingWallpaper = Wallpaper::where('solar_system_id', $solarSystemId)->first();

            if ($existingWallpaper) {
                $existingWallpaper->update([
                    'wallpaper_settings' => $validated['wallpaper_settings'],
                    'wallpaper_created_at' => now()
                ]);
                return $this->success($existingWallpaper, 'Wallpaper updated successfully');
            }

            $wallpaper = Wallpaper::create([
                'wallpaper_settings' => $validated['wallpaper_settings'],
                'user_id' => $user->user_id,
                'galaxy_id' => $galaxyId,
                'solar_system_id' => $solarSystemId
            ]);

            return $this->success($wallpaper, 'Wallpaper created successfully', 201);
        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Galaxy or solar system not found', 404);
        } catch (\Exception $e) {
            return $this->error('Error creating wallpaper', 500);
        }
    }

    /**
     * Delete wallpaper for a solar system
     * 
     * Removes the wallpaper associated with the specified solar system
     *
     * @param Request $request Contains user authentication data
     * @param string $galaxyId Galaxy identifier
     * @param string $solarSystemId Solar system identifier
     * @return JsonResponse Success confirmation
     */
    public function destroy(Request $request, string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->error('Unauthorized', 401);
            }

            $galaxy = Galaxy::findOrFail($galaxyId);
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)->findOrFail($solarSystemId);

            $wallpaper = Wallpaper::where('solar_system_id', $solarSystemId)->firstOrFail();
            $wallpaper->delete();

            return $this->success(null, 'Wallpaper deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Galaxy, solar system, or wallpaper not found', 404);
        } catch (\Exception $e) {
            return $this->error('Error deleting wallpaper', 500);
        }
    }

    /**
     * Get most liked wallpapers in a galaxy
     * 
     * Returns wallpapers ordered by likes count in descending order
     *
     * @param Request $request Contains optional limit parameter
     * @param string $galaxyId Galaxy identifier
     * @return JsonResponse Most liked wallpapers with user and solar system data
     */
    public function getMostLikedWallpapers(Request $request, string $galaxyId): JsonResponse
    {
        try {
            $limit = (int) $request->query('limit', 10);

            $galaxy = Galaxy::findOrFail($galaxyId);

            $wallpapers = Wallpaper::with([
                'user:user_id,user_login',
                'solarSystem:solar_system_id,solar_system_name'
            ])
            ->withCount('likes')
            ->where('galaxy_id', $galaxyId)
            ->orderByDesc('likes_count')
            ->limit($limit)
            ->get();

            return $this->success($wallpapers, 'Most liked wallpapers retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Galaxy not found', 404);
        } catch (\Exception $e) {
            return $this->error('Error retrieving most liked wallpapers', 500);
        }
    }

    /**
     * Get most recent wallpapers in a galaxy
     * 
     * Returns wallpapers ordered by creation date in descending order
     *
     * @param Request $request Contains optional limit parameter
     * @param string $galaxyId Galaxy identifier
     * @return JsonResponse Most recent wallpapers with user and solar system data
     */
    public function getMostRecentWallpapers(Request $request, string $galaxyId): JsonResponse
    {
        try {
            $limit = (int) $request->query('limit', 10);

            $galaxy = Galaxy::findOrFail($galaxyId);

            $wallpapers = Wallpaper::with([
                'user:user_id,user_login',
                'solarSystem:solar_system_id,solar_system_name'
            ])
            ->withCount('likes')
            ->where('galaxy_id', $galaxyId)
            ->orderByDesc('wallpaper_created_at')
            ->limit($limit)
            ->get();

            return $this->success($wallpapers, 'Most recent wallpapers retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Galaxy not found', 404);
        } catch (\Exception $e) {
            return $this->error('Error retrieving most recent wallpapers', 500);
        }
    }
}