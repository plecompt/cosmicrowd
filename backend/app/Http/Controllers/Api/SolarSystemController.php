<?php

namespace App\Http\Controllers\Api;

use App\Models\SolarSystem;
use App\Models\User;
use App\Models\Planet;
use App\Models\Moon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SolarSystemController
{
    // Get all solarSystem for given galaxyId with full information
    public function index($galaxyId): JsonResponse
    {
        try {
            $solarSystems = SolarSystem::where('galaxy_id', $galaxyId)
                ->select([
                    'solar_system_id',
                    'solar_system_name',
                    'solar_system_desc',
                    'solar_system_type',
                    'solar_system_gravity',
                    'solar_system_surface_temp',
                    'solar_system_diameter',
                    'solar_system_mass',
                    'solar_system_luminosity',
                    'solar_system_initial_x',
                    'solar_system_initial_y',
                    'solar_system_initial_z',
                    'galaxy_id'
                ])
                ->get();

            return response()->json($solarSystems);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while getting solar systems'], 500);
        }
    }

    // Get SolarSystem for given solarSystemId
    public function show($galaxyId, $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
                ->where('solar_system_id', $solarSystemId)
                ->select([
                    'solar_system_id',
                    'solar_system_name',
                    'solar_system_desc',
                    'solar_system_type',
                    'solar_system_gravity',
                    'solar_system_surface_temp',
                    'solar_system_diameter',
                    'solar_system_mass',
                    'solar_system_luminosity',
                    'solar_system_initial_x',
                    'solar_system_initial_y',
                    'solar_system_initial_z',
                    'galaxy_id'
                ])
                ->first();

            if (!$solarSystem) {
                return response()->json([
                    'error' => 'Solar system not found'
                ], 404);
            }

            return response()->json($solarSystem);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while getting solar system'], 500);
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

            if (!$solarSystem->owner) {
                return response()->json([
                    'error' => 'No owner for this solar system'
                ], 404);
            }

            return response()->json([
                'owner' => $solarSystem->owner
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while getting owner'], 500);
        }
    }

    // Add new solar system
    public function add(Request $request, $galaxyId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'solar_system_name' => 'required|string|max:50',
            'solar_system_desc' => 'nullable|string|max:255',
            'solar_system_type' => 'required|in:brown_dwarf,red_dwarf,yellow_dwarf,white_dwarf,red_giant,blue_giant,red_supergiant,blue_supergiant,hypergiant,neutron_star,pulsar,variable,binary,ternary,black_hole',
            'solar_system_gravity' => 'required|numeric|min:0',
            'solar_system_surface_temp' => 'required|numeric|min:0',
            'solar_system_diameter' => 'required|integer|min:0',
            'solar_system_mass' => 'required|integer|min:0',
            'solar_system_luminosity' => 'required|integer|min:0',
            'solar_system_initial_x' => 'required|integer',
            'solar_system_initial_y' => 'required|integer',
            'solar_system_initial_z' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $solarSystem = new SolarSystem($validator->validated());
            $solarSystem->galaxy_id = $galaxyId;
            $solarSystem->save();

            return response()->json(['message' => 'Solar system created successfully', 'solar_system' => $solarSystem], 201);
        } catch (\Exception $e) {
            Log::error('Error adding solar system: ' . $e->getMessage());
            return response()->json(['error' => 'Error while adding solar system'], 500);
        }
    }

    // Update solar system
    public function update(Request $request, $galaxyId, $solarSystemId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'solar_system_name' => 'sometimes|required|string|max:50',
            'solar_system_desc' => 'nullable|string|max:255',
            'solar_system_type' => 'sometimes|required|in:brown_dwarf,red_dwarf,yellow_dwarf,white_dwarf,red_giant,blue_giant,red_supergiant,blue_supergiant,hypergiant,neutron_star,pulsar,variable,binary,ternary,black_hole',
            'solar_system_gravity' => 'sometimes|required|numeric|min:0',
            'solar_system_surface_temp' => 'sometimes|required|numeric|min:0',
            'solar_system_diameter' => 'sometimes|required|integer|min:0',
            'solar_system_mass' => 'sometimes|required|integer|min:0',
            'solar_system_luminosity' => 'sometimes|required|integer|min:0',
            'solar_system_initial_x' => 'sometimes|required|integer',
            'solar_system_initial_y' => 'sometimes|required|integer',
            'solar_system_initial_z' => 'sometimes|required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)->where('solar_system_id', $solarSystemId)->first();

            if (!$solarSystem) {
                return response()->json(['error' => 'Solar system not found'], 404);
            }

            $solarSystem->update($validator->validated());

            return response()->json(['message' => 'Solar system updated successfully', 'solar_system' => $solarSystem]);
        } catch (\Exception $e) {
            Log::error('Error updating solar system: ' . $e->getMessage());
            return response()->json(['error' => 'Error while updating solar system'], 500);
        }
    }

    // Delete solar system
    public function delete($galaxyId, $solarSystemId): JsonResponse
    {
        try {
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)->where('solar_system_id', $solarSystemId)->first();

            if (!$solarSystem) {
                return response()->json(['error' => 'Solar system not found'], 404);
            }

            $solarSystem->delete();

            return response()->json(['message' => 'Solar system deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting solar system: ' . $e->getMessage());
            return response()->json(['error' => 'Error while deleting solar system'], 500);
        }
    }
}
