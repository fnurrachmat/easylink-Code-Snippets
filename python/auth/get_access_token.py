import sys
from pathlib import Path
sys.path.append(str(Path(__file__).resolve().parent.parent))

from helpers import send_easylink_request

base_url   = 'https://sandbox.easylink.id'
app_id     = 'YOUR_APP_ID'
app_secret = 'YOUR_APP_SECRET'

def get_access_token(base_url: str, app_id: str, app_secret: str) -> str:
    response = send_easylink_request(
        base_url,
        '/get-access-token',
        'POST',
        {'appId': app_id, 'appSecret': app_secret},
        '',
        ''
    )

    if response['status_code'] != 200:
        raise Exception(f"Failed to get token: {response}")

    data = response['data']
    token = data.get('accessToken') or data.get('access_token') or (data.get('data') and data.get('data').get('accessToken'))

    if not token:
        raise Exception("Access token not found in response.")

    return token

if __name__ == '__main__':
    try:
        print("Requesting Access Token...")
        token = get_access_token(base_url, app_id, app_secret)
        print(f"Access Token retrieved successfully:\n{token}")
    except Exception as e:
        print(f"Error: {e}")
