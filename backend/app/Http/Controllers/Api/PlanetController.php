<?php

namespace App\Http\Controllers\Api;

use App\Models\Planet;
use App\Models\Star;
use App\Models\SolarSystem;
use App\Models\UserSolarSystemOwnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Traits\ApiResponse;

class PlanetController
{
    use ApiResponse;

    public function index($galaxyId, $solarSystemId)
    {
        try {
            $planets = Planet::where('solar_system_id', $solarSystemId)->get();
            return $this->success($planets, 'Planets fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Error while fetching planets', 500);
        }
    }

    public function show($galaxyId, $solarSystemId, $planetId)
    {
        try {
            $planet = Planet::findOrFail($planetId);
            return $this->success($planet, 'Planet fetched successfully');
        } catch (\Exception $e) {
            return $this->error('Error while fetching planet', 500);
        }
    }

    public function store(Request $request, $galaxyId, $solarSystemId)
    {
        try {
            $ownership = UserSystemOwnership::where('solar_system_id', $solarSystemId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$ownership) {
                return $this->error('You don\'t own this solar system', 403);
            }

            $validated = $request->validate([
                'planet_name' => 'required|string|max:50',
                'planet_desc' => 'nullable|string|max:255',
                'planet_type' => 'required|in:terrestrial,gas,ice,super_earth,sub_neptune,dwarf,lava,carbon,ocean',
                'planet_gravity' => 'required|numeric|min:0',
                'planet_surface_temp' => 'required|numeric|min:0',
                'planet_orbital_longitude' => 'required|numeric|min:0|max:360',
                'planet_eccentricity' => 'required|numeric|min:0|max:1',
                'planet_apogee' => 'required|integer|min:0',
                'planet_perigee' => 'required|integer|min:0',
                'planet_orbital_inclination' => 'required|integer|min:0|max:360',
                'planet_average_distance' => 'required|integer|min:0',
                'planet_orbital_period' => 'required|integer|min:0',
                'planet_inclination_angle' => 'required|integer|min:0|max:360',
                'planet_rotation_period' => 'required|integer|min:0',
                'planet_mass' => 'required|integer|min:0',
                'planet_diameter' => 'required|integer|min:0',
                'planet_rings' => 'required|integer|min:0',
                'planet_initial_x' => 'required|integer',
                'planet_initial_y' => 'required|integer',
                'planet_initial_z' => 'required|integer'
            ]);

            if ($validated['planet_perigee'] > $validated['planet_apogee']) {
                return $this->error('Perigee must be less than apogee', 422);
            }

            $planet = Planet::create(array_merge($validated, [
                'solar_system_id' => $solarSystemId,
                'user_id' => Auth::id()
            ]));

            return $this->success($planet, 'Planet created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Error while creating planet', 500);
        }
    }

    public function update(Request $request, $galaxyId, $solarSystemId, $planetId)
    {
        try {
            $ownership = UserSystemOwnership::where('solar_system_id', $solarSystemId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$ownership) {
                return $this->error('You don\'t own this solar system', 403);
            }

            $planet = Planet::findOrFail($planetId);

            $validated = $request->validate([
                'planet_name' => 'sometimes|string|max:50',
                'planet_desc' => 'nullable|string|max:255',
                'planet_type' => 'sometimes|in:terrestrial,gas,ice,super_earth,sub_neptune,dwarf,lava,carbon,ocean',
                'planet_gravity' => 'sometimes|numeric|min:0',
                'planet_surface_temp' => 'sometimes|numeric|min:0',
                'planet_orbital_longitude' => 'sometimes|numeric|min:0|max:360',
                'planet_eccentricity' => 'sometimes|numeric|min:0|max:1',
                'planet_apogee' => 'sometimes|integer|min:0',
                'planet_perigee' => 'sometimes|integer|min:0',
                'planet_orbital_inclination' => 'sometimes|integer|min:0|max:360',
                'planet_average_distance' => 'sometimes|integer|min:0',
                'planet_orbital_period' => 'sometimes|integer|min:0',
                'planet_inclination_angle' => 'sometimes|integer|min:0|max:360',
                'planet_rotation_period' => 'sometimes|integer|min:0',
                'planet_mass' => 'sometimes|integer|min:0',
                'planet_diameter' => 'sometimes|integer|min:0',
                'planet_rings' => 'sometimes|integer|min:0',
                'planet_initial_x' => 'sometimes|integer',
                'planet_initial_y' => 'sometimes|integer',
                'planet_initial_z' => 'sometimes|integer'
            ]);

            if (isset($validated['planet_perigee']) && isset($validated['planet_apogee']) &&
                $validated['planet_perigee'] > $validated['planet_apogee']) {
                return $this->error('Perigee must be less than apogee', 422);
            }

            $planet->update($validated);

            return $this->success($planet, 'Planet updated successfully');
        } catch (\Exception $e) {
            return $this->error('Error while updating planet', 500);
        }
    }

    public function destroy($galaxyId, $solarSystemId, $planetId)
    {
        try {
            $ownership = UserSystemOwnership::where('solar_system_id', $solarSystemId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$ownership) {
                return $this->error('You don\'t own this solar system', 403);
            }

            $planet = Planet::findOrFail($planetId);
            $planet->delete();

            return $this->success(null, 'Planet deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Error while deleting planet', 500);
        }
    }

    public function getOwner($galaxyId, $solarSystemId, $planetId): JsonResponse
    {
        try {
            $planet = Planet::with(['user' => function ($query) {
                $query->select('user_id', 'user_login', 'user_email', 'user_role', 'user_date_inscription');
            }])
                ->where('solar_system_id', $solarSystemId)
                ->find($planetId);

            if (!$planet) {
                return $this->error('Planet not found', 404);
            }

            return $this->success(['owner' => $planet->user], 'Planet owner fetched');
        } catch (\Exception $e) {
            return $this->error('Error while fetching planet owner', 500);
        }
    }

    private function checkPlanetOwnership($solarSystemId)
    {
        $userId = Auth::id();

        $ownership = UserSolarSystemOwnership::where('solar_system_id', $solarSystemId)
            ->where('user_id', $userId)
            ->first();
        return !!$ownership;
    }
}