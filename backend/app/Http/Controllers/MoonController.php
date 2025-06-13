<?php

namespace App\Http\Controllers;

use App\Models\Moon;
use App\Models\Planet;
use App\Models\UserSystemOwnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class MoonController extends Controller
{
    /**
     * Retourne la liste des lunes d'une planète
     */
    public function index($galaxyId, $solarSystemId, $planetId)
    {
        try {
            $moons = Moon::where('planet_id', $planetId)->get();
            return response()->json($moons);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while fetching moons'], 500);
        }
    }

    /**
     * Retourne une lune spécifique
     */
    public function show($galaxyId, $solarSystemId, $planetId, $moonId)
    {
        try {
            $moon = Moon::findOrFail($moonId);
            return response()->json($moon);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while fetching moon'], 500);
        }
    }

    /**
     * Crée une nouvelle lune
     */
    public function store(Request $request, $galaxyId, $solarSystemId, $planetId)
    {
        try {
            // Vérifie que l'utilisateur possède le système solaire
            $ownership = UserSystemOwnership::where('solar_system_id', $solarSystemId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$ownership) {
                return response()->json(['error' => 'You don\'t own this solar system'], 403);
            }

            $validated = $request->validate([
                'moon_name' => 'required|string|max:50',
                'moon_desc' => 'nullable|string|max:255',
                'moon_type' => 'required|in:rocky,icy,mixed,primitive,regular,irregular,trojan,coorbital',
                'moon_gravity' => 'required|numeric|min:0',
                'moon_surface_temp' => 'required|numeric|min:-273.15',
                'moon_orbital_longitude' => 'required|numeric|min:0|max:360',
                'moon_eccentricity' => 'required|numeric|min:0|max:1',
                'moon_apogee' => 'required|integer|min:0',
                'moon_perigee' => 'required|integer|min:0',
                'moon_orbital_inclination' => 'required|integer|min:0|max:360',
                'moon_average_distance' => 'required|integer|min:0',
                'moon_orbital_period' => 'required|integer|min:0',
                'moon_inclination_angle' => 'required|integer|min:0|max:360',
                'moon_rotation_period' => 'required|integer|min:0',
                'moon_mass' => 'required|integer|min:0',
                'moon_diameter' => 'required|integer|min:0',
                'moon_rings' => 'required|integer|min:0',
                'moon_initial_x' => 'required|integer',
                'moon_initial_y' => 'required|integer',
                'moon_initial_z' => 'required|integer'
            ]);

            // Vérifie que le périgée est inférieur à l'apogée
            if ($validated['moon_perigee'] > $validated['moon_apogee']) {
                return response()->json(['error' => 'Perigee must be less than apogee'], 422);
            }

            $moon = Moon::create([
                'planet_id' => $planetId,
                'user_id' => Auth::id(),
                'moon_name' => $validated['moon_name'],
                'moon_desc' => $validated['moon_desc'],
                'moon_type' => $validated['moon_type'],
                'moon_gravity' => $validated['moon_gravity'],
                'moon_surface_temp' => $validated['moon_surface_temp'],
                'moon_orbital_longitude' => $validated['moon_orbital_longitude'],
                'moon_eccentricity' => $validated['moon_eccentricity'],
                'moon_apogee' => $validated['moon_apogee'],
                'moon_perigee' => $validated['moon_perigee'],
                'moon_orbital_inclination' => $validated['moon_orbital_inclination'],
                'moon_average_distance' => $validated['moon_average_distance'],
                'moon_orbital_period' => $validated['moon_orbital_period'],
                'moon_inclination_angle' => $validated['moon_inclination_angle'],
                'moon_rotation_period' => $validated['moon_rotation_period'],
                'moon_mass' => $validated['moon_mass'],
                'moon_diameter' => $validated['moon_diameter'],
                'moon_rings' => $validated['moon_rings'],
                'moon_initial_x' => $validated['moon_initial_x'],
                'moon_initial_y' => $validated['moon_initial_y'],
                'moon_initial_z' => $validated['moon_initial_z']
            ]);

            return response()->json($moon, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while creating moon'], 500);
        }
    }

    /**
     * Met à jour une lune
     */
    public function update(Request $request, $galaxyId, $solarSystemId, $planetId, $moonId)
    {
        try {
            // Vérifie que l'utilisateur possède le système solaire
            $ownership = UserSystemOwnership::where('solar_system_id', $solarSystemId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$ownership) {
                return response()->json(['error' => 'You don\'t own this solar system'], 403);
            }

            $moon = Moon::findOrFail($moonId);

            $validated = $request->validate([
                'moon_name' => 'sometimes|string|max:50',
                'moon_desc' => 'nullable|string|max:255',
                'moon_type' => 'sometimes|in:rocky,icy,mixed,primitive,regular,irregular,trojan,coorbital',
                'moon_gravity' => 'sometimes|numeric|min:0',
                'moon_surface_temp' => 'sometimes|numeric|min:-273.15',
                'moon_orbital_longitude' => 'sometimes|numeric|min:0|max:360',
                'moon_eccentricity' => 'sometimes|numeric|min:0|max:1',
                'moon_apogee' => 'sometimes|integer|min:0',
                'moon_perigee' => 'sometimes|integer|min:0',
                'moon_orbital_inclination' => 'sometimes|integer|min:0|max:360',
                'moon_average_distance' => 'sometimes|integer|min:0',
                'moon_orbital_period' => 'sometimes|integer|min:0',
                'moon_inclination_angle' => 'sometimes|integer|min:0|max:360',
                'moon_rotation_period' => 'sometimes|integer|min:0',
                'moon_mass' => 'sometimes|integer|min:0',
                'moon_diameter' => 'sometimes|integer|min:0',
                'moon_rings' => 'sometimes|integer|min:0',
                'moon_initial_x' => 'sometimes|integer',
                'moon_initial_y' => 'sometimes|integer',
                'moon_initial_z' => 'sometimes|integer'
            ]);

            // Vérifie que le périgée est inférieur à l'apogée si les deux sont fournis
            if (isset($validated['moon_perigee']) && isset($validated['moon_apogee'])) {
                if ($validated['moon_perigee'] > $validated['moon_apogee']) {
                    return response()->json(['error' => 'Perigee must be less than apogee'], 422);
                }
            }

            $moon->update($validated);

            return response()->json($moon);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while updating moon'], 500);
        }
    }

    /**
     * Supprime une lune
     */
    public function destroy($galaxyId, $solarSystemId, $planetId, $moonId)
    {
        try {
            // Vérifie que l'utilisateur possède le système solaire
            $ownership = UserSystemOwnership::where('solar_system_id', $solarSystemId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$ownership) {
                return response()->json(['error' => 'You don\'t own this solar system'], 403);
            }

            $moon = Moon::findOrFail($moonId);
            $moon->delete();

            return response()->json(['message' => 'Moon deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while deleting moon'], 500);
        }
    }

    /**
     * Retourne le propriétaire d'une lune
     */
    public function getOwner($galaxyId, $solarSystemId, $planetId, $moonId): JsonResponse
    {
        try {
            $moon = Moon::with(['user' => function($query) {
                $query->select('user_id', 'user_login', 'user_email', 'user_role', 'user_date_inscription');
            }])
            ->where('planet_id', $planetId)
            ->find($moonId);

            if (!$moon) {
                return response()->json([
                    'error' => 'Lune non trouvée'
                ], 404);
            }

            return response()->json([
                'owner' => $moon->user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du propriétaire'
            ], 500);
        }
    }
}
