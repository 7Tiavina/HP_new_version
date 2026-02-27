<?php
/**
 * Test API BDM avec cURL - Version mise à jour
 */

$baseUrl = 'https://recette-erp.bagagesdumonde.com';
$username = 'hellopassenger@bdm.com';
$password = 'f$RkP%x86M';

echo "=== TEST API BDM ===\n\n";
echo "URL: $baseUrl\n";
echo "Username: $username\n";
echo "Password: " . str_repeat('*', strlen($password)) . "\n\n";

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

echo "   HTTP Code: $httpCode\n";
echo "   Réponse: $response\n\n";

$json = json_decode($response, true);

if (isset($json['isSucceed']) && $json['isSucceed']) {
    echo "✅ Authentification RÉUSSIE !\n";
    $token = $json['data']['accessToken'] ?? null;
    
    if ($token) {
        echo "   Token: " . substr($token, 0, 30) . "...\n\n";
        
        // Étape 2: Tester les plateformes
        echo "2. Récupération des plateformes...\n";
        $ch = curl_init($baseUrl . '/api/plateforme');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "   HTTP Code: $httpCode\n";
        
        if ($httpCode == 200) {
            $data = json_decode($response, true);
            $count = is_array($data['content'] ?? $data['data'] ?? []) ? count($data['content'] ?? $data['data'] ?? []) : 0;
            echo "   Plateformes trouvées: $count\n\n";
            echo "✅ API BDM fonctionnelle !\n";
        } else {
            echo "   Réponse: $response\n";
            echo "❌ Échec de la récupération des plateformes\n";
        }
    }
} else {
    echo "❌ Authentification ÉCHOUÉE\n";
    echo "   Message: " . ($json['message'] ?? json_encode($json['messages'] ?? 'Inconnu')) . "\n";
    echo "   isSucceed: " . ($json['isSucceed'] ?? 'non défini') . "\n";
}

echo "\n=== FIN DU TEST ===\n";
