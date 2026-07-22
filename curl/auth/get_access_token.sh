#!/usr/bin/env bash

BASE_URL="https://sandbox.easylink.id"
APP_ID="YOUR_APP_ID"
APP_SECRET="YOUR_APP_SECRET"

echo "Requesting Access Token..."

curl -s -X POST "${BASE_URL}/get-access-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "appId": "'"${APP_ID}"'",
    "appSecret": "'"${APP_SECRET}"'"
  }'

echo ""
