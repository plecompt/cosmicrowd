<?php

use App\Http\Middleware\RateLimitMiddleware;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\CheckOwnershipMiddleware;
use App\Http\Middleware\IsAdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        health: '/up', //route health check
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'cors' => CorsMiddleware::class,
            'check.owner' => CheckOwnershipMiddleware::class,
            'admin' => IsAdminMiddleware::class,
            //throttle with differents rules ( attemps / minutes )    
            'throttle.strict' => RateLimitMiddleware::class . ':5,1',
            'throttle.moderate' => RateLimitMiddleware::class . ':30,1',
            'throttle.normal' => RateLimitMiddleware::class, // 60,1
            'throttle.relaxed' => RateLimitMiddleware::class . ':100,1',
        ]);
        
        // Global middleware for all api
        $middleware->api(prepend: [ //prepend => executed before others middlewares
            \App\Http\Middleware\CorsMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
