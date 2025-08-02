<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\Moon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class MoonController
{
    use ApiResponse;

    /**
     * Get all moons for a specific planet
     * 
     * Returns list of all moons belonging to the specified planet
     *
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier (not used but required for routing)
     * @param string $planetId Planet identifier
     * @return JsonResponse List of moons for the planet
     */
    public function index(string $galaxyId, string $solarSystemId, string $planetId): JsonResponse
    {
        try {
            $moons = Moon::where('planet_id', $planetId)->get();

            return $this->success($moons, 'Moons retrieved');
        } catch (\Exception $e) {
            return $this->error('Error retrieving moons', 500);
        }
    }

    /**
     * Get specific moon details
     * 
     * Returns detailed information for the specified moon
     *
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier (not used but required for routing)
     * @param string $planetId Planet identifier (not used but required for routing)
     * @param string $moonId Moon identifier
     * @return JsonResponse Moon details
     */
    public function show(string $galaxyId, string $solarSystemId, string $planetId, string $moonId): JsonResponse
    {
        try {
            $moon = Moon::findOrFail($moonId);

            return $this->success($moon, 'Moon retrieved');
        } catch (\Exception $e) {
            return $this->error('Moon not found', 404);
        }
    }

    /**
     * Create new moon for a planet
     * 
     * Creates a new moon with validated parameters for the authenticated user
     *
     * @param Request $request Contains moon creation data
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier (not used but required for routing)
     * @param string $planetId Planet identifier
     * @return JsonResponse Created moon data
     */
    public function store(Request $request, string $galaxyId, string $solarSystemId, string $planetId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'moon_name' => 'required|string|max:50',
                'moon_desc' => 'nullable|string|max:255',
                'moon_type' => 'required|in:rocky,icy,mixed,primitive,regular,irregular,trojan,coorbital',
                'moon_gravity' => 'required|numeric|min:0|max:25',
                'moon_surface_temp' => 'required|numeric|min:0|max:700',
                'moon_orbital_longitude' => 'required|numeric|min:0|max:360',
                'moon_eccentricity' => 'required|numeric|min:0|max:1',
                'moon_apogee' => 'required|integer|min:100|max:10000000',
                'moon_perigee' => 'required|integer|min:100|max:10000000',
                'moon_orbital_inclination' => 'required|integer|min:0|max:360',
                'moon_average_distance' => 'required|integer|min:0',
                'moon_orbital_period' => 'required|integer|min:1|max:10000',
                'moon_inclination_angle' => 'required|integer|min:0|max:360',
                'moon_rotation_period' => 'required|integer|min:1|max:2000',
                'moon_mass' => 'required|integer|min:0|max:1000',
                'moon_diameter' => 'required|integer|min:0|max:10000',
                'moon_rings' => 'required|integer|min:0|max:10',
                'moon_initial_x' => 'required|integer',
                'moon_initial_y' => 'required|integer',
                'moon_initial_z' => 'required|integer'
            ]);

            // Validate orbital mechanics
            if ($validated['moon_perigee'] > $validated['moon_apogee']) {
                return $this->error('Perigee must be less than apogee', 422);
            }

            $moon = Moon::create(array_merge($validated, [
                'planet_id' => $planetId,
                'user_id' => Auth::id()
            ]));

            return $this->success($moon, 'Moon created successfully', 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation failed', 422);
        } catch (\Exception $e) {
            return $this->error('Error creating moon', 500);
        }
    }

    /**
     * Update existing moon
     * 
     * Updates moon parameters with validated data
     *
     * @param Request $request Contains updated moon data
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier (not used but required for routing)
     * @param string $planetId Planet identifier (not used but required for routing)
     * @param string $moonId Moon identifier
     * @return JsonResponse Updated moon data
     */
    public function update(Request $request, string $galaxyId, string $solarSystemId, string $planetId, string $moonId): JsonResponse
    {
        try {
            $moon = Moon::findOrFail($moonId);

            $validated = $request->validate([
                'moon_name' => 'required|string|max:50',
                'moon_desc' => 'nullable|string|max:255',
                'moon_type' => 'required|in:rocky,icy,mixed,primitive,regular,irregular,trojan,coorbital',
                'moon_gravity' => 'required|numeric|min:0|max:25',
                'moon_surface_temp' => 'required|numeric|min:0|max:700',
                'moon_orbital_longitude' => 'required|numeric|min:0|max:360',
                'moon_eccentricity' => 'required|numeric|min:0|max:1',
                'moon_apogee' => 'required|integer|min:100|max:10000000',
                'moon_perigee' => 'required|integer|min:100|max:10000000',
                'moon_orbital_inclination' => 'required|integer|min:0|max:360',
                'moon_average_distance' => 'required|integer|min:0',
                'moon_orbital_period' => 'required|integer|min:1|max:10000',
                'moon_inclination_angle' => 'required|integer|min:0|max:360',
                'moon_rotation_period' => 'required|integer|min:1|max:2000',
                'moon_mass' => 'required|integer|min:0|max:1000',
                'moon_diameter' => 'required|integer|min:0|max:10000',
                'moon_rings' => 'required|integer|min:0|max:10',
                'moon_initial_x' => 'required|integer',
                'moon_initial_y' => 'required|integer',
                'moon_initial_z' => 'required|integer'
            ]);

            // Validate orbital mechanics
            if (isset($validated['moon_perigee'], $validated['moon_apogee']) && $validated['moon_perigee'] > $validated['moon_apogee']) {
                return $this->error('Perigee must be less than apogee', 422);
            }

            $moon->update($validated);

            return $this->success($moon, 'Moon updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Moon not found', 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation failed', 422);
        } catch (\Exception $e) {
            return $this->error('Error updating moon', 500);
        }
    }

    /**
     * Delete moon
     * 
     * Permanently removes the specified moon from the database
     *
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier (not used but required for routing)
     * @param string $planetId Planet identifier (not used but required for routing)
     * @param string $moonId Moon identifier
     * @return JsonResponse Success confirmation
     */
    public function destroy(string $galaxyId, string $solarSystemId, string $planetId, string $moonId): JsonResponse
    {
        try {
            $moon = Moon::findOrFail($moonId);
            $moon->delete();

            return $this->success(null, 'Moon deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Moon not found', 404);
        } catch (\Exception $e) {
            return $this->error('Error deleting moon', 500);
        }
    }

    /**
     * Get moon owner information
     * 
     * Returns the user who owns/claimed the specified moon
     *
     * @param string $galaxyId Galaxy identifier (not used but required for routing)
     * @param string $solarSystemId Solar system identifier (not used but required for routing)
     * @param string $planetId Planet identifier
     * @param string $moonId Moon identifier
     * @return JsonResponse Moon owner user data
     */
    public function getOwner(string $galaxyId, string $solarSystemId, string $planetId, string $moonId): JsonResponse
    {
        try {
            $moon = Moon::with(['user' => function($query): void {
                $query->select('user_id', 'user_login', 'user_email', 'user_role', 'user_date_inscription');
            }])
            ->where('planet_id', $planetId)
            ->find($moonId);

            if (!$moon) {
                return $this->error('Moon not found', 404);
            }

            return $this->success(['owner' => $moon->user], 'Moon owner retrieved');

        } catch (\Exception $e) {
            return $this->error('Error retrieving moon owner', 500);
        }
    }
}
