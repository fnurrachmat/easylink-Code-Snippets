<?php

declare(strict_types=1);

namespace EasylinkIntegrator\Modules;

use EasylinkIntegrator\Client;
use EasylinkIntegrator\Exceptions\EasylinkException;

/**
 * Class PayoutInternational
 *
 * Handles international money transfers and quotes.
 */
class PayoutInternational
{
    private Client $client;

    /**
     * PayoutInternational constructor.
     *
     * @param Client $client The core client instance
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Gets a payout exchange rate quote.
     *
     * @param array $payload Payload containing quote details (e.g., sourceCurrency, targetCurrency, sourceAmount)
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getQuote(array $payload): array
    {
        return $this->client->request('POST', '/quotes/get-quotes', $payload);
    }

    /**
     * Creates an international transfer transaction.
     *
     * @param array $payload Payload containing recipient and bank details for international money transfer
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function createTransfer(array $payload): array
    {
        return $this->client->request('POST', '/transfer/create-international-transfer', $payload);
    }

    /**
     * Confirms an international transfer transaction.
     * Must be called within 5 minutes of creating the transfer.
     *
     * @param array $payload Payload containing transaction reference (e.g., reference)
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function confirmTransfer(array $payload): array
    {
        return $this->client->request('POST', '/transfer/confirm-international-transfer', $payload);
    }

    /**
     * Retrieves the status or details of an international transfer.
     *
     * @param array $payload Payload containing query details (e.g., reference)
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getInternationalTransfer(array $payload): array
    {
        return $this->client->request('POST', '/transfer/get-international-transfer', $payload);
    }

    /**
     * Lists supported country and currency combinations.
     *
     * @param array $payload Optional query details
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getCountriesCurrencies(array $payload = []): array
    {
        return $this->client->request('POST', '/data/countries-currencies', $payload);
    }

    /**
     * Lists available currencies and their minimum unit details.
     *
     * @param array $payload Optional query details
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getCurrencies(array $payload = []): array
    {
        return $this->client->request('POST', '/data/list-currencies', $payload);
    }

    /**
     * Lists all supported remittance purpose options and their codes.
     *
     * @param array $payload Optional query details
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getRemittancePurposes(array $payload = []): array
    {
        return $this->client->request('POST', '/data/get-remittance-purposes', $payload);
    }

    /**
     * Lists all source of funds options and their codes.
     *
     * @param array $payload Optional query details
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getSourcesOfFunds(array $payload = []): array
    {
        return $this->client->request('POST', '/data/get-sources-of-funds', $payload);
    }

    /**
     * Lists all relationship options and their codes.
     *
     * @param array $payload Optional query details
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getRelationships(array $payload = []): array
    {
        return $this->client->request('POST', '/data/get-relationships', $payload);
    }
}

