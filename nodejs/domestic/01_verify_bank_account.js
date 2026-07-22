const { sendEasylinkRequest } = require('../helpers');

const baseUrl       = 'https://sandbox.easylink.id';
const appKey        = 'YOUR_APP_KEY';
const privateKeyPem = '/path/to/private.pem';
const accessToken   = 'YOUR_ACCESS_TOKEN';

(async () => {
  const payload = {
    bankCode: 'BCA',
    accountNumber: '1234567890',
  };

  console.log('Verifying Bank Account...');
  try {
    const res = await sendEasylinkRequest(
      baseUrl,
      '/v2/transfer/verify-bank-account',
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
