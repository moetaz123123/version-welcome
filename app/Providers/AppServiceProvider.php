<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Tenant; // Assure-toi que ce chemin est correct

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Tu peux enregistrer ici tes services
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ne bloque que les requêtes HTTP, pas les commandes artisan
        if (app()->runningInConsole()) {
            return;
        }

        if (request()->server('HTTP_HOST')) {
            $host = request()->getHost();

            if (preg_match('/^www\.(.+)\.localhost$/', $host, $matches)) {
                $tenantSubdomain = $matches[1];

                // Chercher le tenant dans la BDD
                if (!Tenant::where('subdomain', $tenantSubdomain)->exists()) {
                    abort(403, 'Sous-domaine non reconnu.');
                }

                // Définir dynamiquement le tenant courant
                config(['app.tenant_subdomain' => $tenantSubdomain]);

            } else {
                abort(403, 'Format de sous-domaine invalide.');
            }
        }
    }
}
