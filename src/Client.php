<?php

declare(strict_types=1);

namespace EasylinkIntegrator;

use EasylinkIntegrator\Exceptions\EasylinkException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Throwable;

/**
 * Class Client
 *
 * The core client class for Easylink Integrator SDK.
 * Handles configuration, token lifecycle management, request signing, and API communication.
 */
class Client
{
    private string $appId;
    private string $appSecret;
    private string $appKey;
    private string $privateKey;
    private string $environment;
    private string $baseUri;
    private string $tokenEndpoint;
    private GuzzleClient $httpClient;

    // Runtime token cache
    private ?string $accessToken = null;
    private int $tokenExpiresAt = 0;

    /**
     * Client constructor.
     *
     * @param array $config Configuration array containing:
     *                      - appId: (string) Merchant App ID
     *                      - appSecret: (string) Merchant App Secret
     *                      - appKey: (string) Merchant App Key
     *                      - privateKey: (string) Merchant RSA Private Key (PEM content or file path)
     *                      - environment: (string) 'sandbox' or 'production' (default: 'sandbox')
     *                      - baseUri: (string) Optional custom base URI
     *                      - tokenEndpoint: (string) Optional custom token endpoint path (default: '/api/v1/auth/token')
     * @param GuzzleClient|null $httpClient Optional pre-configured Guzzle HTTP client
     *
     * @throws EasylinkException If any required config parameter is missing
     */
    public function __construct(array $config, ?GuzzleClient $httpClient = null)
    {
        $this->validateConfig($config);

        $this->appId = $config['appId'];
        $this->appSecret = $config['appSecret'];
        $this->appKey = $config['appKey'];
        $this->privateKey = $config['privateKey'];
        $this->environment = $config['environment'] ?? 'sandbox';
        
        $this->baseUri = $config['baseUri'] ?? (
            $this->environment === 'production' 
                ? 'https://openapi.easylink.id' 
                : 'https://sandbox.easylink.id'
        );
        
        $this->tokenEndpoint = $config['tokenEndpoint'] ?? '/get-access-token';

        $this->httpClient = $httpClient ?? new GuzzleClient([
            'base_uri' => $this->baseUri,
            'timeout'  => 30.0,
        ]);
    }

    /**
     * Validates configuration parameters.
     *
     * @param array $config
     * @throws EasylinkException
     */
    private function validateConfig(array $config): void
    {
        $requiredKeys = ['appId', 'appSecret', 'appKey', 'privateKey'];
        foreach ($requiredKeys as $key) {
            if (empty($config[$key])) {
                throw new EasylinkException("Missing required configuration parameter: '{$key}'");
            }
        }
    }

    /**
     * Get base URI.
     *
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * Get environment.
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Refreshes or retrieves a cached access token.
     *
     * @return string
     * @throws EasylinkException
     */
    public function getAccessToken(): string
    {
        // Check if token is cached and not expired (using a 10-second buffer)
        if ($this->accessToken !== null && time() < ($this->tokenExpiresAt - 10)) {
            return $this->accessToken;
        }

        try {
            // Flexible request payload to accommodate different server parameter naming variations
            $response = $this->httpClient->post($this->tokenEndpoint, [
                'json' => [
                    'appId' => $this->appId,
                    'appSecret' => $this->appSecret,
                    'app_id' => $this->appId,
                    'app_secret' => $this->appSecret,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            
            // Check for common token return keys (accessToken or access_token or token)
            $token = null;
            if (isset($body['data']) && is_string($body['data'])) {
                $token = $body['data'];
            } else {
                $token = $body['accessToken'] 
                    ?? $body['access_token'] 
                    ?? $body['data']['accessToken'] 
                    ?? $body['data']['access_token'] 
                    ?? null;
            }

            if (empty($token)) {
                throw new EasylinkException(
                    "Token endpoint did not return an access token in the response.",
                    $response->getStatusCode(),
                    $body,
                    $response->getStatusCode()
                );
            }

            // Expiry duration, default to 10 minutes (600 seconds) if not returned
            $expiresIn = $body['expiresIn'] 
                ?? $body['expires_in'] 
                ?? $body['data']['expiresIn'] 
                ?? $body['data']['expires_in'] 
                ?? 600;

            $this->accessToken = (string) $token;
            $this->tokenExpiresAt = time() + (int) $expiresIn;

            return $this->accessToken;
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 500;
            $bodyContent = $response ? json_decode($response->getBody()->getContents(), true) : null;
            
            throw new EasylinkException(
                "Authentication failed: " . $e->getMessage(),
                $e->getCode(),
                $bodyContent,
                $statusCode,
                $e
            );
        } catch (Throwable $e) {
            if ($e instanceof EasylinkException) {
                throw $e;
            }
            throw new EasylinkException("Failed to fetch access token: " . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Generates a digital signature for the request.
     *
     * @param string $nonce A unique request ID
     * @param string $timestamp Millisecond-level timestamp
     * @param array $body Request body array
     * @return string Base64 encoded signature
     * @throws EasylinkException
     */
    public function generateSignature(string $nonce, string $timestamp, array $body = []): string
    {
        try {
            // 1. Gather all common header parameters and business body parameters
            $params = [
                'X-EasyLink-AppKey' => $this->appKey,
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

            // 2. Sort all parameters alphabetically by the ASCII value of their key
            ksort($params, SORT_STRING);

            // 3. Concatenate key=value joined by &
            $pairs = [];
            foreach ($params as $key => $value) {
                $pairs[] = "{$key}=" . urlencode((string) $value);
            }
            $originalString = implode('&', $pairs);

            // 4. Sandwich the original string with the AppKey at the beginning and the end
            $stringToSign = $this->appKey . $originalString . $this->appKey;

            // 5. Sign the string using SHA-256 and the RSA private key
            $privateKeyPem = $this->getPrivateKeyPem();
            
            $signature = '';
            $success = openssl_sign($stringToSign, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);
            
            if (!$success) {
                throw new EasylinkException("OpenSSL failed to sign the string. Please verify your private key format.");
            }

            // 6. Base64 encode the resulting signature
            return base64_encode($signature);
        } catch (Throwable $e) {
            // Security: Ensure private key is NEVER exposed in the exception
            if ($e instanceof EasylinkException) {
                throw $e;
            }
            throw new EasylinkException("Signature generation failed: " . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Resolves and validates the RSA Private Key to its PEM format string.
     *
     * @return string PEM formatted private key content
     * @throws EasylinkException
     */
    private function getPrivateKeyPem(): string
    {
        if (strpos($this->privateKey, '-----BEGIN') !== false) {
            return $this->privateKey;
        }

        if (file_exists($this->privateKey)) {
            $content = file_get_contents($this->privateKey);
            if ($content === false) {
                throw new EasylinkException("Unable to read private key file.");
            }
            return $content;
        }

        throw new EasylinkException("Invalid private key. Must be a PEM formatted string or a valid path to a private key file.");
    }

    /**
     * Sends an authenticated and signed HTTP request to Easylink API.
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.)
     * @param string $endpoint The endpoint path relative to the base URI
     * @param array $payload The payload body (automatically sent as JSON for non-GET requests)
     * @return array Decoded response body
     * @throws EasylinkException
     */
    public function request(string $method, string $endpoint, array $payload = []): array
    {
        $method = strtoupper($method);
        
        // 1. Retrieve valid access token
        $token = $this->getAccessToken();

        // 2. Prepare headers with timestamp and nonce
        $timestamp = (string) round(microtime(true) * 1000);
        $nonce = bin2hex(random_bytes(16));

        // 3. Generate request signature
        $signature = $this->generateSignature($nonce, $timestamp, $payload);

        $headers = [
            'Authorization'        => "Bearer {$token}",
            'Content-Type'         => 'application/json',
            'Accept'               => 'application/json',
            'X-EasyLink-AppKey'    => $this->appKey,
            'X-EasyLink-Nonce'     => $nonce,
            'X-EasyLink-Timestamp' => $timestamp,
            'X-EasyLink-Sign'      => $signature,
            'X-Signature'          => $signature, // For PRD X-Signature header compatibility
        ];

        $options = [
            'headers' => $headers,
        ];

        if ($method === 'GET') {
            if (!empty($payload)) {
                $options['query'] = $payload;
            }
        } else {
            if (!empty($payload)) {
                $options['json'] = $payload;
            }
        }

        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            $responseBody = $response->getBody()->getContents();
            
            return json_decode($responseBody, true) ?? [];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 500;
            $bodyContent = $response ? json_decode($response->getBody()->getContents(), true) : null;
            
            throw new EasylinkException(
                "API Request Failed [{$method} {$endpoint}]: " . $e->getMessage(),
                $e->getCode(),
                $bodyContent,
                $statusCode,
                $e
            );
        } catch (GuzzleException $e) {
            throw new EasylinkException(
                "Network communication failed: " . $e->getMessage(),
                0,
                null,
                null,
                $e
            );
        } catch (Throwable $e) {
            throw new EasylinkException(
                "An unexpected error occurred during the request: " . $e->getMessage(),
                0,
                null,
                null,
                $e
            );
        }
    }

    /**
     * Verifies the signature of an incoming webhook/notification from Easylink.
     *
     * @param array $headers Incoming headers (typically from $_SERVER or getallheaders())
     * @param string $easylinkPublicKey PEM formatted Easylink public key
     * @return bool True if signature is valid, false otherwise
     */
    public function verifyNotificationSignature(array $headers, string $easylinkPublicKey): bool
    {
        // Normalize headers to case-insensitive lookup
        $normalized = [];
        foreach ($headers as $key => $val) {
            $normalized[strtolower($key)] = is_array($val) ? $val[0] : $val;
        }

        $appKey = $normalized['x-easylink-appkey'] ?? null;
        $timestamp = $normalized['x-easylink-timestamp'] ?? null;
        $signBase64 = $normalized['x-easylink-sign'] ?? null;

        if (!$appKey || !$timestamp || !$signBase64) {
            return false;
        }

        // Reconstruct the signed string
        // The parameters signed for notification are X-EasyLink-AppKey and X-EasyLink-Timestamp
        $params = [
            'X-EasyLink-AppKey' => $appKey,
            'X-EasyLink-Timestamp' => $timestamp,
        ];

        ksort($params, SORT_STRING);

        $pairs = [];
        foreach ($params as $key => $value) {
            $pairs[] = "{$key}={$value}";
        }
        $originalString = implode('&', $pairs);
        $stringToVerify = $this->appKey . $originalString . $this->appKey;

        $signature = base64_decode($signBase64);
        if ($signature === false) {
            return false;
        }

        $pubKeyResource = openssl_pkey_get_public($easylinkPublicKey);
        if (!$pubKeyResource) {
            return false;
        }

        $result = openssl_verify($stringToVerify, $signature, $pubKeyResource, OPENSSL_ALGO_SHA256);
        
        // Clean up public key resource if PHP version is < 8.0
        if (PHP_VERSION_ID < 80000 && is_resource($pubKeyResource)) {
            openssl_free_key($pubKeyResource);
        }

        return $result === 1;
    }
}
