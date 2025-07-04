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

    ppublic function boot(): void
{
    if (app()->runningInConsole()) {
        return;
    }

    $host = request()->getHost();
    if (!str_ends_with($host, '.localhost')) {
        abort(403, 'Unauthorized host.');
    }

    if (!preg_match('/^([a-zA-Z0-9_-]+)\.localhost$/', $host, $matches)) {
        abort(403, 'Invalid subdomain format.');
    }

    $currentSubdomain = $matches[2];
    $allowedSubdomain = session('allowed_subdomain');

    if (!$allowedSubdomain || $currentSubdomain !== $allowedSubdomain) {
        abort(403, 'Access to this subdomain is forbidden.');
    }
}
}
