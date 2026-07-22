#!/usr/bin/env bash

BASE_URL="https://sandbox.easylink.id"
APP_ID="lQNJ0nL07Ucmemaa"
APP_SECRET="HrfFeuRmoyBsZhxDi3w3JNdxwYu19lL4"

echo "Requesting Access Token..."

curl -s -X POST "${BASE_URL}/get-access-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "app_id": "'"${APP_ID}"'",
    "app_secret": "'"${APP_SECRET}"'"
  }'

echo ""
