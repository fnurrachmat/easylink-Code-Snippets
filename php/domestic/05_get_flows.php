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
    'start_time' => date('Y-m-d H:i:s', strtotime('-7 days')),
    'end_time'   => date('Y-m-d H:i:s')
];

echo "Fetching Cash Flows...\n";
try {
    $res = sendEasylinkRequest(
        $baseUrl,
        '/v2/transfer/get-flow',
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
