<?php

/**
 * Script de test de connectivité des APIs externes
 * 
 * Usage : php test_api_connectivity.php
 * 
 * Ce script vérifie si le serveur peut atteindre :
 * - BDM API
 * - 1min.ai API
 * - Monetico API
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "===========================================\n";
echo "🔍 Test de connectivité des APIs externes\n";
echo "===========================================\n\n";

$apis = [
    'BDM API' => [
        'url' => config('services.bdm.base_url') ?? env('BDM_API_BASE_URL'),
        'endpoint' => '/health',
    ],
    '1min.ai API' => [
        'url' => config('services.onemin.base_url') ?? env('ONEMIN_AI_BASE_URL'),
        'endpoint' => '',
    ],
    'Monetico API' => [
        'url' => config('monetico.base_url'),
        'endpoint' => '',
    ],
];

$results = [];

foreach ($apis as $name => $config) {
    $url = rtrim($config['url'], '/');
    
    echo "📡 Test de {$name}...\n";
    echo "   URL: {$url}\n";
    
    try {
        $startTime = microtime(true);
        
        $response = Http::timeout(10)->get($url . $config['endpoint']);
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        if ($response->successful()) {
            echo "   ✅ SUCCÈS ({$response->status()} - {$duration}ms)\n";
            $results[$name] = ['status' => 'OK', 'code' => $response->status(), 'time' => $duration];
        } else {
            echo "   ⚠️ RÉPONSE NON-200 ({$response->status()} - {$duration}ms)\n";
            $results[$name] = ['status' => 'WARNING', 'code' => $response->status(), 'time' => $duration];
        }
        
    } catch (\Illuminate\Http\Client\ConnectionException $e) {
        echo "   ❌ ÉCHEC DE CONNEXION\n";
        echo "      Erreur: " . $e->getMessage() . "\n";
        $results[$name] = ['status' => 'CONNECTION_ERROR', 'error' => $e->getMessage()];
        
    } catch (\Illuminate\Http\Client\RequestException $e) {
        echo "   ❌ ÉCHEC DE REQUÊTE\n";
        echo "      Erreur: " . $e->getMessage() . "\n";
        $results[$name] = ['status' => 'REQUEST_ERROR', 'error' => $e->getMessage()];
        
    } catch (\Exception $e) {
        echo "   ❌ ÉCHEC INATTENDU\n";
        echo "      Erreur: " . $e->getMessage() . "\n";
        $results[$name] = ['status' => 'ERROR', 'error' => $e->getMessage()];
    }
    
    echo "\n";
}

// Résumé
echo "===========================================\n";
echo "📊 RÉSUMÉ\n";
echo "===========================================\n\n";

$okCount = 0;
$errorCount = 0;

foreach ($results as $name => $result) {
    $icon = ($result['status'] === 'OK') ? '✅' : '❌';
    echo "{$icon} {$name}: {$result['status']}\n";
    
    if ($result['status'] === 'OK') {
        $okCount++;
    } else {
        $errorCount++;
    }
}

echo "\n";
echo "Total: {$okCount} OK, {$errorCount} ÉCHEC\n";
echo "\n";

if ($errorCount > 0) {
    echo "⚠️  Des problèmes de connectivité ont été détectés.\n";
    echo "   Consultez le fichier RECOMMANDATIONS_SERVER.md pour les solutions.\n";
    exit(1);
} else {
    echo "✅ Toutes les APIs sont accessibles.\n";
    exit(0);
}
