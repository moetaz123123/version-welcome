<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session; // pour plus de clarté
use Illuminate\Support\Facades\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Enregistre les services si besoin
    }

    public function boot(): void
    {
        if (app()->runningInConsole()) {
            return; // ne bloque pas les commandes Artisan
        }

        $host = Request::getHost();

        // 1. Vérifie que le domaine se termine par .localhost
        if (!str_ends_with($host, '.localhost')) {
            abort(403, 'Unauthorized host.');
        }

        // 2. Extrait le sous-domaine
        if (!preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            abort(403, 'Invalid subdomain format.');
        }

        $currentSubdomain = $matches[1];

        // 3. Récupère le sous-domaine autorisé pour la session
        $allowedSubdomain = Session::get('allowed_subdomain');

        // Si la session ne contient pas de sous-domaine autorisé : blocage
        if (!$allowedSubdomain) {
            abort(403, 'No subdomain allowed for this user.');
        }

        // 4. Comparaison stricte
        if ($currentSubdomain !== $allowedSubdomain) {
            abort(403, 'Access to this subdomain is forbidden.');
        }
    }
}
