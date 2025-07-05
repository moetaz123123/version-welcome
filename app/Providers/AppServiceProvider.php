<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Rien à faire ici pour l'instant
    }

    public function boot(): void
    {
        if (app()->runningInConsole()) {
            return; // Ignore les commandes Artisan
        }

        // Récupérer le host sans le port (ex: "taz.localhost" depuis "taz.localhost:2405")
        $host = request()->getHost();

        // Autoriser l'accès direct via localhost ou 127.0.0.1
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return;
        }

        // Vérifie que le host est bien un sous-domaine *.localhost
        if (!preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            abort(403, 'Format de sous-domaine invalide.');
        }

        $subdomain = $matches[1];

        // Vérifie que la table "tenants" existe avant de faire la requête
        if (Schema::hasTable('tenants')) {
            $exists = DB::table('tenants')->where('subdomain', $subdomain)->exists();

            if (!$exists) {
                abort(403, "Sous-domaine '{$subdomain}' non reconnu.");
            }
        }
    }
}
