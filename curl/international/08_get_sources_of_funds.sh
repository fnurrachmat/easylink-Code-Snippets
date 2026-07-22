#!/usr/bin/env bash

BASE_URL="https://sandbox.easylink.id"
APP_KEY="3f9a7f74-de23-4fde-af75-da7684528a59"
ACCESS_TOKEN="YOUR_ACCESS_TOKEN"

TIMESTAMP=$(date +%s%3N 2>/dev/null || echo $(($(date +%s)*1000)))
NONCE=$(openssl rand -hex 16)
SIGNATURE="COMPUTED_RSA_SHA256_BASE64_SIGNATURE"

echo "Fetching Sources of Funds via cURL..."

curl -s -X POST "${BASE_URL}/data/get-sources-of-funds" \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-EasyLink-AppKey: ${APP_KEY}" \
  -H "X-EasyLink-Nonce: ${NONCE}" \
  -H "X-EasyLink-Timestamp: ${TIMESTAMP}" \
  -H "X-Signature: ${SIGNATURE}" \
  -H "X-EasyLink-Sign: ${SIGNATURE}" \
  -d '{
    "destination_country": "SG",
    "segment": "business"
  }'

echo ""
