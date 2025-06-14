<?php

namespace App\Http\Controllers;

use App\Models\Galaxy;
use Illuminate\Http\Request;

class GalaxyController extends Controller
{
    /**
     * Affiche la liste des galaxies avec leurs stats
     */
    public function index()
    {
        $galaxies = Galaxy::withCount(['solarSystems', 'planets', 'moons'])->get();
        return response()->json($galaxies);
    }

    /**
     * Affiche les détails d'une galaxie avec ses stats
     */
    public function show($id)
    {
        $galaxy = Galaxy::withCount(['solarSystems', 'planets', 'moons'])
            ->findOrFail($id);
            
        return response()->json($galaxy);
    }

    /**
     * Retourne les systèmes solaires pour l'animation (données complètes)
     */
    public function getSolarSystemsForAnimation($id)
    {
        $galaxy = Galaxy::findOrFail($id);
        
        $solarSystems = $galaxy->solarSystems()
            ->select('*')
            ->get();
            
        return response()->json($solarSystems);
    }

    /**
     * Retourne les systèmes solaires les plus likés
     */
    public function getMostLikedSolarSystems($id)
    {
        $galaxy = Galaxy::findOrFail($id);
        
        $solarSystems = $galaxy->solarSystems()
            ->withCount('likes')
            ->orderBy('likes_count', 'desc')
            ->take(10) // A voir plus tard combien, peut etre une variable d'environnement ?
            ->get();
            
        return response()->json($solarSystems);
    }

    /**
     * Retourne les systèmes solaires les plus récents
     */
    public function getRecentSolarSystems($id)
    {
        $galaxy = Galaxy::findOrFail($id);
        
        $solarSystems = $galaxy->solarSystems()
            ->orderBy('created_at', 'desc')
            ->take(10) // A voir plus tard combien, peut etre une variable d'environnement ?
            ->get();
            
        return response()->json($solarSystems);
    }
}
