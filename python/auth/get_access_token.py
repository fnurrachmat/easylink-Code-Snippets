import sys
from pathlib import Path
sys.path.append(str(Path(__file__).resolve().parent.parent))

from helpers import send_easylink_request

base_url   = 'https://sandbox.easylink.id'
app_id     = 'lQNJ0nL07Ucmemaa'
app_secret = 'HrfFeuRmoyBsZhxDi3w3JNdxwYu19lL4'

def get_access_token(base_url: str, app_id: str, app_secret: str) -> str:
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

if __name__ == '__main__':
    try:
        print("Requesting Access Token...")
        token = get_access_token(base_url, app_id, app_secret)
        print(f"Access Token retrieved successfully:\n{token}")
    except Exception as e:
        print(f"Error: {e}")
