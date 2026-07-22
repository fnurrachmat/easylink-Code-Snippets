<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

$baseUrl       = 'https://sandbox.easylink.id';
$appKey        = 'YOUR_APP_KEY';
$privateKeyPem = '/path/to/private.pem';
$accessToken   = 'YOUR_ACCESS_TOKEN';

echo "Fetching Remittance Purposes...\n";
try {
    $res = sendEasylinkRequest(
        $baseUrl,
        '/data/get-remittance-purposes',
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
