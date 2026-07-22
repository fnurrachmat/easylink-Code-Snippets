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
    'partner_reference_no' => 'INT-' . time(),
    'sender_country'       => 'ID',
    'receiver_country'     => 'SG',
    'source_currency'      => 'IDR',
    'target_currency'      => 'SGD',
    'amount'               => 1000000,
    'recipient_name'       => 'Alice Smith',
    'account_number'       => '9876543210',
    'bank_name'            => 'DBS Bank',
    'swift_code'           => 'DBSSSGSG'
];

echo "Creating International Transfer...\n";
try {
    $res = sendEasylinkRequest(
        $baseUrl,
        '/transfer/create-international-transfer',
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
