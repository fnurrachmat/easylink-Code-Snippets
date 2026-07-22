import sys
import base64
import json
import time
import subprocess
from pathlib import Path
sys.path.append(str(Path(__file__).resolve().parent.parent))

from helpers import php_url_encode

try:
    from cryptography.hazmat.primitives import hashes
    from cryptography.hazmat.primitives.asymmetric import padding
    from cryptography.hazmat.primitives.serialization import load_pem_public_key, load_pem_private_key
    HAS_CRYPTOGRAPHY = True
except ImportError:
    HAS_CRYPTOGRAPHY = False

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
        if isinstance(v, dict):
            for k2, v2 in v.items():
                if not isinstance(v2, (dict, list)):
                    params[f"{k}.{k2}"] = str(v2)
        else:
            params[k] = str(v)

    sorted_keys = sorted(params.keys())
    pairs = [f"{k}={php_url_encode(params[k])}" for k in sorted_keys]
    original_string = "&".join(pairs)
    string_to_sign = f"{app_key}{original_string}{app_key}"

    pub_key_path = None
    if "-----BEGIN" not in easylink_public_key_pem:
        if Path(easylink_public_key_pem).exists():
            pub_key_path = easylink_public_key_pem
            easylink_public_key_pem = Path(easylink_public_key_pem).read_text()
        elif Path("private_key.pem").exists():
            pub_key_path = "private_key.pem"
            easylink_public_key_pem = Path("private_key.pem").read_text()

    if HAS_CRYPTOGRAPHY:
        try:
            public_key = load_pem_public_key(easylink_public_key_pem.encode('utf-8'))
        except Exception:
            # If public key load failed, try private key extract
            try:
                priv = load_pem_private_key(easylink_public_key_pem.encode('utf-8'), password=None)
                public_key = priv.public_key()
            except Exception:
                return False

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
    else:
        # Fallback to OpenSSL CLI
        temp_pub_created = False
        if not pub_key_path:
            pub_key_path = "/tmp/easylink_temp_pub.pem"
            Path(pub_key_path).write_text(easylink_public_key_pem)
            temp_pub_created = True

        sig_bin_path = "/tmp/easylink_temp_sig.bin"
        Path(sig_bin_path).write_bytes(base64.b64decode(signature_base64))

        try:
            proc = subprocess.Popen(
                ['openssl', 'dgst', '-sha256', '-verify', pub_key_path, '-signature', sig_bin_path],
                stdin=subprocess.PIPE,
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE
            )
            stdout, stderr = proc.communicate(input=string_to_sign.encode('utf-8'))
            return proc.returncode == 0
        finally:
            if temp_pub_created and Path(pub_key_path).exists():
                Path(pub_key_path).unlink()
            if Path(sig_bin_path).exists():
                Path(sig_bin_path).unlink()

# --- Example Webhook Notification Handler & Demo Execution ---
app_key         = '3f9a7f74-de23-4fde-af75-da7684528a59'
private_key_pem = str(Path(__file__).resolve().parent.parent.parent / 'private_key.pem')

if not Path(private_key_pem).exists():
    private_key_pem = 'private_key.pem'

nonce     = 'dummy_nonce_12345'
timestamp = str(int(time.time() * 1000))
body      = {'referenceNo': 'REF-12345', 'status': 'SUCCESS'}

from helpers import generate_easylink_signature
signature_base64 = generate_easylink_signature(app_key, nonce, timestamp, body, private_key_pem)

print("Verifying Webhook Signature...")
is_valid = verify_easylink_webhook_signature(
    app_key,
    nonce,
    timestamp,
    body,
    signature_base64,
    private_key_pem
)

if is_valid:
    print("Webhook signature is VALID!")
else:
    print("Invalid Webhook signature!")
