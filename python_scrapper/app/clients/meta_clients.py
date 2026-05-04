import httpx
from app.core.config import settings
from app.core.exceptions import RetryableError, PermanentError, TokenExpiredError
from datetime import datetime, timedelta
import pytz


class MetaClient:

    BASE_URL = "https://graph.facebook.com/v24.0"

    def __init__(self):
        self.timeout = httpx.Timeout(10.0, connect=5.0)

    def fetch_period_insights(self, ig_id, access_token):
        url = f"{self.BASE_URL}/{ig_id}/insights"

        tz = pytz.timezone("Asia/Jakarta")
        today = datetime.now(tz).date()
        since = today - timedelta(days=7)

        params = {
            "metric": "reach,views,likes,comments,follows_and_unfollows",
            "period": "day",
            "since": since.isoformat(),
            "until": today.isoformat(),
            "access_token": access_token,
        }

        try:
            r = httpx.get(url, params=params, timeout=self.timeout)
        except Exception as e:
            raise RetryableError(f"HTTP request failed: {str(e)}")

        print("\n=== META DEBUG ===")
        print("URL:", r.request.url)
        print("STATUS:", r.status_code)
        print("BODY:", r.text[:500])
        print("==================\n")

        if r.status_code == 429:
            raise RetryableError("Rate limit")

        if r.status_code != 200:
            self.handle_graph_error(r)

        try:
            payload = r.json()
        except Exception:
            raise RetryableError("Invalid JSON response")

        if "data" not in payload:
            raise RetryableError(f"Invalid response structure: {payload}")

        # SAFE PARSING
        metrics = {
            "reach": 0,
            "views": 0,
            "likes": 0,
            "comments": 0,
            "follows_and_unfollows": 0
        }

        for item in payload.get("data", []):
            name = item.get("name")
            values = item.get("values", [])

            if not values:
                continue

            value = values[0].get("value", 0)

            if name in metrics:
                metrics[name] = value

        return {
            "metrics": metrics,
            "since": since.isoformat(),
            "until": today.isoformat()
        }

    def exchange_long_lived_token(self, short_token):
        url = f"{self.BASE_URL}/oauth/access_token"

        params = {
            "grant_type": "fb_exchange_token",
            "client_id": settings.META_APP_ID,
            "client_secret": settings.META_APP_SECRET,
            "fb_exchange_token": short_token
        }

        r = httpx.get(url, params=params, timeout=self.timeout)

        if r.status_code != 200:
            raise Exception(f"Token exchange failed: {r.text}")

        data = r.json()

        return {
            "access_token": data.get("access_token"),
            "expires_in": data.get("expires_in")
        }

    def handle_graph_error(self, response):
        try:
            error = response.json().get("error", {})
        except Exception:
            raise RetryableError("Unknown Meta error")

        code = error.get("code")
        message = error.get("message", "")

        print("META ERROR:", response.text)

        if code in [4, 17, 32]:
            raise RetryableError(f"Rate limit: {message}")

        if code == 190:
            if "expired" in message.lower():
                raise TokenExpiredError(message)
            raise PermanentError(message)

        if code in [10, 100]:
            raise PermanentError(message)

        raise RetryableError(message)