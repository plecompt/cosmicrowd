<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GalaxyController;
use App\Http\Controllers\SolarSystemController;
use App\Http\Controllers\PlanetController;
use App\Http\Controllers\MoonController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\LikerMoonController;
use App\Http\Controllers\LikerPlanetController;
use App\Http\Controllers\LikerSolarSystemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSolarSystemOwnershipController;
use App\Http\Controllers\RecoveryTokenController;

// Routes publiques (pas d'authentification requise)
Route::prefix('v1')->group(function () {
    // Authentification
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/reset-password', [RecoveryTokenController::class, 'resetPassword']);
    
    // Galaxies et leurs systèmes solaires
    Route::get('galaxies', [GalaxyController::class, 'index']); //liste des galaxies avec leurs stats
    Route::get('galaxies/{id}', [GalaxyController::class, 'show']); //une galaxie avec ses stats
    Route::get('galaxies/{id}/animation', [GalaxyController::class, 'getSolarSystemsForAnimation']); //liste des systemes solaires pour l'animation
    Route::get('galaxies/{id}/most-liked', [GalaxyController::class, 'getMostLikedSolarSystems']); //les x systemes solaires les plus aimés
    Route::get('galaxies/{id}/recent', [GalaxyController::class, 'getRecentSolarSystems']); //les x systemes solaires les plus récents
    
    // Systèmes solaires d'une galaxie
    Route::get('galaxies/{galaxyId}/solar-systems', [SolarSystemController::class, 'index']); //liste des systemes solaire pour cette galaxie
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}', [SolarSystemController::class, 'show']); //le systeme solaire avec ses stats pour cette galaxie
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/likes', [LikeController::class, 'count']); //nombre de likes pour ce systeme solaire
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/likes-stats', [LikeController::class, 'stats']); //stats des likes (infos plus completes) pour ce systeme solaire
    
    // Planètes d'un système solaire
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets', [PlanetController::class, 'index']); //liste des planetes pour ce systeme solaire
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}', [PlanetController::class, 'show']); //une planète avec ses stats
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/likes', [LikeController::class, 'count']); //nombre de likes pour cette planete
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/likes-stats', [LikeController::class, 'stats']); //stats des likes (infos plus completes) pour cette planete
    
    // Lunes d'une planète
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons', [MoonController::class, 'index']); //liste des lunes pour cette planete
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}', [MoonController::class, 'show']); //une lune avec ses stats
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}/likes', [LikeController::class, 'count']); //nombre de likes pour cette lune
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}/likes-stats', [LikeController::class, 'stats']); //stats des likes (infos plus completes) pour cette lune
    
    // Search
    Route::get('search', [SearchController::class, 'search']); //retourne une liste de systemes solaires, planetes, lunes, galaxies, etc.
    
    // User Solar System Ownership (lecture seule)
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/ownership', [UserSolarSystemOwnershipController::class, 'show']); //retourne si le système est claim ou non
});

// Routes protégées (authentification requise)
Route::prefix('v1')->middleware(['auth:sanctum', 'rate.limit'])->group(function () {
    // Authentification
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);
    Route::post('auth/change-email', [AuthController::class, 'changeEmail']);
    
    // Galaxies (modification) Pas de modification des galaxies, seul l'admin peut les modifier/creer/supprimer
    //Route::post('galaxies', [GalaxyController::class, 'store']);
    //Route::put('galaxies/{id}', [GalaxyController::class, 'update']);
    //Route::delete('galaxies/{id}', [GalaxyController::class, 'destroy']);
    
    // Solar Systems (modification) Pour l'instant, pas de modification des systemes solaires, a voir plus tard
    //Route::post('galaxies/{galaxyId}/solar-systems', [SolarSystemController::class, 'store']);
    //Route::put('galaxies/{galaxyId}/solar-systems/{solarSystemId}', [SolarSystemController::class, 'update']);
    //Route::delete('galaxies/{galaxyId}/solar-systems/{solarSystemId}', [SolarSystemController::class, 'destroy']);
    
    // Planets (modification)
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets', [PlanetController::class, 'store']);
    Route::put('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}', [PlanetController::class, 'update']);
    Route::delete('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}', [PlanetController::class, 'destroy']);
    
    // Moons (modification)
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons', [MoonController::class, 'store']);
    Route::put('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}', [MoonController::class, 'update']);
    Route::delete('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}', [MoonController::class, 'destroy']);
    
    // Liker Routes (toutes privées)
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/to-like', [LikeController::class, 'toggleSolarSystem']); //to like ou unlike un systeme solaire
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/to-like', [LikeController::class, 'togglePlanet']); //to like ou unlike une planete
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}/to-like', [LikeController::class, 'toggleMoon']); //to like ou unlike une lune
    
    // User Solar System Ownership (modification)
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/ownership', [UserSolarSystemOwnershipController::class, 'store']);
    Route::put('galaxies/{galaxyId}/solar-systems/{solarSystemId}/ownership', [UserSolarSystemOwnershipController::class, 'update']);
    Route::delete('galaxies/{galaxyId}/solar-systems/{solarSystemId}/ownership', [UserSolarSystemOwnershipController::class, 'destroy']);
});


// Route catch-all pour les endpoints non existants
Route::fallback(function () {
    return response()->json(['message' => 'Endpoint not found'], 404);
});
