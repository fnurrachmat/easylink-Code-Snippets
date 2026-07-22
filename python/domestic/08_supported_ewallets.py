import sys
import json
from pathlib import Path
sys.path.append(str(Path(__file__).resolve().parent.parent))

from helpers import send_easylink_request, get_access_token

base_url        = 'https://sandbox.easylink.id'
app_id          = 'lQNJ0nL07Ucmemaa'
app_secret      = 'HrfFeuRmoyBsZhxDi3w3JNdxwYu19lL4'
app_key         = '3f9a7f74-de23-4fde-af75-da7684528a59'
private_key_pem = str(Path(__file__).resolve().parent.parent.parent / 'private_key.pem')

print("Fetching Supported E-Wallet Codes...")
try:
    access_token = get_access_token(base_url, app_id, app_secret)
    res = send_easylink_request(
        base_url,
        '/v2/data/supported-inst-code',
        'POST',
        {},
        app_key,
        private_key_pem,
        access_token
    )
    print("Response:\n", json.dumps(res['data'], indent=2))
except Exception as e:
    print("Error:", e)
