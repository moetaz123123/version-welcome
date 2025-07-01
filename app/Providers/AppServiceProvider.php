<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
    public function boot()
{
    // Autoriser uniquement l'accès via le sous-domaine (ex: touzadaw.localhost)
    $host = request()->getHost();
    if (!str_ends_with($host, '.localhost')) {
        abort(403, 'Unauthorized host.');
    }
}
}
