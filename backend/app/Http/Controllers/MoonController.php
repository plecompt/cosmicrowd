<?php

namespace App\Http\Controllers;

use App\Models\Moon;
use App\Models\Planet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MoonController extends Controller
{
    // Récupérer toutes les lunes
    public function index(Request $request)
    {
        $query = Moon::with(['planet.star.user', 'likes']);

        // Filtrer par planète
        if ($request->has('planet_id')) {
            $query->where('planet_id', $request->planet_id);
        }

        // Recherche par nom
        if ($request->has('search')) {
            $query->where('moon_name', 'LIKE', '%' . $request->search . '%');
        }

        $moons = $query->orderBy('moon_average_distance', 'asc')
                      ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'moons' => $moons
        ]);
    }

    // Récupérer une lune spécifique
    public function show($id)
    {
        $moon = Moon::with(['planet.star.user', 'likes.user'])
                   ->find($id);

        if (!$moon) {
            return response()->json([
                'success' => false,
                'message' => 'Lune non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'moon' => $moon
        ]);
    }

    // Créer une nouvelle lune
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'moon_name' => 'required|string|max:100',
            'moon_type' => 'required|string|max:50',
            'moon_gravity' => 'required|numeric|min:0.01|max:10',
            'moon_surface_temp' => 'required|numeric',
            'moon_orbital_longitude' => 'required|numeric',
            'moon_eccentricity' => 'required|numeric|min:0|max:1',
            'moon_apogee' => 'required|integer|min:0',
            'moon_perigee' => 'required|integer|min:0',
            'moon_orbital_inclination' => 'required|integer',
            'moon_average_distance' => 'required|integer|min:0',
            'moon_orbital_period' => 'required|integer|min:0',
            'moon_inclination_angle' => 'required|integer',
            'moon_rotation_period' => 'required|integer|min:0',
            'moon_mass' => 'required|integer|min:0',
            'moon_diameter' => 'required|integer|min:0',
            'moon_rings' => 'required|integer|min:0',
            'moon_initial_x' => 'required|integer',
            'moon_initial_y' => 'required|integer',
            'moon_initial_z' => 'required|integer',
            'planet_id' => 'required|integer|exists:planet,planet_id',
            'user_id' => 'required|integer|exists:user,user_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        // Vérifier que la planète existe
        $planet = Planet::find($request->planet_id);
        if (!$planet) {
            return response()->json([
                'success' => false,
                'message' => 'Planète non trouvée'
            ], 404);
        }

        $moon = Moon::create([
            'moon_name' => $request->moon_name,
            'moon_type' => $request->moon_type,
            'moon_gravity' => $request->moon_gravity,
            'moon_surface_temp' => $request->moon_surface_temp,
            'moon_orbital_longitude' => $request->moon_orbital_longitude,
            'moon_eccentricity' => $request->moon_eccentricity,
            'moon_apogee' => $request->moon_apogee,
            'moon_perigee' => $request->moon_perigee,
            'moon_orbital_inclination' => $request->moon_orbital_inclination,
            'moon_average_distance' => $request->moon_average_distance,
            'moon_orbital_period' => $request->moon_orbital_period,
            'moon_inclination_angle' => $request->moon_inclination_angle,
            'moon_rotation_period' => $request->moon_rotation_period,
            'moon_mass' => $request->moon_mass,
            'moon_diameter' => $request->moon_diameter,
            'moon_rings' => $request->moon_rings,
            'moon_initial_x' => $request->moon_initial_x,
            'moon_initial_y' => $request->moon_initial_y,
            'moon_initial_z' => $request->moon_initial_z,
            'planet_id' => $request->planet_id,
            'user_id' => $request->user_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lune créée avec succès',
            'moon' => $moon->load(['planet.star.user'])
        ], 201);
    }

    // Mettre à jour une lune
    public function update(Request $request, $id)
    {
        $moon = Moon::find($id);

        if (!$moon) {
            return response()->json([
                'success' => false,
                'message' => 'Lune non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'moon_name' => 'sometimes|required|string|max:100',
            'moon_type' => 'sometimes|required|string|max:50',
            'moon_gravity' => 'sometimes|required|numeric|min:0.01|max:10',
            'moon_surface_temp' => 'sometimes|required|numeric',
            'moon_orbital_longitude' => 'sometimes|required|numeric',
            'moon_eccentricity' => 'sometimes|required|numeric|min:0|max:1',
            'moon_apogee' => 'sometimes|required|integer|min:0',
            'moon_perigee' => 'sometimes|required|integer|min:0',
            'moon_orbital_inclination' => 'sometimes|required|integer',
            'moon_average_distance' => 'sometimes|required|integer|min:0',
            'moon_orbital_period' => 'sometimes|required|integer|min:0',
            'moon_inclination_angle' => 'sometimes|required|integer',
            'moon_rotation_period' => 'sometimes|required|integer|min:0',
            'moon_mass' => 'sometimes|required|integer|min:0',
            'moon_diameter' => 'sometimes|required|integer|min:0',
            'moon_rings' => 'sometimes|required|integer|min:0',
            'moon_initial_x' => 'sometimes|required|integer',
            'moon_initial_y' => 'sometimes|required|integer',
            'moon_initial_z' => 'sometimes|required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $moon->update($request->only([
            'moon_name', 'moon_type', 'moon_gravity', 'moon_surface_temp',
            'moon_orbital_longitude', 'moon_eccentricity', 'moon_apogee',
            'moon_perigee', 'moon_orbital_inclination', 'moon_average_distance',
            'moon_orbital_period', 'moon_inclination_angle', 'moon_rotation_period',
            'moon_mass', 'moon_diameter', 'moon_rings', 'moon_initial_x',
            'moon_initial_y', 'moon_initial_z'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Lune mise à jour avec succès',
            'moon' => $moon->load(['planet.star.user'])
        ]);
    }

    // Supprimer une lune
    public function destroy($id)
    {
        $moon = Moon::find($id);

        if (!$moon) {
            return response()->json([
                'success' => false,
                'message' => 'Lune non trouvée'
            ], 404);
        }

        $moon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lune supprimée avec succès'
        ]);
    }

    // Récupérer les lunes d'une planète spécifique
    public function getByPlanetId($planetId)
    {
        $planet = Planet::find($planetId);
        if (!$planet) {
            return response()->json([
                'success' => false,
                'message' => 'Planète non trouvée'
            ], 404);
        }

        $moons = Moon::with(['likes'])
                     ->where('planet_id', $planetId)
                     ->orderBy('moon_average_distance', 'asc')
                     ->get();

        return response()->json([
            'success' => true,
            'planet' => $planet,
            'moons' => $moons
        ]);
    }
}
