<?php

namespace App\Http\Controllers;

use App\Models\LikerSolarSystem;
use App\Models\LikerPlanet;
use App\Models\LikerMoon;
use App\Models\SolarSystem;
use App\Models\Planet;
use App\Models\Moon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Retourne le nombre de likes pour un élément
     */
    private function countLikes($model, $id)
    {
        try {
            $count = $model::where($this->getForeignKey($model), $id)->count();
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors du comptage des likes'], 500);
        }
    }

    /**
     * Retourne les statistiques détaillées des likes pour un élément
     */
    private function getStats($model, $id)
    {
        try {
            $likes = $model::with('user:id,user_login')
                ->where($this->getForeignKey($model), $id)
                ->orderBy($this->getDateField($model), 'desc')
                ->get();
                
            return response()->json($likes);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des likes'], 500);
        }
    }

    /**
     * Ajoute ou retire un like pour un élément
     */
    private function toggleLike($model, $id, $dateField)
    {
        try {
            // Vérifie que l'élément existe
            $this->findOrFail($model, $id);
            
            $userId = Auth::id();
            $foreignKey = $this->getForeignKey($model);
            
            // Vérifie si l'utilisateur a déjà liké
            $existingLike = $model::where($foreignKey, $id)
                ->where('user_id', $userId)
                ->first();
                
            if ($existingLike) {
                // Si le like existe, on le supprime
                $existingLike->delete();
                return response()->json(['message' => 'Like retiré', 'liked' => false]);
            } else {
                // Sinon on crée un nouveau like
                $model::create([
                    $foreignKey => $id,
                    'user_id' => $userId,
                    $dateField => now()
                ]);
                return response()->json(['message' => 'Like ajouté', 'liked' => true]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la modification du like'], 500);
        }
    }

    /**
     * Vérifie si l'utilisateur a liké un élément
     */
    private function checkLikeStatus($model, $id)
    {
        try {
            $userId = Auth::id();
            
            $liked = $model::where($this->getForeignKey($model), $id)
                ->where('user_id', $userId)
                ->exists();
                
            return response()->json(['liked' => $liked]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la vérification du like'], 500);
        }
    }

    /**
     * Retourne la clé étrangère en fonction du modèle
     */
    private function getForeignKey($model)
    {
        return match($model) {
            LikerSolarSystem::class => 'solar_system_id',
            LikerPlanet::class => 'planet_id',
            LikerMoon::class => 'moon_id',
            default => throw new \InvalidArgumentException('Modèle non supporté')
        };
    }

    /**
     * Retourne le nom du champ de date en fonction du modèle
     */
    private function getDateField($model)
    {
        return match($model) {
            LikerSolarSystem::class => 'liker_solar_system_date',
            LikerPlanet::class => 'liker_planet_date',
            LikerMoon::class => 'liker_moon_date',
            default => throw new \InvalidArgumentException('Modèle non supporté')
        };
    }

    /**
     * Trouve un élément ou lance une exception
     */
    private function findOrFail($model, $id)
    {
        return match($model) {
            LikerSolarSystem::class => SolarSystem::findOrFail($id),
            LikerPlanet::class => Planet::findOrFail($id),
            LikerMoon::class => Moon::findOrFail($id),
            default => throw new \InvalidArgumentException('Modèle non supporté')
        };
    }

    // Routes pour les systèmes solaires
    public function countSolarSystem($galaxyId, $solarSystemId)
    {
        return $this->countLikes(LikerSolarSystem::class, $solarSystemId);
    }

    public function statsSolarSystem($galaxyId, $solarSystemId)
    {
        return $this->getStats(LikerSolarSystem::class, $solarSystemId);
    }

    public function toggleSolarSystem($galaxyId, $solarSystemId)
    {
        try {
            // Vérifie que le système solaire existe
            $solarSystem = SolarSystem::findOrFail($solarSystemId);
            
            $userId = Auth::id();
            
            // Vérifie si l'utilisateur a déjà liké
            $existingLike = LikerSolarSystem::where('solar_system_id', $solarSystemId)
                ->where('user_id', $userId)
                ->first();
                
            if ($existingLike) {
                // Si le like existe, on le supprime
                $existingLike->delete();
                return response()->json(['message' => 'Like retiré', 'liked' => false]);
            } else {
                // Sinon on crée un nouveau like
                LikerSolarSystem::create([
                    'solar_system_id' => $solarSystemId,
                    'user_id' => $userId,
                    'liker_solar_system_date' => now()
                ]);
                return response()->json(['message' => 'Like ajouté', 'liked' => true]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la modification du like'], 500);
        }
    }

    public function statusSolarSystem($galaxyId, $solarSystemId)
    {
        return $this->checkLikeStatus(LikerSolarSystem::class, $solarSystemId);
    }

    // Routes pour les planètes
    public function countPlanet($galaxyId, $solarSystemId, $planetId)
    {
        return $this->countLikes(LikerPlanet::class, $planetId);
    }

    public function statsPlanet($galaxyId, $solarSystemId, $planetId)
    {
        return $this->getStats(LikerPlanet::class, $planetId);
    }

    public function togglePlanet($galaxyId, $solarSystemId, $planetId)
    {
        try {
            // Vérifie que la planète existe
            $planet = Planet::findOrFail($planetId);
            
            $userId = Auth::id();
            
            // Vérifie si l'utilisateur a déjà liké
            $existingLike = LikerPlanet::where('planet_id', $planetId)
                ->where('user_id', $userId)
                ->first();
                
            if ($existingLike) {
                // Si le like existe, on le supprime
                $existingLike->delete();
                return response()->json(['message' => 'Like retiré', 'liked' => false]);
            } else {
                // Sinon on crée un nouveau like
                LikerPlanet::create([
                    'planet_id' => $planetId,
                    'user_id' => $userId,
                    'liker_planet_date' => now()
                ]);
                return response()->json(['message' => 'Like ajouté', 'liked' => true]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la modification du like'], 500);
        }
    }

    public function statusPlanet($galaxyId, $solarSystemId, $planetId)
    {
        return $this->checkLikeStatus(LikerPlanet::class, $planetId);
    }

    // Routes pour les lunes
    public function countMoon($galaxyId, $solarSystemId, $planetId, $moonId)
    {
        return $this->countLikes(LikerMoon::class, $moonId);
    }

    public function statsMoon($galaxyId, $solarSystemId, $planetId, $moonId)
    {
        return $this->getStats(LikerMoon::class, $moonId);
    }

    public function toggleMoon($galaxyId, $solarSystemId, $planetId, $moonId)
    {
        try {
            // Vérifie que la lune existe
            $moon = Moon::findOrFail($moonId);
            
            $userId = Auth::id();
            
            // Vérifie si l'utilisateur a déjà liké
            $existingLike = LikerMoon::where('moon_id', $moonId)
                ->where('user_id', $userId)
                ->first();
                
            if ($existingLike) {
                // Si le like existe, on le supprime
                $existingLike->delete();
                return response()->json(['message' => 'Like retiré', 'liked' => false]);
            } else {
                // Sinon on crée un nouveau like
                LikerMoon::create([
                    'moon_id' => $moonId,
                    'user_id' => $userId,
                    'liker_moon_date' => now()
                ]);
                return response()->json(['message' => 'Like ajouté', 'liked' => true]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la modification du like'], 500);
        }
    }

    public function statusMoon($galaxyId, $solarSystemId, $planetId, $moonId)
    {
        return $this->checkLikeStatus(LikerMoon::class, $moonId);
    }

    /**
     * Retourne tous les likes de l'utilisateur connecté
     */
    public function userLikes()
    {
        try {
            $userId = Auth::id();
            
            $likes = [
                'solar_systems' => LikerSolarSystem::with('solarSystem')
                    ->where('user_id', $userId)
                    ->get(),
                'planets' => LikerPlanet::with('planet')
                    ->where('user_id', $userId)
                    ->get(),
                'moons' => LikerMoon::with('moon')
                    ->where('user_id', $userId)
                    ->get()
            ];
            
            return response()->json($likes);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des likes'], 500);
        }
    }
}
