<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Ne rien faire si on est dans le terminal (artisan)
        if (app()->runningInConsole()) {
            return;
        }

        // Récupérer le sous-domaine et le port
        $host = request()->getHost(); // exemple : moetaz.localhost
        $port = request()->getPort(); // exemple : 8547

        // DEBUG : Log toutes les requêtes
        \Log::info("DEBUG - Host: {$host}, Port: {$port}");

        // Autoriser accès direct si localhost
        if (in_array($host, ['localhost', '127.0.0.1'])) {
            \Log::info("DEBUG - Accès localhost autorisé");
            return;
        }

        // Vérifier le format sous-domaine.localhost
        if (!preg_match('/^([a-zA-Z0-9_-]+)\\.localhost$/', $host, $matches)) {
            \Log::error("DEBUG - Format invalide pour: {$host}");
            abort(403, 'Format de sous-domaine invalide.');
        }

        $subdomain = $matches[1];
        \Log::info("DEBUG - Subdomain extrait: {$subdomain}");

        // Vérifier que la table tenants existe
        if (Schema::hasTable('tenants')) {
            \Log::info("DEBUG - Table tenants existe");
            
            // Vérifier que le sous-domaine est présent
            $tenant = DB::table('tenants')->where('subdomain', $subdomain)->first();
            if (!$tenant) {
                \Log::error("DEBUG - Tenant non trouvé pour: {$subdomain}");
                abort(403, "Sous-domaine '{$subdomain}' non autorisé.");
            }
            
            \Log::info("DEBUG - Tenant trouvé: " . json_encode($tenant));

            // Vérifier que l'utilisateur connecté correspond au tenant
            if (auth()->check()) {
                $user = auth()->user();
                // Supposons que le user a un champ tenant_id
                if ($user->tenant_id != $tenant->id) {
                    \Log::error("DEBUG - User tenant_id ne correspond pas");
                    abort(403, "Vous n'avez pas accès à ce sous-domaine.");
                }
            }

            // Récupérer le port attendu depuis le cache (mis lors de la création du tenant)
            $expectedPort = Cache::get("tenant_port_{$subdomain}");
            \Log::info("DEBUG - Port attendu pour {$subdomain}: " . ($expectedPort ?? 'NULL'));
            
            if (!$expectedPort) {
                \Log::error("DEBUG - Port non trouvé pour: {$subdomain}");
                abort(403, "Port pour '{$subdomain}' non trouvé. Veuillez réessayer plus tard.");
            }

            // Vérifier que le port correspond au sous-domaine
            if ((int)$expectedPort !== (int)$port) {
                \Log::error("DEBUG - Port incorrect: attendu {$expectedPort}, reçu {$port}");
                abort(403, "Port invalide pour '{$subdomain}'. Utilisez le port : {$expectedPort}");
            }

            // VERIFICATION : Détecter si le sous-domaine a été modifié pour ce port
            \Log::info("DEBUG - Appel verifySubdomainModification");
            $this->verifySubdomainModification($subdomain, $port);
        } else {
            \Log::error("DEBUG - Table tenants n'existe pas");
        }
    }

    /**
     * Vérifier si le sous-domaine a été modifié pour ce port spécifique
     */
    private function verifySubdomainModification(string $subdomain, int $port): void
    {
        // Récupérer le sous-domaine qui était associé à ce port
        $expectedSubdomain = Cache::get("port_subdomain_{$port}");
        
        // Debug : Log les valeurs pour comprendre ce qui se passe
        \Log::info("Debug - Port: {$port}, Subdomain actuel: {$subdomain}, Subdomain attendu: " . ($expectedSubdomain ?? 'NULL'));
        
        // Si on a un sous-domaine en cache pour ce port
        if ($expectedSubdomain) {
            // Vérifier si le sous-domaine a été modifié
            if ($expectedSubdomain !== $subdomain) {
                \Log::warning("Tentative de modification détectée : {$expectedSubdomain} -> {$subdomain} sur port {$port}");
                abort(403, "Modification détectée : Le port {$port} était associé au sous-domaine '{$expectedSubdomain}', pas '{$subdomain}'.");
            }
            \Log::info("Accès autorisé pour {$subdomain} sur port {$port}");
        } else {
            // Premier accès : sauvegarder l'association port -> subdomain
            Cache::put("port_subdomain_{$port}", $subdomain, now()->addDays(30));
            \Log::info("Premier accès enregistré : {$subdomain} sur port {$port}");
        }
    }
}
