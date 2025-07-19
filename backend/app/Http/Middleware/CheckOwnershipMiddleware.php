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

    // Handle an incoming request.
    public function handle(Request $request, Closure $next, $resource): Response
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
        
        $isOwner = $this->checkOwnership($user->user_id, $resource, $resourceId);
        
        if (!$isOwner) {
            return $this->error('You are not authorized to modify this resource', 403);
        }
        
        return $next($request);
    }
    
    // Check if user owns the resource.
    private function checkOwnership($userId, $resource, $resourceId): bool
    {
        switch ($resource) {
            case 'solar_system':
                return SolarSystem::where('solar_system_id', $resourceId)
                    ->where('user_id', $userId)
                    ->exists();
                
            case 'planet':
                return Planet::where('planet_id', $resourceId)
                    ->where('user_id', $userId)
                    ->exists();
                
            case 'moon':
                return Moon::where('moon_id', $resourceId)
                    ->where('user_id', $userId)
                    ->exists();
                
            default:
                return false;
        }
    }
}