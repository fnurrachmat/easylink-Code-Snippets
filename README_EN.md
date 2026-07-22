# Easylink API Integration — Multi-Language Code Snippets

[![Language: Indonesian](https://img.shields.io/badge/Language-Bahasa_Indonesia-blue.svg)](README.md)
[![Language: English](https://img.shields.io/badge/Language-English-green.svg)](README_EN.md)

This repository contains ready-to-use, standalone **Code Snippets** categorized into **Domestic** and **International** modules to help developers seamlessly integrate with the **Easylink API** across various programming languages without external SDK dependencies.

Supported Languages & Environments:
- 🐘 **PHP** (Native cURL + OpenSSL, zero Composer dependencies)
- 🟢 **Node.js / JavaScript** (Built-in `crypto` module + `fetch`)
- 🐍 **Python 3** (`cryptography` + `requests`)
- 🔵 **Go / Golang** (`crypto/rsa` + `net/http`)
- 🐚 **cURL / Bash** (`curl` + `openssl` CLI)

---

## 📂 Project Directory Structure

Each programming language folder is categorized into the following API subfolders:

```text
.
├── php/
│   ├── helpers.php
│   ├── auth/
│   │   └── get_access_token.php           # Request B2B Access Token
│   ├── domestic/
│   │   ├── 01_verify_bank_account.php      # Bank & E-Wallet Account Inquiry
│   │   ├── 02_create_domestic_transfer.php  # Domestic & E-Wallet Payout
│   │   ├── 03_get_domestic_transfer_status.php # Domestic Transfer Status Query
│   │   ├── 04_get_balances.php             # Merchant Account Balances
│   │   ├── 05_get_flows.php                # Cash Flow & Account Mutations
│   │   ├── 06_get_remittance_list.php      # Remittance Transaction History
│   │   ├── 07_supported_banks.php          # Local Bank Codes List
│   │   └── 08_supported_ewallets.php       # E-Wallet Codes List
│   ├── international/
│   │   ├── 01_get_quotes.php               # Exchange Rate & Quote Inquiry
│   │   ├── 02_create_int_transfer.php      # International Remittance Payout
│   │   ├── 03_confirm_int_transfer.php     # Confirm International Transfer
│   │   ├── 04_get_int_transfer_status.php  # International Status Query
│   │   ├── 05_get_countries_currencies.php # Countries & Currencies List
│   │   ├── 06_get_currencies.php           # Currencies & Minimum Unit List
│   │   ├── 07_get_remittance_purposes.php  # Remittance Purposes List
│   │   ├── 08_get_sources_of_funds.php     # Sources of Funds List
│   │   └── 09_get_relationships.php        # Sender-Recipient Relationships List
│   └── webhook/
│       └── verify_signature.php            # RSA Signature Webhook Callback Verification
├── nodejs/                                # (Identical folder structure as PHP)
├── python/                                # (Identical folder structure as PHP)
├── go/                                    # (Identical folder structure as PHP)
└── curl/                                  # (Identical folder structure as PHP)
```

---

## 🔐 RSA SHA-256 Signature Generation Rules

Every authenticated request sent to the Easylink API requires the following headers:
- `Authorization: Bearer <ACCESS_TOKEN>`
- `X-EasyLink-AppKey: <APP_KEY>`
- `X-EasyLink-Nonce: <UNIQUE_NONCE>`
- `X-EasyLink-Timestamp: <TIMESTAMP_IN_MS>`
- `X-Signature: <BASE64_RSA_SHA256_SIGNATURE>`
- `X-EasyLink-Sign: <BASE64_RSA_SHA256_SIGNATURE>`

### String-to-Sign Formula:
1. Collect header parameters (`X-EasyLink-AppKey`, `X-EasyLink-Nonce`, `X-EasyLink-Timestamp`) and all body payload parameters (nested arrays/objects must be JSON-encoded).
2. Sort all parameters alphabetically based on their **ASCII key values**.
3. Format each pair as `key=urlencode(value)` and join them with `&`.
4. Sandwich the joined string with `AppKey` at both the beginning and end:
   $$\text{stringToSign} = \text{AppKey} + \text{originalString} + \text{AppKey}$$
5. Sign `stringToSign` using **RSA SHA-256** with your merchant RSA Private Key, then **Base64 encode** the result.

---

## ⚙️ Setup & Credentials Configuration

Before running any code snippet, open the snippet file you wish to execute and replace the placeholder credentials with your merchant API credentials from the Easylink Merchant Dashboard:

- `appId` / `app_id`: Merchant App ID
- `appSecret` / `app_secret`: Merchant App Secret
- `appKey` / `app_key`: Merchant App Key
- `privateKeyPem` / `private_key_pem`: Path to your RSA private key file (`.pem`) or the PEM string content
- `accessToken` / `access_token`: Access Token generated via `auth/get_access_token`

---

## ⚡ Complete Execution Guide

### 🐘 1. PHP (Native cURL)
**Prerequisite:** PHP `>= 7.4` with `curl` and `openssl` extensions enabled.

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
**Prerequisite:** Node.js `>= 18.0`.

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
**Prerequisite:** Python `>= 3.8`. Install dependencies:
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
**Prerequisite:** Go `>= 1.18`.

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
**Prerequisite:** Bash Shell & OpenSSL CLI (`openssl`).

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
