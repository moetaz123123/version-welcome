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
        // Tu peux enregistrer ici tes services
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Autoriser uniquement les accès via un sous-domaine de localhost (ex: aziz.localhost)
        $host = request()->getHost();

        if (!str_ends_with($host, '.localhost')) {
            abort(403, 'Unauthorized host.');
        }
    }
}
