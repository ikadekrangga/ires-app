import os
import httpx
from dotenv import load_dotenv

load_dotenv()

class InstagramGraphAPIClient:
    def __init__(self, access_token: str):
        self.access_token = access_token
        self.base_url = "https://graph.facebook.com/v24.0"
        
    
    async def get_instagram_id_dynamic(self, page_id : str) -> dict :
        """Mencari Instagram Business ID secara dinamis berdasarkan PAGE ID.
           Digunakan Laravel untuk mengisi kolom ig_pageId yang sebelumnya adalah NULL"""
        
        url = f"{self.base_url}/{page_id}"
        
        params = {
            "fields" : "instagram_business_account{id, username}, name",
            "access_token" : self.access_token
        }
        
        try:
            async with httpx.AsyncClient() as client :
                response = await client.get(url, params = params)
                ig_res = response.json()
            
            if 'error' in ig_res :
                return {
                    "success" : False,
                    "message" : ig_res['error'].get('message' ,'API ERROR'),
                    "code" : ig_res['error'].get('code')
                }
            
            ig_account = ig_res.get("instagram_business_account")
            if not ig_account:
                return{
                    "success" : False,
                    "message" : f"Page '{ig_res.get('name', page_id)}' tidak terhubung dengan Instagram"
                }
            
            return{
                "success" : True,
                "ig_id"   : ig_account['id'],
                "ig_username" : ig_account.get('username'),
                "page_name" : ig_res.get('name')
            }
        except Exception as e:
            return {"success" : False, "message" : f"Koneksi Meta Gagal : {str(e)}"}
        
    async def get_account_insight_bulk(self, ig_id : str) -> dict:
        """
        Mengambil metrik utama untuk laporan mingguan Indosat"""
        
        endpoint = f"{self.base_url}/{ig_id}/insights"
        params = {
            "metric" : "impressions, reach, profile_views",
            "period" : "day",
            "access_token" : self.access_token
        }   
        
        try :
            async with httpx.AsyncClient() as client:
                response = await client.get(endpoint, params = params)
                res = response.json()
            
            if 'error' in res:
                return { 
                        "success" : False,
                        "message" : res['error'].geT('message', 'API ERROR')
                }
            
            #Format data agar laravel mudah melakukan pengulangan
            
            raw_data = res.get("data" , [])
            formatted_metrics = {}
            for metric in raw_data :
                name = metric['name']
                value = metric['values'][0]['value'] if metric['values'] else 0
                formatted_metrics[name] = value
                
            return {
                "success" : True,
                "ig_id" : ig_id,
                "metrics" : formatted_metrics
            }
            
        except Exception as e :
            return{"success" : False, "message" : f"Gagal mendapatkan insight : {str(e)}"}