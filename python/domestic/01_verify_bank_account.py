import sys
import json
from pathlib import Path
sys.path.append(str(Path(__file__).resolve().parent.parent))

from helpers import send_easylink_request

base_url        = 'https://sandbox.easylink.id'
app_key         = 'YOUR_APP_KEY'
private_key_pem = '/path/to/private.pem'
access_token    = 'YOUR_ACCESS_TOKEN'

payload = {
    'bankCode': 'BCA',
    'accountNumber': '1234567890'
}

print("Verifying Bank Account...")
try:
    res = send_easylink_request(
        base_url,
        '/v2/transfer/verify-bank-account',
        'POST',
        payload,
        app_key,
        private_key_pem,
        access_token
    )
    print("Response:\n", json.dumps(res['data'], indent=2))
except Exception as e:
    print("Error:", e)
