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
     * @param string $maxAttempts Maximum allowed requests (default: 60)
     * @param string $decayMinutes Time window in minutes (default: 1)
     * @return Response Either continues request or returns rate limit error
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '60', string $decayMinutes = '1'): Response
    {
        $maxAttemptsInt = (int) $maxAttempts;
        $decayMinutesInt = (int) $decayMinutes;
        
        $key = $this->resolveRequestSignature($request);
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttemptsInt) {
            return $this->error(
                'Too many attempts. Please try again in ' . $decayMinutesInt . ' minute(s).', 
                429,
                ['retry_after' => $decayMinutesInt]
            );
        }
        
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutesInt));
        
        $response = $next($request);
        
        $response->headers->set('X-RateLimit-Limit', $maxAttemptsInt);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttemptsInt - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($decayMinutesInt)->timestamp);
        
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
