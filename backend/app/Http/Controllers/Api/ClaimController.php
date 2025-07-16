<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\SolarSystem;
use App\Models\Planet;
use App\Models\Moon;
use App\Models\User;
use Illuminate\Http\Request;

class ClaimController
{
    use ApiResponse;

    public function isClaimable(Request $request, $galaxyId, $solarSystemId)
    {
        $userId = $request->input('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            return $this->error('You need to login to claim systems', 404);
        }

        $claimedSolarSystemCount = SolarSystem::where('user_id', $userId)->count();

        if ($claimedSolarSystemCount >= 3){
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
    }

    public function claim(Request $request, $galaxyId, $solarSystemId)
    {
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
    }

    public function unclaim(Request $request, $galaxyId, $solarSystemId)
    {
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

        $solarSystem->user_id = null;
        $solarSystem->save();

        // Unclaim all planets and their moons
        $planets = Planet::where('solar_system_id', $solarSystemId)->get();
        
        foreach ($planets as $planet) {
            $planet->user_id = null;
            $planet->save();
            // Unclaim all moons of this planet
            Moon::where('planet_id', $planet->planet_id)->update(['user_id' => null]);
        }

        return $this->success($solarSystem, 'Solar system unclaimed successfully !');
    }
}