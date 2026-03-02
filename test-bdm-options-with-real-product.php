<?php
/**
 * Test endpoint commande/options - Avec vrai produit depuis l'API
 */

$baseUrl = 'https://recette-erp.bagagesdumonde.com';
$username = 'hellopassenger@bdm.com';
$password = 'f$RkP%x86M';
$serviceId = 'dfb8ac1b-8bb1-4957-afb4-1faedaf641b7';
$plateformeId = '88bb89e0-b966-4420-9ed3-7a6745e4d947'; // CDG

echo "=== TEST COMMANDE OPTIONS (Avec vrai produit) ===\n\n";

// Authentification
$loginData = json_encode(['username' => $username, 'password' => $password]);
$ch = curl_init($baseUrl . '/User/Login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
$response = curl_exec($ch);
curl_close($ch);
$authResult = json_decode($response, true);
$token = $authResult['data']['accessToken'];
echo "Token obtenu ✓\n\n";

// Étape 1: Récupérer les vrais produits
echo "1. Récupération des produits pour la plateforme $plateformeId...\n";
$ch = curl_init($baseUrl . "/api/plateforme/$plateformeId/service/$serviceId/1/produits");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP: $httpCode\n";
$productsResult = json_decode($response, true);

if (isset($productsResult['isSucceed']) && $productsResult['isSucceed'] && isset($productsResult['content'])) {
    echo "   ✅ Produits récupérés avec succès\n";
    
    // Prendre le premier produit valide
    $firstProduct = null;
    foreach ($productsResult['content'] as $product) {
        if (isset($product['id'])) {
            $firstProduct = $product;
            break;
        }
    }
    
    if ($firstProduct) {
        echo "   Produit utilisé:\n";
        echo "      - ID: " . $firstProduct['id'] . "\n";
        echo "      - Libellé: " . ($firstProduct['libelle'] ?? 'N/A') . "\n";
        echo "      - Prix: " . ($firstProduct['prixUnitaire'] ?? 'N/A') . "€\n\n";
        
        // Étape 2: Tester commande/options avec ce produit
        echo "2. Test commande/options avec ce produit...\n";
        
        $payload = [
            'CommandeLignes' => [
                [
                    'idProduit' => $firstProduct['id'],
                    'idService' => $serviceId,
                    'dateDebut' => '2026-03-15T15:00:00Z',
                    'dateFin' => '2026-03-16T16:00:00Z',
                    'prixTTC' => $firstProduct['prixUnitaire'] ?? 0,
                    'prixTTCAvantRemise' => $firstProduct['prixUnitaireAvantRemise'] ?? $firstProduct['prixUnitaire'] ?? 0,
                    'tauxRemise' => $firstProduct['tauxRemise'] ?? 0,
                    'quantite' => 1
                ]
            ],
            'CommandeOptions' => [],
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
        echo "   Réponse: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        
        // Analyse
        echo "3. Analyse...\n";
        if (isset($data['isSucceed']) && $data['isSucceed']) {
            echo "   ✅ isSucceed: TRUE\n";
            if (isset($data['content'])) {
                echo "   ✅ Options trouvées: " . count($data['content']) . "\n";
                foreach ($data['content'] as $option) {
                    echo "      - " . ($option['libelle'] ?? 'N/A') . " : " . ($option['prixUnitaire'] ?? 'N/A') . "€\n";
                }
            }
        } else {
            echo "   ❌ isSucceed: " . ($data['isSucceed'] ?? 'FALSE') . "\n";
            echo "   message: " . ($data['message'] ?? 'N/A') . "\n";
            if (isset($data['errors'])) {
                echo "   errors: " . json_encode($data['errors']) . "\n";
            }
        }
    } else {
        echo "   ❌ Aucun produit valide trouvé\n";
    }
} else {
    echo "   ❌ Échec récupération produits\n";
    echo "   Réponse: " . json_encode($productsResult, JSON_PRETTY_PRINT) . "\n";
}

echo "\n=== FIN DU TEST ===\n";
