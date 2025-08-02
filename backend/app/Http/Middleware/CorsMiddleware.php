<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle CORS (Cross-Origin Resource Sharing) for incoming requests
     * 
     * Manages CORS headers to allow cross-origin requests from authorized domains.
     * Handles preflight OPTIONS requests and sets appropriate CORS headers.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the pipeline
     * @return Response Response with CORS headers applied
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        $allowedOrigins = $this->getAllowedOrigins();
        $origin = $request->header('Origin');
        
        // If request comes from an allowed origin, set origin-specific headers
        if ($this->isOriginAllowed($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Expose-Headers', 'X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset');
        }
        
        // Set CORS headers for all responses
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        $response->headers->set('Access-Control-Max-Age', '86400');
        
        // Handle preflight OPTIONS requests
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200);
        }
        
        return $response;
    }
    
    /**
     * Get allowed origins from environment configuration
     * 
     * Retrieves and parses allowed origins from environment variables.
     * Supports multiple origins separated by commas.
     *
     * @return array List of allowed origin URLs
     */
    private function getAllowedOrigins(): array
    {
        $origins = env('CORS_ALLOWED_ORIGINS', 'http://localhost:4200');
        
        return array_map('trim', explode(',', $origins));
    }
    
    /**
     * Check if request origin is allowed
     * 
     * Validates if the request's Origin header matches any of the configured
     * allowed origins for CORS requests.
     *
     * @param string|null $origin The Origin header value from request
     * @param array $allowedOrigins List of allowed origin URLs
     * @return bool True if origin is allowed, false otherwise
     */
    private function isOriginAllowed(?string $origin, array $allowedOrigins): bool
    {
        if (!$origin) {
            return false;
        }
        
        return in_array($origin, $allowedOrigins);
    }
}
