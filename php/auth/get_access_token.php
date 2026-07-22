<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers.php';

// --- Configuration ---
$baseUrl   = 'https://sandbox.easylink.id'; // Use 'https://openapi.easylink.id' for Production
$appId     = 'YOUR_APP_ID';
$appSecret = 'YOUR_APP_SECRET';

/**
 * Request Access Token
 */
function getAccessToken(string $baseUrl, string $appId, string $appSecret): string
{
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
    
    // Check for string token in data['data'] or object keys
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

// --- Execution ---
try {
    echo "Requesting Access Token...\n";
    $token = getAccessToken($baseUrl, $appId, $appSecret);
    echo "Access Token retrieved successfully:\n{$token}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
