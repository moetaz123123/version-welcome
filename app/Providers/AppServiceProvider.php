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
        // Ne bloque que les requêtes HTTP, pas les commandes artisan
        if (app()->runningInConsole()) {
            return;
        }

        $host = request()->getHost();
        if (!str_ends_with($host, '.localhost')) {
            abort(403, 'Unauthorized host.');
        }
    }
}
