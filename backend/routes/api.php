<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GalaxyController;
use App\Http\Controllers\StarController;
use App\Http\Controllers\PlanetController;
use App\Http\Controllers\MoonController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\SearchController;

// Routes publiques (pas d'authentification requise)
Route::prefix('v1')->group(function () {
    
    // Authentification
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    
    // Galaxie (lecture seule)
    Route::get('galaxy', [GalaxyController::class, 'index']);
    Route::get('galaxy/stats', [GalaxyController::class, 'getStats']);
    Route::get('galaxy/stars/animation', [GalaxyController::class, 'getStarsForAnimation']);
    Route::get('galaxy/stars/most-liked', [GalaxyController::class, 'getMostLikedStars']);
    Route::get('galaxy/stars/recent', [GalaxyController::class, 'getRecentStars']);
    
    // Étoiles (lecture seule)
    Route::get('stars', [StarController::class, 'index']);
    Route::get('stars/{id}', [StarController::class, 'show']);
    Route::get('stars/most-liked', [StarController::class, 'getMostLikedStars']);
    Route::get('stars/recent', [StarController::class, 'getRecentStars']);
    
    // Planètes (lecture seule)
    Route::get('stars/{starId}/planets', [PlanetController::class, 'index']);
    Route::get('planets/{id}', [PlanetController::class, 'show']);
    
    // Lunes (lecture seule)
    Route::get('planets/{planetId}/moons', [MoonController::class, 'index']);
    Route::get('moons/{id}', [MoonController::class, 'show']);
    
    // Recherche
    Route::get('search', [SearchController::class, 'search']);
    
    // Nombre de likes (lecture seule)
    Route::get('stars/{id}/likes/count', [LikeController::class, 'count']);
    
});

// Routes protégées (authentification requise)
Route::prefix('v1')->middleware(['auth:sanctum', 'rate.limit'])->group(function () {
    
    // Authentification
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'user']);
    
    // Étoiles (CRUD complet)
    Route::post('stars', [StarController::class, 'store']);
    Route::middleware('check.owner:star')->group(function () {
        Route::put('stars/{id}', [StarController::class, 'update']);
        Route::delete('stars/{id}', [StarController::class, 'destroy']);
    });
    
    // Planètes (CRUD complet)
    Route::post('stars/{starId}/planets', [PlanetController::class, 'store']);
    Route::middleware('check.owner:planet')->group(function () {
        Route::put('planets/{id}', [PlanetController::class, 'update']);
        Route::delete('planets/{id}', [PlanetController::class, 'destroy']);
    });
    
    // Lunes (CRUD complet)
    Route::post('planets/{planetId}/moons', [MoonController::class, 'store']);
    Route::middleware('check.owner:moon')->group(function () {
        Route::put('moons/{id}', [MoonController::class, 'update']);
        Route::delete('moons/{id}', [MoonController::class, 'destroy']);
    });
    
    // Likes
    Route::post('stars/{id}/like', [LikeController::class, 'toggle']);
    Route::get('stars/{id}/likes/status', [LikeController::class, 'status']);
    Route::get('user/likes', [LikeController::class, 'userLikes']);
    
    // Mes créations
    Route::get('user/stars', [StarController::class, 'getStarsByUser']);
    
});

// Route catch-all pour les endpoints non existants
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint non trouvé',
        'available_endpoints' => [
            'POST /api/v1/auth/register' => 'Inscription',
            'POST /api/v1/auth/login' => 'Connexion',
            'GET /api/v1/galaxy' => 'Données de la galaxie',
            'GET /api/v1/stars' => 'Liste des étoiles',
            'GET /api/v1/search' => 'Recherche globale',
        ]
    ], 404);
});
