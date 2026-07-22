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
 * Requests a fresh B2B Access Token from Easylink API.
 */
async function getAccessToken(baseUrl, appId, appSecret) {
  const response = await sendEasylinkRequest(
    baseUrl,
    '/get-access-token',
    'POST',
    {
      app_id: appId,
      app_secret: appSecret,
    },
    '',
    ''
  );

  if (response.status !== 200) {
    throw new Error(`Failed to get token: ${JSON.stringify(response)}`);
  }

  const data = response.data;
  let token = null;

  if (typeof data.data === 'string') {
    token = data.data;
  } else {
    token = data.accessToken || data.access_token || (data.data && (data.data.accessToken || data.data.access_token));
  }

  if (!token) {
    throw new Error(`Access token not found in response: ${JSON.stringify(data)}`);
  }

  return token;
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
    if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
      for (const [k2, v2] of Object.entries(value)) {
        if (typeof v2 !== 'object' || v2 === null) {
          params[`${key}.${k2}`] = String(v2);
        }
      }
    } else {
      params[key] = String(value);
    }
  }

  const sortedKeys = Object.keys(params).sort();
  const pairs = sortedKeys.map((key) => `${key}=${phpUrlEncode(params[key])}`);
  const originalString = pairs.join('&');
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
  getAccessToken,
  generateEasylinkSignature,
  sendEasylinkRequest,
  phpUrlEncode,
};
