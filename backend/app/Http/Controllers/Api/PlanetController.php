<?php

namespace App\Http\Controllers\Api;

use App\Models\Planet;
use App\Models\Star;
use App\Models\SolarSystem;
use App\Models\UserSolarSystemOwnership;
use App\Models\UserSystemOwnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class PlanetController extends Controller
{
    // Return the list of planets for given SolarSystemId
    public function index($galaxyId, $solarSystemId)
    {
        try {
            $planets = Planet::where('solar_system_id', $solarSystemId)->get();
            return response()->json($planets);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while fetching planets'], 500);
        }
    }

    // Return the planet for given planetId
    public function show($galaxyId, $solarSystemId, $planetId)
    {
        try {
            $planet = Planet::findOrFail($planetId);
            return response()->json($planet);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while fetching planet'], 500);
        }
    }

    // Create a new planet for given solarSystemId
    public function store(Request $request, $galaxyId, $solarSystemId)
    {
        try {
            // Verify owner own solarSystem
            $ownership = UserSystemOwnership::where('solar_system_id', $solarSystemId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$ownership) {
                return response()->json(['error' => 'You don\'t own this solar system'], 403);
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

            // Checking perigee < apogee
            if ($validated['planet_perigee'] > $validated['planet_apogee']) {
                return response()->json(['error' => 'Perigee must be less than apogee'], 422);
            }

            $planet = Planet::create([
                'solar_system_id' => $solarSystemId,
                'user_id' => Auth::id(),
                'planet_name' => $validated['planet_name'],
                'planet_desc' => $validated['planet_desc'],
                'planet_type' => $validated['planet_type'],
                'planet_gravity' => $validated['planet_gravity'],
                'planet_surface_temp' => $validated['planet_surface_temp'],
                'planet_orbital_longitude' => $validated['planet_orbital_longitude'],
                'planet_eccentricity' => $validated['planet_eccentricity'],
                'planet_apogee' => $validated['planet_apogee'],
                'planet_perigee' => $validated['planet_perigee'],
                'planet_orbital_inclination' => $validated['planet_orbital_inclination'],
                'planet_average_distance' => $validated['planet_average_distance'],
                'planet_orbital_period' => $validated['planet_orbital_period'],
                'planet_inclination_angle' => $validated['planet_inclination_angle'],
                'planet_rotation_period' => $validated['planet_rotation_period'],
                'planet_mass' => $validated['planet_mass'],
                'planet_diameter' => $validated['planet_diameter'],
                'planet_rings' => $validated['planet_rings'],
                'planet_initial_x' => $validated['planet_initial_x'],
                'planet_initial_y' => $validated['planet_initial_y'],
                'planet_initial_z' => $validated['planet_initial_z']
            ]);

            return response()->json($planet, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while creating planet'], 500);
        }
    }

    // Update planet for given solarSystemId
    public function update(Request $request, $galaxyId, $solarSystemId, $planetId)
    {
        try {
            // Verify owner own solarSystem
            $ownership = UserSystemOwnership::where('solar_system_id', $solarSystemId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$ownership) {
                return response()->json(['error' => 'You don\'t own this solar system'], 403);
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

            // Check perigee < apogee
            if (isset($validated['planet_perigee']) && isset($validated['planet_apogee'])) {
                if ($validated['planet_perigee'] > $validated['planet_apogee']) {
                    return response()->json(['error' => 'Perigee must be less than apogee'], 422);
                }
            }

            $planet->update($validated);

            return response()->json($planet);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while updating planet'], 500);
        }
    }

    // Delete a planet with planetId
    public function destroy($galaxyId, $solarSystemId, $planetId)
    {
        try {
            // Check owner own this solarSystem
            $ownership = UserSystemOwnership::where('solar_system_id', $solarSystemId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$ownership) {
                return response()->json(['error' => 'You don\'t own this solar system'], 403);
            }

            $planet = Planet::findOrFail($planetId);
            $planet->delete();

            return response()->json(['message' => 'Planet deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while deleting planet'], 500);
        }
    }

    // get planet owner
    public function getOwner($galaxyId, $solarSystemId, $planetId): JsonResponse
    {
        try {
            $planet = Planet::with(['user' => function($query) {
                $query->select('user_id', 'user_login', 'user_email', 'user_role', 'user_date_inscription');
            }])
            ->where('solar_system_id', $solarSystemId)
            ->find($planetId);

            if (!$planet) {
                return response()->json([
                    'error' => 'Planète non trouvée'
                ], 404);
            }

            return response()->json([
                'owner' => $planet->user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du propriétaire'
            ], 500);
        }
    }

    //Verify that user own solarSystem
    private function checkPlanetOwnership($solarSystemId)
    {
        $userId = Auth::id();

        $ownership = UserSolarSystemOwnership::where('solar_system_id', $solarSystemId)
            ->where('user_id', $userId)
            ->first();
            
        if (!$ownership) {
            return false;
        }
        
        return true;
    }
}
