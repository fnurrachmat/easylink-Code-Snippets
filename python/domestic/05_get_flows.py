import sys
import json
from datetime import datetime, timedelta
from pathlib import Path
sys.path.append(str(Path(__file__).resolve().parent.parent))

from helpers import send_easylink_request, get_access_token

base_url        = 'https://sandbox.easylink.id'
app_id          = 'YOUR_APP_ID'
app_secret      = 'YOUR_APP_SECRET'
app_key         = 'YOUR_APP_KEY'
private_key_pem = '/path/to/private_key.pem'

now = datetime.now()
seven_days_ago = now - timedelta(days=7)

payload = {
    'start_time': seven_days_ago.strftime('%Y-%m-%d %H:%M:%S'),
    'end_time': now.strftime('%Y-%m-%d %H:%M:%S')
}

print("Fetching Cash Flows...")
try:
    access_token = get_access_token(base_url, app_id, app_secret)
    res = send_easylink_request(
        base_url,
        '/v2/transfer/get-flow',
        'POST',
        payload,
        app_key,
        private_key_pem,
        access_token
    )
    print("Response:\n", json.dumps(res['data'], indent=2))
except Exception as e:
    print("Error:", e)
