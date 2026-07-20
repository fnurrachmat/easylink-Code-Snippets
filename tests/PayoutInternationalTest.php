<?php

declare(strict_types=1);

namespace EasylinkIntegrator\Tests;

use EasylinkIntegrator\Client;
use EasylinkIntegrator\Modules\PayoutInternational;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class PayoutInternationalTest extends TestCase
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

    private function createPayoutInternational(array $mockResponses): PayoutInternational
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

        return new PayoutInternational($client);
    }

    public function testGetQuote(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => [
                'source_currency' => 'IDR',
                'destination_currency' => 'CNH',
                'rate' => 28056.8299
            ]
        ];

        $payout = $this->createPayoutInternational([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getQuote([
            'source_currency'      => 'IDR',
            'destination_currency' => 'CNH',
            'source_amount'        => '10000000',
        ]);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testCreateTransfer(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => [
                'merchant_id' => 1001118,
                'reference' => 'INT_TX_123',
                'disbursement_id' => '202607202200620000223245',
            ]
        ];

        $payout = $this->createPayoutInternational([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->createTransfer([
            'transaction' => [
                'destination_country'  => 'CHN',
                'destination_currency' => 'CNH',
                'destination_amount'   => 466.98,
            ],
            'source' => [
                'segment' => 'business',
                'address_country' => 'IDN',
                'company_name' => 'Sender Co',
            ],
            'destination' => [
                'segment' => 'individual',
                'beneficiary_account_type' => 'Bank Account',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'bank_name' => 'ICBC',
                'bank_account_number' => '123456',
            ],
            'reference' => 'INT_TX_123',
        ]);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testConfirmTransfer(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => 'Success'
        ];

        $payout = $this->createPayoutInternational([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->confirmTransfer([
            'reference' => 'INT_TX_123'
        ]);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetInternationalTransfer(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => [
                'disbursement_id' => '202607202200620000223245',
                'reference' => 'INT_TX_123',
                'remittance_type' => 'international',
                'state' => 7
            ]
        ];

        $payout = $this->createPayoutInternational([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getInternationalTransfer([
            'reference' => 'INT_TX_123'
        ]);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetCountriesCurrencies(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => [['country' => 'CHN', 'currency' => 'CNH']]
        ];

        $payout = $this->createPayoutInternational([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getCountriesCurrencies();
        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetCurrencies(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => [['currency' => 'USD', 'min_amount' => 1]]
        ];

        $payout = $this->createPayoutInternational([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getCurrencies();
        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetRemittancePurposes(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => [['purpose_code' => '008-01', 'purpose_name' => 'Trade Settlement']]
        ];

        $payout = $this->createPayoutInternational([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getRemittancePurposes();
        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetSourcesOfFunds(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => [['source_code' => '01', 'source_name' => 'Bank Deposit']]
        ];

        $payout = $this->createPayoutInternational([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getSourcesOfFunds();
        $this->assertEquals($expectedResponse, $response);
    }

    public function testGetRelationships(): void
    {
        $expectedResponse = [
            'code' => 0,
            'message' => '',
            'data' => [['relation_code' => '04', 'relation_name' => 'Business']]
        ];

        $payout = $this->createPayoutInternational([
            new Response(200, [], json_encode($expectedResponse))
        ]);

        $response = $payout->getRelationships();
        $this->assertEquals($expectedResponse, $response);
    }
}
