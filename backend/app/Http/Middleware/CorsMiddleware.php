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
        
        // Get allowed origins from environment
        $allowedOrigins = $this->getAllowedOrigins();
        
        $origin = $request->header('Origin');
        
        // If request comes from an allowed origin, give access
        if ($this->isOriginAllowed($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Expose-Headers', 'X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset');
        }
        
        // Set CORS headers for all responses
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        $response->headers->set('Access-Control-Max-Age', '86400'); // Cache preflight for 24 hours
        
        // Handle preflight OPTIONS requests
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200);
        }
        
        return $response;
    }
    
    private function getAllowedOrigins(): array
    {
        $origins = env('CORS_ALLOWED_ORIGINS', 'http://localhost:4200');
        
        // Support multiple origins separated by comma
        return array_map('trim', explode(',', $origins));
    }
    
    private function isOriginAllowed(?string $origin, array $allowedOrigins): bool
    {
        if (!$origin) {
            return false;
        }
        
        return in_array($origin, $allowedOrigins);
    }
}