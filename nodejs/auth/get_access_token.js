const { sendEasylinkRequest } = require('../helpers');

const baseUrl   = 'https://sandbox.easylink.id';
const appId     = 'YOUR_APP_ID';
const appSecret = 'YOUR_APP_SECRET';

async function getAccessToken(baseUrl, appId, appSecret) {
  const response = await sendEasylinkRequest(
    baseUrl,
    '/get-access-token',
    'POST',
    {
      appId,
      appSecret,
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

(async () => {
  try {
    console.log('Requesting Access Token...');
    const token = await getAccessToken(baseUrl, appId, appSecret);
    console.log('Access Token retrieved successfully:\n', token);
  } catch (error) {
    console.error('Error:', error.message);
  }
})();
