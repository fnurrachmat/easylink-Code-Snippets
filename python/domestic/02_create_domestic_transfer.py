import sys
import time
import json
from pathlib import Path
sys.path.append(str(Path(__file__).resolve().parent.parent))

from helpers import send_easylink_request

base_url        = 'https://sandbox.easylink.id'
app_key         = 'YOUR_APP_KEY'
private_key_pem = '/path/to/private.pem'
access_token    = 'YOUR_ACCESS_TOKEN'

payload = {
    'partnerReferenceNo': f"REF-{int(time.time())}",
    'amount': 100000,
    'bankCode': 'BCA',
    'accountNumber': '1234567890',
    'recipientName': 'John Doe',
    'remark': 'Payment for Order #1001'
}

print("Creating Domestic Transfer...")
try:
    res = send_easylink_request(
        base_url,
        '/v2/transfer/create-domestic-transfer',
        'POST',
        payload,
        app_key,
        private_key_pem,
        access_token
    )
    print("Response:\n", json.dumps(res['data'], indent=2))
except Exception as e:
    print("Error:", e)
