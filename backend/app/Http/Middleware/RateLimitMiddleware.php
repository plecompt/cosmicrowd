<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Traits\ApiResponse;

class RateLimitMiddleware
{
    use ApiResponse;

    /**
     * Handle incoming request with rate limiting
     * 
     * Implements rate limiting by tracking request attempts per user/IP combination.
     * Blocks requests when limit is exceeded and adds rate limit headers to responses.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the pipeline
     * @param int $maxAttempts Maximum allowed requests (default: 60)
     * @param int $decayMinutes Time window in minutes (default: 1)
     * @return Response Either continues request or returns rate limit error
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            return $this->error(
                'Too many attempts. Please try again in ' . $decayMinutes . ' minute(s).', 
                429,
                ['retry_after' => $decayMinutes * 60]
            );
        }
        
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        
        $response = $next($request);
        
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($decayMinutes)->timestamp);
        
        return $response;
    }
    
    /**
     * Generate unique signature for request rate limiting
     * 
     * Creates a unique cache key combining user ID (or 'guest'), IP address,
     * and route name to track rate limits per specific request context.
     *
     * @param Request $request The incoming HTTP request
     * @return string Unique cache key for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $userId = $request->user()?->id ?? 'guest';
        $ip = $request->ip();
        $route = $request->route()?->getName() ?? $request->path();
        
        return 'rate_limit:' . $userId . ':' . $ip . ':' . $route;
    }
}