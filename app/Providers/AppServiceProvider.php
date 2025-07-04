<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Enregistre les services si besoin
    }

    public function boot(): void
    {
        // ✅ Ne bloque pas les commandes Artisan (migrate, serve, etc.)
        if (app()->runningInConsole()) {
            return;
        }

        $host = Request::getHost(); // Ex: fr.localhost

        // ✅ Autoriser localhost seul
        if (in_array($host, ['localhost', '127.0.0.1'])) {
            return;
        }

        // ✅ Vérifie que l’hôte est sous forme: sub.localhost
        if (preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            $subdomain = $matches[1]; // Extrait "fr" de "fr.localhost"

            // (Debug facultatif) log::info("Subdomain reçu : " . $subdomain);

            try {
                $exists = DB::table('users')->where('name', $subdomain)->exists();
            } catch (\Exception $e) {
                Log::error("Erreur base de données : " . $e->getMessage());
                abort(500, 'Erreur lors de la connexion à la base de données.');
            }

            if (!$exists) {
                abort(403, "Le sous-domaine \"$subdomain\" ne correspond à aucun utilisateur.");
            }

            // ✅ Sous-domaine valide → continuer normalement
            return;
        }

        // ❌ Si le host ne correspond pas au format attendu
        abort(403, 'Nom d’hôte invalide. Format attendu : sousdomaine.localhost');
    }
}
