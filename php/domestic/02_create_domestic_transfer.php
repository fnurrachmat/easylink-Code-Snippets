<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

// --- Configuration ---
$baseUrl       = 'https://sandbox.easylink.id';
$appId         = 'lQNJ0nL07Ucmemaa';
$appSecret     = 'HrfFeuRmoyBsZhxDi3w3JNdxwYu19lL4';
$appKey        = '3f9a7f74-de23-4fde-af75-da7684528a59';
$privateKeyPem = __DIR__ . '/../../private_key.pem';

// Auto-fetch Access Token
$accessToken   = getAccessToken($baseUrl, $appId, $appSecret);

$payload = [
    'reference'           => 'REF-' . time(),
    'amount'              => 100000,
    'bank_id'             => '1', // 1 for BCA (Check supported-bank-code)
    'account_number'      => '1234567890',
    'account_holder_name' => 'John Doe',
    'remark'              => 'Payment for Order #1001'
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
