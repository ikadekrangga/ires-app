from pydantic import BaseModel, Field
from typing import List, Optional

class InsightRequest(BaseModel):
    access_token : str =  Field(..., min_length=1, description="Long-lived Access Token akun Instagram.")
    page_ids : Optional[List[str]] = None
    instagram_business_ids: Optional[List[str]] = None