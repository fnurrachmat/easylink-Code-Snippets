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
      reference: `INT-${Date.now()}`,
      source: {
        country: 'IDN',
        currency: 'IDR',
        segment: 'business',
        company_name: 'PT Merchant Indonesia',
        company_trading_name: 'Merchant ID',
        company_registration_number: '123456789',
        company_registration_country: 'IDN',
        address_line: 'Jl. Sudirman No. 1',
        address_city: 'Jakarta',
        address_country: 'IDN',
      },
      destination: {
        country: 'SGP',
        currency: 'SGD',
        segment: 'business',
        beneficiary_account_type: 'Bank Account',
        company_name: 'DBS Bank Corporate',
        swift_code: 'DBSSSGSG',
        bank_account_number: '9876543210',
        address_line: '12 Marina Boulevard',
        address_city: 'Singapore',
        address_country: 'SGP',
        source_of_income_code: '01',
        purpose_code: '01',
        relation_code: '04',
      },
      transaction: {
        amount: 1000000,
        destination_amount: 35,
        destination_country: 'SGP',
        destination_currency: 'SGD',
      },
    };

    console.log('Creating International Transfer...');
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
