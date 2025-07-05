<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Ne rien faire si on est dans le terminal (artisan)
        if (app()->runningInConsole()) {
            return;
        }

        // Récupérer le sous-domaine
        $host = request()->getHost(); // exemple : moetaz.localhost
        $port = request()->getPort(); // exemple : 8547

        // Autoriser accès direct si localhost
        if (in_array($host, ['localhost', '127.0.0.1'])) {
            return;
        }

        // Vérifier le format sous-domaine.localhost
        if (!preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            abort(403, 'Format de sous-domaine invalide.');
        }

        $subdomain = $matches[1];

        // Vérifier que la table tenants existe
        if (Schema::hasTable('tenants')) {
            // Vérifier que le sous-domaine est présent
            $tenantExists = DB::table('tenants')->where('subdomain', $subdomain)->exists();

            if (!$tenantExists) {
                abort(403, "Sous-domaine '{$subdomain}' non autorisé.");
            }

            // Récupérer le port attendu depuis le cache (mis lors de la création du tenant)
            $expectedPort = Cache::get("tenant_port_{$subdomain}");

            if (!$expectedPort) {
                abort(403, "Port pour '{$subdomain}' non trouvé. Veuillez réessayer plus tard.");
            }

            if ((int)$expectedPort !== (int)$port) {
                abort(403, "Port invalide pour '{$subdomain}'. Utilisez : {$expectedPort}");
            }
        }
    }
}
