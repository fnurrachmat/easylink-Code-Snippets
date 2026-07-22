const { sendEasylinkRequest } = require('../helpers');

const baseUrl       = 'https://sandbox.easylink.id';
const appKey        = 'YOUR_APP_KEY';
const privateKeyPem = '/path/to/private.pem';
const accessToken   = 'YOUR_ACCESS_TOKEN';

(async () => {
  const payload = {
    partnerReferenceNo: `INT-${Date.now()}`,
    senderCountry: 'ID',
    receiverCountry: 'SG',
    sourceCurrency: 'IDR',
    targetCurrency: 'SGD',
    amount: 1000000,
    recipientName: 'Alice Smith',
    accountNumber: '9876543210',
    bankName: 'DBS Bank',
    swiftCode: 'DBSSSGSG',
  };

  console.log('Creating International Transfer...');
  try {
    const res = await sendEasylinkRequest(
      baseUrl,
      '/transfer/create-international-transfer',
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
