const { sendEasylinkRequest, getAccessToken } = require('../helpers');

const baseUrl       = 'https://sandbox.easylink.id';
const appId         = 'lQNJ0nL07Ucmemaa';
const appSecret     = 'HrfFeuRmoyBsZhxDi3w3JNdxwYu19lL4';
const appKey        = '3f9a7f74-de23-4fde-af75-da7684528a59';
const privateKeyPem = `${__dirname}/../../private_key.pem`;

(async () => {
  try {
    const accessToken = await getAccessToken(baseUrl, appId, appSecret);
    const payload = {
      destination_country: 'SG',
      segment: 'business',
      beneficiary_account_type: 'bank_account',
    };

    console.log('Fetching Remittance Purposes...');
    const res = await sendEasylinkRequest(
      baseUrl,
      '/data/get-remittance-purposes',
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
