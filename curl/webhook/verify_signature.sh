#!/usr/bin/env bash

APP_KEY="YOUR_APP_KEY"
PUBLIC_KEY_PATH="/path/to/easylink_public_key.pem"

NONCE="dummy_nonce"
TIMESTAMP="1700000000000"
SIGNATURE_BASE64="RAW_BASE64_SIGNATURE_FROM_HEADER"

STRING_TO_SIGN="${APP_KEY}X-EasyLink-AppKey=${APP_KEY}&X-EasyLink-Nonce=${NONCE}&X-EasyLink-Timestamp=${TIMESTAMP}&referenceNo=REF-12345&status=SUCCESS${APP_KEY}"

echo -n "${SIGNATURE_BASE64}" | openssl base64 -d -A > /tmp/sig.bin
echo -n "${STRING_TO_SIGN}" | openssl dgst -sha256 -verify "${PUBLIC_KEY_PATH}" -signature /tmp/sig.bin

if [ $? -eq 0 ]; then
  echo "Webhook Signature is VALID!"
else
  echo "Webhook Signature is INVALID!"
fi

rm -f /tmp/sig.bin
