import time
import secrets
import json
import urllib.parse
from pathlib import Path
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.primitives.asymmetric import padding
from cryptography.hazmat.primitives.serialization import load_pem_private_key
import requests

def php_url_encode(val: str) -> str:
    """Matches PHP's urlencode (spaces encoded as '+')."""
    return urllib.parse.quote_plus(str(val))

def generate_easylink_signature(app_key: str, nonce: str, timestamp: str, body: dict, private_key_pem: str) -> str:
    """Generates Easylink RSA-SHA256 signature."""
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

    # Sort keys alphabetically by ASCII
    sorted_keys = sorted(params.keys())
    pairs = [f"{k}={php_url_encode(params[k])}" for k in sorted_keys]
    original_string = "&".join(pairs)

    string_to_sign = f"{app_key}{original_string}{app_key}"

    if "-----BEGIN" not in private_key_pem and Path(private_key_pem).exists():
        private_key_pem = Path(private_key_pem).read_text()

    private_key = load_pem_private_key(private_key_pem.encode('utf-8'), password=None)
    signature = private_key.sign(
        string_to_sign.encode('utf-8'),
        padding.PKCS1v15(),
        hashes.SHA256()
    )

    import base64
    return base64.b64encode(signature).decode('utf-8')

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

    return {
        'status_code': resp.status_code,
        'data': data
    }
