# International Payout Documentation (Cross-Border Remittance) - English

The `PayoutInternational` module handles cross-border money transfers (remittances) with real-time foreign exchange rates via a **single endpoint**: `POST /transfer/create-international-transfer`. The payload structure varies by country, currency, and transaction segment (B2B, B2C, C2B, C2C).

## Module Initialization

```php
use EasylinkIntegrator\Modules\PayoutInternational;

$payoutInternational = new PayoutInternational($client);
```

---

## Transaction Flow

```
getQuote()  →  createTransfer()  →  confirmTransfer()  →  getInternationalTransfer()
 (Rate)        (FX locked 5 min)   (Required confirm)      (Poll status)
```

---

## 1. Fetch Lookup & Compliance Data

Before initiating any transfer, retrieve the required reference data for compliance fields:

```php
// Supported country & currency combinations
$countriesCurrencies = $payoutInternational->getCountriesCurrencies();

// All available currencies
$currencies = $payoutInternational->getCurrencies();

// Remittance purpose codes (filter required)
$purposes = $payoutInternational->getRemittancePurposes([
    'destination_country'      => 'CHN',
    'segment'                  => 'business',
    'beneficiary_account_type' => 'bank_account',
]);

// Sources of funds
$sources = $payoutInternational->getSourcesOfFunds([
    'destination_country' => 'CHN',
    'segment'             => 'business',
]);

// Sender-beneficiary relationship codes
$relationships = $payoutInternational->getRelationships([
    'segment' => 'business',
]);
```

---

## 2. Get Exchange Rate Quote

```php
$quote = $payoutInternational->getQuote([
    'source_currency'      => 'IDR',
    'destination_currency' => 'CNH',
    'source_amount'        => '10000000',
]);
```

---

## 3. Create Transfer — Payload Examples by Region & Segment

> **Note**: All variants use the same `createTransfer()` method. Only the payload changes per country/segment combination.

---

### 🇨🇳 China (CHN)

#### B2B Wire (Business → Business, via Swift/Wire)
```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'CHN',
        'destination_currency' => 'CNY', // or CNH
        'destination_amount'   => 1000,
    ],
    'source' => [
        'segment'                      => 'business',
        'company_name'                 => 'PT Sender Company',
        'company_trading_name'         => 'Trading Name',
        'company_registration_number'  => '63746192873912',
        'company_registration_country' => 'IDN',
        'address_line'                 => 'Sudirman St. 10',
        'address_city'                 => 'Jakarta',
        'address_country'              => 'IDN',
    ],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Wire',
        'company_name'             => 'Recipient Company Ltd',
        'swift_code'               => 'BKCHCNBJ',
        'bank_account_number'      => '12345678901',
        'address_line'             => 'No. 1 Beijing Road',
        'address_city'             => 'Beijing',
        'address_country'          => 'CHN',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'fee_payer'                => 1, // 1 = sender, 2 = receiver
    ],
]);
```

#### B2C Bank Account (Business → Individual, Bank Account)
```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'CHN',
        'destination_currency' => 'CNY',
        'destination_amount'   => 466.98,
    ],
    'source' => [
        'segment'                      => 'business',
        'company_name'                 => 'PT Sender Company',
        'company_trading_name'         => 'Trading Name',
        'company_registration_number'  => '63746192873912',
        'company_registration_country' => 'IDN',
        'address_line'                 => 'Sudirman St. 10',
        'address_city'                 => 'Jakarta',
        'address_country'              => 'IDN', // Note: source.address_country can differ from source country
    ],
    'destination' => [
        'segment'                  => 'individual',
        'beneficiary_account_type' => 'Bank Account',
        'recipient_name'           => 'Zhang San',
        'id_number'                => '110101199003071234',    // Chinese National ID
        'bank_account_number'      => '6227001002003456789',
        'mobile_number'            => '13721473389',
        'ewallet_type'             => 'bank',
        'relation'                 => 'Self',
        'purpose'                  => 'Services trade',
        'source_of_income'         => 'Salary',
    ],
]);
```

#### B2C E-Wallet (Business → Individual, Alipay/WeChat)
```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'CHN',
        'destination_currency' => 'CNY',
        'destination_amount'   => 466.98,
    ],
    'source' => [
        'segment'                      => 'business',
        'company_name'                 => 'PT Sender Company',
        'company_trading_name'         => 'Trading Name',
        'company_registration_number'  => '63746192873912',
        'company_registration_country' => 'IDN',
        'address_line'                 => 'Sudirman St. 10',
        'address_city'                 => 'Jakarta',
        'address_country'              => 'CHN',
    ],
    'destination' => [
        'segment'                  => 'individual',
        'beneficiary_account_type' => 'eWallet',
        'ewallet_type'             => 'alipay',  // 'alipay' or 'wechat'
        'ewallet_id'               => '13721473389', // Alipay/WeChat account ID
        'first_name'               => 'Li',
        'last_name'                => 'Lei',
        'id_number'                => '110101199003071234',
        'date_of_birth'            => '1990-03-07',
    ],
]);
```

#### C2C Bank Account (Individual → Individual, Bank Account)
```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'CHN',
        'destination_currency' => 'CNY',
        'destination_amount'   => 466.98,
    ],
    'source' => [
        'segment'          => 'individual',
        'first_name'       => 'John',
        'last_name'        => 'Doe',
        'id_number'        => 'A1234567',
        'nationality'      => 'IDN',
        'date_of_birth'    => '1990-01-01',
        'gender'           => 'Male',
        'occupation'       => 'Engineer',
        'id_issue_date'    => '1999-01-01',
        'id_expiry_date'   => '2099-01-01',
        'address_line'     => 'Jl. Merdeka No. 10',
        'address_city'     => 'Jakarta',
        'address_state'    => 'DKI Jakarta',
        'address_country'  => 'IDN',
    ],
    'destination' => [
        'segment'                  => 'individual',
        'beneficiary_account_type' => 'Bank Account',
        'first_name'               => 'Li',
        'last_name'                => 'Wei',
        'bank_account_number'      => '6227000010810083602',
        'source_of_income'         => 'Employment income',
        'purpose'                  => 'Family Support',
        'relation'                 => 'Self',
    ],
]);
```

---

### 🇭🇰 Hong Kong (HKG)

#### B2B CNH Bank Account
```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'HKG',
        'destination_currency' => 'CNH',
        'destination_amount'   => 466.98,
    ],
    'source' => [
        'segment'                      => 'business',
        'company_name'                 => 'PT Sender Company',
        'company_trading_name'         => 'Trading Name',
        'company_registration_number'  => 'companyregistrationnumber',
        'company_registration_country' => 'CHN',
        'address_line'                 => 'two street',
        'address_city'                 => 'Aksu',
        'address_country'              => 'CHN',
    ],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'TESTCOMPANY',
        'bank_code'                => '003',             // HKG local bank code
        'bank_account_number'      => '142342344234234',
        'source_of_income_code'    => '01',
        'purpose_code'             => '008-01',
        'relation_code'            => 'Vendor',
        'relation'                 => 'Vendor',
    ],
]);
```

#### B2B HKD Wire
```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'HKG',
        'destination_currency' => 'HKD',
        'destination_amount'   => 500,
    ],
    'source' => [/* same business source fields */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Wire',
        'company_name'             => 'HK Company Ltd',
        'swift_code'               => 'HSBCHKHHHKH',
        'bank_account_number'      => '123456789012',
        'address_line'             => 'Central, Hong Kong',
        'address_city'             => 'Hong Kong',
        'address_country'          => 'HKG',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'fee_payer'                => 1,
    ],
]);
```

---

### 🇸🇬 Singapore (SGP) — SGD

#### B2B Bank Account
```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'SGP',
        'destination_currency' => 'SGD',
        'destination_amount'   => 90.79,
    ],
    'source' => [
        'segment'                      => 'business',
        'company_name'                 => 'PT Sender Company',
        'company_trading_name'         => 'Trading Name',
        'company_registration_number'  => '63746192873912',
        'company_registration_country' => 'IDN',
        'address_line'                 => 'two street',
        'address_city'                 => 'Jakarta',
        'address_country'              => 'IDN',
    ],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'Surfin Company',
        'swift_code'               => 'CIBBSGSG',
        'bank_account_number'      => '34567896788',
        'address_line'             => 'Raffles Place 1',
        'address_city'             => 'Singapore',
        'address_country'          => 'SGP',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'relation_code'            => '04',
    ],
]);
```

#### B2C / C2C / C2B Bank Account
The same fields apply, with the `source.segment` and `destination.segment` changed accordingly:
- **B2C**: `source.segment = 'business'`, `destination.segment = 'individual'`
- **C2C**: `source.segment = 'individual'`, `destination.segment = 'individual'`
- **C2B**: `source.segment = 'individual'`, `destination.segment = 'business'`

---

### 🇲🇾 Malaysia (MYS) — MYR

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'MYS',
        'destination_currency' => 'MYR',
        'destination_amount'   => 200,
    ],
    'source' => [
        'segment'                      => 'business',
        'company_name'                 => 'PT Sender Company',
        'company_registration_number'  => '63746192873912',
        'company_registration_country' => 'IDN',
        'address_line'                 => 'Sudirman St.',
        'address_city'                 => 'Jakarta',
        'address_country'              => 'IDN',
    ],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'Malaysia Company Sdn Bhd',
        'swift_code'               => 'CIBBMYKL',
        'bank_account_number'      => '1234567890',
        'address_line'             => 'Jalan Bukit Bintang',
        'address_city'             => 'Kuala Lumpur',
        'address_country'          => 'MYS',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'relation_code'            => '04',
    ],
]);
```

---

### 🇦🇺 Australia (AUS) — AUD

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'AUS',
        'destination_currency' => 'AUD',
        'destination_amount'   => 150,
    ],
    'source' => [/* business source fields */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'Aus Company Pty Ltd',
        'swift_code'               => 'ANZBAU3M',
        'bank_account_number'      => '123456789',
        'bsb_code'                 => '012345',          // Australian BSB code (routing)
        'address_line'             => 'George Street 1',
        'address_city'             => 'Sydney',
        'address_country'          => 'AUS',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'relation_code'            => '04',
    ],
]);
```

---

### 🇨🇦 Canada (CAN) — CAD

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'CAN',
        'destination_currency' => 'CAD',
        'destination_amount'   => 90.79,
    ],
    'source' => [/* business source fields */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'Canada Corp',
        'swift_code'               => 'ROYCCAT2',
        'bank_account_number'      => '34567896788',
        'address_line'             => 'Bay Street 1',
        'address_city'             => 'Toronto',
        'address_country'          => 'CAN',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'relation_code'            => '04',
    ],
]);
```

---

### 🌍 SEPA / Europe — EUR

> **Note**: SEPA transfers require **IBAN** instead of plain `bank_account_number`.

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'DEU',  // ISO country code of the destination (e.g., DEU, FRA, ITA)
        'destination_currency' => 'EUR',
        'destination_amount'   => 466.98,
    ],
    'source' => [
        'segment'                      => 'business',
        'company_name'                 => 'PT Sender Company',
        'company_trading_name'         => 'Trading Name',
        'company_registration_number'  => 'companyregistrationnumber',
        'company_registration_country' => 'IDN',
        'address_line'                 => 'two street',
        'address_city'                 => 'Jakarta',
        'address_country'              => 'IDN',
    ],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'European GmbH',
        'swift_code'               => 'BWKPUT1Q',
        'iban'                     => 'DE89370400440532013000',  // IBAN is required for SEPA
        'address_line'             => 'Unter den Linden 1',
        'address_city'             => 'Berlin',
        'address_country'          => 'DEU',
        'source_of_income_code'    => '01',
        'purpose_code'             => '008-01',
        'relation_code'            => 'Vendor',
    ],
]);
```

---

### 🇬🇧 United Kingdom (GBR) — GBP

> **Note**: UK transfers require `sort_code` (6-digit routing code) in addition to `bank_account_number`.

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'GBR',
        'destination_currency' => 'GBP',
        'destination_amount'   => 300,
    ],
    'source' => [/* business source fields */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'UK Company Ltd',
        'swift_code'               => 'NWBKGB2L',
        'bank_account_number'      => '12345678',
        'sort_code'                => '200000',         // UK Sort Code (6 digits)
        'address_line'             => 'Canary Wharf 1',
        'address_city'             => 'London',
        'address_country'          => 'GBR',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'relation_code'            => '04',
    ],
]);
```

---

### 🇳🇿 New Zealand (NZL) — NZD

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'NZL',
        'destination_currency' => 'NZD',
        'destination_amount'   => 100,
    ],
    'source' => [/* business source fields */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'NZ Company Ltd',
        'swift_code'               => 'ANZBNZ22',
        'bank_account_number'      => '01-0142-0123456-00',
        'address_line'             => 'Queen Street 1',
        'address_city'             => 'Auckland',
        'address_country'          => 'NZL',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'relation_code'            => '04',
    ],
]);
```

---

### 🇵🇭 Philippines (PHL) — PHP

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'PHL',
        'destination_currency' => 'PHP',
        'destination_amount'   => 5000,
    ],
    'source' => [/* business source fields */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'PHL Company Inc',
        'swift_code'               => 'BPIAPHMMXXX',
        'bank_account_number'      => '1234567890123',
        'address_line'             => 'Ayala Ave 1',
        'address_city'             => 'Makati',
        'address_country'          => 'PHL',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'relation_code'            => '04',
    ],
]);
```

---

### 🇯🇵 Japan (JPN) — JPY (Wire only)

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'JPN',
        'destination_currency' => 'JPY',
        'destination_amount'   => 50000,
    ],
    'source' => [/* business source fields */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Wire',
        'company_name'             => 'Japan Corp KK',
        'swift_code'               => 'MHCBJPJT',
        'bank_account_number'      => '1234567',
        'address_line'             => 'Marunouchi 1',
        'address_city'             => 'Tokyo',
        'address_country'          => 'JPN',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'fee_payer'                => 1,
    ],
]);
```

---

### 🇰🇷 South Korea (KOR) — KRW

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'KOR',
        'destination_currency' => 'KRW',
        'destination_amount'   => 100000,
    ],
    'source' => [/* business source fields */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'Korean Corp Ltd',
        'swift_code'               => 'HVBKKRSE',
        'bank_account_number'      => '110123456789',
        'address_line'             => 'Gangnam-daero 1',
        'address_city'             => 'Seoul',
        'address_country'          => 'KOR',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'relation_code'            => '04',
    ],
]);
```

---

### 🇹🇭 Thailand (THA) — THB

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'THA',
        'destination_currency' => 'THB',
        'destination_amount'   => 3000,
    ],
    'source' => [/* business source fields */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'Thai Co Ltd',
        'swift_code'               => 'BKKBTHBK',
        'bank_account_number'      => '1234567890',
        'address_line'             => 'Silom Road 1',
        'address_city'             => 'Bangkok',
        'address_country'          => 'THA',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'relation_code'            => '04',
    ],
]);
```

---

### 🇮🇳 India (IND) — INR

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'IND',
        'destination_currency' => 'INR',
        'destination_amount'   => 10000,
    ],
    'source' => [/* business source fields */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'India Pvt Ltd',
        'swift_code'               => 'HDFCINBB',
        'bank_account_number'      => '50100123456789',
        'ifsc_code'                => 'HDFC0001234',    // IFSC routing code for India
        'address_line'             => 'MG Road 1',
        'address_city'             => 'Mumbai',
        'address_country'          => 'IND',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'relation_code'            => '04',
    ],
]);
```

---

### 🌐 Global Wire (B2B & C2C — Unsupported Country Local Banks)

For countries not listed above, use the Global Wire option:

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'CAN', // any country code
        'destination_currency' => 'USD', // wire typically in USD
        'destination_amount'   => 90.79,
    ],
    'source' => [/* business or individual source fields */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Wire',
        'fee_payer'                => '2', // 1 = OUR (sender), 2 = BEN (receiver), 3 = SHA
        'company_name'             => 'Foreign Company Inc',
        'swift_code'               => 'CITIUS33',
        'bank_account_number'      => '34567896788',
        'address_line'             => 'Bay Street 1',
        'address_city'             => 'Toronto',
        'address_country'          => 'CAN',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
    ],
]);
```

---

## 4. Confirm Transfer

> **Must be called within 5 minutes** of `createTransfer`.

```php
$payoutInternational->confirmTransfer([
    'reference' => 'YOUR_TRANSACTION_REFERENCE'
]);
```

---

## 5. Check Transfer Status

```php
$status = $payoutInternational->getInternationalTransfer([
    'reference' => 'YOUR_TRANSACTION_REFERENCE'
]);
```

### Transaction State Codes

| State | Description |
|-------|-------------|
| `1`   | Awaiting Confirmation |
| `2`   | Processing |
| `3`   | Completed |
| `4`   | Failed |
| `8`   | Expired (not confirmed within 5 minutes) |

---

## Field Reference by Country

| Country | Routing Field | Account Type Value | Notes |
|---------|-------------|-------------------|-------|
| China (CHN) | — | `Bank Account` / `Wire` / `eWallet` | eWallet: `ewallet_type` = `alipay`/`wechat` |
| Hong Kong (HKG) | `bank_code` | `Bank Account` / `Wire` | CNH or HKD currency |
| Singapore (SGP) | `swift_code` | `Bank Account` | SGD |
| Malaysia (MYS) | `swift_code` | `Bank Account` | MYR |
| Europe/SEPA | `iban` | `Bank Account` | Required for SEPA countries |
| UK (GBR) | `sort_code` | `Bank Account` | 6-digit sort code |
| Australia (AUS) | `bsb_code` | `Bank Account` | AUD |
| Canada (CAN) | `swift_code` | `Bank Account` / `Wire` | CAD or USD |
| New Zealand (NZL) | `swift_code` | `Bank Account` | NZD |
| Philippines (PHL) | `swift_code` | `Bank Account` | PHP |
| Japan (JPN) | `swift_code` | `Wire` only | JPY |
| South Korea (KOR) | `swift_code` | `Bank Account` | KRW |
| Thailand (THA) | `swift_code` | `Bank Account` | THB |
| India (IND) | `ifsc_code` | `Bank Account` | INR |
| Global Wire | `swift_code` | `Wire` | For unlisted countries |
