<?php

namespace App\Http\Controllers\Api;

use App\Models\SolarSystem;
use App\Models\UserSolarSystemOwnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSystemOwnershipController
{
    // Check if given SolarSystemId is claimable for user
    public function isClaimable($galaxyId, $solarSystemId)
    {
        try {
            // Vérifie que le système solaire existe dans la bonne galaxie
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
                ->findOrFail($solarSystemId);
            
            // Vérifie si le système est déjà claimé
            $existingClaim = UserSolarSystemOwnership::where('solar_system_id', $solarSystemId)->first();
            if ($existingClaim) {
                return response()->json([
                    'claimable' => false,
                    'reason' => 'This solar system is already claimed'
                ]);
            }
            
            // Compte le nombre de systèmes claimés par l'utilisateur A REVOIR POUR RECUPERER LUSER
            $userClaimsCount = UserSolarSystemOwnership::where('user_id', Auth::id())->count();
            if ($userClaimsCount >= 3) {
                return response()->json([
                    'claimable' => false,
                    'reason' => 'You can\'t have more than 3 claimed solar systems at the same time'
                ]);
            }
            
            return response()->json([
                'claimable' => true,
                'reason' => 'Solar System is available'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la vérification du claim'], 500);
        }
    }

    /**
     * Tente de claimer un système solaire
     */
    public function claim($galaxyId, $solarSystemId)
    {
        try {
            // Vérifie que le système solaire existe dans la bonne galaxie
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
                ->findOrFail($solarSystemId);
            
            // Vérifie si le système est déjà claimé
            $existingClaim = UserSolarSystemOwnership::where('solar_system_id', $solarSystemId)->first();
            if ($existingClaim) {
                return response()->json(['error' => 'This solar system is already claimed'], 400);
            }
            
            // Vérifie le nombre de systèmes claimés par l'utilisateur
            $userClaimsCount = UserSolarSystemOwnership::where('user_id', Auth::id())->count();
            if ($userClaimsCount >= 3) {
                return response()->json(['error' => 'You can\'t have more than 3 claimed solar systems at the same time'], 400);
            }
            
            // Crée le claim
            UserSolarSystemOwnership::create([
                'solar_system_id' => $solarSystemId,
                'user_id' => Auth::id(),
                'ownership_type' => 'claimed',
                'owned_at' => now()
            ]);
            
            return response()->json(['message' => 'Solar system claimed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while claiming the solar system'], 500);
        }
    }

    /**
     * Tente de un-claimer un système solaire
     */
    public function unclaim($galaxyId, $solarSystemId)
    {
        try {
            // Vérifie que le système solaire existe dans la bonne galaxie
            $solarSystem = SolarSystem::where('galaxy_id', $galaxyId)
                ->findOrFail($solarSystemId);
            
            // Vérifie si le système est claimé par l'utilisateur
            $existingClaim = UserSolarSystemOwnership::where('solar_system_id', $solarSystemId)
                ->where('user_id', Auth::id())
                ->first();
                
            if (!$existingClaim) {
                return response()->json(['error' => 'You don\'t have own this solar system'], 400);
            }
            
            // Supprime le claim
            $existingClaim->delete();
            
            return response()->json(['message' => 'Solar system un-claimed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error while un-claiming the solar system'], 500);
        }
    }
} 