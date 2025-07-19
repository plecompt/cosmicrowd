<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Traits\ApiResponse;

class IsAdmin
{
    use ApiResponse;

    // Check if user is admin
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
