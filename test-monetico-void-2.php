<?php

$login = "43559169";
$password = "testpassword_IjdI4ul8FCSXorHPWELNDkkNwzQ4UYwUlnd6sNcTyeH6O";
$baseUrl = "https://api.gateway.monetico-retail.com/api-payment/V4";

// Identifiant de transaction à tester (récupéré de tes logs récents)
$transactionId = "c0e2ddb63ca44387bf5b215006d02a4d"; 

echo "Testing Monetico Void (Correction uuid) with Transaction ID: $transactionId\n";

$endpoints = [
    "/Transaction/CancelOrRefund", // Avec body {"uuid": "$transactionId"}
];

foreach ($endpoints as $endpoint) {
    echo "--- Testing Endpoint: $endpoint ---\n";
    
    $url = $baseUrl . $endpoint;
    $payload = ['uuid' => $transactionId];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($login . ':' . $password),
        'Content-Type: application/json',
        'Accept: application/json',
    ]);
    
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status: $status\n";
    echo "Body: $response\n";
    
    $json = json_decode($response, true);
    if (isset($json['status']) && $json['status'] === 'SUCCESS') {
        echo ">>> SUCCESS! This is the correct endpoint.\n";
    } else {
        $errorCode = $json['answer']['errorCode'] ?? ($json['status'] ?? 'Unknown');
        echo ">>> FAILED: $errorCode\n";
    }
}
