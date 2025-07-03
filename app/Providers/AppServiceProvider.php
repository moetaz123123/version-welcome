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
   public function boot()
{
    $host = request()->getHost();
    if (!str_ends_with($host, '.localhost')) {
        abort(403, 'Unauthorized host.');
    }

    if (!preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
        abort(403, 'Invalid subdomain format.');
    }

    $subdomain = $matches[1];

    // Récupère le sous-domaine autorisé depuis la session
    $allowedSubdomain = session('allowed_subdomain');

    // Si aucun sous-domaine autorisé n'est défini (pas connecté), on laisse passer ou on bloque selon ta logique
    if (!$allowedSubdomain) {
        // abort(403, 'No subdomain allowed for this user.');
        return;
    }

    // Vérifie que le sous-domaine courant est bien celui autorisé pour l'utilisateur
    if ($subdomain !== $allowedSubdomain) {
        abort(403, 'You are not allowed to access this subdomain.');
    }
}
}
