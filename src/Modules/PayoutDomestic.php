<?php

declare(strict_types=1);

namespace EasylinkIntegrator\Modules;

use EasylinkIntegrator\Client;
use EasylinkIntegrator\Exceptions\EasylinkException;

/**
 * Class PayoutDomestic
 *
 * Handles domestic transfer transactions and bank account verification.
 */
class PayoutDomestic
{
    private Client $client;

    /**
     * PayoutDomestic constructor.
     *
     * @param Client $client The core client instance
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Verifies a bank account before executing a transfer.
     *
     * @param array $payload Payload data containing account details (e.g., bankCode, accountNumber)
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function verifyBankAccount(array $payload): array
    {
        return $this->client->request('POST', '/v2/transfer/verify-bank-account', $payload);
    }

    /**
     * Creates a domestic transfer transaction.
     *
     * @param array $payload Payload containing transfer details (e.g., amount, accountNumber, bankCode, recipientName)
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function createTransfer(array $payload): array
    {
        return $this->client->request('POST', '/v2/transfer/create-domestic-transfer', $payload);
    }

    /**
     * Lists all balances of the account.
     *
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getBalances(): array
    {
        return $this->client->request('POST', '/finance-account/balances');
    }

    /**
     * Lists all balances of the account (explicit name match for List All Balance).
     *
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function listAllBalances(): array
    {
        return $this->getBalances();
    }

    /**
     * Retrieves all account/flow transaction details.
     *
     * @param array $payload Payload containing query details (e.g., start_time, end_time, last_id, count)
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getFlows(array $payload = []): array
    {
        return $this->client->request('POST', '/v2/transfer/get-flow', $payload);
    }

    /**
     * Gets a domestic transfer status/detail by reference.
     *
     * @param array $payload Payload containing query details (e.g., reference)
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getDomesticTransfer(array $payload): array
    {
        return $this->client->request('POST', '/transfer/get-domestic-transfer', $payload);
    }

    /**
     * Retrieves lists of remittance transactions.
     *
     * @param array $payload Payload containing query details (e.g., start_datetime, end_datetime, page_size, page_number)
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getRemittanceList(array $payload = []): array
    {
        return $this->client->request('POST', '/transfer/get-remittance-list', $payload);
    }

    /**
     * Lists all supported local banks and their bank codes.
     *
     * @param array $payload Optional query details
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getSupportedBanks(array $payload = []): array
    {
        return $this->client->request('POST', '/v2/data/supported-bank-code', $payload);
    }

    /**
     * Lists all supported local E-wallets and their codes.
     *
     * @param array $payload Optional query details
     * @return array Decoded response
     * @throws EasylinkException
     */
    public function getSupportedEwallets(array $payload = []): array
    {
        return $this->client->request('POST', '/v2/data/supported-inst-code', $payload);
    }
}
