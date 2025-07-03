<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Tenant;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Enregistrer les services si besoin
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ne bloque pas les commandes artisan (php artisan ...)
        if (app()->runningInConsole()) {
            return;
        }

        $host = request()->getHost();  // ex: www.moetaz.localhost
        $port = request()->getPort();  // ex: 4567

        // Vérifie que le host finit par '.localhost'
        if (!str_ends_with($host, '.localhost')) {
            abort(403, 'Unauthorized host.');
        }

        // Vérifie le format du sous-domaine : www.sousdomaine.localhost
        if (!preg_match('/^www\.([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            abort(403, 'Invalid subdomain format.');
        }

        $subdomain = $matches[1];

        // Cherche le tenant en base de données
        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (!$tenant) {
            abort(403, 'Tenant not found.');
        }

        // Vérifie que le port correspond au port enregistré pour ce tenant
        if ($tenant->port != $port) {
            abort(403, 'Unauthorized port for this tenant.');
        }

        // Optionnel : stocker les infos du tenant dans la config
        config(['app.tenant_subdomain' => $subdomain]);
        config(['app.tenant_port' => $port]);
    }
}
