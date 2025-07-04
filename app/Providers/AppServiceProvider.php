<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Enregistre les services si besoin
    }

    public function boot()
    {
        if (app()->runningInConsole()) {
            return;
        }

        $host = request()->getHost();

        // Autorise localhost pur
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return;
        }

        // Autoriser uniquement les *.localhost
        if (!str_ends_with($host, '.localhost')) {
            abort(403, 'Unauthorized host.');
        }

        // Vérifie si le sous-domaine existe dans /etc/hosts
        $hostsFile = '/etc/hosts';
        $hostsContent = @file_get_contents($hostsFile); // le @ évite les warnings

        if (!$hostsContent || !str_contains($hostsContent, $host)) {
            abort(403, "Subdomain '{$host}' not found in /etc/hosts.");
        }
    }
}
