<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

$baseUrl       = 'https://sandbox.easylink.id';
$appKey        = 'YOUR_APP_KEY';
$privateKeyPem = '/path/to/private.pem';
$accessToken   = 'YOUR_ACCESS_TOKEN';

echo "Fetching Supported E-Wallet Codes...\n";
try {
    $res = sendEasylinkRequest(
        $baseUrl,
        '/v2/data/supported-inst-code',
        'POST',
        [],
        $appKey,
        $privateKeyPem,
        $accessToken
    );
    echo "Response:\n" . json_encode($res['data'], JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
