# Domestic Payout Documentation (Local Bank & E-Wallet Transfer) - English

The `PayoutDomestic` module is used to handle domestic money transfers in Indonesia, supporting both local bank transfers and e-wallet disbursements.

## Module Initialization
First, initialize the domestic payout module by passing your configured `Client` instance:

```php
use EasylinkIntegrator\Modules\PayoutDomestic;

$payoutDomestic = new PayoutDomestic($client);
```

---

## 1. Check Merchant Balance
Check the current balance available in your Easylink merchant account.

```php
try {
    $balances = $payoutDomestic->getBalances();
    // Or use the alias method: $balances = $payoutDomestic->listAllBalances();
    
    print_r($balances);
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Failed to retrieve balance: " . $e->getMessage();
}
```

---

## 2. Get Supported Banks & E-Wallets
Retrieve the list of bank IDs and e-wallet codes supported by Easylink.

```php
// List Supported Local Banks
$banks = $payoutDomestic->getSupportedBanks();
print_r($banks);

// List Supported E-Wallets (OVO, DANA, GOPAY, ShopeePay, etc.)
$ewallets = $payoutDomestic->getSupportedEwallets();
print_r($ewallets);
```

---

## 3. Verify Bank Account (Account Inquiry)
It is highly recommended to verify the bank account holder's name before executing a transfer to minimize processing errors.

```php
try {
    $response = $payoutDomestic->verifyBankAccount([
        'account_number' => '1234567890',
        'bank_id'        => '2', // Bank ID from getSupportedBanks()
        'payment_method' => '1', // 1 = Bank Transfer, 2 = E-Wallet
    ]);
    
    if ($response['code'] === 0) {
        echo "Account Holder Name: " . $response['data']['account_name'];
    }
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Account verification failed: " . $e->getMessage();
}
```

---

## 4. Create Domestic Transfer (Domestic Payout)
Initiate a funds transfer to a domestic bank account or e-wallet.

```php
try {
    $transfer = $payoutDomestic->createTransfer([
        'reference'           => 'UNIQUE_TX_ID_' . time(),
        'bank_id'             => '1', // Target bank or e-wallet ID
        'account_holder_name' => 'Recipient Name',
        'account_number'      => '888801000157508',
        'amount'              => '50000', // Transfer amount in IDR
        'payment_method'      => 1, // 1 = Bank, 2 = E-wallet
        'description'         => 'Payment for Invoice #102',
    ]);
    
    if ($transfer['code'] === 0) {
        echo "Transfer successfully initiated. Transaction ID: " . $transfer['data']['disbursement_id'];
    }
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Transfer failed: " . $e->getMessage();
    print_r($e->getResponsePayload());
}
```

---

## 5. Check Domestic Transaction Status
Monitor the status of a domestic transfer by querying its reference ID.

```php
try {
    $status = $payoutDomestic->getDomesticTransfer([
        'reference' => 'YOUR_TRANSACTION_REFERENCE_ID'
    ]);
    print_r($status);
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Failed to fetch transaction status: " . $e->getMessage();
}
```

---

## 6. Remittance History List
Retrieve list of historical remittance transactions within a specific timeframe.

```php
try {
    $remittanceList = $payoutDomestic->getRemittanceList([
        'start_datetime' => '2025-03-01T00:00:00.000Z',
        'end_datetime'   => '2026-03-31T00:00:00.000Z',
        'page_size'      => '5',
        'page_number'    => '1',
    ]);
    print_r($remittanceList);
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Failed to retrieve remittance history: " . $e->getMessage();
}
```

---

## 7. Account Mutates / Balance Flow List
View the credit and debit flow history of your merchant account balance.

```php
try {
    $flows = $payoutDomestic->getFlows([
        'start_time' => '2025-12-01 21:00:00',
        'end_time'   => '2025-12-30 01:00:00',
        'last_id'    => 9,
        'count'      => 5,
    ]);
    print_r($flows);
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Failed to fetch flow history: " . $e->getMessage();
}
```
