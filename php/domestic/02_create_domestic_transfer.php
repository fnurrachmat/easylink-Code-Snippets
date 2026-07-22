<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

// --- Configuration ---
$baseUrl       = 'https://sandbox.easylink.id';
$appKey        = 'YOUR_APP_KEY';
$privateKeyPem = '/path/to/private.pem';
$accessToken   = 'YOUR_ACCESS_TOKEN';

$payload = [
    'partnerReferenceNo' => 'REF-' . time(),
    'amount'             => 100000,
    'bankCode'           => 'BCA',
    'accountNumber'      => '1234567890',
    'recipientName'      => 'John Doe',
    'remark'             => 'Payment for Order #1001'
];

echo "Creating Domestic Transfer...\n";
try {
    $res = sendEasylinkRequest(
        $baseUrl,
        '/v2/transfer/create-domestic-transfer',
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
