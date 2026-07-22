<?php

declare(strict_types=1);

/**
 * Requests a fresh B2B Access Token from Easylink API.
 *
 * @param string $baseUrl Base API URL
 * @param string $appId Merchant App ID
 * @param string $appSecret Merchant App Secret
 * @return string Access Token JWT
 */
function getAccessToken(string $baseUrl, string $appId, string $appSecret): string {
    $response = sendEasylinkRequest(
        $baseUrl,
        '/get-access-token',
        'POST',
        [
            'app_id'     => $appId,
            'app_secret' => $appSecret,
        ],
        '',
        ''
    );

    if ($response['status_code'] !== 200) {
        throw new Exception("Failed to get token: " . json_encode($response));
    }

    $data = $response['data'];
    if (isset($data['data']) && is_string($data['data'])) {
        $token = $data['data'];
    } else {
        $token = $data['accessToken'] 
            ?? $data['access_token'] 
            ?? $data['data']['accessToken'] 
            ?? $data['data']['access_token'] 
            ?? null;
    }

    if (!$token) {
        throw new Exception("Access token not found in response: " . json_encode($data));
    }

    return (string) $token;
}

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

    // Validate and load Private Key
    if (strpos($privateKeyPem, '-----BEGIN') === false) {
        if (file_exists($privateKeyPem)) {
            $keyContent = file_get_contents($privateKeyPem);
            if ($keyContent === false) {
                throw new Exception("Unable to read private key file at path: {$privateKeyPem}");
            }
            $privateKeyPem = $keyContent;
        } else {
            throw new Exception("Private key file not found at: '{$privateKeyPem}'. Please set \$privateKeyPem to a valid .pem file path or PEM key string.");
        }
    }

    $signature = '';
    $success = @openssl_sign($stringToSign, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);
    if (!$success) {
        throw new Exception("OpenSSL failed to sign data. Please verify your RSA private key content or file format.");
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
