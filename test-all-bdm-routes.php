<?php
/**
 * Test complet de TOUS les endpoints API BDM
 */

$baseUrl = 'https://recette-erp.bagagesdumonde.com';
$username = 'hellopassenger@bdm.com';
$password = 'f$RkP%x86M';
$serviceId = 'dfb8ac1b-8bb1-4957-afb4-1faedaf641b7';
$plateformeId = '88bb89e0-b966-4420-9ed3-7a6745e4d947'; // CDG

echo "=== TEST COMPLET API BDM ===\n\n";
echo "URL: $baseUrl\n";
echo "Plateforme ID: $plateformeId\n";
echo "Service ID: $serviceId\n\n";

// Étape 1: Authentification
echo "1. Authentification...\n";
$loginData = json_encode([
    'username' => $username,
    'password' => $password
]);

$ch = curl_init($baseUrl . '/User/Login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP: $httpCode\n";
$json = json_decode($response, true);

if (!isset($json['isSucceed']) || !$json['isSucceed']) {
    echo "❌ Échec auth: " . json_encode($json) . "\n";
    exit;
}

$token = $json['data']['accessToken'];
echo "✅ Token obtenu\n\n";

// Liste de tous les endpoints à tester
$endpoints = [
    // Plateformes
    ['method' => 'GET', 'path' => '/api/plateforme', 'desc' => 'Liste des plateformes'],
    ['method' => 'GET', 'path' => '/api/plateforme/' . $plateformeId, 'desc' => 'Détails plateforme'],
    ['method' => 'GET', 'path' => '/api/service/' . $serviceId . '/plateformes', 'desc' => 'Plateformes par service'],
    
    // Lieux
    ['method' => 'GET', 'path' => '/api/lieux', 'desc' => 'Liste des lieux'],
    ['method' => 'GET', 'path' => '/api/plateforme/' . $plateformeId . '/lieux', 'desc' => 'Lieux par plateforme'],
    
    // Services
    ['method' => 'GET', 'path' => '/api/service', 'desc' => 'Liste des services'],
    ['method' => 'GET', 'path' => '/api/service/' . $serviceId, 'desc' => 'Détails service'],
    
    // Produits
    ['method' => 'GET', 'path' => '/api/produit', 'desc' => 'Liste des produits'],
    ['method' => 'GET', 'path' => '/api/service/' . $serviceId . '/produits', 'desc' => 'Produits par service'],
    ['method' => 'GET', 'path' => '/api/plateforme/' . $plateformeId . '/service/' . $serviceId . '/1/produits', 'desc' => 'Produits (durée 1 jour)'],
    
    // Options
    ['method' => 'GET', 'path' => '/api/option', 'desc' => 'Liste des options'],
    ['method' => 'GET', 'path' => '/api/service/' . $serviceId . '/options', 'desc' => 'Options par service'],
    ['method' => 'GET', 'path' => '/api/plateforme/' . $plateformeId . '/options', 'desc' => 'Options par plateforme'],
    ['method' => 'POST', 'path' => '/api/plateforme/' . $plateformeId . '/commande/options', 'desc' => 'Options quote', 'body' => ['commandeLignes' => [], 'commandeOptions' => []]],
    
    // Disponibilité
    ['method' => 'POST', 'path' => '/api/plateforme/' . $plateformeId . '/disponibilite', 'desc' => 'Vérifier disponibilité', 'body' => ['dateToCheck' => '20260227T1520']],
    
    // Tarifs/Quote
    ['method' => 'GET', 'path' => '/api/plateforme/' . $plateformeId . '/service/' . $serviceId . '/1/produits', 'desc' => 'Tarifs (1 jour)'],
    
    // Commande
    ['method' => 'POST', 'path' => '/api/plateforme/' . $plateformeId . '/commande', 'desc' => 'Créer commande (TEST SEULEMENT)', 'skip' => true],
];

$headers = [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
];

echo "\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($endpoints as $endpoint) {
    if (!empty($endpoint['skip'])) {
        echo "⏭️  SKIP: " . $endpoint['desc'] . "\n";
        echo "   " . $endpoint['method'] . " " . $endpoint['path'] . "\n\n";
        continue;
    }
    
    echo "📍 " . $endpoint['desc'] . "\n";
    echo "   " . $endpoint['method'] . " " . $endpoint['path'] . "\n";
    
    $ch = curl_init($baseUrl . $endpoint['path']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($endpoint['method'] === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (isset($endpoint['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($endpoint['body']));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   HTTP: $httpCode\n";
    
    $data = json_decode($response, true);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        if ($data === null) {
            echo "   ⚠️  Réponse vide ou JSON invalide\n";
        } elseif (is_array($data)) {
            if (isset($data[0])) {
                echo "   ✅ Tableau: " . count($data) . " élément(s)\n";
                if (count($data) > 0) {
                    echo "   Premier élément: " . json_encode($data[0], JSON_UNESCAPED_UNICODE) . "\n";
                }
            } elseif (isset($data['content']) && is_array($data['content'])) {
                echo "   ✅ Contenu: " . count($data['content']) . " élément(s)\n";
                if (count($data['content']) > 0) {
                    echo "   Premier: " . json_encode($data['content'][0], JSON_UNESCAPED_UNICODE) . "\n";
                }
            } elseif (isset($data['statut'])) {
                echo "   ✅ Statut: " . $data['statut'] . ", Message: " . ($data['message'] ?? 'N/A') . "\n";
                if (isset($data['content'])) {
                    echo "   Content type: " . gettype($data['content']) . "\n";
                }
            } else {
                echo "   ✅ Réponse: " . json_encode(array_keys($data), JSON_UNESCAPED_UNICODE) . "\n";
            }
        } else {
            echo "   ✅ Réponse: " . substr(json_encode($data), 0, 200) . "...\n";
        }
    } else {
        echo "   ❌ Erreur: " . substr($response, 0, 200) . "\n";
    }
    
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

echo "\n=== FIN DU TEST ===\n";
