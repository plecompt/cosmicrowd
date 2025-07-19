<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\SolarSystem;
use App\Models\Planet;
use App\Models\Moon;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController
{
    use ApiResponse;

    // WIP
    public function globalSearch(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);

        if (empty($query)) {
            return $this->error('Empty query', 400);
        }

        $solarSystems = SolarSystem::where('solar_system_name', 'LIKE', '%' . $query . '%')
                        ->limit($limit)
                        ->get();

        $planets = Planet::where('planet_name', 'LIKE', '%' . $query . '%')
                        ->limit($limit)
                        ->get();

        $moons = Moon::where('moon_name', 'LIKE', '%' . $query . '%')
                    ->limit($limit)
                    ->get();

        $users = User::where('user_login', 'LIKE', '%' . $query . '%')
                    ->limit($limit)
                    ->get();

        return $this->success([
            'results' => [
                'solar_systems' => $solarSystems,
                'planets' => $planets,
                'moons' => $moons,
                'users' => $users
            ],
            'total_results' => $solarSystems->count() + $planets->count() + $moons->count() + $users->count()
        ]);
    }

    // WIP
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

        if (!empty($query)) {
            $solarSystemsQuery->where('solar_system_name', 'LIKE', '%' . $query . '%');
        }
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

        return $this->success([
            'solar_systems' => $solarSystems
        ]);
    }

    // WIP
    public function searchPlanets(Request $request)
    {
        $query = $request->get('q', '');
        $typeFilter = $request->get('type');
        $gravityMin = $request->get('gravity_min');
        $gravityMax = $request->get('gravity_max');
        $luminosityMin = $request->get('luminosity_min');
        $luminosityMax = $request->get('luminosity_max');
        $limit = $request->get('limit', 15);

        $planetsQuery = Planet::with(['user', 'solarSystem', 'moons', 'likes']);

        if (!empty($query)) {
            $planetsQuery->where('planet_name', 'LIKE', '%' . $query . '%');
        }
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

        return $this->success([
            'planets' => $planets
        ]);
    }

    // WIP    
    public function searchMoons(Request $request)
    {
        $query = $request->get('q', '');
        $typeFilter = $request->get('type');
        $gravityMin = $request->get('gravity_min');
        $gravityMax = $request->get('gravity_max');
        $luminosityMin = $request->get('luminosity_min');
        $luminosityMax = $request->get('luminosity_max');
        $limit = $request->get('limit', 15);

        $moonsQuery = Moon::with(['user', 'planet', 'likes']);

        if (!empty($query)) {
            $moonsQuery->where('moon_name', 'LIKE', '%' . $query . '%');
        }
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

        return $this->success([
            'moons' => $moons
        ]);
    }

    // WIP
    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 15);

        $usersQuery = User::with(['solarSystems', 'planets', 'moons']);

        if (!empty($query)) {
            $usersQuery->where(function($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                  ->orWhere('email', 'LIKE', '%' . $query . '%');
            });
        }

        $users = $usersQuery->orderBy('id', 'desc')
                        ->paginate($limit);

        return $this->success([
            'users' => $users
        ]);
    }
}
