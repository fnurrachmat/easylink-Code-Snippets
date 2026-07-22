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
    'partnerReferenceNo': f"INT-{int(time.time())}",
    'senderCountry': 'ID',
    'receiverCountry': 'SG',
    'sourceCurrency': 'IDR',
    'targetCurrency': 'SGD',
    'amount': 1000000,
    'recipientName': 'Alice Smith',
    'accountNumber': '9876543210',
    'bankName': 'DBS Bank',
    'swiftCode': 'DBSSSGSG'
}

print("Creating International Transfer...")
try:
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
