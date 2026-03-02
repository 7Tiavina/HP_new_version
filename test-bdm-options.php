<?php
/**
 * Test endpoint commande/options - Format exact du code Laravel
 */

$baseUrl = 'https://recette-erp.bagagesdumonde.com';
$username = 'hellopassenger@bdm.com';
$password = 'f$RkP%x86M';
$serviceId = 'dfb8ac1b-8bb1-4957-afb4-1faedaf641b7';
$plateformeId = '88bb89e0-b966-4420-9ed3-7a6745e4d947'; // CDG

echo "=== TEST COMMANDE OPTIONS (Format Laravel) ===\n\n";

// Authentification
$loginData = json_encode(['username' => $username, 'password' => $password]);
$ch = curl_init($baseUrl . '/User/Login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
$response = curl_exec($ch);
curl_close($ch);
$token = json_decode($response, true)['data']['accessToken'];
echo "Token obtenu ✓\n\n";

// Format EXACT comme dans BdmApiService.php
$baggages = [
    [
        'productId' => 'cf2192d7-8358-41e7-82e1-4341658bbeb0',
        'serviceId' => $serviceId,
        'dateDebut' => '2026-02-27T15:00:00Z',
        'dateFin' => '2026-02-28T16:00:00Z',
        'quantity' => 1
    ]
];

$commandeLignes = array_map(function($baggage) {
    return [
        "idProduit" => $baggage['productId'],
        "idService" => $baggage['serviceId'] ?? $serviceId,
        "dateDebut" => $baggage['dateDebut'],
        "dateFin" => $baggage['dateFin'],
        "prixTTC" => 0,
        "prixTTCAvantRemise" => 0,
        "tauxRemise" => 0,
        "quantite" => $baggage['quantity']
    ];
}, $baggages);

$payload = [
    'commandeLignes' => $commandeLignes,
    'commandeOptions' => [] // Vide pour découvrir
];

echo "Payload:\n" . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

// Test avec ?lg=fr comme dans le code Laravel
echo "1. Test avec ?lg=fr...\n";
$url = $baseUrl . '/api/plateforme/' . $plateformeId . '/commande/options?lg=fr';
echo "   URL: $url\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP: $httpCode\n";
$data = json_decode($response, true);
echo "   Réponse: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

echo "\n";

// Test 2: Vérifier structure de réponse
echo "2. Analyse de la réponse...\n";
if (isset($data['isSucceed'])) {
    if ($data['isSucceed']) {
        echo "   ✅ isSucceed: TRUE\n";
        if (isset($data['content'])) {
            echo "   content: " . json_encode($data['content'], JSON_UNESCAPED_UNICODE) . "\n";
        }
    } else {
        echo "   ❌ isSucceed: FALSE\n";
        echo "   message: " . ($data['message'] ?? 'N/A') . "\n";
    }
} else {
    echo "   ⚠️  Pas de champ 'isSucceed'\n";
}

echo "\n=== FIN DU TEST ===\n";
