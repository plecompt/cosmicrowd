<?php

namespace App\Http\Controllers\Api;

use App\Models\SolarSystem;
use App\Models\Planet;
use App\Models\Moon;
use App\Models\User;
use Illuminate\Http\Request;

// May want to refacto this. Don't like all this if...
class SearchController
{
    // Search in everything
    public function globalSearch(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Empty query'
            ], 400);
        }

        // Looking in SolarSystems
        $solarSystems = SolarSystem::where('solar_system_name', 'LIKE', '%' . $query . '%')
                    ->limit($limit)
                    ->get();

        // Looking in Planets
        $planets = Planet::where('planet_name', 'LIKE', '%' . $query . '%')
                        ->limit($limit)
                        ->get();

        // Looking in Moons
        $moons = Moon::where('moon_name', 'LIKE', '%' . $query . '%')
                    ->limit($limit)
                    ->get();

        // Looking in users
        $users = User::where('user_login', 'LIKE', '%' . $query . '%')
                    ->limit($limit)
                    ->get();

        return response()->json([
            'success' => true,
            'results' => [
                'solar_systems' => $solarSystems,
                'planets' => $planets,
                'moons' => $moons,
                'users' => $users
            ],
            'total_results' => $solarSystems->count() + $planets->count() + $moons->count() + $users->count()
        ]);
    }

    // Search for Solar Systems
    public function searchSolarSystems(Request $request)
    {
        $query = $request->get('q', '');
        $typeFilter = $request->get('type');
        $gravityMin = $request->get('gravity_min');
        $gravityMax = $request->get('gravity_max');
        $luminosityMin = $request->get('luminosity_min');
        $luminosityMax = $request->get('luminosity_max');
        $limit = $request->get('limit', 15);

        $solarSystemsQuery = SolarSystem::with(['user', 'planets', 'likes']);

        // Search by name
        if (!empty($query)) {
            $solarSystemsQuery->where('solar_system_name', 'LIKE', '%' . $query . '%');
        }

        // Filters
        if ($typeFilter) {
            $solarSystemsQuery->where('solar_system_type', $typeFilter);
        }

        if ($gravityMin) {
            $solarSystemsQuery->where('solar_system_gravity', '>=', $gravityMin);
        }

        if ($gravityMax) {
            $solarSystemsQuery->where('solar_system_gravity', '<=', $gravityMax);
        }

        if ($luminosityMin) {
            $solarSystemsQuery->where('solar_system_luminosity', '>=', $luminosityMin);
        }

        if ($luminosityMax) {
            $solarSystemsQuery->where('solar_system_luminosity', '<=', $luminosityMax);
        }

        $solarSystems = $solarSystemsQuery->orderBy('solar_system_id', 'desc')
                                        ->paginate($limit);

        return response()->json([
            'success' => true,
            'solar_systems' => $solarSystems
        ]);
    }

    // Search for Planets  
    public function searchPlanets(Request $request)
    {
        $query = $request->get('q', '');
        $typeFilter = $request->get('type');
        $gravityMin = $request->get('gravity_min');
        $gravityMax = $request->get('gravity_max');
        $diameterMin = $request->get('diameter_min');
        $diameterMax = $request->get('diameter_max');
        $massMin = $request->get('mass_min');
        $massMax = $request->get('mass_max');
        $limit = $request->get('limit', 15);

        $planetsQuery = Planet::with(['user', 'solarSystem', 'moons', 'likes']);

        // Search by name
        if (!empty($query)) {
            $planetsQuery->where('planet_name', 'LIKE', '%' . $query . '%');
        }

        // Filters
        if ($typeFilter) {
            $planetsQuery->where('planet_type', $typeFilter);
        }

        if ($gravityMin) {
            $planetsQuery->where('planet_gravity', '>=', $gravityMin);
        }

        if ($gravityMax) {
            $planetsQuery->where('planet_gravity', '<=', $gravityMax);
        }

        if ($diameterMin) {
            $planetsQuery->where('planet_diameter', '>=', $diameterMin);
        }

        if ($diameterMax) {
            $planetsQuery->where('planet_diameter', '<=', $diameterMax);
        }

        if ($massMin) {
            $planetsQuery->where('planet_mass', '>=', $massMin);
        }

        if ($massMax) {
            $planetsQuery->where('planet_mass', '<=', $massMax);
        }

        $planets = $planetsQuery->orderBy('planet_id', 'desc')
                            ->paginate($limit);

        return response()->json([
            'success' => true,
            'planets' => $planets
        ]);
    }

    // Search for Moons
    public function searchMoons(Request $request)
    {
        $query = $request->get('q', '');
        $typeFilter = $request->get('type');
        $gravityMin = $request->get('gravity_min');
        $gravityMax = $request->get('gravity_max');
        $diameterMin = $request->get('diameter_min');
        $diameterMax = $request->get('diameter_max');
        $massMin = $request->get('mass_min');
        $massMax = $request->get('mass_max');
        $limit = $request->get('limit', 15);

        $moonsQuery = Moon::with(['user', 'planet', 'likes']);

        // Search by name
        if (!empty($query)) {
            $moonsQuery->where('moon_name', 'LIKE', '%' . $query . '%');
        }

        // Filters
        if ($typeFilter) {
            $moonsQuery->where('moon_type', $typeFilter);
        }

        if ($gravityMin) {
            $moonsQuery->where('moon_gravity', '>=', $gravityMin);
        }

        if ($gravityMax) {
            $moonsQuery->where('moon_gravity', '<=', $gravityMax);
        }

        if ($diameterMin) {
            $moonsQuery->where('moon_diameter', '>=', $diameterMin);
        }

        if ($diameterMax) {
            $moonsQuery->where('moon_diameter', '<=', $diameterMax);
        }

        if ($massMin) {
            $moonsQuery->where('moon_mass', '>=', $massMin);
        }

        if ($massMax) {
            $moonsQuery->where('moon_mass', '<=', $massMax);
        }

        $moons = $moonsQuery->orderBy('moon_id', 'desc')
                        ->paginate($limit);

        return response()->json([
            'success' => true,
            'moons' => $moons
        ]);
    }

    // Search for Users
    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 15);

        $usersQuery = User::with(['solarSystems', 'planets', 'moons']);

        // Search by name or email
        if (!empty($query)) {
            $usersQuery->where(function($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                ->orWhere('email', 'LIKE', '%' . $query . '%');
            });
        }

        $users = $usersQuery->orderBy('id', 'desc')
                        ->paginate($limit);

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
}
