# Easylink Integrator PHP SDK

[![PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Easylink Integrator PHP SDK adalah wrapper API resmi untuk memudahkan merchant berintegrasi dengan layanan pembayaran **Easylink**. SDK ini mengabstraksi kompleksitas autentikasi token, pembuatan signature digital (RSA SHA-256), serta penanganan request HTTP.

> **English documentation is also available** — see the links in the [Documentation](#documentation) section below.

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

### Opsi 1 — Via Packagist (Direkomendasikan)

Pastikan sudah menginstall [Composer](https://getcomposer.org), lalu jalankan perintah berikut di root project Anda:

```bash
composer require easylink/easylink-php-sdk
```

Composer akan otomatis mendownload SDK dan semua dependensinya ke folder `vendor/`, serta men-generate autoloader.

### Opsi 2 — Via VCS / GitHub (Private / Pre-release)

Jika SDK belum dipublish ke Packagist, tambahkan konfigurasi berikut ke `composer.json` project Anda:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/your-org/easylink-php-sdk"
        }
    ],
    "require": {
        "easylink/easylink-php-sdk": "^1.0"
    }
}
```

Kemudian jalankan:

```bash
composer install
```

### Verifikasi Instalasi

Pastikan autoloader Composer sudah di-require di file PHP Anda:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

---

## Inisialisasi SDK Client

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

## Documentation

Dokumentasi penggunaan modul dibagi berdasarkan jenis transfer dan bahasa:

### 🏦 Payout Domestic (Transfer Domestik & E-Wallet)

Transfer uang ke rekening bank lokal atau e-wallet di Indonesia (OVO, DANA, GOPAY, ShopeePay, dll).

| Bahasa | Link |
|--------|------|
| 🇮🇩 Bahasa Indonesia | [README_domestic_id.md](README_domestic_id.md) |
| 🇬🇧 English | [README_domestic_en.md](README_domestic_en.md) |

### 🌍 Payout International (Remitansi / Cross-Border)

Transfer uang ke rekening bank luar negeri menggunakan nilai tukar mata uang asing secara real-time.

| Bahasa | Link |
|--------|------|
| 🇮🇩 Bahasa Indonesia | [README_international_id.md](README_international_id.md) |
| 🇬🇧 English | [README_international_en.md](README_international_en.md) |

---

## Verifikasi Webhook / Notifikasi Callback

Ketika status transaksi berubah, Easylink akan mengirimkan HTTP POST ke server Anda. Verifikasi signature-nya untuk memastikan request benar-benar berasal dari Easylink.

```php
// Ambil semua header request yang masuk
$headers = getallheaders();

// Masukkan Public Key Easylink (didapatkan dari Dashboard Easylink)
$easylinkPublicKey = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...\n-----END PUBLIC KEY-----";

// Verifikasi signature
$isValid = $client->verifyNotificationSignature($headers, $easylinkPublicKey);

if ($isValid) {
    // Signature valid! Proses payload di sini.
    $payload = json_decode(file_get_contents('php://input'), true);
    http_response_code(200);
    echo json_encode(['code' => 0, 'message' => 'Success']);
} else {
    // Signature tidak cocok, tolak request
    http_response_code(400);
    echo json_encode(['code' => -1, 'message' => 'Invalid signature']);
}
```

---

## Penanganan Error (Error Handling)

Setiap request yang gagal akan melempar `EasylinkException`. Tangkap exception ini untuk mendapatkan detail error dari API.

```php
use EasylinkIntegrator\Exceptions\EasylinkException;

try {
    $balances = $payoutDomestic->getBalances();
} catch (EasylinkException $e) {
    echo "Error: "       . $e->getMessage()        . "\n";
    echo "HTTP Status: " . $e->getStatusCode()     . "\n";
    print_r($e->getResponsePayload()); // Payload respons dari Easylink API
}
```

---

## Lisensi

Proyek ini dilisensikan di bawah Lisensi MIT. Lihat file [LICENSE](LICENSE) untuk detail lebih lanjut.
