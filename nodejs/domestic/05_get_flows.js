const { sendEasylinkRequest, getAccessToken } = require('../helpers');

const baseUrl       = 'https://sandbox.easylink.id';
const appId         = 'YOUR_APP_ID';
const appSecret     = 'YOUR_APP_SECRET';
const appKey        = 'YOUR_APP_KEY';
const privateKeyPem = `${__dirname}/../../private_key.pem`;

(async () => {
  try {
    const accessToken = await getAccessToken(baseUrl, appId, appSecret);
    const now = new Date();
    const sevenDaysAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);

    const formatDate = (d) => d.toISOString().replace('T', ' ').substring(0, 19);

    const payload = {
      start_time: formatDate(sevenDaysAgo),
      end_time: formatDate(now),
    };

    console.log('Fetching Cash Flows...');
    const res = await sendEasylinkRequest(
      baseUrl,
      '/v2/transfer/get-flow',
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
