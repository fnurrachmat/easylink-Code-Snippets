<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

// --- Configuration ---
$baseUrl       = 'https://sandbox.easylink.id';
$appId         = 'lQNJ0nL07Ucmemaa';
$appSecret     = 'HrfFeuRmoyBsZhxDi3w3JNdxwYu19lL4';
$appKey        = '3f9a7f74-de23-4fde-af75-da7684528a59';
$privateKeyPem = '/path/to/private.pem';

// Auto-fetch Access Token (or assign string token manually: $accessToken = 'YOUR_ACCESS_TOKEN';)
$accessToken   = getAccessToken($baseUrl, $appId, $appSecret);

$payload = [
    'bankCode'      => 'BCA',
    'accountNumber' => '1234567890'
];

echo "Verifying Bank Account...\n";
try {
    $res = sendEasylinkRequest(
        $baseUrl,
        '/v2/transfer/verify-bank-account',
        'POST',
        $payload,
        $appKey,
        $privateKeyPem,
        $accessToken
    );
    echo "Response:\n" . json_encode($res['data'], JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
