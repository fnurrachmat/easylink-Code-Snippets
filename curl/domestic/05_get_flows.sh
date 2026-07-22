#!/usr/bin/env bash

BASE_URL="https://sandbox.easylink.id"
APP_KEY="YOUR_APP_KEY"
ACCESS_TOKEN="YOUR_ACCESS_TOKEN"

TIMESTAMP=$(date +%s%3N 2>/dev/null || echo $(($(date +%s)*1000)))
NONCE=$(openssl rand -hex 16)
SIGNATURE="COMPUTED_RSA_SHA256_BASE64_SIGNATURE"

START_TIME=$(date -v-7d +'%Y-%m-%d %H:%M:%S' 2>/dev/null || date -d '7 days ago' +'%Y-%m-%d %H:%M:%S')
END_TIME=$(date +'%Y-%m-%d %H:%M:%S')

echo "Fetching Cash Flows via cURL..."

curl -s -X POST "${BASE_URL}/v2/transfer/get-flow" \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-EasyLink-AppKey: ${APP_KEY}" \
  -H "X-EasyLink-Nonce: ${NONCE}" \
  -H "X-EasyLink-Timestamp: ${TIMESTAMP}" \
  -H "X-Signature: ${SIGNATURE}" \
  -H "X-EasyLink-Sign: ${SIGNATURE}" \
  -d '{
    "start_time": "'"${START_TIME}"'",
    "end_time": "'"${END_TIME}"'"
  }'

echo ""
