# Easylink API Integration — Multi-Language Code Snippets

[![Language: Indonesian](https://img.shields.io/badge/Language-Bahasa_Indonesia-blue.svg)](README.md)
[![Language: English](https://img.shields.io/badge/Language-English-green.svg)](README_EN.md)

Repositori ini berisi kumpulan **Code Snippets** siap pakai (*copy-paste ready*) yang dikategorikan berdasarkan modul **Domestic** dan **International** untuk mengintegrasikan **Easylink API** di berbagai bahasa pemrograman.

Bahasa & Lingkungan yang Didukung:
- 🐘 **PHP** (Native cURL + OpenSSL, tanpa Composer)
- 🟢 **Node.js / JavaScript** (`crypto` module + `fetch`)
- 🐍 **Python 3** (`cryptography` + `requests`)
- 🔵 **Go / Golang** (`crypto/rsa` + `net/http`)
- 🐚 **cURL / Bash** (`curl` + `openssl` CLI)

---

## 📂 Struktur Folder Project

Setiap folder bahasa dikategorikan ke dalam subfolder modul API berikut:

```text
.
├── php/
│   ├── helpers.php
│   ├── auth/
│   │   └── get_access_token.php           # Request Access Token B2B
│   ├── domestic/
│   │   ├── 01_verify_bank_account.php      # Inquiry Rekening Bank
│   │   ├── 02_create_domestic_transfer.php  # Transfer Lokal & E-Wallet
│   │   ├── 03_get_domestic_transfer_status.php # Status Transfer Domestik
│   │   ├── 04_get_balances.php             # Saldo Akun Merchant
│   │   ├── 05_get_flows.php                # Mutasi Kas & Flow Akun
│   │   ├── 06_get_remittance_list.php      # Daftar Transaksi Remitansi
│   │   ├── 07_supported_banks.php          # Daftar Kode Bank Lokal
│   │   └── 08_supported_ewallets.php       # Daftar Kode E-Wallet
│   ├── international/
│   │   ├── 01_get_quotes.php               # Inquiry Kurs & Quote
│   │   ├── 02_create_int_transfer.php      # Transfer Remitansi Internasional
│   │   ├── 03_confirm_int_transfer.php     # Konfirmasi Transfer Internasional
│   │   ├── 04_get_int_transfer_status.php  # Status Transfer Internasional
│   │   ├── 05_get_countries_currencies.php # Daftar Negara & Mata Uang
│   │   ├── 06_get_currencies.php           # Daftar Currency & Satuan Minimum
│   │   ├── 07_get_remittance_purposes.php  # Daftar Tujuan Remitansi
│   │   ├── 08_get_sources_of_funds.php     # Daftar Sumber Dana
│   │   └── 09_get_relationships.php        # Daftar Hubungan Pengirim-Penerima
│   └── webhook/
│       └── verify_signature.php            # Verifikasi RSA Signature Webhook Callback
├── nodejs/                                # (Struktur sama seperti PHP)
├── python/                                # (Struktur sama seperti PHP)
├── go/                                    # (Struktur sama seperti PHP)
└── curl/                                  # (Struktur sama seperti PHP)
```

---

## 🔐 Aturan Pembuatan RSA SHA-256 Signature

Setiap request ke API Easylink memerlukan header autentikasi & signature:
- `Authorization: Bearer <ACCESS_TOKEN>`
- `X-EasyLink-AppKey: <APP_KEY>`
- `X-EasyLink-Nonce: <UNIQUE_NONCE>`
- `X-EasyLink-Timestamp: <TIMESTAMP_IN_MS>`
- `X-Signature: <BASE64_RSA_SHA256_SIGNATURE>`
- `X-EasyLink-Sign: <BASE64_RSA_SHA256_SIGNATURE>`

---

## ⚙️ Langkah Persiapan & Konfigurasi Kredensial

Sebelum menjalankan snippet apapun, buka file snippet yang ingin Anda jalankan dan ganti variabel kredensial berikut dengan data merchant Anda dari Dashboard Easylink:

- `$appId` / `app_id` : Merchant App ID
- `$appSecret` / `app_secret` : Merchant App Secret
- `$appKey` / `app_key` : Merchant App Key
- `$privateKeyPem` / `private_key_pem` : Path ke file private key RSA Anda (`.pem`) atau string PEM langsung
- `$accessToken` / `access_token` : Access Token yang didapatkan dari endpoint `auth/get_access_token`

---

## ⚡ Panduan Lengkap Menjalankan Snippet

### 🐘 1. PHP (Native cURL)
**Prasyarat:** PHP `>= 7.4` dengan ekstensi `curl` dan `openssl` terinstall.

```bash
# Auth Access Token
php php/auth/get_access_token.php

# Domestic Payout
php php/domestic/01_verify_bank_account.php
php php/domestic/02_create_domestic_transfer.php
php php/domestic/03_get_domestic_transfer_status.php
php php/domestic/04_get_balances.php
php php/domestic/05_get_flows.php
php php/domestic/06_get_remittance_list.php
php php/domestic/07_supported_banks.php
php php/domestic/08_supported_ewallets.php

# International Payout
php php/international/01_get_quotes.php
php php/international/02_create_int_transfer.php
php php/international/03_confirm_int_transfer.php
php php/international/04_get_int_transfer_status.php
php php/international/05_get_countries_currencies.php
php php/international/06_get_currencies.php
php php/international/07_get_remittance_purposes.php
php php/international/08_get_sources_of_funds.php
php php/international/09_get_relationships.php

# Webhook Verification
php php/webhook/verify_signature.php
```

---

### 🟢 2. Node.js / JavaScript
**Prasyarat:** Node.js `>= 18.0`.

```bash
# Auth Access Token
node nodejs/auth/get_access_token.js

# Domestic Payout
node nodejs/domestic/01_verify_bank_account.js
node nodejs/domestic/02_create_domestic_transfer.js

# International Payout
node nodejs/international/01_get_quotes.js
node nodejs/international/02_create_int_transfer.js

# Webhook Verification
node nodejs/webhook/verify_signature.js
```

---

### 🐍 3. Python 3
**Prasyarat:** Python `>= 3.8`. Install package pendukung jika belum ada:
```bash
pip install requests cryptography
```

```bash
# Auth Access Token
python3 python/auth/get_access_token.py

# Domestic Payout
python3 python/domestic/01_verify_bank_account.py
python3 python/domestic/02_create_domestic_transfer.py

# International Payout
python3 python/international/01_get_quotes.py
python3 python/international/02_create_int_transfer.py

# Webhook Verification
python3 python/webhook/verify_signature.py
```

---

### 🔵 4. Go (Golang)
**Prasyarat:** Go `>= 1.18`.

```bash
# Auth Access Token
go run go/auth/helpers.go go/auth/get_access_token.go

# Domestic Payout
go run go/domestic/helpers.go go/domestic/01_verify_bank_account.go
go run go/domestic/helpers.go go/domestic/02_create_domestic_transfer.go

# International Payout
go run go/international/helpers.go go/international/01_get_quotes.go
go run go/international/helpers.go go/international/02_create_int_transfer.go

# Webhook Verification
go run go/webhook/verify_signature.go
```

---

### 🐚 5. cURL / Bash
**Prasyarat:** Terminal Bash & OpenSSL CLI (`openssl`).

```bash
# Auth Access Token
bash curl/auth/get_access_token.sh

# Domestic Payout
bash curl/domestic/01_verify_bank_account.sh
bash curl/domestic/02_create_domestic_transfer.sh

# International Payout
bash curl/international/01_get_quotes.sh
bash curl/international/02_create_int_transfer.sh

# Webhook Verification
bash curl/webhook/verify_signature.sh
```
