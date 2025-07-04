<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Enregistre les services si besoin
    }

    public function boot(): void
    {
        if (app()->runningInConsole()) {
            return; // Ne bloque pas les commandes Artisan
        }

        $host = Request::getHost(); // Ex: touzadaw.localhost

        // ✅ Cas normal pour localhost (page principale)
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return;
        }

        // ✅ Vérifie que l'host se termine bien par .localhost
        if (!str_ends_with($host, '.localhost')) {
            abort(403, 'Nom d\'hôte non autorisé.');
        }

        // ✅ Extraire le sous-domaine
        if (preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            $subdomain = $matches[1];

            // ✅ Vérifie dans la BDD s'il existe un utilisateur avec ce nom
            $exists = DB::table('users')->where('name', $subdomain)->exists();

            if (!$exists) {
                abort(403, 'Ce sous-domaine ne correspond à aucun utilisateur.');
            }

            // ✅ Sous-domaine reconnu, autorisé
            return;
        }

        // ❌ Si format de sous-domaine incorrect
        abort(403, 'Format de sous-domaine invalide.');
    }
}
