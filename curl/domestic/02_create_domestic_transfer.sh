#!/usr/bin/env bash

BASE_URL="https://sandbox.easylink.id"
APP_KEY="YOUR_APP_KEY"
ACCESS_TOKEN="YOUR_ACCESS_TOKEN"

TIMESTAMP=$(date +%s%3N 2>/dev/null || echo $(($(date +%s)*1000)))
NONCE=$(openssl rand -hex 16)
SIGNATURE="COMPUTED_RSA_SHA256_BASE64_SIGNATURE"

echo "Creating Domestic Transfer via cURL..."

curl -s -X POST "${BASE_URL}/v2/transfer/create-domestic-transfer" \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-EasyLink-AppKey: ${APP_KEY}" \
  -H "X-EasyLink-Nonce: ${NONCE}" \
  -H "X-EasyLink-Timestamp: ${TIMESTAMP}" \
  -H "X-Signature: ${SIGNATURE}" \
  -H "X-EasyLink-Sign: ${SIGNATURE}" \
  -d '{
    "reference": "REF-'$(date +%s)'",
    "amount": 100000,
    "bank_id": "1",
    "account_number": "1234567890",
    "account_holder_name": "John Doe",
    "remark": "Payment for Order #1001"
  }'

echo ""
