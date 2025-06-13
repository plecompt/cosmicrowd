<?php

namespace App\Http\Controllers;

use App\Models\SolarSystem;
use App\Models\User;
use App\Models\Planet;
use App\Models\Moon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SolarSystemController extends Controller
{
    /**
     * Retourne la liste des systèmes solaires d'une galaxie
     */
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
            return response()->json([
                'error' => 'Erreur lors de la récupération des systèmes solaires'
            ], 500);
        }
    }

    /**
     * Retourne un système solaire spécifique
     */
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
                    'error' => 'Système solaire non trouvé'
                ], 404);
            }

            return response()->json($solarSystem);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du système solaire'
            ], 500);
        }
    }

    /**
     * Retourne le propriétaire d'un système solaire
     */
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
                    'error' => 'Aucun propriétaire trouvé pour ce système solaire'
                ], 404);
            }

            return response()->json([
                'owner' => $solarSystem->owner
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans getOwner: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'error' => 'Erreur lors de la récupération du propriétaire',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retourne le propriétaire d'une lune
     */
    public function getMoonOwner($moonId): JsonResponse
    {
        try {
            $moon = Moon::with(['user' => function($query) {
                $query->select('user_id', 'user_login', 'user_email', 'user_role', 'user_date_inscription');
            }])
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

    // // Créer un nouveau système solaire
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'solar_system_name' => 'required|string|max:50',
    //         'solar_system_desc' => 'nullable|string|max:255',
    //         'solar_system_type' => 'required|string|max:50',
    //         'solar_system_gravity' => 'required|numeric|min:0',
    //         'solar_system_surface_temp' => 'required|numeric|min:-273.15',
    //         'solar_system_diameter' => 'required|integer|min:0',
    //         'solar_system_mass' => 'required|numeric|min:0',
    //         'solar_system_luminosity' => 'required|integer|min:0',
    //         'galaxy_id' => 'required|exists:galaxy,galaxy_id'
    //     ]);

    //     $solarSystem = new SolarSystem();
    //     $solarSystem->solar_system_name = $request->solar_system_name;
    //     $solarSystem->solar_system_desc = $request->solar_system_desc;
    //     $solarSystem->solar_system_type = $request->solar_system_type;
    //     $solarSystem->solar_system_gravity = $request->solar_system_gravity;
    //     $solarSystem->solar_system_surface_temp = $request->solar_system_surface_temp;
    //     $solarSystem->solar_system_diameter = $request->solar_system_diameter;
    //     $solarSystem->solar_system_mass = $request->solar_system_mass;
    //     $solarSystem->solar_system_luminosity = $request->solar_system_luminosity;
    //     $solarSystem->galaxy_id = $request->galaxy_id;
    //     $solarSystem->user_id = Auth::id();
        
    //     // Générer des coordonnées aléatoires pour l'initialisation
    //     $solarSystem->solar_system_initial_x = rand(-1000, 1000);
    //     $solarSystem->solar_system_initial_y = rand(-1000, 1000);
    //     $solarSystem->solar_system_initial_z = rand(-1000, 1000);
        
    //     $solarSystem->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Solar System successfully created',
    //         'solar_system' => $solarSystem
    //     ], 201);
    // }

    // // Mettre à jour un système solaire
    // public function update(Request $request, $id)
    // {
    //     $solarSystem = SolarSystem::find($id);

    //     if (!$solarSystem) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Solar System not found'
    //         ], 404);
    //     }

    //     if ($solarSystem->user_id !== Auth::id()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Cannot modify this solar system'
    //         ], 403);
    //     }

    //     $request->validate([
    //         'solar_system_name' => 'sometimes|required|string|max:50',
    //         'solar_system_desc' => 'sometimes|nullable|string|max:255',
    //         'solar_system_type' => 'sometimes|required|string|max:50',
    //         'solar_system_gravity' => 'sometimes|required|numeric|min:0',
    //         'solar_system_surface_temp' => 'sometimes|required|numeric|min:-273.15',
    //         'solar_system_diameter' => 'sometimes|required|integer|min:0',
    //         'solar_system_mass' => 'sometimes|required|numeric|min:0',
    //         'solar_system_luminosity' => 'sometimes|required|integer|min:0',
    //         'galaxy_id' => 'sometimes|required|exists:galaxy,galaxy_id'
    //     ]);

    //     $solarSystem->update($request->only([
    //         'solar_system_name',
    //         'solar_system_desc',
    //         'solar_system_type',
    //         'solar_system_gravity',
    //         'solar_system_surface_temp',
    //         'solar_system_diameter',
    //         'solar_system_mass',
    //         'solar_system_luminosity',
    //         'galaxy_id'
    //     ]));

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Solar System updated successfully',
    //         'solar_system' => $solarSystem
    //     ]);
    // }

    // // Supprimer un système solaire
    // public function destroy($id)
    // {
    //     $solarSystem = SolarSystem::find($id);

    //     if (!$solarSystem) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Solar System not found'
    //         ], 404);
    //     }

    //     if ($solarSystem->user_id !== Auth::id()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unauthorized to delete this solar system'
    //         ], 403);
    //     }

    //     $solarSystem->delete();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Solar System deleted successfully'
    //     ]);
    // }

}
