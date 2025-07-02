<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        //maybe set this in .env ?
        $allowedOrigins = [
            'http://localhost:4200'
        ];
        
        $origin = $request->header('Origin');
        
        //if request comes from an allowed origin, give access, allow cookies and exposes rate limiting headers
        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Expose-Headers', 'X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset');
        }
        
        //allow this methods and this headers
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        
        // Angular send a prerequest before post to see if post is allowed
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200);
        }
        
        return $response;
    }
}
