<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
    if (request()->server('HTTP_HOST')) {
        $host = request()->getHost();

        if (preg_match('/^www\.(.+)\.localhost$/', $host, $matches)) {
            $tenantSubdomain = $matches[1];

            // Ici tu peux chercher le tenant dans la BDD
            if (!Tenant::where('subdomain', $tenantSubdomain)->exists()) {
                abort(403, 'Sous-domaine non reconnu.');
            }

            // Optionnel : définir le tenant courant (global scope, config dynamique, etc.)
            config(['app.tenant_subdomain' => $tenantSubdomain]);

        } else {
            abort(403, 'Format de sous-domaine invalide.');
        }
    }
}

