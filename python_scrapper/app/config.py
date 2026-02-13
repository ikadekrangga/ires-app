import os
from dotenv import load_dotenv

load_dotenv()

class Settings:
    ACCESS_TOKEN : str = os.getenv("ACCESS_TOKEN", "")
    FB_VERSION_APP : str = os.getenv("FB_API_VERSION", "")

Settings()