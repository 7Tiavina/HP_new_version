<?php

$login = "43559169";
$password = "testpassword_IjdI4ul8FCSXorHPWELNDkkNwzQ4UYwUlnd6sNcTyeH6O";
$baseUrl = "https://api.gateway.monetico-retail.com/api-payment/V4";
$transactionId = "c0e2ddb63ca44387bf5b215006d02a4d"; 

echo "Testing Capture URL existence for: $transactionId\n";

$url = $baseUrl . "/Charge/$transactionId/Capture";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
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
