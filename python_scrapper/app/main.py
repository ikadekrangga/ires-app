from fastapi import FastAPI, HTTPException, BackgroundTasks
from app.models import InsightRequest
import uvicorn
from app.tasks import sync_id as run_sync, fetch_insight as run_fetch

app = FastAPI(
    title=("Instagram Insights API"),
    description=("Services khusus penarik data Meta Graph API untuk laravel.")
)

LARAVEL_INTERNAL_URL = "http://127.0.0.1:8000/api/internal"

@app.get("/")
def root():
    return{"status" : "online", "message" : "Python Scrapper Server is running!"}

@app.post("/instagram/sync-ids")
async def handle_sync_ids(request: InsightRequest, background_tasks: BackgroundTasks):
    # Memanggil fungsi yang ada di folder tasks
    background_tasks.add_task(run_sync, request.page_ids, request.access_token)
    return {"message": "Sync Process started in background"}

@app.post("/instagram/fetch-insights")
async def handle_fetch_insights(request: InsightRequest, background_tasks: BackgroundTasks):
    # Memanggil fungsi yang ada di folder tasks
    background_tasks.add_task(run_fetch, request.instagram_business_ids, request.access_token)
    return {"message": "Fetching process started in background"}
    