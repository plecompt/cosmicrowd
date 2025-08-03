<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Traits\ApiResponse;

class IsAdminMiddleware
{
    use ApiResponse;

    /**
     * Handle incoming request to verify admin privileges
     * 
     * Checks if the authenticated user has admin role before allowing
     * access to admin-restricted endpoints and operations.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the pipeline
     * @return Response Either continues request or returns error response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->error('Authentication required', 401);
        }
        
        if ($user->user_role !== 'admin') {
            return $this->error('Admin access required', 403);
        }
        
        return $next($request);
    }
}
