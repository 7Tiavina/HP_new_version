<?php
/**
 * Test API BDM - endpoint commande/options avec TOUS les champs requis
 */

$baseUrl = 'https://recette-erp.bagagesdumonde.com';
$username = 'hellopassenger@bdm.com';
$password = 'f$RkP%x86M';
$serviceId = 'dfb8ac1b-8bb1-4957-afb4-1faedaf641b7';
$plateformeId = '88bb89e0-b966-4420-9ed3-7a6745e4d947'; // CDG

echo "=== TEST API BDM - COMMANDE/OPTIONS (CHAMPS REQUIS) ===\n\n";

// Authentification
$loginData = json_encode(['username' => $username, 'password' => $password]);
$ch = curl_init($baseUrl . '/User/Login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);
$token = json_decode($response, true)['data']['accessToken'];
echo "✅ Token obtenu\n\n";

// Payload avec TOUS les champs requis
$payload = [
    'commandeLignes' => [
        [
            'idProduit' => 'cf2192d7-8358-41e7-82e1-4341658bbeb0',
            'idService' => $serviceId,
            'dateDebut' => '2026-02-27T15:00:00Z',
            'dateFin' => '2026-02-28T16:00:00Z',
            'prixTTC' => 0,
            'prixTTCAvantRemise' => 0,
            'tauxRemise' => 0,
            'quantite' => 1
        ]
    ],
    'commandeOptions' => [], // Vide pour découvrir les options
    'client' => [
        'Nom' => 'TEST',
        'Prenom' => 'Test',
        'Email' => 'test@test.com',
        'Telephone' => '+33612345678',
        'Adresse' => ''
    ],
    'commandeInfos' => [
        'modeTransport' => '',
        'lieu' => '',
        'commentaires' => 'Test options API'
    ]
];

echo "1. Test avec commandeOptions = [] (découvrir options)...\n";
$url = $baseUrl . '/api/plateforme/' . $plateformeId . '/commande/options?lg=fr';

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
echo "   Réponse:\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Test 2: Avec option Priority
echo "2. Test avec commandeOptions = [['libelle' => 'Priority']]...\n";
$payload['commandeOptions'] = [['libelle' => 'Priority']];

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
echo "   Réponse:\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Test 3: Avec option Premium
echo "3. Test avec commandeOptions = [['libelle' => 'Premium']]...\n";
$payload['commandeOptions'] = [['libelle' => 'Premium']];

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
echo "   Réponse:\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Test 4: Avec les deux options
echo "4. Test avec commandeOptions = [['Priority'], ['Premium']]...\n";
$payload['commandeOptions'] = [['libelle' => 'Priority'], ['libelle' => 'Premium']];

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
echo "   Réponse:\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== FIN DU TEST ===\n";
