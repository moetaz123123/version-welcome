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

  public function boot(): void
{
    if (app()->runningInConsole()) {
        return;
    }

    $host = \Illuminate\Support\Facades\Request::getHost();

    if ($host === 'localhost' || $host === '127.0.0.1') {
        return;
    }

    if (preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
        $subdomain = $matches[1];
        // Vérifie dans la table tenants
        $exists = \Illuminate\Support\Facades\DB::table('tenants')->where('name', $subdomain)->exists();
        if (!$exists) {
            abort(403, 'Ce sous-domaine ne correspond à aucun tenant.');
        }
        return;
    }

    abort(403, 'Nom d\'hôte non autorisé.');
}
}
