const { sendEasylinkRequest, getAccessToken } = require('../helpers');

const baseUrl       = 'https://sandbox.easylink.id';
const appId         = 'YOUR_APP_ID';
const appSecret     = 'YOUR_APP_SECRET';
const appKey        = 'YOUR_APP_KEY';
const privateKeyPem = `${__dirname}/../../private_key.pem`;

(async () => {
  try {
    const accessToken = await getAccessToken(baseUrl, appId, appSecret);
    const payload = {
      segment: 'business',
    };

    console.log('Fetching Relationships...');
    const res = await sendEasylinkRequest(
      baseUrl,
      '/data/get-relationships',
      'POST',
      payload,
      appKey,
      privateKeyPem,
      accessToken
    );
    console.log('Response:\n', JSON.stringify(res.data, null, 2));
  } catch (err) {
    console.error('Error:', err.message);
  }
})();
