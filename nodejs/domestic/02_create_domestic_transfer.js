const { sendEasylinkRequest } = require('../helpers');

const baseUrl       = 'https://sandbox.easylink.id';
const appKey        = 'YOUR_APP_KEY';
const privateKeyPem = '/path/to/private.pem';
const accessToken   = 'YOUR_ACCESS_TOKEN';

(async () => {
  const payload = {
    partnerReferenceNo: `REF-${Date.now()}`,
    amount: 100000,
    bankCode: 'BCA',
    accountNumber: '1234567890',
    recipientName: 'John Doe',
    remark: 'Payment for Order #1001',
  };

  console.log('Creating Domestic Transfer...');
  try {
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
