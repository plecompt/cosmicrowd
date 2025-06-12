<?php

namespace App\Http\Controllers;

use App\Models\Moon;
use App\Models\Planet;
use App\Models\UserSolarSystemOwnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MoonController extends Controller
{
    /**
     * Vérifie si l'utilisateur a le droit de modifier cette lune
     * Un utilisateur ne peut modifier que les lunes des planètes appartenant à ses systèmes claim
     */
    private function checkMoonOwnership($planetId)
    {
        $userId = Auth::id();
        
        // Récupère la planète et son système solaire
        $planet = Planet::with('solarSystem')->findOrFail($planetId);
        
        // Vérifie si l'utilisateur est propriétaire du système solaire
        $ownership = UserSolarSystemOwnership::where('solar_system_id', $planet->solar_system_id)
            ->where('user_id', $userId)
            ->first();
            
        if (!$ownership) {
            return false;
        }
        
        return true;
    }

    /**
     * Affiche la liste des lunes d'une planète
     */
    public function index($galaxyId, $solarSystemId, $planetId)
    {
        $moons = Moon::where('planet_id', $planetId)->get();
        return response()->json($moons);
    }

    /**
     * Affiche les détails d'une lune
     */
    public function show($galaxyId, $solarSystemId, $planetId, $moonId)
    {
        $moon = Moon::where('planet_id', $planetId)
            ->where('moon_id', $moonId)
            ->firstOrFail();
            
        return response()->json($moon);
    }

    /**
     * Crée une nouvelle lune
     */
    public function store(Request $request, $galaxyId, $solarSystemId, $planetId)
    {
        // Vérifie si l'utilisateur peut créer une lune pour cette planète
        if (!$this->checkMoonOwnership($planetId)) {
            return response()->json(['message' => 'Vous n\'avez pas la permission de créer une lune pour cette planète'], 403);
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
            'moon_initial_z' => 'required|integer',
        ]);

        // Vérifie que le périgée est inférieur à l'apogée
        if ($validated['moon_perigee'] > $validated['moon_apogee']) {
            return response()->json(['message' => 'Le périgée doit être inférieur à l\'apogée'], 422);
        }

        $validated['planet_id'] = $planetId;
        $validated['user_id'] = Auth::id();

        $moon = Moon::create($validated);
        return response()->json($moon, 201);
    }

    /**
     * Met à jour une lune
     */
    public function update(Request $request, $galaxyId, $solarSystemId, $planetId, $moonId)
    {
        // Vérifie si l'utilisateur peut modifier cette lune
        if (!$this->checkMoonOwnership($planetId)) {
            return response()->json(['message' => 'Vous n\'avez pas la permission de modifier cette lune'], 403);
        }

        $moon = Moon::where('planet_id', $planetId)
            ->where('moon_id', $moonId)
            ->firstOrFail();

        $validated = $request->validate([
            'moon_name' => 'sometimes|required|string|max:50',
            'moon_desc' => 'nullable|string|max:255',
            'moon_type' => 'sometimes|required|in:rocky,icy,mixed,primitive,regular,irregular,trojan,coorbital',
            'moon_gravity' => 'sometimes|required|numeric|min:0',
            'moon_surface_temp' => 'sometimes|required|numeric|min:-273.15',
            'moon_orbital_longitude' => 'sometimes|required|numeric|min:0|max:360',
            'moon_eccentricity' => 'sometimes|required|numeric|min:0|max:1',
            'moon_apogee' => 'sometimes|required|integer|min:0',
            'moon_perigee' => 'sometimes|required|integer|min:0',
            'moon_orbital_inclination' => 'sometimes|required|integer|min:0|max:360',
            'moon_average_distance' => 'sometimes|required|integer|min:0',
            'moon_orbital_period' => 'sometimes|required|integer|min:0',
            'moon_inclination_angle' => 'sometimes|required|integer|min:0|max:360',
            'moon_rotation_period' => 'sometimes|required|integer|min:0',
            'moon_mass' => 'sometimes|required|integer|min:0',
            'moon_diameter' => 'sometimes|required|integer|min:0',
            'moon_rings' => 'sometimes|required|integer|min:0',
            'moon_initial_x' => 'sometimes|required|integer',
            'moon_initial_y' => 'sometimes|required|integer',
            'moon_initial_z' => 'sometimes|required|integer',
        ]);

        // Vérifie que le périgée est inférieur à l'apogée si les deux sont fournis
        if (isset($validated['moon_perigee']) && isset($validated['moon_apogee'])) {
            if ($validated['moon_perigee'] > $validated['moon_apogee']) {
                return response()->json(['message' => 'Le périgée doit être inférieur à l\'apogée'], 422);
            }
        }

        $moon->update($validated);
        return response()->json($moon);
    }

    /**
     * Supprime une lune
     */
    public function destroy($galaxyId, $solarSystemId, $planetId, $moonId)
    {
        // Vérifie si l'utilisateur peut supprimer cette lune
        if (!$this->checkMoonOwnership($planetId)) {
            return response()->json(['message' => 'Vous n\'avez pas la permission de supprimer cette lune'], 403);
        }

        $moon = Moon::where('planet_id', $planetId)
            ->where('moon_id', $moonId)
            ->firstOrFail();

        $moon->delete();
        return response()->json(null, 204);
    }
}
