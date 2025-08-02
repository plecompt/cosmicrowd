<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\SolarSystem;
use App\Models\Planet;
use App\Models\Moon;
use App\Models\User;
use App\Models\Wallpaper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClaimController
{
    use ApiResponse;

    /**
     * Check if a solar system is claimable by a user
     * 
     * Verifies user authentication, claim limit (max 3 systems),
     * and system availability before allowing claim
     *
     * @param Request $request Contains user_id in body
     * @param string $galaxyId Galaxy identifier from URL
     * @param string $solarSystemId Solar system identifier from URL
     * @return JsonResponse Success with claimable status or error message
     */
    public function isClaimable(Request $request, string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $userId = $request->input('user_id');
            
            if (!$userId) {
                return $this->error('User ID is required', 400);
            }
            
            $user = User::find($userId);
            if (!$user) {
                return $this->error('You need to login to claim systems', 404);
            }

            $claimedSolarSystemCount = SolarSystem::where('user_id', $userId)->count();

            if ($claimedSolarSystemCount >= 3) {
                return $this->error('You can\'t claim more than 3 systems', 400);
            }

            $solarSystem = SolarSystem::where('solar_system_id', $solarSystemId)
                ->where('galaxy_id', $galaxyId)
                ->first();
                
            if (!$solarSystem) {
                return $this->error('Solar system not found', 404);
            }

            if ($solarSystem->user_id) {
                return $this->success([
                    'claimable' => false,
                    'reason' => 'This solar system is already claimed'
                ], 'System already claimed');
            }

            return $this->success(['claimable' => true], 'System is claimable');
            
        } catch (\Exception $e) {
            return $this->error('Error checking system claimability', 500);
        }
    }

    /**
     * Claim a solar system for a user
     * 
     * Claims the specified solar system and all its planets and moons
     * for the authenticated user. Updates ownership in database.
     *
     * @param Request $request Contains user_id in body
     * @param string $galaxyId Galaxy identifier from URL
     * @param string $solarSystemId Solar system identifier from URL
     * @return JsonResponse Success with claimed system data or error message
     */
    public function claim(Request $request, string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $userId = $request->input('user_id');
            
            $user = User::find($userId);
            if (!$user) {
                return $this->error('You need to login to claim systems', 404);
            }

            $solarSystem = SolarSystem::where('solar_system_id', $solarSystemId)
                ->where('galaxy_id', $galaxyId)
                ->first();

            if (!$solarSystem) {
                return $this->error('Solar system not found', 404);
            }

            if ($solarSystem->user_id) {
                return $this->error('Solar system already claimed', 400);
            }

            $solarSystem->user_id = $userId;
            $solarSystem->save();

            // Claim all planets and their moons
            $planets = Planet::where('solar_system_id', $solarSystemId)->get();
            
            foreach ($planets as $planet) {
                $planet->user_id = $userId;
                $planet->save();
                
                // Claim all moons of this planet
                Moon::where('planet_id', $planet->planet_id)->update(['user_id' => $userId]);
            }

            return $this->success($solarSystem, 'Solar system claimed successfully !');
            
        } catch (\Exception $e) {
            return $this->error('Error claiming solar system', 500);
        }
    }

    /**
     * Unclaim a solar system for a user
     * 
     * Removes ownership of the specified solar system from the user.
     * Also deletes all associated wallpapers and updates ownership
     * for planets and moons via database cascades.
     *
     * @param Request $request Contains user_id in body
     * @param string $galaxyId Galaxy identifier from URL
     * @param string $solarSystemId Solar system identifier from URL
     * @return JsonResponse Success with unclaimed system data or error message
     */
    public function unclaim(Request $request, string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $userId = $request->input('user_id');
            
            $user = User::find($userId);
            if (!$user) {
                return $this->error('You need to login to unclaim systems', 404);
            }

            $solarSystem = SolarSystem::where('solar_system_id', $solarSystemId)
                ->where('galaxy_id', $galaxyId)
                ->first();

            if (!$solarSystem) {
                return $this->error('Solar system not found', 404);
            }

            if ($solarSystem->user_id != $userId) {
                return $this->error('You can only unclaim your own solar systems', 403);
            }

            Wallpaper::where('solar_system_id', $solarSystemId)->delete();

            $solarSystem->user_id = null;
            $solarSystem->save();

            return $this->success($solarSystem, 'Solar system unclaimed successfully !');
            
        } catch (\Exception $e) {
            return $this->error('Error unclaiming solar system', 500);
        }
    }
}
