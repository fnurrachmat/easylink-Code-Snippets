import sys
import time
import json
from pathlib import Path
sys.path.append(str(Path(__file__).resolve().parent.parent))

from helpers import send_easylink_request, get_access_token

base_url        = 'https://sandbox.easylink.id'
app_id          = 'YOUR_APP_ID'
app_secret      = 'YOUR_APP_SECRET'
app_key         = 'YOUR_APP_KEY'
private_key_pem = '/path/to/private_key.pem'

payload = {
    'reference': f"INT-{int(time.time())}",
    'source': {
        'country': 'IDN',
        'currency': 'IDR',
        'segment': 'business',
        'company_name': 'PT Merchant Indonesia',
        'company_trading_name': 'Merchant ID',
        'company_registration_number': '123456789',
        'company_registration_country': 'IDN',
        'address_line': 'Jl. Sudirman No. 1',
        'address_city': 'Jakarta',
        'address_country': 'IDN'
    },
    'destination': {
        'country': 'SGP',
        'currency': 'SGD',
        'segment': 'business',
        'beneficiary_account_type': 'Bank Account',
        'company_name': 'DBS Bank Corporate',
        'swift_code': 'DBSSSGSG',
        'bank_account_number': '9876543210',
        'address_line': '12 Marina Boulevard',
        'address_city': 'Singapore',
        'address_country': 'SGP',
        'source_of_income_code': '01',
        'purpose_code': '01',
        'relation_code': '04'
    },
    'transaction': {
        'amount': 1000000,
        'destination_amount': 35,
        'destination_country': 'SGP',
        'destination_currency': 'SGD'
    }
}

print("Creating International Transfer...")
try:
    access_token = get_access_token(base_url, app_id, app_secret)
    res = send_easylink_request(
        base_url,
        '/transfer/create-international-transfer',
        'POST',
        payload,
        app_key,
        private_key_pem,
        access_token
    )
    print("Response:\n", json.dumps(res['data'], indent=2))
except Exception as e:
    print("Error:", e)
