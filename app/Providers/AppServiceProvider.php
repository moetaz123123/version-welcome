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
        // Ne bloque pas les commandes Artisan
        if (app()->runningInConsole()) {
            return;
        }

     

        $subdomain = $matches[1];

        // Vérifie si ce sous-domaine existe comme nom d’utilisateur
        $exists = DB::table('users')->where('name', $subdomain)->exists();

        if (!$exists) {
            abort(403, 'This subdomain is not associated with any user.');
        }

        // ✅ Si on arrive ici, le sous-domaine est valide
    }
}
