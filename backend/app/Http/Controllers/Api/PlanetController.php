<?php

namespace App\Http\Controllers\Api;

use App\Models\Planet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Traits\ApiResponse;

class PlanetController
{
    use ApiResponse;

    /**
     * Get all planets for a specific solar system
     * 
     * Returns list of all planets belonging to the specified solar system
     *
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier
     * @return JsonResponse List of planets for the solar system
     */
    public function index(string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $planets = Planet::where('solar_system_id', $solarSystemId)->get();

            return $this->success($planets, 'Planets retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Error retrieving planets', 500);
        }
    }

    /**
     * Get specific planet details
     * 
     * Returns detailed information for the specified planet
     *
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier (not used but required for routing)
     * @param string $planetId Planet identifier
     * @return JsonResponse Planet details
     */
    public function show(string $galaxyId, string $solarSystemId, string $planetId): JsonResponse
    {
        try {
            $planet = Planet::findOrFail($planetId);

            return $this->success($planet, 'Planet retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Planet not found', 404);
        }
    }

    /**
     * Create new planet for a solar system
     * 
     * Creates a new planet with validated parameters for the authenticated user
     *
     * @param Request $request Contains planet creation data
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier
     * @return JsonResponse Created planet data
     */
    public function store(Request $request, string $galaxyId, string $solarSystemId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'planet_name' => 'required|string|max:50',
                'planet_desc' => 'nullable|string|max:255',
                'planet_type' => 'required|in:terrestrial,gas,ice,super_earth,sub_neptune,dwarf,lava,carbon,ocean',
                'planet_gravity' => 'required|numeric|min:0|max:1000',
                'planet_surface_temp' => 'required|numeric|min:0|max:5000',
                'planet_orbital_longitude' => 'required|numeric|min:0|max:360',
                'planet_eccentricity' => 'required|numeric|min:0|max:1',
                'planet_apogee' => 'required|integer|min:0|max:15000000000',
                'planet_perigee' => 'required|integer|min:0|max:15000000000',
                'planet_orbital_inclination' => 'required|integer|min:0|max:360',
                'planet_average_distance' => 'required|integer|min:0',
                'planet_orbital_period' => 'required|integer|min:0|max:365000',
                'planet_inclination_angle' => 'required|integer|min:0|max:360',
                'planet_rotation_period' => 'required|integer|min:1|max:24000',
                'planet_mass' => 'required|integer|min:0|max:100000',
                'planet_diameter' => 'required|integer|min:0|max:200000',
                'planet_rings' => 'required|integer|min:0|max:10',
                'planet_initial_x' => 'required|integer',
                'planet_initial_y' => 'required|integer',
                'planet_initial_z' => 'required|integer'
            ]);

            // Validate orbital mechanics
            if ($validated['planet_perigee'] > $validated['planet_apogee']) {
                return $this->error('Perigee must be less than apogee', 422);
            }

            $planet = Planet::create(array_merge($validated, [
                'solar_system_id' => $solarSystemId,
                'user_id' => Auth::id()
            ]));

            return $this->success($planet, 'Planet created successfully', 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation failed', 422);
        } catch (\Exception $e) {
            return $this->error('Error creating planet', 500);
        }
    }

    /**
     * Update existing planet
     * 
     * Updates planet parameters with validated data
     *
     * @param Request $request Contains updated planet data
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier (not used but required for routing)
     * @param string $planetId Planet identifier
     * @return JsonResponse Updated planet data
     */
    public function update(Request $request, string $galaxyId, string $solarSystemId, string $planetId): JsonResponse
    {
        try {
            $planet = Planet::findOrFail($planetId);

            $validated = $request->validate([
                'planet_name' => 'required|string|max:50',
                'planet_desc' => 'nullable|string|max:255',
                'planet_type' => 'required|in:terrestrial,gas,ice,super_earth,sub_neptune,dwarf,lava,carbon,ocean',
                'planet_gravity' => 'required|numeric|min:0|max:1000',
                'planet_surface_temp' => 'required|numeric|min:0|max:5000',
                'planet_orbital_longitude' => 'required|numeric|min:0|max:360',
                'planet_eccentricity' => 'required|numeric|min:0|max:1',
                'planet_apogee' => 'required|integer|min:0|max:15000000000',
                'planet_perigee' => 'required|integer|min:0|max:15000000000',
                'planet_orbital_inclination' => 'required|integer|min:0|max:360',
                'planet_average_distance' => 'required|integer|min:0',
                'planet_orbital_period' => 'required|integer|min:0|max:365000',
                'planet_inclination_angle' => 'required|integer|min:0|max:360',
                'planet_rotation_period' => 'required|integer|min:1|max:24000',
                'planet_mass' => 'required|integer|min:0|max:100000',
                'planet_diameter' => 'required|integer|min:0|max:200000',
                'planet_rings' => 'required|integer|min:0|max:10',
                'planet_initial_x' => 'required|integer',
                'planet_initial_y' => 'required|integer',
                'planet_initial_z' => 'required|integer'
            ]);

            // Validate orbital mechanics
            if (isset($validated['planet_perigee'], $validated['planet_apogee']) && $validated['planet_perigee'] > $validated['planet_apogee']) {
                return $this->error('Perigee must be less than apogee', 422);
            }

            $planet->update($validated);

            return $this->success($planet, 'Planet updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Planet not found', 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation failed', 422);
        } catch (\Exception $e) {
            return $this->error('Error updating planet', 500);
        }
    }

    /**
     * Delete planet and its moons
     * 
     * Permanently removes the specified planet and all its associated moons
     *
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier (not used but required for routing)
     * @param string $planetId Planet identifier
     * @return JsonResponse Success confirmation
     */
    public function destroy(string $galaxyId, string $solarSystemId, string $planetId): JsonResponse
    {
        try {
            $planet = Planet::findOrFail($planetId);
            $planet->delete();
            
            return $this->success(null, 'Planet and its moons deleted successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Planet not found', 404);
        } catch (\Exception $e) {
            return $this->error('Error deleting planet', 500);
        }
    }

    /**
     * Get planet owner information
     * 
     * Returns the user who owns/claimed the specified planet
     *
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier
     * @param string $planetId Planet identifier
     * @return JsonResponse Planet owner user data
     */
    public function getOwner(string $galaxyId, string $solarSystemId, string $planetId): JsonResponse
    {
        try {
            $planet = Planet::with(['user' => function ($query): void {
                $query->select('user_id', 'user_login', 'user_email', 'user_role', 'user_date_inscription');
            }])
            ->where('solar_system_id', $solarSystemId)
            ->find($planetId);

            if (!$planet) {
                return $this->error('Planet not found', 404);
            }

            return $this->success(['owner' => $planet->user], 'Planet owner retrieved');
        } catch (\Exception $e) {
            return $this->error('Error retrieving planet owner', 500);
        }
    }
}
