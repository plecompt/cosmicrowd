<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\User;
use App\Models\Moon;
use App\Models\Planet;
use App\Models\SolarSystem;
use App\Models\Wallpaper;
use App\Models\LikeSolarSystem;
use App\Models\LikePlanet;
use App\Models\LikeMoon;
use App\Models\LikeWallpaper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SolarSystemController
{
    use ApiResponse;

    // Get all solarSystem for given galaxyId with full information
    public function index($galaxyId): JsonResponse
    {
        try {
            $solarSystems = SolarSystem::where('galaxy_id', $galaxyId)
                ->select('*')
                ->get();

            return $this->success($solarSystems);
        } catch (\Exception $e) {
            return $this->error('Error while getting solar systems', 500);
        }
    }

    // Get SolarSystem with planets and moons for given solarSystemId
    public function show($galaxyId, $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::with(['planets.moons'])
                ->where('solar_system_id', $solarSystemId)
                ->where('galaxy_id', $galaxyId)
                ->first();

            if (!$solarSystem) {
                return $this->error('Solar system not found', 404);
            }

            return $this->success(['solar_system' => $solarSystem]);
        } catch (\Exception $e) {
            return $this->error('Error while getting solar system', 500);
        }
    }

    // Get owner for given solarSystem
    public function getOwner($galaxyId, $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::with(['owner' => function($query) {
                $query->select('user.user_id', 'user.user_login', 'user.user_email', 'user.user_role', 'user.user_date_inscription');
            }])
            ->where('galaxy_id', $galaxyId)
            ->findOrFail($solarSystemId);

            return $this->success(['owner' => $solarSystem->owner ? $solarSystem->owner->user_login : null]);
        } catch (\Exception $e) {
            return $this->error('Error while getting owner', 500);
        }
    }

    // Get all solar systems for given user with likes and with wallpapers for each body 
    public function getSolarSystemsByUser($galaxyId): JsonResponse
    {
        try {
            $userId = request()->get('user_id');
            
            $solarSystems = SolarSystem::with(['planets.moons'])
                ->where('user_id', $userId)
                ->where('galaxy_id', $galaxyId)
                ->get();

            // Add likes count to each element
            foreach ($solarSystems as $system) {
                $system->likes_count = LikeSolarSystem::where('solar_system_id', $system->solar_system_id)->count();
                
                // Get wallpaper for this system if exists
                $wallpaper = Wallpaper::where('solar_system_id', $system->solar_system_id)->first();
                if ($wallpaper) {
                    $wallpaper->likes_count = LikeWallpaper::where('wallpaper_id', $wallpaper->wallpaper_id)->count();
                    $system->wallpaper = $wallpaper;
                } else {
                    $system->wallpaper = null;
                }
                
                foreach ($system->planets as $planet) {
                    $planet->likes_count = LikePlanet::where('planet_id', $planet->planet_id)->count();
                    
                    foreach ($planet->moons as $moon) {
                        $moon->likes_count = LikeMoon::where('moon_id', $moon->moon_id)->count();
                    }
                }
            }

            return $this->success(['solar_systems' => $solarSystems]);
        } catch (\Exception $e) {
            return $this->error('Error while getting solar systems', 500);
        }
    }

    // Add new solar system
    public function add(Request $request, $galaxyId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'solar_system_name' => 'required|string|max:50',
            'solar_system_desc' => 'nullable|string|max:255',
            'solar_system_type' => 'required|in:brown_dwarf,red_dwarf,yellow_dwarf,white_dwarf,red_giant,blue_giant,red_supergiant,blue_supergiant,hypergiant,neutron_star,pulsar,variable,binary,ternary,black_hole',
            'solar_system_gravity' => 'required|numeric|min:0|max:1000000000000',
            'solar_system_surface_temp' => 'required|numeric|min:0|max:200000',
            'solar_system_diameter' => 'required|integer|min:0|max:600000000000',
            'solar_system_mass' => 'required|integer|min:0|max:25000000000',
            'solar_system_luminosity' => 'required|integer|min:0|max:10000000',
            'solar_system_initial_x' => 'required|integer',
            'solar_system_initial_y' => 'required|integer',
            'solar_system_initial_z' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        try {
            $solarSystem = new SolarSystem($validator->validated());
            $solarSystem->galaxy_id = $galaxyId;
            $solarSystem->save();

            return $this->success(['solar_system' => $solarSystem], 'Solar system created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Error while adding solar system', 500);
        }
    }

    // Update solar system
    public function update(Request $request, $galaxyId, $solarSystemId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'solar_system_name' => 'required|string|max:50',
            'solar_system_desc' => 'nullable|string|max:255',
            'solar_system_type' => 'required|in:brown_dwarf,red_dwarf,yellow_dwarf,white_dwarf,red_giant,blue_giant,red_supergiant,blue_supergiant,hypergiant,neutron_star,pulsar,variable,binary,ternary,black_hole',
            'solar_system_gravity' => 'required|numeric|min:0|max:1000000000000',
            'solar_system_surface_temp' => 'required|numeric|min:0|max:200000',
            'solar_system_diameter' => 'required|integer|min:0|max:600000000000',
            'solar_system_mass' => 'required|integer|min:0|max:25000000000',
            'solar_system_luminosity' => 'required|integer|min:0|max:10000000',
            'solar_system_initial_x' => 'required|integer',
            'solar_system_initial_y' => 'required|integer',
            'solar_system_initial_z' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        try {
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)->where('solar_system_id', $solarSystemId)->first();

            if (!$solarSystem) {
                return $this->error('Solar system not found', 404);
            }

            $solarSystem->update($validator->validated());

            return $this->success(['solar_system' => $solarSystem], 'Solar system updated successfully');
        } catch (\Exception $e) {
            return $this->error('Error while updating solar system', 500);
        }
    }

    // Delete solar system
    public function delete($galaxyId, $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)->where('solar_system_id', $solarSystemId)->first();

            if (!$solarSystem) {
                return $this->error('Solar system not found', 404);
            }

            $solarSystem->delete();

            return $this->success(null, 'Solar system deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Error while deleting solar system', 500);
        }
    }
}