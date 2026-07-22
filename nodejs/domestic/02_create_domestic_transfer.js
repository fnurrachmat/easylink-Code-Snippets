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
      reference: `REF-${Date.now()}`,
      amount: 100000,
      bank_id: '1',
      account_number: '1234567890',
      account_holder_name: 'John Doe',
      remark: 'Payment for Order #1001',
    };

    console.log('Creating Domestic Transfer...');
    const res = await sendEasylinkRequest(
      baseUrl,
      '/v2/transfer/create-domestic-transfer',
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
