<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

function verifyEasylinkWebhookSignature(
    string $appKey,
    string $nonce,
    string $timestamp,
    array $body,
    string $signatureBase64,
    string $easylinkPublicKeyPem
): bool {
    $params = [
        'X-EasyLink-AppKey' => $appKey,
        'X-EasyLink-Nonce' => $nonce,
        'X-EasyLink-Timestamp' => $timestamp,
    ];

    foreach ($body as $key => $value) {
        if (is_array($value)) {
            $params[$key] = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $params[$key] = (string) $value;
        }
    }

    ksort($params, SORT_STRING);

    $pairs = [];
    foreach ($params as $key => $value) {
        $pairs[] = "{$key}=" . urlencode((string) $value);
    }
    $originalString = implode('&', $pairs);
    $stringToSign = $appKey . $originalString . $appKey;

    if (strpos($easylinkPublicKeyPem, '-----BEGIN') === false && file_exists($easylinkPublicKeyPem)) {
        $easylinkPublicKeyPem = file_get_contents($easylinkPublicKeyPem);
    }

    $rawSignature = base64_decode($signatureBase64);
    $result = openssl_verify($stringToSign, $rawSignature, $easylinkPublicKeyPem, OPENSSL_ALGO_SHA256);

    return $result === 1;
}

// --- Example Webhook Notification Handler ---
$appKey               = 'YOUR_APP_KEY';
$easylinkPublicKeyPem = '/path/to/easylink_public_key.pem';

$nonce           = $_SERVER['HTTP_X_EASYLINK_NONCE'] ?? 'dummy_nonce';
$timestamp       = $_SERVER['HTTP_X_EASYLINK_TIMESTAMP'] ?? '1700000000000';
$signatureBase64 = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
$body            = ['referenceNo' => 'REF-12345', 'status' => 'SUCCESS'];

echo "Verifying Webhook Notification Signature...\n";
$isValid = verifyEasylinkWebhookSignature(
    $appKey,
    $nonce,
    $timestamp,
    $body,
    $signatureBase64,
    $easylinkPublicKeyPem
);

if ($isValid) {
    echo "Webhook signature is VALID!\n";
} else {
    echo "Invalid Webhook signature!\n";
}
