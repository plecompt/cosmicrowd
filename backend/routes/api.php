<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GalaxyController;
use App\Http\Controllers\Api\SolarSystemController;
use App\Http\Controllers\Api\PlanetController;
use App\Http\Controllers\Api\MoonController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ClaimController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\RateLimitMiddleware as RateLimit;

// Routes publiques (pas d'authentification requise)
Route::prefix('v1')->group(function () {
    // Authentification
    Route::post('auth/login', [AuthController::class, 'login']); //login
    
    // User
    Route::post('users/register', [UserController::class, 'register']); //inscription
    Route::post('users/forgot-password', [UserController::class, 'forgotPassword']); //envoi du token par mail
    Route::post('users/verify-token', [UserController::class, 'verifyToken']); // verification du token
    Route::post('users/reset-password', [UserController::class, 'resetPassword']); // changement de mdp apres mdp oublié
    Route::post('users/check-login', [UserController::class, 'checkLoginAvailability']); //verifie si un login est disponible en bdd
    Route::post('users/check-email', [UserController::class, 'checkEmailAvailability']); //verifie si un mdp est disponible en bdd
    Route::post('users/contact', [UserController::class, 'contact']); //envoi un mail à CosmiCrowd + confirmation à l'utiliateur
    Route::get('/user/{userId}', [UserController::class, 'view']);//retourne un user

    // GALAXIES et leurs systèmes solaires
    Route::get('galaxies', [GalaxyController::class, 'index']); //liste des galaxies avec leurs stats
    Route::get('galaxies/{id}', [GalaxyController::class, 'show']); //une galaxie avec ses stats
    Route::get('galaxies/{id}/animation', [GalaxyController::class, 'getSolarSystemsForAnimation']); //liste des systemes solaires pour l'animation
    Route::get('galaxies/{id}/most-liked', [GalaxyController::class, 'getMostLikedSolarSystems']); //les x systemes solaires les plus aimés
    Route::get('galaxies/{id}/recent', [GalaxyController::class, 'getRecentSolarSystems']); //les x systemes solaires les plus récents
    
    // SOLAR SYSTEMS d'une galaxie
    Route::get('galaxies/{galaxyId}/solar-systems', [SolarSystemController::class, 'index']); //liste des systemes solaire pour cette galaxie
    Route::get('galaxies/{galaxyId}/solar-systems/systems', [SolarSystemController::class, 'getSolarSystemsByUser']); //liste des systemes owned pour l'utilisateur donné
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}', [SolarSystemController::class, 'show']); //le systeme solaire avec ses stats pour cette galaxie
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/owner', [SolarSystemController::class, 'getOwner']); //le propriétaire du systeme solaire
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/likes', [LikeController::class, 'countSolarSystemLikes']); //nombre de likes pour ce systeme solaire
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/likes-stats', [LikeController::class, 'getSolarSystemLikesStats']); //stats des likes (infos plus completes) pour ce systeme solaire
    
    // PLANETES d'un systeme solaire
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets', [PlanetController::class, 'index']); //liste des planetes pour ce systeme solaire
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}', [PlanetController::class, 'show']); //une planète avec ses stats
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/owner', [PlanetController::class, 'getOwner']); //le propriétaire de la planete
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/likes', [LikeController::class, 'countPlanetLikes']); //nombre de likes pour cette planete
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/likes-stats', [LikeController::class, 'getPlanetLikesStats']); //stats des likes (infos plus completes) pour cette planete
    
    // MOONS d'une planète
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons', [MoonController::class, 'index']); //liste des lunes pour cette planete
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}', [MoonController::class, 'show']); //une lune avec ses stats
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}/owner', [MoonController::class, 'getOwner']); //le propriétaire de la lune
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}/likes', [LikeController::class, 'countMoonLikes']); //nombre de likes pour cette lune
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}/likes-stats', [LikeController::class, 'getMoonLikesStats']); //stats des likes (infos plus completes) pour cette lune
    
    // Search
    Route::get('search', [SearchController::class, 'globalSearch']); //retourne une liste de systemes solaires, planetes, lunes, galaxies, etc... qui matchent la requette.
    
    // ClaimController, check if the system is claimable for given user id
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/is-claimable', [ClaimController::class, 'isClaimable']); //retourne si le system est 'claimable' pour l'utilisateur donné
});

// Routes protégées (authentification requise)
Route::prefix('v1')->middleware(['auth:sanctum', RateLimit::class])->group(function () {
    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']); //deconnexion
    Route::get('auth/me', [AuthController::class, 'me']); //retourn l'utilisateur actuel

    // User
    Route::post('users/change-password', [UserController::class, 'changePassword']); //changement mdp via profil
    Route::post('users/change-email', [UserController::class, 'changeEmail']); //changement de mail via profil
    Route::post('users/delete-account', [UserController::class, 'deleteAccount']); //suppression du compte
      
    // Solar Systems (modification) Pour l'instant, pas d'ajout et de suppression des systemes solaires, juste modifications des systems pré-générés, a voir plus tard
    Route::put('galaxies/{galaxyId}/solar-systems/{solarSystemId}', [SolarSystemController::class, 'update']);
    
    // Planets (modification)
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets', [PlanetController::class, 'store']);
    Route::put('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}', [PlanetController::class, 'update']);
    Route::delete('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}', [PlanetController::class, 'destroy']);
    
    // Moons (modification)
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons', [MoonController::class, 'store']);
    Route::put('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}', [MoonController::class, 'update']);
    Route::delete('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}', [MoonController::class, 'destroy']);
    
    // Like Routes (toutes privées)
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/to-like', [LikeController::class, 'toggleSolarSystem']); //to like ou unlike un systeme solaire
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/to-like', [LikeController::class, 'togglePlanet']); //to like ou unlike une planete
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}/to-like', [LikeController::class, 'toggleMoon']); //to like ou unlike une lune
    
    // Routes pour les claims de systèmes solaires
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/claim', [ClaimController::class, 'claim']); //claim le solarSystem
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/unclaim', [ClaimController::class, 'unclaim']); //unclaim le solarSystem
});

// Routes Admin (authentification + admin requise)
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api', IsAdmin::class])->group(function () {
    // Galaxies
    Route::post('galaxies', [GalaxyController::class, 'store']); //Création
    Route::put('galaxies/{id}', [GalaxyController::class, 'update']); //Modification
    Route::delete('galaxies/{id}', [GalaxyController::class, 'destroy']); //Suppression
    
    // Solar Systems
    Route::post('galaxies/{galaxyId}/solar-systems', [SolarSystemController::class, 'store']); //creation d'un system solaire
    Route::delete('galaxies/{galaxyId}/solar-systems/{solarSystemId}', [SolarSystemController::class, 'destroy']); //suppression d'un systeme solaire
    
    // User
    Route::post('/user/{userId}', [AdminController::class, 'add']); //ajout d'un user
    Route::delete('/user/{userId}', [AdminController::class, 'delete']); //suppression d'un user
});
