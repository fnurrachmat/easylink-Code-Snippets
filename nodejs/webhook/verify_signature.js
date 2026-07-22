const crypto = require('crypto');
const fs = require('fs');
const { phpUrlEncode } = require('../helpers');

function verifyEasylinkWebhookSignature(
  appKey,
  nonce,
  timestamp,
  body,
  signatureBase64,
  easylinkPublicKeyPem
) {
  const params = {
    'X-EasyLink-AppKey': appKey,
    'X-EasyLink-Nonce': nonce,
    'X-EasyLink-Timestamp': timestamp,
  };

  for (const [key, value] of Object.entries(body || {})) {
    if (typeof value === 'object' && value !== null) {
      params[key] = JSON.stringify(value);
    } else {
      params[key] = String(value);
    }
  }

  const sortedKeys = Object.keys(params).sort();
  const pairs = sortedKeys.map((key) => `${key}=${phpUrlEncode(params[key])}`);
  const originalString = pairs.join('&');
  const stringToSign = `${appKey}${originalString}${appKey}`;

  let pem = easylinkPublicKeyPem;
  if (!pem.includes('-----BEGIN') && fs.existsSync(pem)) {
    pem = fs.readFileSync(pem, 'utf8');
  }

  const verifier = crypto.createVerify('RSA-SHA256');
  verifier.update(stringToSign);
  verifier.end();

  return verifier.verify(pem, signatureBase64, 'base64');
}

const appKey               = 'YOUR_APP_KEY';
const easylinkPublicKeyPem = '/path/to/easylink_public_key.pem';

const nonce           = 'dummy_nonce';
const timestamp       = '1700000000000';
const signatureBase64 = 'DUMMY_SIGNATURE';
const body            = { referenceNo: 'REF-12345', status: 'SUCCESS' };

console.log('Verifying Webhook Signature...');
const isValid = verifyEasylinkWebhookSignature(
  appKey,
  nonce,
  timestamp,
  body,
  signatureBase64,
  easylinkPublicKeyPem
);

if (isValid) {
  console.log('Webhook signature is VALID!');
} else {
  console.log('Invalid Webhook signature!');
}
