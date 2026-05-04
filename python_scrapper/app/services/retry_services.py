import time
from app.core.config import settings


def should_retry(attempt):
    return attempt < settings.RETRY_LIMIT

def backoff_sleep(attempt):
    sleep_time = 2 ** attempt
    time.sleep(sleep_time)