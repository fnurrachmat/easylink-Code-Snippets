<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

/**
 * Verifies Easylink Webhook RSA-SHA256 Signature.
 *
 * @param string $appKey Merchant App Key
 * @param string $nonce Request nonce from X-EasyLink-Nonce header
 * @param string $timestamp Request timestamp from X-EasyLink-Timestamp header
 * @param array $body Webhook JSON body payload
 * @param string $signatureBase64 Signature string from X-Signature or X-EasyLink-Sign header
 * @param string $easylinkPublicKeyPem Easylink RSA Public Key (PEM format string or file path)
 * @return bool True if valid signature, false otherwise
 */
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
            foreach ($value as $k2 => $v2) {
                if (!is_array($v2)) {
                    $params["{$key}.{$k2}"] = (string) $v2;
                }
            }
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

    if (strpos($easylinkPublicKeyPem, '-----BEGIN') === false) {
        if (file_exists($easylinkPublicKeyPem)) {
            $keyContent = file_get_contents($easylinkPublicKeyPem);
            if ($keyContent === false) {
                throw new Exception("Unable to read public key file at path: {$easylinkPublicKeyPem}");
            }
            $easylinkPublicKeyPem = $keyContent;
        } else {
            throw new Exception("Public key file not found at: '{$easylinkPublicKeyPem}'. Please set \$easylinkPublicKeyPem to a valid .pem file path or PEM key string.");
        }
    }

    $rawSignature = base64_decode($signatureBase64);
    $result = openssl_verify($stringToSign, $rawSignature, $easylinkPublicKeyPem, OPENSSL_ALGO_SHA256);

    return $result === 1;
}

// --- Example Webhook Notification Handler & Demo Execution ---
$appKey        = '3f9a7f74-de23-4fde-af75-da7684528a59';
$privateKeyPem = __DIR__ . '/../../private_key.pem';

// Auto-extract Public Key from private_key.pem for standalone demo running
if (file_exists($privateKeyPem)) {
    $privateKeyContent = file_get_contents($privateKeyPem);
    $pkeyRes = openssl_pkey_get_private($privateKeyContent);
    if ($pkeyRes !== false) {
        $details = openssl_pkey_get_details($pkeyRes);
        $easylinkPublicKeyPem = $details['key'];
    } else {
        $easylinkPublicKeyPem = '/path/to/easylink_public_key.pem';
    }
} else {
    $easylinkPublicKeyPem = '/path/to/easylink_public_key.pem';
}

$nonce     = 'dummy_nonce_12345';
$timestamp = (string) round(microtime(true) * 1000);
$body      = ['referenceNo' => 'REF-12345', 'status' => 'SUCCESS'];

// Generate matching RSA-SHA256 signature using private key for testing
$signatureBase64 = generateEasylinkSignature(
    $appKey,
    $nonce,
    $timestamp,
    $body,
    $privateKeyPem
);

echo "Verifying Webhook Notification Signature...\n";
try {
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
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
