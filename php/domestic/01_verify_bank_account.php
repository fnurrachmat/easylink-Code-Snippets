<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

// --- Configuration ---
$baseUrl       = 'https://sandbox.easylink.id';
$appId         = 'YOUR_APP_ID';
$appSecret     = 'YOUR_APP_SECRET';
$appKey        = 'YOUR_APP_KEY';
$privateKeyPem = '/path/to/private_key.pem';

// Auto-fetch Access Token
$accessToken   = getAccessToken($baseUrl, $appId, $appSecret);

$payload = [
    'account_number' => '1234567890',
    'bank_id'        => 'BCA'
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
