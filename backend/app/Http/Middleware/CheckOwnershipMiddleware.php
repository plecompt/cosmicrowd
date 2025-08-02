<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SolarSystem;
use App\Models\Planet;
use App\Models\Moon;
use App\Http\Traits\ApiResponse;

class CheckOwnershipMiddleware
{
    use ApiResponse;

    /**
     * Handle incoming request to verify resource ownership
     * 
     * Checks if the authenticated user owns the specified resource before allowing
     * access to modify operations. Supports solar systems, planets, and moons.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the pipeline
     * @param string $resource The type of resource to check (solar_system, planet, moon)
     * @return Response Either continues request or returns error response
     */
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->error('Authentication required', 401);
        }
        
        // Get the correct route parameter based on resource type
        $resourceId = match($resource) {
            'solar_system' => $request->route('solarSystemId'),
            'planet' => $request->route('planetId'),
            'moon' => $request->route('moonId'),
            default => null
        };
        
        if (!$resourceId) {
            return $this->error('Resource ID missing', 400);
        }
        
        if (!$this->checkOwnership($user->user_id, $resource, $resourceId)) {
            return $this->error('You are not authorized to modify this resource', 403);
        }
        
        return $next($request);
    }
    
    /**
     * Check if user owns the specified resource
     * 
     * Verifies ownership by checking if the user_id matches the resource owner
     *
     * @param int $userId The user ID to check ownership for
     * @param string $resource The type of resource to check
     * @param string $resourceId The ID of the resource
     * @return bool True if user owns the resource, false otherwise
     */
    private function checkOwnership(int $userId, string $resource, string $resourceId): bool
    {
        return match($resource) {
            'solar_system' => SolarSystem::where('solar_system_id', $resourceId)
                ->where('user_id', $userId)
                ->exists(),
                
            'planet' => Planet::where('planet_id', $resourceId)
                ->where('user_id', $userId)
                ->exists(),
                
            'moon' => Moon::where('moon_id', $resourceId)
                ->where('user_id', $userId)
                ->exists(),
                
            default => false
        };
    }
}
