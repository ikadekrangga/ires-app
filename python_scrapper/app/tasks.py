import httpx
from app.services.instagram_service import InstagramGraphAPIClient

LARAVEL_URL = "http://host.docker.internal:9000/api/internal"

async def sync_id(page_ids: list, token:str):
    client = InstagramGraphAPIClient(access_token=token)
    
    async with httpx.AsyncClient() as http_client:
        for p_id in page_ids:
            res = await client.get_instagram_id_dynamic(p_id)
            if res['success']:
                payload = {
                    "fb_pageId" : p_id,
                    "ig_pageId" : res['ig_id'],
                    "ig_username" : res['ig_username']
                }
                await http_client.post(f"{LARAVEL_URL}/update-account", json=payload)

async def fetch_insight(ig_ids:list, token:str):
    client = InstagramGraphAPIClient(access_token=token)
    async with httpx.AsyncClient() as http_client:
        for ig_id in ig_ids:
            res = await client.get_account_insight_bulk(ig_id)
            if res['success']:
                payload = {"ig_pageId" : ig_id, "metrics" : res['metrics']}
                await http_client.post(f"{LARAVEL_URL}/store-insights", json=payload)