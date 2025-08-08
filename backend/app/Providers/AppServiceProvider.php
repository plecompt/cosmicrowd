<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void
    {
        // Si Railway envoie une requête HTTPS via un proxy, Laravel ne le voit pas automatiquement.
        // On le force ici.
        if ($request->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}