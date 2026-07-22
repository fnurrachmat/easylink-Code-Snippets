const crypto = require('crypto');
const fs = require('fs');

/**
 * Custom URL encoding to match PHP's urlencode (spaces encoded as '+').
 */
function phpUrlEncode(str) {
  return encodeURIComponent(str)
    .replace(/!/g, '%21')
    .replace(/'/g, '%27')
    .replace(/\(/g, '%28')
    .replace(/\)/g, '%29')
    .replace(/\*/g, '%2A')
    .replace(/%20/g, '+');
}

/**
 * Generates Easylink RSA-SHA256 signature.
 */
function generateEasylinkSignature(appKey, nonce, timestamp, body, privateKeyPem) {
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

  // Sort keys alphabetically by ASCII value
  const sortedKeys = Object.keys(params).sort();

  // Create key=value pairs with urlencode
  const pairs = sortedKeys.map((key) => `${key}=${phpUrlEncode(params[key])}`);
  const originalString = pairs.join('&');

  // Sandwich string with appKey
  const stringToSign = `${appKey}${originalString}${appKey}`;

  let pem = privateKeyPem;
  if (!pem.includes('-----BEGIN') && fs.existsSync(pem)) {
    pem = fs.readFileSync(pem, 'utf8');
  }

  const signer = crypto.createSign('RSA-SHA256');
  signer.update(stringToSign);
  signer.end();

  return signer.sign(pem, 'base64');
}

/**
 * Sends HTTP request to Easylink API.
 */
async function sendEasylinkRequest(baseUrl, endpoint, method, payload, appKey, privateKeyPem, accessToken = null) {
  const url = `${baseUrl.replace(/\/$/, '')}/${endpoint.replace(/^\//, '')}`;
  const timestamp = String(Date.now());
  const nonce = crypto.randomBytes(16).toString('hex');

  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  if (accessToken) {
    headers['Authorization'] = `Bearer ${accessToken}`;
    const signature = generateEasylinkSignature(appKey, nonce, timestamp, payload, privateKeyPem);
    headers['X-EasyLink-AppKey'] = appKey;
    headers['X-EasyLink-Nonce'] = nonce;
    headers['X-EasyLink-Timestamp'] = timestamp;
    headers['X-Signature'] = signature;
    headers['X-EasyLink-Sign'] = signature;
  }

  const options = {
    method: method.toUpperCase(),
    headers,
  };

  if (payload && Object.keys(payload).length > 0 && method.toUpperCase() !== 'GET') {
    options.body = JSON.stringify(payload);
  }

  const response = await fetch(url, options);
  const data = await response.json().catch(() => ({}));

  return {
    status: response.status,
    data,
  };
}

module.exports = {
  generateEasylinkSignature,
  sendEasylinkRequest,
  phpUrlEncode,
};
