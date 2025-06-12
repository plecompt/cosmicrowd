<?php

namespace App\Http\Controllers;

use App\Models\Star;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StarController extends Controller
{
    // Récupérer toutes les étoiles
    public function index()
    {
        $stars = Star::with(['user', 'planets', 'likes'])
                    ->orderBy('star_id', 'desc')
                    ->get();

        return response()->json([
            'success' => true,
            'stars' => $stars
        ]);
    }

    // Récupérer une étoile spécifique
    public function show($id)
    {
        $star = Star::with(['user', 'planets.moons', 'likes'])
                    ->find($id);

        if (!$star) {
            return response()->json([
                'success' => false,
                'message' => 'Étoile non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'star' => $star
        ]);
    }

    // Créer une nouvelle étoile
    public function store(Request $request)
    {
        $request->validate([
            'star_name' => 'required|string|max:255',
            'star_type' => 'required|string|max:50',
            'star_gravity' => 'required|numeric',
            'star_surface_temp' => 'required|numeric',
            'star_diameter' => 'required|numeric',
            'star_mass' => 'required|numeric',
            'star_luminosity' => 'required|numeric',
            'galaxy_id' => 'required|exists:galaxies,galaxy_id'
        ]);

        $star = new Star();
        $star->star_name = $request->star_name;
        $star->star_type = $request->star_type;
        $star->star_gravity = $request->star_gravity;
        $star->star_surface_temp = $request->star_surface_temp;
        $star->star_diameter = $request->star_diameter;
        $star->star_mass = $request->star_mass;
        $star->star_luminosity = $request->star_luminosity;
        $star->galaxy_id = $request->galaxy_id;
        $star->user_id = Auth::id();
        $star->save();

        return response()->json([
            'success' => true,
            'message' => 'Étoile créée avec succès',
            'star' => $star
        ], 201);
    }

    // Mettre à jour une étoile
    public function update(Request $request, $id)
    {
        $star = Star::find($id);

        if (!$star) {
            return response()->json([
                'success' => false,
                'message' => 'Étoile non trouvée'
            ], 404);
        }

        if ($star->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé à modifier cette étoile'
            ], 403);
        }

        $request->validate([
            'star_name' => 'sometimes|required|string|max:255',
            'star_type' => 'sometimes|required|string|max:50',
            'star_gravity' => 'sometimes|required|numeric',
            'star_surface_temp' => 'sometimes|required|numeric',
            'star_diameter' => 'sometimes|required|numeric',
            'star_mass' => 'sometimes|required|numeric',
            'star_luminosity' => 'sometimes|required|numeric',
            'galaxy_id' => 'sometimes|required|exists:galaxies,galaxy_id'
        ]);

        $star->update($request->only([
            'star_name',
            'star_type',
            'star_gravity',
            'star_surface_temp',
            'star_diameter',
            'star_mass',
            'star_luminosity',
            'galaxy_id'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Étoile mise à jour avec succès',
            'star' => $star
        ]);
    }

    // Supprimer une étoile
    public function destroy($id)
    {
        $star = Star::find($id);

        if (!$star) {
            return response()->json([
                'success' => false,
                'message' => 'Étoile non trouvée'
            ], 404);
        }

        if ($star->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé à supprimer cette étoile'
            ], 403);
        }

        $star->delete();

        return response()->json([
            'success' => true,
            'message' => 'Étoile supprimée avec succès'
        ]);
    }

    public function getStarsByUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        $stars = Star::with(['planets.moons', 'likes'])
                    ->where('user_id', $userId)
                    ->orderBy('star_id', 'desc')
                    ->get();

        return response()->json([
            'success' => true,
            'user' => $user,
            'stars' => $stars
        ]);
    }

    public function getMostLikedStars()
    {
        $stars = Star::with(['user', 'planets', 'likes'])
                    ->withCount('likes')
                    ->orderBy('likes_count', 'desc')
                    ->take(10)
                    ->get();

        return response()->json([
            'success' => true,
            'stars' => $stars
        ]);
    }

    public function getRecentStars()
    {
        $stars = Star::with(['user', 'planets', 'likes'])
                    ->orderBy('star_id', 'desc')
                    ->take(10)
                    ->get();

        return response()->json([
            'success' => true,
            'stars' => $stars
        ]);
    }
}
