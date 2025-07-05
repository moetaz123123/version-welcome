<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

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

        $host = Request::getHost(); // ex: client1.localhost

        // Laisser passer les accès directs à localhost
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return;
        }

        // Vérifier que l'hôte est bien un sous-domaine de localhost
        if (!str_ends_with($host, '.localhost')) {
            abort(403, 'Nom d\'hôte non autorisé.');
        }

        // Extraire le sous-domaine (ex: "client1" de "client1.localhost")
        if (preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            $subdomain = $matches[1];

            // Vérifie dans la table tenants si le sous-domaine existe
            $exists = DB::table('tenants')->where('subdomain', $subdomain)->exists();

            if (!$exists) {
                abort(403, 'Ce sous-domaine ne correspond à aucun espace.');
            }

            // OK : le sous-domaine existe dans la table `tenants`
            return;
        }

        // Si le format du sous-domaine est invalide
        abort(403, 'Format de sous-domaine invalide.');
    }
}
