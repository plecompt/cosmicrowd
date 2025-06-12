<?php

namespace App\Http\Controllers;

use App\Models\LikerStar;
use App\Models\LikerPlanet;
use App\Models\LikerMoon;
use App\Models\Star;
use App\Models\Planet;
use App\Models\Moon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
    // Liker/Unliker une étoile
    public function toggleStarLike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'star_id' => 'required|integer|exists:star,star_id',
            'user_id' => 'required|integer|exists:user,user_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $starId = $request->star_id;
        $userId = $request->user_id;

        $existingLike = LikerStar::where('star_id', $starId)
                                ->where('user_id', $userId)
                                ->first();

        if ($existingLike) {
            // Unliker
            $existingLike->delete();
            $action = 'unliked';
        } else {
            // Liker
            LikerStar::create([
                'star_id' => $starId,
                'user_id' => $userId,
                'liker_star_date' => now()
            ]);
            $action = 'liked';
        }

        $star = Star::with(['likes', 'user'])->find($starId);
        $likesCount = $star->likes()->count();

        return response()->json([
            'success' => true,
            'message' => 'Étoile ' . $action . ' avec succès',
            'action' => $action,
            'likes_count' => $likesCount,
            'star' => $star
        ]);
    }

    // Liker/Unliker une planète
    public function togglePlanetLike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'planet_id' => 'required|integer|exists:planet,planet_id',
            'user_id' => 'required|integer|exists:user,user_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $planetId = $request->planet_id;
        $userId = $request->user_id;

        $existingLike = LikerPlanet::where('planet_id', $planetId)
                                  ->where('user_id', $userId)
                                  ->first();

        if ($existingLike) {
            // Unliker
            $existingLike->delete();
            $action = 'unliked';
        } else {
            // Liker
            LikerPlanet::create([
                'planet_id' => $planetId,
                'user_id' => $userId,
                'liker_planet_date' => now()
            ]);
            $action = 'liked';
        }

        $planet = Planet::with(['likes', 'star.user'])->find($planetId);
        $likesCount = $planet->likes()->count();

        return response()->json([
            'success' => true,
            'message' => 'Planète ' . $action . ' avec succès',
            'action' => $action,
            'likes_count' => $likesCount,
            'planet' => $planet
        ]);
    }

    // Liker/Unliker une lune
    public function toggleMoonLike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'moon_id' => 'required|integer|exists:moon,moon_id',
            'user_id' => 'required|integer|exists:user,user_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $moonId = $request->moon_id;
        $userId = $request->user_id;

        $existingLike = LikerMoon::where('moon_id', $moonId)
                                ->where('user_id', $userId)
                                ->first();

        if ($existingLike) {
            // Unliker
            $existingLike->delete();
            $action = 'unliked';
        } else {
            // Liker
            LikerMoon::create([
                'moon_id' => $moonId,
                'user_id' => $userId,
                'liker_moon_date' => now()
            ]);
            $action = 'liked';
        }

        $moon = Moon::with(['likes', 'planet.star.user'])->find($moonId);
        $likesCount = $moon->likes()->count();

        return response()->json([
            'success' => true,
            'message' => 'Lune ' . $action . ' avec succès',
            'action' => $action,
            'likes_count' => $likesCount,
            'moon' => $moon
        ]);
    }

    // Récupérer les étoiles likées par un utilisateur
    public function getUserLikedStars($userId)
    {
        $likedStars = Star::whereHas('likes', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['user', 'planets', 'likes'])->get();

        return response()->json([
            'success' => true,
            'liked_stars' => $likedStars
        ]);
    }

    // Récupérer les planètes likées par un utilisateur
    public function getUserLikedPlanets($userId)
    {
        $likedPlanets = Planet::whereHas('likes', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['star.user', 'moons', 'likes'])->get();

        return response()->json([
            'success' => true,
            'liked_planets' => $likedPlanets
        ]);
    }

    // Récupérer les lunes likées par un utilisateur
    public function getUserLikedMoons($userId)
    {
        $likedMoons = Moon::whereHas('likes', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['planet.star.user', 'likes'])->get();

        return response()->json([
            'success' => true,
            'liked_moons' => $likedMoons
        ]);
    }

    // Récupérer tous les likes d'un utilisateur
    public function getUserLikes($userId)
    {
        $likedStars = $this->getUserLikedStars($userId)->getData()->liked_stars;
        $likedPlanets = $this->getUserLikedPlanets($userId)->getData()->liked_planets;
        $likedMoons = $this->getUserLikedMoons($userId)->getData()->liked_moons;

        return response()->json([
            'success' => true,
            'user_likes' => [
                'stars' => $likedStars,
                'planets' => $likedPlanets,
                'moons' => $likedMoons
            ]
        ]);
    }

    // Vérifier si un utilisateur a liké un élément
    public function checkUserLike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:user,user_id',
            'type' => 'required|string|in:star,planet,moon',
            'item_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $userId = $request->user_id;
        $type = $request->type;
        $itemId = $request->item_id;

        $isLiked = false;

        switch ($type) {
            case 'star':
                $isLiked = LikerStar::isLikedBy($itemId, $userId);
                break;
            case 'planet':
                $isLiked = LikerPlanet::isLikedBy($itemId, $userId);
                break;
            case 'moon':
                $isLiked = LikerMoon::isLikedBy($itemId, $userId);
                break;
        }

        return response()->json([
            'success' => true,
            'is_liked' => $isLiked
        ]);
    }
}
