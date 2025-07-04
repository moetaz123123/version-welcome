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

}
}
