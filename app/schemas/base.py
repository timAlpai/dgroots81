from pydantic import BaseModel, Field, ConfigDict
from datetime import datetime
from typing import Optional

class BaseSchema(BaseModel):
    """Schéma de base pour tous les modèles Pydantic"""
    id: Optional[int] = None
    created_at: Optional[datetime] = None
    updated_at: Optional[datetime] = None
    
    model_config = ConfigDict(
        from_attributes=True,
        populate_by_name=True
    )