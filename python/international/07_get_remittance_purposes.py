import sys
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
    'destination_country': 'SG',
    'segment': 'business',
    'beneficiary_account_type': 'bank_account'
}

print("Fetching Remittance Purposes...")
try:
    access_token = get_access_token(base_url, app_id, app_secret)
    res = send_easylink_request(
        base_url,
        '/data/get-remittance-purposes',
        'POST',
        payload,
        app_key,
        private_key_pem,
        access_token
    )
    print("Response:\n", json.dumps(res['data'], indent=2))
except Exception as e:
    print("Error:", e)
