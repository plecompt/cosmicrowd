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
        $planet = Planet::find($planetId);
        
        if (!$planet) {
            return $this->error('Planet not found', 404);
        }
        
        $planet->moons()->delete();
        $planet->delete();
        
        return $this->success(null, 'Planet and its moons deleted successfully');
    }

    public function getOwner($galaxyId, $solarSystemId, $planetId)
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
}