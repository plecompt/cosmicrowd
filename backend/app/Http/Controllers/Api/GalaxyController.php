<?php

namespace App\Http\Controllers\Api;

use App\Models\Galaxy;
use Illuminate\Http\Request;

class GalaxyController extends Controller
{
    // Return the list of all galaxy with their solarSystems, their planets and moons
    public function index()
    {
        $galaxies = Galaxy::withCount(['solarSystems', 'planets', 'moons'])->get();
        
        return response()->json($galaxies);
    }

    // Return the given galaxy with galaxy's solarSystems, planets and moons
    public function show($id)
    {
        $galaxy = Galaxy::withCount(['solarSystems', 'planets', 'moons'])
            ->findOrFail($id);
            
        return response()->json($galaxy);
    }

    // Return the list of SolarSystems in the given galaxy, used for main animation
    public function getSolarSystemsForAnimation($id)
    {
        $galaxy = Galaxy::findOrFail($id);
        
        $solarSystems = $galaxy->solarSystems()
            ->select('*')
            ->get();
            
        return response()->json($solarSystems);
    }

    // Return the most liked solarSystems
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

    // Return the most recent solarSystems
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
