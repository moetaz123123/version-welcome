<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Aucun service à enregistrer ici
    }

    public function boot(): void
    {
        // Ignore les commandes artisan
        if (app()->runningInConsole()) {
            return;
        }

        // Récupère juste le host sans vérifier le port
        $host = request()->getHost(); // ex: "moetaz.localhost"

        // Autoriser localhost ou 127.0.0.1 directement
        if (in_array($host, ['localhost', '127.0.0.1'])) {
            return;
        }

        // Vérifie que le sous-domaine est au bon format
        if (!preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            abort(403, 'Format de sous-domaine invalide.');
        }

        $subdomain = $matches[1]; // par exemple "moetaz"

        // Vérifie que le sous-domaine existe dans la table tenants
        if (Schema::hasTable('tenants')) {
            $exists = DB::table('tenants')->where('subdomain', $subdomain)->exists();

            if (!$exists) {
                abort(403, "Sous-domaine '{$subdomain}' non autorisé.");
            }
        } else {
            // Facultatif : bloque si la table n'existe pas
            abort(500, "Table 'tenants' non trouvée.");
        }
    }
}
