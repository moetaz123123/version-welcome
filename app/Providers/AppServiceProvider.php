<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Tenant; // Assure-toi que ce chemin est correct

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
       public function boot()
{
    // Autoriser uniquement l'accès via le sous-domaine (ex: touzadaw.localhost)
    $host = request()->getHost();
    if (!str_ends_with($host, '.localhost')) {
        abort(403, 'Unauthorized host.');
    }
}
}
