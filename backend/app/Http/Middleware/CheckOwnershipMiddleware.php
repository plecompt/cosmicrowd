<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Star;
use App\Models\Planet;
use App\Models\Moon;

class CheckOwnershipMiddleware
{
    // Handle an incoming request.
    public function handle(Request $request, Closure $next, $resource): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }
        
        $resourceId = $request->route('id');
        
        if (!$resourceId) {
            return response()->json([
                'success' => false,
                'message' => 'Resource ID missing'
            ], 400);
        }
        
        $isOwner = $this->checkOwnership($user->user_id, $resource, $resourceId);
        
        if (!$isOwner) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to modify this resource'
            ], 403);
        }
        
        return $next($request);
    }
    
    // A REVOIR
    // Check if user owns the resource.
    // private function checkOwnership($userId, $resource, $resourceId): bool
    // {
    //     switch ($resource) {
    //         case 'star':
    //             $star = Star::find($resourceId);
    //             return $star && $star->user_id === $userId;
                
    //         case 'planet':
    //             $planet = Planet::with('star')->find($resourceId);
    //             return $planet && $planet->star->user_id === $userId;
                
    //         case 'moon':
    //             $moon = Moon::with('planet.star')->find($resourceId);
    //             return $moon && $moon->planet->star->user_id === $userId;
                
    //         default:
    //             return false;
    //     }
    // }
}
