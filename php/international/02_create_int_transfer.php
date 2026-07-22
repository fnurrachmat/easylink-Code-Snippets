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
    'reference' => 'INT-' . time(),
    'source' => [
        'country'                     => 'IDN',
        'currency'                    => 'IDR',
        'segment'                     => 'business',
        'company_name'                => 'PT Merchant Indonesia',
        'company_trading_name'        => 'Merchant ID',
        'company_registration_number' => '123456789',
        'company_registration_country'=> 'IDN',
        'address_line'                => 'Jl. Sudirman No. 1',
        'address_city'                => 'Jakarta',
        'address_country'             => 'IDN'
    ],
    'destination' => [
        'country'                  => 'SGP',
        'currency'                 => 'SGD',
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'DBS Bank Corporate',
        'swift_code'               => 'DBSSSGSG',
        'bank_account_number'      => '9876543210',
        'address_line'             => '12 Marina Boulevard',
        'address_city'             => 'Singapore',
        'address_country'          => 'SGP',
        'source_of_income_code'    => '01',
        'purpose_code'             => '01',
        'relation_code'            => '04'
    ],
    'transaction' => [
        'amount'               => 1000000,
        'destination_amount'   => 35,
        'destination_country'  => 'SGP',
        'destination_currency' => 'SGD'
    ]
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
