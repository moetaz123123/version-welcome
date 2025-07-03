<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Tenant;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Enregistrer les services si besoin
    }

    public function boot(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $host = request()->getHost();  // ex: saas.localhost
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
        config(['app.tenant' => $tenant]);
    }
}
