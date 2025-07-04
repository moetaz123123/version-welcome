<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Enregistre les services si besoin
    }

    public function boot(): void
    {
        // Ne pas bloquer les commandes Artisan
        if (app()->runningInConsole()) {
            return;
        }

        $host = Request::getHost();

        // 1. Vérifie que le host termine par .localhost
        if (!str_ends_with($host, '.localhost')) {
            return; // On laisse passer, ne bloque pas toute l'app (utile pour le domaine principal)
        }

        // 2. Extraire le sous-domaine
        if (!preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            return; // Format non reconnu => ne pas bloquer brutalement
        }

        $currentSubdomain = $matches[1];

        // 3. Ne rien faire si on est sur la page d'accueil (ex: "/") et pas encore connecté
        if (!Session::has('allowed_subdomain')) {
            return; // Laisser passer la requête, le sous-domaine va être autorisé plus tard
        }

        $allowedSubdomain = Session::get('allowed_subdomain');

        // 4. Si le sous-domaine courant ne correspond pas à celui en session
        if ($currentSubdomain !== $allowedSubdomain) {
            abort(403, 'Tu ne peux pas accéder à ce sous-domaine.');
        }
    }
}
