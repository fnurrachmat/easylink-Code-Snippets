const { sendEasylinkRequest } = require('../helpers');

const baseUrl       = 'https://sandbox.easylink.id';
const appKey        = 'YOUR_APP_KEY';
const privateKeyPem = '/path/to/private.pem';
const accessToken   = 'YOUR_ACCESS_TOKEN';

(async () => {
  const payload = {
    sourceCurrency: 'IDR',
    targetCurrency: 'SGD',
    amount: 1000000,
  };

  console.log('Fetching Quote...');
  try {
    const res = await sendEasylinkRequest(
      baseUrl,
      '/quotes/get-quotes',
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
