<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Tenant;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Tu peux enregistrer ici tes services
    }

    public function boot(): void
    {
        // Ne bloque pas les commandes artisan (php artisan ...)
        if (app()->runningInConsole()) {
            return;
        }

        $host = request()->getHost();  // ex: touzadaw.localhost ou www.touzadaw.localhost
        $port = request()->getPort();  // ex: 35167

        // Vérifie que le host finit par '.localhost'
        if (!str_ends_with($host, '.localhost')) {
            abort(403, 'Unauthorized host.');
        }

        // Accepte www.sousdomaine.localhost OU sousdomaine.localhost
        if (!preg_match('/^(www\.)?([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            abort(403, 'Invalid subdomain format.');
        }

        $subdomain = $matches[2];

        // Cherche le tenant en base de données
        $tenant = Tenant::where('subdomain', $subdomain)->first();

        // Si le sous-domaine n'est pas dans la base, erreur 403
        if (!$tenant) {
            abort(403, 'Tenant not found or unauthorized subdomain.');
        }

        // Vérifie que le port correspond au port enregistré pour ce tenant (optionnel)
        if ($tenant->port != $port) {
            abort(403, 'Unauthorized port for this tenant.');
        }

        // Optionnel : stocker les infos du tenant dans la config
        config(['app.tenant_subdomain' => $subdomain]);
        config(['app.tenant_port' => $port]);
        config(['app.tenant' => $tenant]);
    }
}
