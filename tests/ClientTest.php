<?php

declare(strict_types=1);

namespace EasylinkIntegrator\Tests;

use EasylinkIntegrator\Client;
use EasylinkIntegrator\Exceptions\EasylinkException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private string $privateKey;
    private string $publicKey;

    protected function setUp(): void
    {
        // Dynamically generate a valid 2048-bit RSA Private Key for testing
        $res = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        $privateKey = '';
        openssl_pkey_export($res, $privateKey);
        $this->privateKey = $privateKey;

        $details = openssl_pkey_get_details($res);
        $this->publicKey = $details['key'];
    }

    /**
     * Test configuration validation.
     */
    public function testMissingConfigThrowsException(): void
    {
        $this->expectException(EasylinkException::class);
        $this->expectExceptionMessage("Missing required configuration parameter: 'appSecret'");

        new Client([
            'appId' => 'test-app-id',
            'appKey' => 'test-app-key',
            'privateKey' => $this->privateKey,
        ]);
    }

    /**
     * Test signature generation logic: sorting, sandwiching, signing.
     */
    public function testSignatureGeneration(): void
    {
        $client = new Client([
            'appId' => 'test-app-id',
            'appSecret' => 'test-app-secret',
            'appKey' => 'test-app-key',
            'privateKey' => $this->privateKey,
        ]);

        $nonce = 'test-nonce';
        $timestamp = '1690000000000';
        $body = [
            'amount' => 100000,
            'bankCode' => 'BCA',
        ];

        // Let's generate signature
        $signature = $client->generateSignature($nonce, $timestamp, $body);

        $this->assertNotEmpty($signature);
        $this->assertIsString($signature);
        
        // Base64 decoded signature must be 256 bytes for a 2048-bit RSA key
        $decoded = base64_decode($signature, true);
        $this->assertNotFalse($decoded);
        $this->assertEquals(256, strlen($decoded));
    }

    /**
     * Test token retrieval and caching.
     */
    public function testTokenRetrievalAndCaching(): void
    {
        // 1. Prepare Guzzle mock handler
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'accessToken' => 'token-123-abc',
                'expiresIn' => 600 // Valid for 10 minutes, past the 10-second expiration buffer
            ])),
            // Second response (should not be called if cached)
            new Response(200, [], json_encode([
                'accessToken' => 'token-should-not-reach',
                'expiresIn' => 600
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

        $client = new Client([
            'appId' => 'test-app-id',
            'appSecret' => 'test-app-secret',
            'appKey' => 'test-app-key',
            'privateKey' => $this->privateKey,
        ], $guzzleClient);

        // Fetch token first time - triggers API call
        $token1 = $client->getAccessToken();
        $this->assertEquals('token-123-abc', $token1);

        // Fetch token second time - should use cache
        $token2 = $client->getAccessToken();
        $this->assertEquals('token-123-abc', $token2);
        
        // Assert only one request was processed by Guzzle mock handler
        $this->assertEquals(1, count($mock));
    }

    /**
     * Test token request failure throwing custom Exception.
     */
    public function testTokenFailureThrowsException(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode([
                'error' => 'invalid_client',
                'message' => 'Unauthorized Client Credentials'
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

        $client = new Client([
            'appId' => 'test-app-id',
            'appSecret' => 'test-app-secret',
            'appKey' => 'test-app-key',
            'privateKey' => $this->privateKey,
        ], $guzzleClient);

        $this->expectException(EasylinkException::class);
        $this->expectExceptionMessage("Authentication failed");

        $client->getAccessToken();
    }

    /**
     * Test verifying notification signature.
     */
    public function testVerifyNotificationSignature(): void
    {
        $client = new Client([
            'appId' => 'test-app-id',
            'appSecret' => 'test-app-secret',
            'appKey' => 'test-app-key',
            'privateKey' => $this->privateKey,
        ]);

        $timestamp = '1690000000000';

        $params = [
            'X-EasyLink-AppKey' => 'test-app-key',
            'X-EasyLink-Timestamp' => $timestamp,
        ];
        ksort($params, SORT_STRING);
        $pairs = [];
        foreach ($params as $key => $val) {
            $pairs[] = "{$key}=" . urlencode($val);
        }
        $stringToSign = 'test-app-key' . implode('&', $pairs) . 'test-app-key';

        $signature = '';
        openssl_sign($stringToSign, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        $signBase64 = base64_encode($signature);

        $headers = [
            'X-EasyLink-AppKey' => 'test-app-key',
            'X-EasyLink-Timestamp' => $timestamp,
            'X-EasyLink-Sign' => $signBase64,
        ];

        $isValid = $client->verifyNotificationSignature($headers, $this->publicKey);
        $this->assertTrue($isValid);

        // Invalid signature test
        $headersInvalid = $headers;
        $headersInvalid['X-EasyLink-Sign'] = base64_encode('invalid-signature');
        $isValidInvalid = $client->verifyNotificationSignature($headersInvalid, $this->publicKey);
        $this->assertFalse($isValidInvalid);
    }
}
