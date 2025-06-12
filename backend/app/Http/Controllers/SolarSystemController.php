<?php

namespace App\Http\Controllers;

use App\Models\SolarSystem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SolarSystemController extends Controller
{
    // Récupérer tous les systèmes solaires
    public function index()
    {
        $solarSystems = SolarSystem::with(['user', 'planets', 'likes'])
                    ->orderBy('solar_system_id', 'desc')
                    ->get();

        return response()->json([
            'success' => true,
            'solar_systems' => $solarSystems
        ]);
    }

    // Récupérer un système solaire spécifique
    public function show($id)
    {
        $solarSystem = SolarSystem::with(['user', 'planets', 'likes'])
                    ->find($id);

        if (!$solarSystem) {
            return response()->json([
                'success' => false,
                'message' => 'Solar System not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'solar_system' => $solarSystem
        ]);
    }

    // Créer un nouveau système solaire
    public function store(Request $request)
    {
        $request->validate([
            'solar_system_name' => 'required|string|max:50',
            'solar_system_desc' => 'nullable|string|max:255',
            'solar_system_type' => 'required|string|max:50',
            'solar_system_gravity' => 'required|numeric|min:0',
            'solar_system_surface_temp' => 'required|numeric|min:-273.15',
            'solar_system_diameter' => 'required|integer|min:0',
            'solar_system_mass' => 'required|numeric|min:0',
            'solar_system_luminosity' => 'required|integer|min:0',
            'galaxy_id' => 'required|exists:galaxy,galaxy_id'
        ]);

        $solarSystem = new SolarSystem();
        $solarSystem->solar_system_name = $request->solar_system_name;
        $solarSystem->solar_system_desc = $request->solar_system_desc;
        $solarSystem->solar_system_type = $request->solar_system_type;
        $solarSystem->solar_system_gravity = $request->solar_system_gravity;
        $solarSystem->solar_system_surface_temp = $request->solar_system_surface_temp;
        $solarSystem->solar_system_diameter = $request->solar_system_diameter;
        $solarSystem->solar_system_mass = $request->solar_system_mass;
        $solarSystem->solar_system_luminosity = $request->solar_system_luminosity;
        $solarSystem->galaxy_id = $request->galaxy_id;
        $solarSystem->user_id = Auth::id();
        
        // Générer des coordonnées aléatoires pour l'initialisation
        $solarSystem->solar_system_initial_x = rand(-1000, 1000);
        $solarSystem->solar_system_initial_y = rand(-1000, 1000);
        $solarSystem->solar_system_initial_z = rand(-1000, 1000);
        
        $solarSystem->save();

        return response()->json([
            'success' => true,
            'message' => 'Solar System successfully created',
            'solar_system' => $solarSystem
        ], 201);
    }

    // Mettre à jour un système solaire
    public function update(Request $request, $id)
    {
        $solarSystem = SolarSystem::find($id);

        if (!$solarSystem) {
            return response()->json([
                'success' => false,
                'message' => 'Solar System not found'
            ], 404);
        }

        if ($solarSystem->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify this solar system'
            ], 403);
        }

        $request->validate([
            'solar_system_name' => 'sometimes|required|string|max:50',
            'solar_system_desc' => 'sometimes|nullable|string|max:255',
            'solar_system_type' => 'sometimes|required|string|max:50',
            'solar_system_gravity' => 'sometimes|required|numeric|min:0',
            'solar_system_surface_temp' => 'sometimes|required|numeric|min:-273.15',
            'solar_system_diameter' => 'sometimes|required|integer|min:0',
            'solar_system_mass' => 'sometimes|required|numeric|min:0',
            'solar_system_luminosity' => 'sometimes|required|integer|min:0',
            'galaxy_id' => 'sometimes|required|exists:galaxy,galaxy_id'
        ]);

        $solarSystem->update($request->only([
            'solar_system_name',
            'solar_system_desc',
            'solar_system_type',
            'solar_system_gravity',
            'solar_system_surface_temp',
            'solar_system_diameter',
            'solar_system_mass',
            'solar_system_luminosity',
            'galaxy_id'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Solar System updated successfully',
            'solar_system' => $solarSystem
        ]);
    }

    // Supprimer un système solaire
    public function destroy($id)
    {
        $solarSystem = SolarSystem::find($id);

        if (!$solarSystem) {
            return response()->json([
                'success' => false,
                'message' => 'Solar System not found'
            ], 404);
        }

        if ($solarSystem->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this solar system'
            ], 403);
        }

        $solarSystem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Solar System deleted successfully'
        ]);
    }

    // Récupérer les systèmes solaires d'un utilisateur
    public function getSolarSystemsByUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $solarSystems = SolarSystem::with(['planets.moons', 'likes'])
                    ->where('user_id', $userId)
                    ->orderBy('solar_system_id', 'desc')
                    ->get();

        return response()->json([
            'success' => true,
            'user' => $user,
            'solar_systems' => $solarSystems
        ]);
    }

    // Récupérer les systèmes solaires les plus likés
    public function getMostLikedSolarSystems()
    {
        $solarSystems = SolarSystem::with(['user', 'planets', 'likes'])
                    ->withCount('likes')
                    ->orderBy('likes_count', 'desc')
                    ->take(10)
                    ->get();

        return response()->json([
            'success' => true,
            'solar_systems' => $solarSystems
        ]);
    }

    // Récupérer les systèmes solaires les plus récents
    public function getRecentSolarSystems()
    {
        $solarSystems = SolarSystem::with(['user', 'planets', 'likes'])
                    ->orderBy('solar_system_id', 'desc')
                    ->take(10)
                    ->get();

        return response()->json([
            'success' => true,
            'solar_systems' => $solarSystems
        ]);
    }
}
