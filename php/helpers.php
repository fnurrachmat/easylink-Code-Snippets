<?php

declare(strict_types=1);

/**
 * Generates Easylink RSA-SHA256 signature.
 *
 * @param string $appKey Merchant App Key
 * @param string $nonce Unique request nonce
 * @param string $timestamp Millisecond timestamp
 * @param array $body Request body parameters
 * @param string $privateKeyPem RSA Private Key content (PEM format or file path)
 * @return string Base64 encoded signature
 */
function generateEasylinkSignature(
    string $appKey,
    string $nonce,
    string $timestamp,
    array $body,
    string $privateKeyPem
): string {
    // 1. Gather all header parameters and body parameters
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

    // 2. Sort parameters alphabetically by key (ASCII order)
    ksort($params, SORT_STRING);

    // 3. Concatenate key=value joined by & with urlencode
    $pairs = [];
    foreach ($params as $key => $value) {
        $pairs[] = "{$key}=" . urlencode((string) $value);
    }
    $originalString = implode('&', $pairs);

    // 4. Sandwich with appKey at start and end
    $stringToSign = $appKey . $originalString . $appKey;

    // 5. Read private key if path is provided
    if (strpos($privateKeyPem, '-----BEGIN') === false && file_exists($privateKeyPem)) {
        $privateKeyPem = file_get_contents($privateKeyPem);
    }

    // 6. Sign using RSA-SHA256
    $signature = '';
    $success = openssl_sign($stringToSign, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);
    if (!$success) {
        throw new Exception("OpenSSL failed to sign data. Please verify your private key.");
    }

    return base64_encode($signature);
}

/**
 * Sends a native cURL HTTP request to Easylink API.
 */
function sendEasylinkRequest(
    string $baseUrl,
    string $endpoint,
    string $method,
    array $payload,
    string $appKey,
    string $privateKeyPem,
    ?string $accessToken = null
): array {
    $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');
    $timestamp = (string) round(microtime(true) * 1000);
    $nonce = bin2hex(random_bytes(16));
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    if ($accessToken) {
        $headers[] = "Authorization: Bearer {$accessToken}";
        $signature = generateEasylinkSignature($appKey, $nonce, $timestamp, $payload, $privateKeyPem);
        $headers[] = "X-EasyLink-AppKey: {$appKey}";
        $headers[] = "X-EasyLink-Nonce: {$nonce}";
        $headers[] = "X-EasyLink-Timestamp: {$timestamp}";
        $headers[] = "X-Signature: {$signature}";
        $headers[] = "X-EasyLink-Sign: {$signature}";
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if (!empty($payload) && strtoupper($method) !== 'GET') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error) {
        throw new Exception("cURL Error: {$error}");
    }

    $decoded = json_decode($response, true);
    return [
        'status_code' => $httpCode,
        'data' => $decoded ?? $response
    ];
}
