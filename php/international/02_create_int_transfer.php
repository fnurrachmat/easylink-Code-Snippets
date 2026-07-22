<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

$baseUrl       = 'https://sandbox.easylink.id';
$appKey        = 'YOUR_APP_KEY';
$privateKeyPem = '/path/to/private.pem';
$accessToken   = 'YOUR_ACCESS_TOKEN';

$payload = [
    'partnerReferenceNo' => 'INT-' . time(),
    'senderCountry'      => 'ID',
    'receiverCountry'    => 'SG',
    'sourceCurrency'     => 'IDR',
    'targetCurrency'     => 'SGD',
    'amount'             => 1000000,
    'recipientName'      => 'Alice Smith',
    'accountNumber'      => '9876543210',
    'bankName'           => 'DBS Bank',
    'swiftCode'          => 'DBSSSGSG'
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
