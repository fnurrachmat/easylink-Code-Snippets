import time
import secrets
import json
import urllib.parse
import subprocess
import base64
from pathlib import Path

# Try importing requests, fallback to urllib.request
try:
    import requests
    HAS_REQUESTS = True
except ImportError:
    import urllib.request
    import urllib.error
    HAS_REQUESTS = False

# Try importing cryptography, fallback to openssl CLI
try:
    from cryptography.hazmat.primitives import hashes
    from cryptography.hazmat.primitives.asymmetric import padding
    from cryptography.hazmat.primitives.serialization import load_pem_private_key
    HAS_CRYPTOGRAPHY = True
except ImportError:
    HAS_CRYPTOGRAPHY = False

def php_url_encode(val: str) -> str:
    """Matches PHP's urlencode (spaces encoded as '+')."""
    return urllib.parse.quote_plus(str(val))

def get_access_token(base_url: str, app_id: str, app_secret: str) -> str:
    """Requests a fresh B2B Access Token from Easylink API."""
    response = send_easylink_request(
        base_url,
        '/get-access-token',
        'POST',
        {
            'app_id': app_id,
            'app_secret': app_secret
        },
        '',
        ''
    )

    if response['status_code'] != 200:
        raise Exception(f"Failed to get token: {response}")

    data = response['data']
    if isinstance(data.get('data'), str):
        token = data['data']
    else:
        token = data.get('accessToken') or data.get('access_token') or (data.get('data') and (data.get('data').get('accessToken') or data.get('data').get('access_token')))

    if not token:
        raise Exception(f"Access token not found in response: {data}")

    return token

def generate_easylink_signature(app_key: str, nonce: str, timestamp: str, body: dict, private_key_pem: str) -> str:
    """Generates Easylink RSA-SHA256 signature."""
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

    key_path = None
    if "-----BEGIN" not in private_key_pem:
        if Path(private_key_pem).exists():
            key_path = private_key_pem
            private_key_pem = Path(private_key_pem).read_text()
        elif Path("private_key.pem").exists():
            key_path = "private_key.pem"
            private_key_pem = Path("private_key.pem").read_text()

    if HAS_CRYPTOGRAPHY:
        private_key = load_pem_private_key(private_key_pem.encode('utf-8'), password=None)
        signature = private_key.sign(
            string_to_sign.encode('utf-8'),
            padding.PKCS1v15(),
            hashes.SHA256()
        )
        return base64.b64encode(signature).decode('utf-8')
    else:
        temp_key_created = False
        if not key_path:
            key_path = "/tmp/easylink_temp_key.pem"
            Path(key_path).write_text(private_key_pem)
            temp_key_created = True
        try:
            proc = subprocess.Popen(
                ['openssl', 'dgst', '-sha256', '-sign', key_path],
                stdin=subprocess.PIPE,
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE
            )
            stdout, stderr = proc.communicate(input=string_to_sign.encode('utf-8'))
            if proc.returncode != 0:
                raise Exception(f"OpenSSL sign error: {stderr.decode('utf-8')}")
            return base64.b64encode(stdout).decode('utf-8').replace('\n', '').replace('\r', '')
        finally:
            if temp_key_created and Path(key_path).exists():
                Path(key_path).unlink()

def send_easylink_request(base_url: str, endpoint: str, method: str, payload: dict, app_key: str, private_key_pem: str, access_token: str = None) -> dict:
    """Sends HTTP request to Easylink API."""
    url = f"{base_url.rstrip('/')}/{endpoint.lstrip('/')}"
    timestamp = str(int(time.time() * 1000))
    nonce = secrets.token_hex(16)

    headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }

    if access_token:
        headers['Authorization'] = f"Bearer {access_token}"
        signature = generate_easylink_signature(app_key, nonce, timestamp, payload, private_key_pem)
        headers['X-EasyLink-AppKey'] = app_key
        headers['X-EasyLink-Nonce'] = nonce
        headers['X-EasyLink-Timestamp'] = timestamp
        headers['X-Signature'] = signature
        headers['X-EasyLink-Sign'] = signature

    if HAS_REQUESTS:
        resp = requests.request(
            method=method.upper(),
            url=url,
            json=payload if payload and method.upper() != 'GET' else None,
            headers=headers,
            timeout=30
        )
        try:
            data = resp.json()
        except Exception:
            data = resp.text
        return {'status_code': resp.status_code, 'data': data}
    else:
        req_data = json.dumps(payload).encode('utf-8') if payload and method.upper() != 'GET' else None
        req = urllib.request.Request(url, data=req_data, headers=headers, method=method.upper())
        try:
            with urllib.request.urlopen(req, timeout=30) as resp:
                body = resp.read().decode('utf-8')
                try:
                    data = json.loads(body)
                except Exception:
                    data = body
                return {'status_code': resp.status, 'data': data}
        except urllib.error.HTTPError as e:
            body = e.read().decode('utf-8')
            try:
                data = json.loads(body)
            except Exception:
                data = body
            return {'status_code': e.code, 'data': data}
