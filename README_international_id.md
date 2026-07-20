# Dokumentasi Payout Internasional (Remitansi Lintas Negara) - Bahasa Indonesia

Modul `PayoutInternational` menangani transfer uang lintas batas (remitansi) dengan kurs real-time melalui **satu endpoint tunggal**: `POST /transfer/create-international-transfer`. Struktur payload bervariasi berdasarkan negara tujuan, mata uang, dan segmen transaksi (B2B, B2C, C2B, C2C).

## Inisialisasi Modul

```php
use EasylinkIntegrator\Modules\PayoutInternational;

$payoutInternational = new PayoutInternational($client);
```

---

## Alur Transaksi

```
getQuote()  →  createTransfer()  →  confirmTransfer()  →  getInternationalTransfer()
 (Cek Kurs)   (FX terkunci 5 mnt)  (Wajib dikonfirmasi)   (Pantau status)
```

---

## 1. Ambil Data Referensi (Lookup)

Sebelum membuat transfer, ambil data referensi untuk field compliance:

```php
// Kombinasi negara & mata uang yang didukung
$countriesCurrencies = $payoutInternational->getCountriesCurrencies();

// Semua mata uang tersedia
$currencies = $payoutInternational->getCurrencies();

// Kode tujuan remitansi
$purposes = $payoutInternational->getRemittancePurposes([
    'destination_country'      => 'CHN',
    'segment'                  => 'business',
    'beneficiary_account_type' => 'bank_account',
]);

// Sumber dana
$sources = $payoutInternational->getSourcesOfFunds([
    'destination_country' => 'CHN',
    'segment'             => 'business',
]);

// Kode hubungan pengirim-penerima
$relationships = $payoutInternational->getRelationships([
    'segment' => 'business',
]);
```

---

## 2. Dapatkan Kurs (Quote)

```php
$quote = $payoutInternational->getQuote([
    'source_currency'      => 'IDR',
    'destination_currency' => 'CNH',
    'source_amount'        => '10000000',
]);
```

---

## 3. Buat Transfer — Contoh Payload per Negara & Segmen

> **Catatan**: Semua varian menggunakan metode `createTransfer()` yang sama. Hanya payload yang berbeda per kombinasi negara/segmen.

---

### 🇨🇳 China (CHN)

#### B2B Wire (Bisnis → Bisnis, via Swift/Wire)
```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'CHN',
        'destination_currency' => 'CNY', // atau CNH
        'destination_amount'   => 1000,
    ],
    'source' => [
        'segment'                      => 'business',
        'company_name'                 => 'PT Perusahaan Pengirim',
        'company_trading_name'         => 'Nama Dagang',
        'company_registration_number'  => '63746192873912',
        'company_registration_country' => 'IDN',
        'address_line'                 => 'Jl. Sudirman No. 10',
        'address_city'                 => 'Jakarta',
        'address_country'              => 'IDN',
    ],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Wire',
        'company_name'             => 'Perusahaan Penerima Ltd',
        'swift_code'               => 'BKCHCNBJ',
        'bank_account_number'      => '12345678901',
        'address_line'             => 'No. 1 Jalan Beijing',
        'address_city'             => 'Beijing',
        'address_country'          => 'CHN',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
        'fee_payer'                => 1, // 1 = pengirim, 2 = penerima
    ],
]);
```

#### B2C Bank Account (Bisnis → Individu, Rekening Bank)
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
        'company_name'                 => 'PT Perusahaan Pengirim',
        'company_trading_name'         => 'Nama Dagang',
        'company_registration_number'  => '63746192873912',
        'company_registration_country' => 'IDN',
        'address_line'                 => 'Jl. Sudirman No. 10',
        'address_city'                 => 'Jakarta',
        'address_country'              => 'IDN',
    ],
    'destination' => [
        'segment'                  => 'individual',
        'beneficiary_account_type' => 'Bank Account',
        'recipient_name'           => 'Zhang San',
        'id_number'                => '110101199003071234',    // NIK China
        'bank_account_number'      => '6227001002003456789',
        'mobile_number'            => '13721473389',
        'ewallet_type'             => 'bank',
        'relation'                 => 'Self',
        'purpose'                  => 'Services trade',
        'source_of_income'         => 'Salary',
    ],
]);
```

#### B2C E-Wallet (Bisnis → Individu, Alipay/WeChat)
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
        'company_name'                 => 'PT Perusahaan Pengirim',
        'company_trading_name'         => 'Nama Dagang',
        'company_registration_number'  => '63746192873912',
        'company_registration_country' => 'IDN',
        'address_line'                 => 'Jl. Sudirman No. 10',
        'address_city'                 => 'Jakarta',
        'address_country'              => 'CHN',
    ],
    'destination' => [
        'segment'                  => 'individual',
        'beneficiary_account_type' => 'eWallet',
        'ewallet_type'             => 'alipay',  // 'alipay' atau 'wechat'
        'ewallet_id'               => '13721473389', // ID akun Alipay/WeChat
        'first_name'               => 'Li',
        'last_name'                => 'Lei',
        'id_number'                => '110101199003071234',
        'date_of_birth'            => '1990-03-07',
    ],
]);
```

#### C2C Bank Account (Individu → Individu, Rekening Bank)
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
        'first_name'       => 'Budi',
        'last_name'        => 'Santoso',
        'id_number'        => '3171234567890001',
        'nationality'      => 'IDN',
        'date_of_birth'    => '1990-01-01',
        'gender'           => 'Male',
        'occupation'       => 'Engineer',
        'id_issue_date'    => '2010-01-01',
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

#### B2B CNH Rekening Bank
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
        'company_name'                 => 'PT Perusahaan Pengirim',
        'company_trading_name'         => 'Nama Dagang',
        'company_registration_number'  => 'nomorregistrasi',
        'company_registration_country' => 'IDN',
        'address_line'                 => 'Jl. Sudirman No. 10',
        'address_city'                 => 'Jakarta',
        'address_country'              => 'IDN',
    ],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'HK Perusahaan Penerima',
        'bank_code'                => '003',         // Kode bank lokal HKG
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
    'source' => [/* field bisnis source */],
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

### 🇸🇬 Singapura (SGP) — SGD

#### B2B Rekening Bank
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
        'company_name'                 => 'PT Perusahaan Pengirim',
        'company_trading_name'         => 'Nama Dagang',
        'company_registration_number'  => '63746192873912',
        'company_registration_country' => 'IDN',
        'address_line'                 => 'Jl. Sudirman No. 10',
        'address_city'                 => 'Jakarta',
        'address_country'              => 'IDN',
    ],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'Singapore Company Pte Ltd',
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

Untuk segmen **B2C / C2C / C2B**, ubah nilai `source.segment` dan `destination.segment`:
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
    'source' => [/* field bisnis source */],
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
    'source' => [/* field bisnis source */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'Aus Company Pty Ltd',
        'swift_code'               => 'ANZBAU3M',
        'bank_account_number'      => '123456789',
        'bsb_code'                 => '012345',       // Kode BSB Australia (routing)
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

### 🇨🇦 Kanada (CAN) — CAD

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'CAN',
        'destination_currency' => 'CAD',
        'destination_amount'   => 90.79,
    ],
    'source' => [/* field bisnis source */],
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

### 🌍 SEPA / Eropa — EUR

> **Catatan**: Transfer SEPA memerlukan **IBAN** sebagai pengganti `bank_account_number` biasa.

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'DEU',  // kode ISO negara tujuan (mis. DEU, FRA, ITA)
        'destination_currency' => 'EUR',
        'destination_amount'   => 466.98,
    ],
    'source' => [
        'segment'                      => 'business',
        'company_name'                 => 'PT Perusahaan Pengirim',
        'company_trading_name'         => 'Nama Dagang',
        'company_registration_number'  => 'nomorregistrasi',
        'company_registration_country' => 'IDN',
        'address_line'                 => 'Jl. Sudirman No. 10',
        'address_city'                 => 'Jakarta',
        'address_country'              => 'IDN',
    ],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'European GmbH',
        'swift_code'               => 'BWKPUT1Q',
        'iban'                     => 'DE89370400440532013000',  // IBAN wajib untuk SEPA
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

### 🇬🇧 Inggris (GBR) — GBP

> **Catatan**: Transfer UK memerlukan `sort_code` (kode routing 6 digit) selain `bank_account_number`.

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'GBR',
        'destination_currency' => 'GBP',
        'destination_amount'   => 300,
    ],
    'source' => [/* field bisnis source */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'UK Company Ltd',
        'swift_code'               => 'NWBKGB2L',
        'bank_account_number'      => '12345678',
        'sort_code'                => '200000',       // Sort Code UK (6 digit)
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

### 🇳🇿 Selandia Baru (NZL) — NZD

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'NZL',
        'destination_currency' => 'NZD',
        'destination_amount'   => 100,
    ],
    'source' => [/* field bisnis source */],
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

### 🇵🇭 Filipina (PHL) — PHP

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'PHL',
        'destination_currency' => 'PHP',
        'destination_amount'   => 5000,
    ],
    'source' => [/* field bisnis source */],
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

### 🇯🇵 Jepang (JPN) — JPY (Wire saja)

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'JPN',
        'destination_currency' => 'JPY',
        'destination_amount'   => 50000,
    ],
    'source' => [/* field bisnis source */],
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

### 🇰🇷 Korea Selatan (KOR) — KRW

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'KOR',
        'destination_currency' => 'KRW',
        'destination_amount'   => 100000,
    ],
    'source' => [/* field bisnis source */],
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
    'source' => [/* field bisnis source */],
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

> **Catatan**: Transfer India memerlukan `ifsc_code` (kode IFSC 11 karakter) sebagai routing.

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'IND',
        'destination_currency' => 'INR',
        'destination_amount'   => 10000,
    ],
    'source' => [/* field bisnis source */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Bank Account',
        'company_name'             => 'India Pvt Ltd',
        'swift_code'               => 'HDFCINBB',
        'bank_account_number'      => '50100123456789',
        'ifsc_code'                => 'HDFC0001234',    // Kode IFSC India
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

### 🌐 Global Wire (B2B & C2C — Negara di Luar Daftar)

Untuk negara yang tidak terdaftar di atas, gunakan opsi Global Wire:

```php
$payoutInternational->createTransfer([
    'reference'   => 'TX_' . time(),
    'transaction' => [
        'destination_country'  => 'USA', // kode negara apapun
        'destination_currency' => 'USD', // wire biasanya dalam USD
        'destination_amount'   => 90.79,
    ],
    'source' => [/* field bisnis atau individu source */],
    'destination' => [
        'segment'                  => 'business',
        'beneficiary_account_type' => 'Wire',
        'fee_payer'                => '2', // 1 = OUR (pengirim), 2 = BEN (penerima), 3 = SHA
        'company_name'             => 'Foreign Company Inc',
        'swift_code'               => 'CITIUS33',
        'bank_account_number'      => '34567896788',
        'address_line'             => 'Main St 1',
        'address_city'             => 'New York',
        'address_country'          => 'USA',
        'purpose_code'             => '008-01',
        'source_of_income_code'    => '01',
    ],
]);
```

---

## 4. Konfirmasi Transfer

> **Wajib dipanggil dalam 5 menit** setelah `createTransfer`.

```php
$payoutInternational->confirmTransfer([
    'reference' => 'REFERENSI_TRANSAKSI_ANDA'
]);
```

---

## 5. Cek Status Transfer

```php
$status = $payoutInternational->getInternationalTransfer([
    'reference' => 'REFERENSI_TRANSAKSI_ANDA'
]);
```

### Kode Status Transaksi

| State | Keterangan |
|-------|-----------|
| `1`   | Menunggu Konfirmasi |
| `2`   | Diproses |
| `3`   | Selesai |
| `4`   | Gagal |
| `8`   | Kadaluarsa (tidak dikonfirmasi dalam 5 menit) |

---

## Referensi Field per Negara

| Negara | Field Routing Khusus | Nilai Account Type | Catatan |
|--------|---------------------|-------------------|---------|
| China (CHN) | — | `Bank Account` / `Wire` / `eWallet` | eWallet: `ewallet_type` = `alipay`/`wechat` |
| Hong Kong (HKG) | `bank_code` | `Bank Account` / `Wire` | CNH atau HKD |
| Singapura (SGP) | `swift_code` | `Bank Account` | SGD |
| Malaysia (MYS) | `swift_code` | `Bank Account` | MYR |
| Eropa/SEPA | `iban` | `Bank Account` | Wajib untuk negara SEPA |
| Inggris (GBR) | `sort_code` | `Bank Account` | 6 digit sort code |
| Australia (AUS) | `bsb_code` | `Bank Account` | AUD |
| Kanada (CAN) | `swift_code` | `Bank Account` / `Wire` | CAD atau USD |
| Selandia Baru (NZL) | `swift_code` | `Bank Account` | NZD |
| Filipina (PHL) | `swift_code` | `Bank Account` | PHP |
| Jepang (JPN) | `swift_code` | `Wire` saja | JPY |
| Korea Selatan (KOR) | `swift_code` | `Bank Account` | KRW |
| Thailand (THA) | `swift_code` | `Bank Account` | THB |
| India (IND) | `ifsc_code` | `Bank Account` | INR |
| Global Wire | `swift_code` | `Wire` | Untuk negara di luar daftar |
