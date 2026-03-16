<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckApiConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie la connectivité des APIs externes (BDM, 1min.ai, Monetico)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification de la connectivité des APIs externes...');
        $this->newLine();

        $apis = [
            'BDM API' => [
                'url' => config('services.bdm.base_url') ?? env('BDM_API_BASE_URL'),
                'test_dns' => 'recette-erp.bagagesdumonde.com',
            ],
            '1min.ai API' => [
                'url' => config('services.onemin.base_url') ?? env('ONEMIN_AI_BASE_URL'),
                'test_dns' => 'api.1min.ai',
            ],
            'Monetico API' => [
                'url' => config('monetico.base_url'),
                'test_dns' => 'api.gateway.monetico-retail.com',
            ],
        ];

        $allOk = true;

        foreach ($apis as $name => $config) {
            $url = rtrim($config['url'], '/');
            
            $this->checkConnection($name, $url, $config['test_dns']);
            
            if (!$this->lastCheckOk) {
                $allOk = false;
            }
        }

        $this->newLine();
        $this->line('===========================================');
        
        if ($allOk) {
            $this->info('✅ Toutes les APIs sont accessibles.');
            Log::info('[api:check] Toutes les APIs sont accessibles.');
            return 0;
        } else {
            $this->error('❌ Des problèmes de connectivité ont été détectés.');
            Log::error('[api:check] Des problèmes de connectivité ont été détectés.');
            return 1;
        }
    }

    private bool $lastCheckOk = true;

    private function checkConnection(string $name, string $url, string $dnsHost): void
    {
        $this->line("📡 Test de {$name}...");
        $this->line("   URL: {$url}");
        $this->line("   Host DNS: {$dnsHost}");

        try {
            $startTime = microtime(true);
            
            $response = Http::timeout(10)->get($url);
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($response->successful()) {
                $this->info("   ✅ SUCCÈS ({$response->status()} - {$duration}ms)");
                Log::info("[api:check] {$name}: OK ({$response->status()} - {$duration}ms)");
                $this->lastCheckOk = true;
            } else {
                $this->warn("   ⚠️ RÉPONSE NON-200 ({$response->status()} - {$duration}ms)");
                Log::warning("[api:check] {$name}: WARNING ({$response->status()} - {$duration}ms)");
                $this->lastCheckOk = false;
            }
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $errorMsg = $e->getMessage();
            $this->error("   ❌ ÉCHEC DE CONNEXION");
            $this->error("      Erreur: {$errorMsg}");
            
            // Vérifier si c'est une erreur DNS
            if (str_contains($errorMsg, 'Could not resolve host')) {
                $this->error("      🚨 PROBLÈME DNS DÉTECTÉ !");
                $this->error("      Solution: Vérifiez la configuration DNS du serveur.");
                Log::error("[api:check] {$name}: DNS_ERROR - {$errorMsg}");
            } else {
                Log::error("[api:check] {$name}: CONNECTION_ERROR - {$errorMsg}");
            }
            
            $this->lastCheckOk = false;
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $this->error("   ❌ ÉCHEC DE REQUÊTE");
            $this->error("      Erreur: " . $e->getMessage());
            Log::error("[api:check] {$name}: REQUEST_ERROR - {$e->getMessage()}");
            $this->lastCheckOk = false;
            
        } catch (\Exception $e) {
            $this->error("   ❌ ÉCHEC INATTENDU");
            $this->error("      Erreur: " . $e->getMessage());
            Log::error("[api:check] {$name}: ERROR - {$e->getMessage()}");
            $this->lastCheckOk = false;
        }

        $this->newLine();
    }
}
