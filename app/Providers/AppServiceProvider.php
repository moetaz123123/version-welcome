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

    // Vérifie le sous-domaine (ex: touzadaw dans touzadaw.localhost)
    if (!preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
        abort(403, 'Invalid subdomain format.');
    }

    $subdomain = $matches[1];

    // Vérifie que le sous-domaine existe dans la table tenants
    $tenant = Tenant::where('subdomain', $subdomain)->first();

    if (!$tenant) {
        abort(403, 'Unauthorized subdomain.');
    }
}
}
