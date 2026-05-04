import os

class Settings:
    LARAVEL_API_URL = os.getenv("LARAVEL_API_URL")
    INTERNAL_API_KEY = os.getenv("INTERNAL_API_KEY")

    POLLING_INTERVAL = int(os.getenv("POLLING_INTERVAL", 5))
    META_TIMEOUT = int(os.getenv("META_TIMEOUT", 15))
    RETRY_LIMIT = int(os.getenv("RETRY_LIMIT", 3))

    META_APP_ID = os.getenv("META_APP_ID")
    META_APP_SECRET = os.getenv("META_APP_SECRET")
settings = Settings()