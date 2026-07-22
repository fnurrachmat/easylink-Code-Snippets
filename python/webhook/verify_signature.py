import sys
import base64
import json
from pathlib import Path
sys.path.append(str(Path(__file__).resolve().parent.parent))

from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.primitives.asymmetric import padding
from cryptography.hazmat.primitives.serialization import load_pem_public_key
from helpers import php_url_encode

def verify_easylink_webhook_signature(
    app_key: str,
    nonce: str,
    timestamp: str,
    body: dict,
    signature_base64: str,
    easylink_public_key_pem: str
) -> bool:
    params = {
        'X-EasyLink-AppKey': app_key,
        'X-EasyLink-Nonce': nonce,
        'X-EasyLink-Timestamp': timestamp,
    }

    for k, v in (body or {}).items():
        if isinstance(v, (dict, list)):
            params[k] = json.dumps(v, separators=(',', ':'))
        else:
            params[k] = str(v)

    sorted_keys = sorted(params.keys())
    pairs = [f"{k}={php_url_encode(params[k])}" for k in sorted_keys]
    original_string = "&".join(pairs)
    string_to_sign = f"{app_key}{original_string}{app_key}"

    if "-----BEGIN" not in easylink_public_key_pem and Path(easylink_public_key_pem).exists():
        easylink_public_key_pem = Path(easylink_public_key_pem).read_text()

    public_key = load_pem_public_key(easylink_public_key_pem.encode('utf-8'))
    raw_signature = base64.b64decode(signature_base64)

    try:
        public_key.verify(
            raw_signature,
            string_to_sign.encode('utf-8'),
            padding.PKCS1v15(),
            hashes.SHA256()
        )
        return True
    except Exception:
        return False

app_key                 = 'YOUR_APP_KEY'
easylink_public_key_pem = '/path/to/easylink_public_key.pem'

nonce            = 'dummy_nonce'
timestamp        = '1700000000000'
signature_base64 = 'DUMMY_SIGNATURE'
body             = {'referenceNo': 'REF-12345', 'status': 'SUCCESS'}

print("Verifying Webhook Signature...")
is_valid = verify_easylink_webhook_signature(
    app_key,
    nonce,
    timestamp,
    body,
    signature_base64,
    easylink_public_key_pem
)

if is_valid:
    print("Webhook signature is VALID!")
else:
    print("Invalid Webhook signature!")
