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
        // Autoriser uniquement l'accès via le sous-domaine (ex: touzadaw.localhost)
        $host = request()->getHost();

        if (!str_ends_with($host, '.localhost')) {
            abort(403, 'Unauthorized host.');
        }

        // Extraire le sous-domaine (ex: "touzadaw" de "touzadaw.localhost")
        if (preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            $subdomain = $matches[1];

            // Vérifie si ce sous-domaine existe comme nom d’utilisateur
            $exists = DB::table('users')->where('name', $subdomain)->exists();

            if (!$exists) {
                abort(403, 'This subdomain is not associated with any user.');
            }
        } else {
            abort(403, 'Invalid subdomain format.');
        }
    }
}

}
