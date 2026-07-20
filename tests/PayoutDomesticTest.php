<?php

declare(strict_types=1);

namespace EasylinkIntegrator\Tests;

use EasylinkIntegrator\Client;
use EasylinkIntegrator\Modules\PayoutDomestic;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class PayoutDomesticTest extends TestCase
{
    private string $privateKey;

    protected function setUp(): void
    {
        $res = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        $privateKey = '';
        openssl_pkey_export($res, $privateKey);
        $this->privateKey = $privateKey;
    }

    private function createPayoutDomestic(array $mockResponses): PayoutDomestic
    {
        // Prep mock Guzzle responses
        $mock = new MockHandler(array_merge([
            // Access Token Response
            new Response(200, [], json_encode([
                'data' => 'mock-jwt-token',
                'expiresIn' => 600
            ]))
        ], $mockResponses));

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

        $client = new Client([
            'appId' => 'test-app-id',
            'appSecret' => 'test-app-secret',
            'appKey' => 'test-app-key',
            'privateKey' => $this->privateKey,
        ], $guzzleClient);

        return new PayoutDomestic($client);
    }

    public function testGetBalances(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => ['balance' => 999999.0]
        ];

        $payout = $this->createPayoutDomestic([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getBalances();
        $this->assertEquals($expectedResponse, $response);
    }

    public function testListAllBalances(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => ['balance' => 999999.0]
        ];

        $payout = $this->createPayoutDomestic([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->listAllBalances();
        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetFlows(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => ['list' => []]
        ];

        $payout = $this->createPayoutDomestic([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getFlows(['count' => 5]);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetDomesticTransfer(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => ['disbursement_id' => '123']
        ];

        $payout = $this->createPayoutDomestic([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getDomesticTransfer(['reference' => 'ref-123']);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetRemittanceList(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => ['list' => []]
        ];

        $payout = $this->createPayoutDomestic([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getRemittanceList();
        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetSupportedBanks(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => [['bank_id' => 1, 'bank_name' => 'BRI']]
        ];

        $payout = $this->createPayoutDomestic([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getSupportedBanks();
        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetSupportedEwallets(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => [['bank_id' => 164, 'bank_name' => 'OVO']]
        ];

        $payout = $this->createPayoutDomestic([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getSupportedEwallets();
        $this->assertEquals($expectedResponse, $response);
    }
}
