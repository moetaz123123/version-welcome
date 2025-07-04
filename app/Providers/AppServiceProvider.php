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

    public function boot()
    {
        // Ne bloque pas les commandes Artisan (ex: php artisan migrate)
        if (app()->runningInConsole()) {
            return;
        }

        $host = Request::getHost(); // ex: sub.localhost

        // Autoriser localhost sans contrôle
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return;
        }

        // Vérifie que l’host est du type sousdomaine.localhost
        if (preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            $subdomain = $matches[1]; // Extrait "sub" depuis "sub.localhost"

            // Vérifie si ce sous-domaine est présent dans la BDD
            $exists = DB::table('users')->where('name', $subdomain)->exists();

            if (!$exists) {
                abort(403, 'Ce sous-domaine ne correspond à aucun utilisateur.');
            }

            // ✅ Sous-domaine valide → continuer normalement
            return;
        }

        // ❌ Si l’host ne correspond pas au format attendu
        abort(403, 'Nom d’hôte invalide.');
    }
}
