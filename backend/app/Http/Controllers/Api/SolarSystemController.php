<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\SolarSystem;
use App\Models\Wallpaper;
use App\Models\LikeSolarSystem;
use App\Models\LikePlanet;
use App\Models\LikeMoon;
use App\Models\LikeWallpaper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SolarSystemController
{
    use ApiResponse;

    /**
     * Get all solar systems for a specific galaxy
     * 
     * Returns list of all solar systems belonging to the specified galaxy
     *
     * @param string $galaxyId Galaxy identifier
     * @return JsonResponse List of solar systems for the galaxy
     */
    public function index(string $galaxyId): JsonResponse
    {
        try {
            $solarSystems = SolarSystem::where('galaxy_id', $galaxyId)->get();
            return $this->success($solarSystems, 'Solar systems retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Error retrieving solar systems', 500);
        }
    }

    /**
     * Get specific solar system with planets and moons
     * 
     * Returns detailed information for the specified solar system including its planets and moons
     *
     * @param string $galaxyId Galaxy identifier
     * @param string $solarSystemId Solar system identifier
     * @return JsonResponse Solar system details with related entities
     */
    public function show(string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::with(['planets.moons'])
                ->where('solar_system_id', $solarSystemId)
                ->where('galaxy_id', $galaxyId)
                ->first();

            if (!$solarSystem) {
                return $this->error('Solar system not found', 404);
            }

            return $this->success(['solar_system' => $solarSystem], 'Solar system retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Error retrieving solar system', 500);
        }
    }

    /**
     * Get solar system owner information
     * 
     * Returns the user who owns/claimed the specified solar system
     *
     * @param string $galaxyId Galaxy identifier
     * @param string $solarSystemId Solar system identifier
     * @return JsonResponse Solar system owner user data
     */
    public function getOwner(string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::with(['owner' => function($query): void {
                $query->select('user_id', 'user_login', 'user_email', 'user_role', 'user_date_inscription');
            }])
            ->where('galaxy_id', $galaxyId)
            ->findOrFail($solarSystemId);

            return $this->success([
                'owner' => $solarSystem->owner ? $solarSystem->owner->user_login : null
            ], 'Solar system owner retrieved');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Solar system not found', 404);
        } catch (\Exception $e) {
            return $this->error('Error retrieving solar system owner', 500);
        }
    }

    /**
     * Get all solar systems for a specific user with likes and wallpapers
     * 
     * Returns solar systems owned by the specified user including likes count
     * and associated wallpapers for each celestial body
     *
     * @param string $galaxyId Galaxy identifier
     * @return JsonResponse User's solar systems with engagement metrics
     */
    public function getSolarSystemsByUser(string $galaxyId): JsonResponse
    {
        try {
            $userId = request()->get('user_id');
            
            if (!$userId) {
                return $this->error('User ID is required', 400);
            }
            
            $solarSystems = SolarSystem::with(['planets.moons'])
                ->where('user_id', $userId)
                ->where('galaxy_id', $galaxyId)
                ->get();

            // Add likes count and wallpapers to each element
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
                
                // Add likes count to planets and moons
                foreach ($system->planets as $planet) {
                    $planet->likes_count = LikePlanet::where('planet_id', $planet->planet_id)->count();
                    
                    foreach ($planet->moons as $moon) {
                        $moon->likes_count = LikeMoon::where('moon_id', $moon->moon_id)->count();
                    }
                }
            }

            return $this->success(['solar_systems' => $solarSystems], 'User solar systems retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Error retrieving user solar systems', 500);
        }
    }

    /**
     * Create new solar system
     * 
     * Creates a new solar system with validated parameters in the specified galaxy
     *
     * @param Request $request Contains solar system creation data
     * @param string $galaxyId Galaxy identifier
     * @return JsonResponse Created solar system data
     */
    public function add(Request $request, string $galaxyId): JsonResponse
    {
        try {
            $validated = $request->validate([
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

            $solarSystem = new SolarSystem($validated);
            $solarSystem->galaxy_id = $galaxyId;
            $solarSystem->save();

            return $this->success(['solar_system' => $solarSystem], 'Solar system created successfully', 201);
        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->error('Error creating solar system', 500);
        }
    }

    /**
     * Update existing solar system
     * 
     * Updates the specified solar system with validated parameters
     *
     * @param Request $request Contains updated solar system data
     * @param string $galaxyId Galaxy identifier
     * @param string $solarSystemId Solar system identifier
     * @return JsonResponse Updated solar system data
     */
    public function update(Request $request, string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $validated = $request->validate([
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

            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
                ->where('solar_system_id', $solarSystemId)
                ->firstOrFail();

            $solarSystem->update($validated);

            return $this->success(['solar_system' => $solarSystem], 'Solar system updated successfully');
        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Solar system not found', 404);
        } catch (\Exception $e) {
            return $this->error('Error updating solar system', 500);
        }
    }

    /**
     * Delete solar system
     * 
     * Removes the specified solar system and all its related entities
     *
     * @param string $galaxyId Galaxy identifier
     * @param string $solarSystemId Solar system identifier
     * @return JsonResponse Success confirmation
     */
    public function delete(string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
                ->where('solar_system_id', $solarSystemId)
                ->firstOrFail();

            $solarSystem->delete();

            return $this->success(null, 'Solar system deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Solar system not found', 404);
        } catch (\Exception $e) {
            return $this->error('Error deleting solar system', 500);
        }
    }
}
