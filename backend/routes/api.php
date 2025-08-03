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
use App\Http\Controllers\Api\WallpaperController;

// Public routes (no authentication required)
Route::prefix('v1')->group(function (): void {

    // Authentication
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle.strict'); // User login

    // Users
    Route::post('users/register', [UserController::class, 'register'])->middleware('throttle.strict'); // User registration
    Route::post('users/forgot-password', [UserController::class, 'forgotPassword'])->middleware('throttle.strict'); // Send reset token by email
    Route::post('users/verify-token', [UserController::class, 'verifyToken'])->middleware('throttle.strict'); // Verify reset token
    Route::post('users/reset-password', [UserController::class, 'resetPassword'])->middleware('throttle.strict'); // Reset password after forgot password
    Route::post('users/check-login', [UserController::class, 'checkLoginAvailability'])->middleware('throttle.relaxed'); // Check if login is available in database
    Route::post('users/check-email', [UserController::class, 'checkEmailAvailability'])->middleware('throttle.relaxed'); // Check if email is available in database
    Route::post('users/contact', [UserController::class, 'contact'])->middleware('throttle.strict'); // Send email to CosmiCrowd + confirmation to user
    Route::get('users/{userId}', [UserController::class, 'view'])->middleware('throttle.relaxed'); // Get user details

    // Wallpapers
    Route::get('galaxies/{id}/wallpapers/most-liked', [WallpaperController::class, 'getMostLikedWallpapers'])->middleware('throttle.relaxed'); // Get most liked wallpapers
    Route::get('galaxies/{id}/wallpapers/most-recent', [WallpaperController::class, 'getMostRecentWallpapers'])->middleware('throttle.relaxed'); // Get most recent wallpapers

    // Galaxies and their solar systems
    Route::get('galaxies', [GalaxyController::class, 'index'])->middleware('throttle.normal'); // List galaxies with their stats
    Route::get('galaxies/{id}', [GalaxyController::class, 'show'])->middleware('throttle.normal'); // Get galaxy with its stats
    Route::get('galaxies/{id}/animation', [GalaxyController::class, 'getSolarSystemsForAnimation'])->middleware('throttle.strict'); // Get solar systems list for animation
    Route::get('galaxies/{id}/most-liked', [GalaxyController::class, 'getMostLikedSolarSystems'])->middleware('throttle.relaxed'); // Get most liked solar systems
    Route::get('galaxies/{id}/most-recent', [GalaxyController::class, 'getMostRecentSolarSystems'])->middleware('throttle.relaxed'); // Get most recent solar systems

    // Solar-systems from a galaxy
    Route::get('galaxies/{galaxyId}/solar-systems', [SolarSystemController::class, 'index'])->middleware('throttle.moderate'); // List solar systems for this galaxy
    Route::get('galaxies/{galaxyId}/solar-systems/systems', [SolarSystemController::class, 'getSolarSystemsByUser'])->middleware('throttle.moderate'); // List owned systems for given user
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}', [SolarSystemController::class, 'show'])->middleware('throttle.normal'); // Get solar system with its stats for this galaxy
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/owner', [SolarSystemController::class, 'getOwner'])->middleware('throttle.moderate'); // Get solar system owner
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/likes', [LikeController::class, 'countSolarSystemLikes'])->middleware('throttle.relaxed'); // Get likes count for this solar system
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/likes-stats', [LikeController::class, 'getSolarSystemLikesStats'])->middleware('throttle.relaxed'); // Get likes stats (more complete info) for this solar system

    // Wallpaper of a solar system
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/wallpapers', [WallpaperController::class, 'show'])->middleware('throttle.moderate'); // Get wallpaper associated with this solar system
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/wallpapers/exists', [WallpaperController::class, 'exists'])->middleware('throttle.moderate'); // Check if wallpaper exists for this solar system
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/wallpapers/likes', [LikeController::class, 'countWallpaperLikes'])->middleware('throttle.relaxed'); // Get likes count for this solar system's wallpaper
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/wallpapers/likes-stats', [LikeController::class, 'getWallpaperLikesStats'])->middleware('throttle.relaxed'); // Get likes stats for this solar system's wallpaper (more complete info)

    // Planets from a solar system
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets', [PlanetController::class, 'index'])->middleware('throttle.moderate'); // List planets for this solar system
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}', [PlanetController::class, 'show'])->middleware('throttle.normal'); // Get planet with its stats
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/owner', [PlanetController::class, 'getOwner'])->middleware('throttle.moderate'); // Get planet owner
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/likes', [LikeController::class, 'countPlanetLikes'])->middleware('throttle.relaxed'); // Get likes count for this planet
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/likes-stats', [LikeController::class, 'getPlanetLikesStats'])->middleware('throttle.relaxed'); // Get likes stats (more complete info) for this planet

    // Moons from a planet
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons', [MoonController::class, 'index'])->middleware('throttle.moderate'); // List moons for this planet
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}', [MoonController::class, 'show'])->middleware('throttle.normal'); // Get moon with its stats
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}/owner', [MoonController::class, 'getOwner'])->middleware('throttle.moderate'); // Get moon owner
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}/likes', [LikeController::class, 'countMoonLikes'])->middleware('throttle.relaxed'); // Get likes count for this moon
    Route::get('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}/likes-stats', [LikeController::class, 'getMoonLikesStats'])->middleware('throttle.relaxed'); // Get likes stats (more complete info) for this moon

    // Search
    Route::get('search', [SearchController::class, 'globalSearch'])->middleware('throttle.moderate'); // Global search returning solar systems, planets, moons, galaxies, etc. matching the query

    // Claim 
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/is-claimable', [ClaimController::class, 'isClaimable'])->middleware('throttle.normal'); // Check if system is claimable for given user
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware('auth:sanctum')->group(function (): void {
    // Authentication
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('throttle.normal'); // User logout
    Route::get('auth/me', [AuthController::class, 'me'])->middleware('throttle.normal'); // Get current authenticated user

    // User account management
    Route::post('users/change-password', [UserController::class, 'changePassword'])->middleware('throttle.strict'); // Change password via profile
    Route::post('users/change-email', [UserController::class, 'changeEmail'])->middleware('throttle.strict'); // Change email via profile
    Route::post('users/delete-account', [UserController::class, 'deleteAccount'])->middleware('throttle.strict'); // Delete user account

    // Solar Systems (modification) - For now, no add/delete of solar systems, only modifications of pre-generated systems, to be reviewed in v2
    Route::put('galaxies/{galaxyId}/solar-systems/{solarSystemId}', [SolarSystemController::class, 'update'])->middleware(['throttle.moderate', 'check.owner:solar_system']); // Update solar system

    // Solar system wallpaper management
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/wallpapers', [WallpaperController::class, 'store'])->middleware(['throttle.moderate', 'check.owner:solar_system']); // Save wallpaper for this solar system
    Route::delete('galaxies/{galaxyId}/solar-systems/{solarSystemId}/wallpapers', [WallpaperController::class, 'destroy'])->middleware(['throttle.moderate', 'check.owner:solar_system']); // Delete wallpaper for this solar system

    // Planets management
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets', [PlanetController::class, 'store'])->middleware(['throttle.moderate', 'check.owner:solar_system']); // Create planet (check solar_system owner since planetId doesn't exist yet)
    Route::put('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}', [PlanetController::class, 'update'])->middleware(['throttle.moderate', 'check.owner:planet']); // Update planet
    Route::delete('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}', [PlanetController::class, 'destroy'])->middleware(['throttle.moderate', 'check.owner:planet']); // Delete planet

    // Moons management
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons', [MoonController::class, 'store'])->middleware(['throttle.moderate', 'check.owner:planet']); // Create moon (check planet owner since moonId doesn't exist yet)
    Route::put('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}', [MoonController::class, 'update'])->middleware(['throttle.moderate', 'check.owner:moon']); // Update moon
    Route::delete('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}', [MoonController::class, 'destroy'])->middleware(['throttle.moderate', 'check.owner:moon']); // Delete moon

    // Like Routes (all private)
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/to-like', [LikeController::class, 'toggleSolarSystem'])->middleware('throttle.moderate'); // Toggle like/unlike solar system
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/wallpapers/{wallpaperId}/to-like', [LikeController::class, 'toggleWallpaper'])->middleware('throttle.moderate'); // Toggle like/unlike wallpaper
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/to-like', [LikeController::class, 'togglePlanet'])->middleware('throttle.moderate'); // Toggle like/unlike planet
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/planets/{planetId}/moons/{moonId}/to-like', [LikeController::class, 'toggleMoon'])->middleware('throttle.moderate'); // Toggle like/unlike moon
    Route::get('user-likes', [LikeController::class, 'checkUserLikes'])->middleware('throttle.relaxed'); // Check if given IDs are liked by user

    // Solar system claim routes
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/claim', [ClaimController::class, 'claim'])->middleware('throttle.moderate'); // Claim solar system
    Route::post('galaxies/{galaxyId}/solar-systems/{solarSystemId}/unclaim', [ClaimController::class, 'unclaim'])->middleware('throttle.moderate'); // Unclaim solar system
});

// Admin routes (authentication + admin role required)
Route::prefix('v1')->middleware(['auth:sanctum', 'admin'])->group(function (): void {
    // Solar Systems admin management
    Route::post('galaxies/{galaxyId}/solar-systems', [SolarSystemController::class, 'store'])->middleware('throttle.moderate'); // Create solar system
    Route::delete('galaxies/{galaxyId}/solar-systems/{solarSystemId}', [SolarSystemController::class, 'destroy'])->middleware('throttle.moderate'); // Delete solar system

    // User admin management
    Route::post('users/{userId}', [AdminController::class, 'add'])->middleware('throttle.strict'); // Add user (admin)
    Route::delete('users/{userId}', [AdminController::class, 'delete'])->middleware('throttle.strict'); // Delete user (admin)
});