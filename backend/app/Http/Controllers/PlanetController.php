<?php

namespace App\Http\Controllers;

use App\Models\Planet;
use App\Models\Star;
use App\Models\SolarSystem;
use App\Models\UserSolarSystemOwnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PlanetController extends Controller
{
    /**
     * Vérifie si l'utilisateur a le droit de modifier cette planète
     * Un utilisateur ne peut modifier que les planètes des systèmes qu'il a claim
     */
    private function checkPlanetOwnership($solarSystemId)
    {
        $userId = Auth::id();
        
        // Vérifie si l'utilisateur est propriétaire du système solaire
        $ownership = UserSolarSystemOwnership::where('solar_system_id', $solarSystemId)
            ->where('user_id', $userId)
            ->first();
            
        if (!$ownership) {
            return false;
        }
        
        return true;
    }

    // Récupérer toutes les planètes
    public function index(Request $request)
    {
        $query = Planet::with(['star.user', 'moons', 'likes']);

        // Filtrer par étoile
        if ($request->has('star_id')) {
            $query->where('star_id', $request->star_id);
        }

        // Recherche par nom
        if ($request->has('search')) {
            $query->where('planet_name', 'LIKE', '%' . $request->search . '%');
        }

        $planets = $query->orderBy('planet_distance_from_star', 'asc')
                        ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'planets' => $planets
        ]);
    }

    // Récupérer une planète spécifique
    public function show($id)
    {
        $planet = Planet::with(['star.user', 'moons', 'likes.user'])
                       ->find($id);

        if (!$planet) {
            return response()->json([
                'success' => false,
                'message' => 'Planète non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'planet' => $planet
        ]);
    }

    // Créer une nouvelle planète
    public function store(Request $request)
    {
        // Vérifie si l'utilisateur peut créer une planète
        if (!$this->checkPlanetOwnership($request->solar_system_id)) {
            return response()->json(['message' => 'Vous n\'avez pas la permission de créer une planète dans ce système'], 403);
        }

        $validator = Validator::make($request->all(), [
            'planet_name' => 'required|string|max:100',
            'planet_type' => 'required|string|max:50',
            'planet_gravity' => 'required|numeric|min:0.01|max:100',
            'planet_surface_temp' => 'required|numeric',
            'planet_orbital_longitude' => 'required|numeric',
            'planet_eccentricity' => 'required|numeric|min:0|max:1',
            'planet_apogee' => 'required|integer|min:0',
            'planet_perigee' => 'required|integer|min:0',
            'planet_orbital_inclination' => 'required|integer',
            'planet_average_distance' => 'required|integer|min:0',
            'planet_orbital_period' => 'required|integer|min:0',
            'planet_inclination_angle' => 'required|integer',
            'planet_rotation_period' => 'required|integer|min:0',
            'planet_mass' => 'required|integer|min:0',
            'planet_diameter' => 'required|integer|min:0',
            'planet_rings' => 'required|integer|min:0',
            'planet_initial_x' => 'required|integer',
            'planet_initial_y' => 'required|integer',
            'planet_initial_z' => 'required|integer',
            'user_id' => 'required|integer|exists:user,user_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        // Vérifier que l'étoile existe
        $star = Star::find($request->star_id);
        if (!$star) {
            return response()->json([
                'success' => false,
                'message' => 'Étoile non trouvée'
            ], 404);
        }

        $planet = Planet::create([
            'planet_name' => $request->planet_name,
            'planet_type' => $request->planet_type,
            'planet_gravity' => $request->planet_gravity,
            'planet_surface_temp' => $request->planet_surface_temp,
            'planet_orbital_longitude' => $request->planet_orbital_longitude,
            'planet_eccentricity' => $request->planet_eccentricity,
            'planet_apogee' => $request->planet_apogee,
            'planet_perigee' => $request->planet_perigee,
            'planet_orbital_inclination' => $request->planet_orbital_inclination,
            'planet_average_distance' => $request->planet_average_distance,
            'planet_orbital_period' => $request->planet_orbital_period,
            'planet_inclination_angle' => $request->planet_inclination_angle,
            'planet_rotation_period' => $request->planet_rotation_period,
            'planet_mass' => $request->planet_mass,
            'planet_diameter' => $request->planet_diameter,
            'planet_rings' => $request->planet_rings,
            'planet_initial_x' => $request->planet_initial_x,
            'planet_initial_y' => $request->planet_initial_y,
            'planet_initial_z' => $request->planet_initial_z,
            'user_id' => $request->user_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Planète créée avec succès',
            'planet' => $planet->load(['star.user'])
        ], 201);
    }

    // Mettre à jour une planète
    public function update(Request $request, $id)
    {
        // Vérifie si l'utilisateur peut modifier cette planète
        if (!$this->checkPlanetOwnership($request->solar_system_id)) {
            return response()->json(['message' => 'Vous n\'avez pas la permission de modifier cette planète'], 403);
        }

        $planet = Planet::find($id);

        if (!$planet) {
            return response()->json([
                'success' => false,
                'message' => 'Planète non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'planet_name' => 'sometimes|required|string|max:100',
            'planet_type' => 'sometimes|required|string|max:50',
            'planet_gravity' => 'sometimes|required|numeric|min:0.01|max:100',
            'planet_surface_temp' => 'sometimes|required|numeric',
            'planet_orbital_longitude' => 'sometimes|required|numeric',
            'planet_eccentricity' => 'sometimes|required|numeric|min:0|max:1',
            'planet_apogee' => 'sometimes|required|integer|min:0',
            'planet_perigee' => 'sometimes|required|integer|min:0',
            'planet_orbital_inclination' => 'sometimes|required|integer',
            'planet_average_distance' => 'sometimes|required|integer|min:0',
            'planet_orbital_period' => 'sometimes|required|integer|min:0',
            'planet_inclination_angle' => 'sometimes|required|integer',
            'planet_rotation_period' => 'sometimes|required|integer|min:0',
            'planet_mass' => 'sometimes|required|integer|min:0',
            'planet_diameter' => 'sometimes|required|integer|min:0',
            'planet_rings' => 'sometimes|required|integer|min:0',
            'planet_initial_x' => 'sometimes|required|integer',
            'planet_initial_y' => 'sometimes|required|integer',
            'planet_initial_z' => 'sometimes|required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $planet->update($request->only([
            'planet_name', 'planet_type', 'planet_gravity', 'planet_surface_temp',
            'planet_orbital_longitude', 'planet_eccentricity', 'planet_apogee',
            'planet_perigee', 'planet_orbital_inclination', 'planet_average_distance',
            'planet_orbital_period', 'planet_inclination_angle', 'planet_rotation_period',
            'planet_mass', 'planet_diameter', 'planet_rings', 'planet_initial_x',
            'planet_initial_y', 'planet_initial_z'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Planète mise à jour avec succès',
            'planet' => $planet->load(['star.user'])
        ]);
    }

    // Supprimer une planète
    public function destroy($id)
    {
        // Vérifie si l'utilisateur peut supprimer cette planète
        if (!$this->checkPlanetOwnership($request->solar_system_id)) {
            return response()->json(['message' => 'Vous n\'avez pas la permission de supprimer cette planète'], 403);
        }

        $planet = Planet::find($id);

        if (!$planet) {
            return response()->json([
                'success' => false,
                'message' => 'Planète non trouvée'
            ], 404);
        }

        $planet->delete();

        return response()->json([
            'success' => true,
            'message' => 'Planète supprimée avec succès'
        ]);
    }

    // Récupérer les planètes d'une étoile spécifique
    public function getByStarId($starId)
    {
        $star = Star::find($starId);
        if (!$star) {
            return response()->json([
                'success' => false,
                'message' => 'Étoile non trouvée'
            ], 404);
        }

        $planets = Planet::with(['moons', 'likes'])
                         ->where('star_id', $starId)
                         ->orderBy('planet_distance_from_star', 'asc')
                         ->get();

        return response()->json([
            'success' => true,
            'star' => $star,
            'planets' => $planets
        ]);
    }
}
