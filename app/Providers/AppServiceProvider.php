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

        $host = Request::getHost();

        if (!str_ends_with($host, '.localhost')) {
            abort(403, 'Unauthorized host.');
        }

        if (!preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
            abort(403, 'Invalid subdomain format.');
        }

        // L’index 1 correspond au premier groupe capturé dans la regex
        $currentSubdomain = $matches[1];

        $allowedSubdomain = Session::get('allowed_subdomain');

        if (!$allowedSubdomain || $currentSubdomain !== $allowedSubdomain) {
            abort(403, 'Access to this subdomain is forbidden.');
        }
    }
}
