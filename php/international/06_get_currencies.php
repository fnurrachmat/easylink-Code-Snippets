<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

$baseUrl       = 'https://sandbox.easylink.id';
$appKey        = 'YOUR_APP_KEY';
$privateKeyPem = '/path/to/private.pem';
$accessToken   = 'YOUR_ACCESS_TOKEN';

echo "Fetching List of Currencies...\n";
try {
    $res = sendEasylinkRequest(
        $baseUrl,
        '/data/list-currencies',
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
