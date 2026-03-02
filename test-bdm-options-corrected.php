<?php
/**
 * Test endpoint commande/options - Avec les bonnes majuscules
 */

$baseUrl = 'https://recette-erp.bagagesdumonde.com';
$username = 'hellopassenger@bdm.com';
$password = 'f$RkP%x86M';
$serviceId = 'dfb8ac1b-8bb1-4957-afb4-1faedaf641b7';
$plateformeId = '88bb89e0-b966-4420-9ed3-7a6745e4d947'; // CDG

echo "=== TEST COMMANDE OPTIONS (Avec majuscules correctes) ===\n\n";

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

// Payload avec MAJUSCULES comme l'API BDM les attend
$payload = [
    'CommandeLignes' => [
        [
            'idProduit' => 'cf2192d7-8358-41e7-82e1-4341658bbeb0',
            'idService' => $serviceId,
            'dateDebut' => '2026-03-15T15:00:00Z',
            'dateFin' => '2026-03-16T16:00:00Z',
            'prixTTC' => 0,
            'prixTTCAvantRemise' => 0,
            'tauxRemise' => 0,
            'quantite' => 1
        ]
    ],
    'CommandeOptions' => [], // Vide pour découvrir
    'Client' => [
        'email' => 'test@test.com',
        'telephone' => '+33612345678',
        'nom' => 'TEST',
        'prenom' => 'Test',
        'civilite' => 'M.',
        'nomSociete' => '',
        'adresse' => '',
        'complementAdresse' => '',
        'ville' => '',
        'codePostal' => '00000',
        'pays' => 'FRA'
    ],
    'CommandeInfos' => [
        'modeTransport' => 'Inconnu',
        'lieu' => 'Inconnu',
        'commentaires' => 'Devis options'
    ]
];

echo "Payload:\n" . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

// Test avec ?lg=fr
echo "1. Test avec ?lg=fr et payload complet...\n";
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

// Analyse de la réponse
echo "2. Analyse de la réponse...\n";
if (isset($data['isSucceed'])) {
    if ($data['isSucceed']) {
        echo "   ✅ isSucceed: TRUE\n";
        if (isset($data['content'])) {
            echo "   ✅ Options trouvées: " . count($data['content']) . "\n";
            foreach ($data['content'] as $option) {
                echo "      - " . ($option['libelle'] ?? 'N/A') . " : " . ($option['prixUnitaire'] ?? 'N/A') . "€\n";
            }
        }
    } else {
        echo "   ❌ isSucceed: FALSE\n";
        echo "   message: " . ($data['message'] ?? 'N/A') . "\n";
    }
} else {
    echo "   ⚠️  Pas de champ 'isSucceed'\n";
    echo "   message: " . ($data['message'] ?? 'N/A') . "\n";
}

echo "\n=== FIN DU TEST ===\n";
