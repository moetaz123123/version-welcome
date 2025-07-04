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
        if (app()->runningInConsole()) {
            return;
        }

        $host = Request::getHost();   // Exemple : touzaa.localhost
        $port = Request::getPort();   // Exemple : 8878

        if (!str_ends_with($host, '.localhost')) {
            abort(403, 'Unauthorized host.');
        }

        if (!preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            abort(403, 'Invalid subdomain format.');
        }

        $currentSubdomain = $matches[1];
        $allowedSubdomain = Session::get('allowed_subdomain');
        $allowedPort = Session::get('allowed_port');

        if (!$allowedSubdomain || !$allowedPort) {
            abort(403, 'No allowed subdomain or port set in session.');
        }

        if ($currentSubdomain !== $allowedSubdomain || $port != $allowedPort) {
            abort(403, "Access forbidden: subdomain or port mismatch.");
        }
    }
}
