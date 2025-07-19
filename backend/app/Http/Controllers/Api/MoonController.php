<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\Moon;
use App\Models\UserSystemOwnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class MoonController
{
    use ApiResponse;

    // Return the list of moons for given planetId
    public function index($galaxyId, $solarSystemId, $planetId): JsonResponse
    {
        try {
            $moons = Moon::where('planet_id', $planetId)->get();
            return $this->success($moons, 'Moons retrieved');
        } catch (\Exception $e) {
            return $this->error('Error while fetching moons', 500);
        }
    }

    // Return the moon for given moonId
    public function show($galaxyId, $solarSystemId, $planetId, $moonId): JsonResponse
    {
        try {
            $moon = Moon::findOrFail($moonId);
            return $this->success($moon, 'Moon retrieved');
        } catch (\Exception $e) {
            return $this->error('Error while fetching moon', 500);
        }
    }

    // Create a new moon for given galaxyId/solarSystemId/planetId
    public function store(Request $request, $galaxyId, $solarSystemId, $planetId): JsonResponse
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

            if ($validated['moon_perigee'] > $validated['moon_apogee']) {
                return $this->error('Perigee must be less than apogee', 422);
            }

            $moon = Moon::create(array_merge($validated, [
                'planet_id' => $planetId,
                'user_id' => Auth::id()
            ]));

            return $this->success($moon, 'Moon created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Error while creating moon', 500);
        }
    }

    // Update a moon
    public function update(Request $request, $galaxyId, $solarSystemId, $planetId, $moonId): JsonResponse
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

            if (isset($validated['moon_perigee'], $validated['moon_apogee']) &&
                $validated['moon_perigee'] > $validated['moon_apogee']) {
                return $this->error('Perigee must be less than apogee', 422);
            }

            $moon->update($validated);

            return $this->success($moon, 'Moon updated successfully');
        } catch (\Exception $e) {
            return $this->error('Error while updating moon', 500);
        }
    }

    // Delete moon
    public function destroy($galaxyId, $solarSystemId, $planetId, $moonId): JsonResponse
    {
        try {
            $moon = Moon::findOrFail($moonId);
            $moon->delete();

            return $this->success(null, 'Moon deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Error while deleting moon', 500);
        }
    }

    // Get current owner of the moon
    public function getOwner($galaxyId, $solarSystemId, $planetId, $moonId): JsonResponse
    {
        try {
            $moon = Moon::with(['user' => function($query) {
                $query->select('user_id', 'user_login', 'user_email', 'user_role', 'user_date_inscription');
            }])
            ->where('planet_id', $planetId)
            ->find($moonId);

            if (!$moon) {
                return $this->error('Moon not found', 404);
            }

            return $this->success(['owner' => $moon->user], 'Moon owner retrieved');
        } catch (\Exception $e) {
            return $this->error('Error while getting owner', 500);
        }
    }
}
