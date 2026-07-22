<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

// --- Configuration ---
$baseUrl       = 'https://sandbox.easylink.id';
$appKey        = 'YOUR_APP_KEY';
$privateKeyPem = '/path/to/private.pem';
$accessToken   = 'YOUR_ACCESS_TOKEN';

$payload = [
    'partnerReferenceNo' => 'REF-1700000000'
];

echo "Querying Domestic Transfer Status...\n";
try {
    $res = sendEasylinkRequest(
        $baseUrl,
        '/transfer/get-domestic-transfer',
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
