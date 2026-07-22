#!/usr/bin/env bash

BASE_URL="https://sandbox.easylink.id"
APP_KEY="YOUR_APP_KEY"
ACCESS_TOKEN="YOUR_ACCESS_TOKEN"

TIMESTAMP=$(date +%s%3N 2>/dev/null || echo $(($(date +%s)*1000)))
NONCE=$(openssl rand -hex 16)
SIGNATURE="COMPUTED_RSA_SHA256_BASE64_SIGNATURE"

echo "Creating International Transfer via cURL..."

curl -s -X POST "${BASE_URL}/transfer/create-international-transfer" \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-EasyLink-AppKey: ${APP_KEY}" \
  -H "X-EasyLink-Nonce: ${NONCE}" \
  -H "X-EasyLink-Timestamp: ${TIMESTAMP}" \
  -H "X-Signature: ${SIGNATURE}" \
  -H "X-EasyLink-Sign: ${SIGNATURE}" \
  -d '{
    "reference": "INT-'$(date +%s)'",
    "source": {
      "country": "IDN",
      "currency": "IDR",
      "segment": "business",
      "company_name": "PT Merchant Indonesia",
      "company_trading_name": "Merchant ID",
      "company_registration_number": "123456789",
      "company_registration_country": "IDN",
      "address_line": "Jl. Sudirman No. 1",
      "address_city": "Jakarta",
      "address_country": "IDN"
    },
    "destination": {
      "country": "SGP",
      "currency": "SGD",
      "segment": "business",
      "beneficiary_account_type": "Bank Account",
      "company_name": "DBS Bank Corporate",
      "swift_code": "DBSSSGSG",
      "bank_account_number": "9876543210",
      "address_line": "12 Marina Boulevard",
      "address_city": "Singapore",
      "address_country": "SGP",
      "source_of_income_code": "01",
      "purpose_code": "01",
      "relation_code": "04"
    },
    "transaction": {
      "amount": 1000000,
      "destination_amount": 35,
      "destination_country": "SGP",
      "destination_currency": "SGD"
    }
  }'

echo ""
