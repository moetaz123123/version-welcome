<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ...
    }

    public function boot(): void
    {
        if (app()->runningInConsole()) {
            return; // Ignore artisan commands
        }

        // Récupère le host sans le port
        $host = request()->getHost(); // ex: "moetaz.localhost"

        // Autoriser localhost direct (localhost ou 127.0.0.1)
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return;
        }

        // Vérifie le format sous-domaine.localhost
        if (!preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            abort(403, 'Format de sous-domaine invalide.');
        }

        $subdomain = $matches[1];

        // Vérifie que la table tenants existe
        if (Schema::hasTable('tenants')) {
            $exists = DB::table('tenants')->where('subdomain', $subdomain)->exists();

            if (!$exists) {
                abort(403, "Sous-domaine '{$subdomain}' non autorisé.");
            }
        }
    }
}
