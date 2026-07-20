# Easylink Integrator PHP SDK

[![PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Easylink Integrator PHP SDK adalah wrapper API resmi untuk memudahkan merchant berintegrasi dengan layanan pembayaran **Easylink**. SDK ini mengabstraksi kompleksitas autentikasi token, pembuatan signature digital (RSA SHA-256), serta penanganan request HTTP.

---

## Fitur Utama

- **Auto Authentication:** Secara otomatis mengambil dan memperbarui Access Token (berlaku 10 menit) sebelum kedaluwarsa.
- **Auto Signature Generation:** Otomatis menghasilkan header `X-Signature` dan `X-EasyLink-Sign` menggunakan Private Key RSA merchant.
- **Environment Switcher:** Pindah dengan mudah antara mode `sandbox` (testing) dan `production` (live).
- **Modul Payout Lengkap:** Mendukung Payout Domestic (Transfer Lokal & E-Wallet) dan Payout International.
- **Validasi Webhook:** Fungsi bawaan untuk memverifikasi keaslian signature notifikasi callback/webhook dari Easylink.

---

## Persyaratan Sistem

- PHP `>= 7.4` (Kompatibel dengan PHP 8.x)
- Ekstensi PHP `openssl` (untuk pembuatan signature)
- Ekstensi PHP `json`

---

## Instalasi

Install SDK menggunakan Composer:

```bash
composer require easylink/easylink-php-sdk
```

---

## Cara Penggunaan

### 1. Inisialisasi SDK Client

Gunakan kredensial API yang Anda dapatkan dari Dashboard Merchant Easylink. Anda juga perlu menyediakan RSA Private Key Anda (bisa berupa teks PEM langsung atau path ke file `.pem`).

```php
use EasylinkIntegrator\Client;

$config = [
    'appId'       => 'YOUR_APP_ID',
    'appSecret'   => 'YOUR_APP_SECRET',
    'appKey'      => 'YOUR_APP_KEY',
    'privateKey'  => '-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANB...', // Isi PEM key atau path file '/path/to/private.pem'
    'environment' => 'sandbox', // Ganti dengan 'production' untuk live env
];

$client = new Client($config);
```

---

### 2. Payout Domestic (Transfer Domestik & E-Wallet)

Inisialisasi modul domestic payout dengan melewatkan `Client` yang telah dibuat:

```php
use EasylinkIntegrator\Modules\PayoutDomestic;

$payoutDomestic = new PayoutDomestic($client);
```

#### A. Cek Saldo Merchant
```php
try {
    $balances = $payoutDomestic->getBalances();
    print_r($balances);
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Gagal mengambil saldo: " . $e->getMessage();
}
```

#### B. Dapatkan Daftar Bank & E-Wallet yang Didukung
```php
// Daftar Bank Lokal
$banks = $payoutDomestic->getSupportedBanks();

// Daftar E-Wallet (OVO, DANA, GOPAY, ShopeePay, dll)
$ewallets = $payoutDomestic->getSupportedEwallets();
```

#### C. Verifikasi Rekening Bank (Inquiry)
Sangat direkomendasikan melakukan verifikasi nama pemilik rekening sebelum melakukan transfer untuk meminimalkan salah transfer.
```php
try {
    $response = $payoutDomestic->verifyBankAccount([
        'account_number' => '1234567890',
        'bank_id'        => '2', // ID bank dari getSupportedBanks()
        'payment_method' => '1', // 1 = Bank Transfer, 2 = E-Wallet
    ]);
    
    if ($response['code'] === 0) {
        echo "Nama Pemilik: " . $response['data']['account_name'];
    }
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Gagal verifikasi rekening: " . $e->getMessage();
}
```

#### D. Lakukan Transfer (Domestic Payout)
```php
try {
    $transfer = $payoutDomestic->createTransfer([
        'reference'           => 'UNIQUE_TX_ID_' . time(),
        'bank_id'             => '1', // ID bank/e-wallet target
        'account_holder_name' => 'Nama Penerima',
        'account_number'      => '888801000157508',
        'amount'              => '50000', // Jumlah transfer dalam IDR
        'payment_method'      => 1, // 1 = Bank, 2 = E-wallet
        'description'         => 'Pembayaran Invoice #102',
    ]);
    
    if ($transfer['code'] === 0) {
        echo "Transfer berhasil diproses. ID Transaksi: " . $transfer['data']['disbursement_id'];
    }
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Transfer gagal: " . $e->getMessage();
    // Mendapatkan respon detail dari API Easylink
    print_r($e->getResponsePayload());
}
```

#### E. Cek Status Transaksi
```php
$status = $payoutDomestic->getDomesticTransfer([
    'reference' => 'YOUR_TRANSACTION_REFERENCE_ID'
]);
print_r($status);
```

---

### 3. Payout International (Transfer Internasional)

Inisialisasi modul international payout:

```php
use EasylinkIntegrator\Modules\PayoutInternational;

$payoutInternational = new PayoutInternational($client);
```

#### A. Cek Kuotasi Kurs & Biaya (Get Quote)
Sebelum melakukan transfer internasional, ambil harga tukar kurs terlebih dahulu.
```php
try {
    $quote = $payoutInternational->getQuote([
        'sourceCurrency' => 'IDR',
        'targetCurrency' => 'USD',
        'sourceAmount'   => '10000000', // 10 juta IDR
    ]);
    print_r($quote);
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Gagal mengambil kurs: " . $e->getMessage();
}
```

#### B. Buat Transfer Internasional
```php
try {
    $response = $payoutInternational->createTransfer([
        'quote_id'            => 'QUOTE_ID_FROM_GET_QUOTE',
        'reference'           => 'INT_TX_' . time(),
        'beneficiary_name'    => 'John Doe',
        'beneficiary_country' => 'USA',
        'bank_name'           => 'JP Morgan Chase',
        'account_number'      => '987654321',
        'routing_number'      => '123456789', // Kode routing (SWIFT/ABA) sesuai negara target
        // ... parameter tambahan lainnya sesuai kebutuhan API target negara
    ]);
    print_r($response);
} catch (\EasylinkIntegrator\Exceptions\EasylinkException $e) {
    echo "Gagal transfer internasional: " . $e->getMessage();
}
```

---

### 4. Verifikasi Webhook/Notifikasi Callback

Ketika status transaksi berubah, Easylink akan mengirimkan HTTP POST notifikasi callback ke server Anda. Anda wajib memverifikasi signature-nya untuk memastikan request benar-benar berasal dari Easylink.

```php
// Ambil semua header request yang masuk
$headers = getallheaders();

// Masukkan Public Key Easylink (didapatkan dari Dashboard Easylink)
$easylinkPublicKey = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...\n-----END PUBLIC KEY-----";

// Verifikasi signature
$isValid = $client->verifyNotificationSignature($headers, $easylinkPublicKey);

if ($isValid) {
    // Signature valid! Silakan proses status transaksi di database Anda.
    $payload = json_decode(file_get_contents('php://input'), true);
    
    // Kirim respon sukses ke Easylink
    http_response_code(200);
    echo json_encode(['code' => 0, 'message' => 'Success']);
} else {
    // Signature tidak cocok, abaikan request ini
    http_response_code(400);
    echo json_encode(['code' => -1, 'message' => 'Invalid signature']);
}
```

---

## Penanganan Error (Error Handling)

Setiap request yang gagal karena masalah jaringan, token kedaluwarsa, atau error validasi dari Easylink API akan melempar `EasylinkException`.

```php
use EasylinkIntegrator\Exceptions\EasylinkException;

try {
    $balances = $payoutDomestic->getBalances();
} catch (EasylinkException $e) {
    // Pesan error umum
    echo "Error: " . $e->getMessage() . "\n";
    
    // HTTP Status Code (misal: 400, 401, 500)
    echo "HTTP Status: " . $e->getStatusCode() . "\n";
    
    // Payload respon dari API Easylink (jika ada)
    echo "Respon API: ";
    print_r($e->getResponsePayload());
}
```

---

## Lisensi

Proyek ini dilisensikan di bawah Lisensi MIT. Lihat file [LICENSE](LICENSE) untuk detail lebih lanjut.
