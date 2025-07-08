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

        // VERIFICATION PRIORITAIRE : Vérifier d'abord si le port est déjà lié à un autre sous-domaine
        $this->verifyPortSubdomainBinding($subdomain, $port);

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
                if (isset($user->tenant_id) && $user->tenant_id != $tenant->id) {
                    \Log::error("DEBUG - User tenant_id ne correspond pas");
                    abort(403, "Vous n'avez pas accès à ce sous-domaine.");
                }
            }

            // Récupérer le port attendu depuis le cache
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

            \Log::info("DEBUG - Accès autorisé pour {$subdomain} sur port {$port}");
        } else {
            \Log::error("DEBUG - Table tenants n'existe pas");
        }
    }

    /**
     * Vérifier et protéger l'association port <-> sous-domaine
     */
    private function verifyPortSubdomainBinding(string $subdomain, int $port): void
    {
        $cacheKey = "port_subdomain_{$port}";
        $expectedSubdomain = Cache::get($cacheKey);
        
        \Log::info("DEBUG - Port: {$port}, Subdomain actuel: {$subdomain}, Subdomain lié: " . ($expectedSubdomain ?? 'AUCUN'));

        if ($expectedSubdomain) {
            // Le port est déjà lié à un sous-domaine
            if ($expectedSubdomain !== $subdomain) {
                \Log::warning("ALERTE SÉCURITÉ - Tentative de modification détectée!");
                \Log::warning("Port {$port} était lié à '{$expectedSubdomain}', tentative d'accès avec '{$subdomain}'");
                
                // Bloquer l'accès avec un message d'erreur explicite
                abort(403, 
                    "Erreur de sécurité : Le port {$port} est déjà associé au sous-domaine '{$expectedSubdomain}'. " .
                    "Vous ne pouvez pas utiliser ce port avec '{$subdomain}'. " .
                    "Veuillez utiliser le port correct pour ce sous-domaine ou libérer le port existant."
                );
            }
            
            // Renouveler le cache pour maintenir l'association
            Cache::put($cacheKey, $subdomain, now()->addDays(30));
            \Log::info("DEBUG - Association confirmée et renouvelée: {$subdomain} <-> port {$port}");
            
        } else {
            // Premier accès : créer l'association
            Cache::put($cacheKey, $subdomain, now()->addDays(30));
            \Log::info("DEBUG - Nouvelle association créée: {$subdomain} <-> port {$port}");
        }
    }

    /**
     * Méthode utilitaire pour libérer un port (à appeler depuis une route admin)
     */
    public static function releasePort(int $port): bool
    {
        $cacheKey = "port_subdomain_{$port}";
        $subdomain = Cache::get($cacheKey);
        
        if ($subdomain) {
            Cache::forget($cacheKey);
            \Log::info("Port {$port} libéré du sous-domaine '{$subdomain}'");
            return true;
        }
        
        return false;
    }

    /**
     * Méthode utilitaire pour voir toutes les associations actives
     */
    public static function getActivePortBindings(): array
    {
        $bindings = [];
        
        // Cette méthode nécessiterait une implémentation plus complexe
        // pour scanner tous les ports en cache. Pour l'instant, on peut
        // la laisser vide ou implémenter un système de tracking séparé.
        
        return $bindings;
    }
}
