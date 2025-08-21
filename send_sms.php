<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

$username = "JOJO-Hotels"; // Live Africa's Talking username
$apiKey = "atsk_848e44cf48e0af69b20bffaf3c38d9283ec3a26c4fcfd1bcffaae4622a7ed6c553a8affc"; // Live API Key

// 📞 Driver's number (real one for demo or production)
$driverPhone = "+233501234567"; // <-- Replace with actual driver's number

// 📩 Dynamic message example
$message = "New pickup request received. Please contact the guest and confirm pickup details. - JOJO Hotels";

$client = new Client([
    'base_uri' => 'https://api.africastalking.com/',
    'headers' => [
        'apiKey' => $apiKey,
        'Accept' => 'application/json',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ]
]);

try {
    $response = $client->post('version1/messaging', [
        'form_params' => [
            'username' => $username,
            'to' => $driverPhone,
            'message' => $message
        ]
    ]);

    $body = $response->getBody()->getContents();
    echo "✅ SMS sent to driver successfully!<br>";
    echo "<pre>$body</pre>";
} catch (Exception $e) {
    echo "❌ Error sending to driver: " . $e->getMessage();
}
