from pydantic import BaseModel, EmailStr, Field
from datetime import datetime

# Schéma de base (utilisé en interne)
class JoueurBase(BaseModel):
    email: EmailStr
    username: str = Field(..., max_length=32)

# Schéma pour l’inscription
class JoueurCreate(JoueurBase):
    password: str = Field(..., min_length=8)

# Schéma pour la réponse API (hors infos sensibles)
class JoueurOut(JoueurBase):
    id: int
    is_active: bool
    is_banned: bool
    email_confirmed: bool
    created_at: datetime
    updated_at: datetime | None = None

    class Config:
        from_attributes = True

# Schéma pour le login
class JoueurLogin(BaseModel):
    email: EmailStr
    password: str

# Schéma de sortie après login
class TokenOut(BaseModel):
    access_token: str
    refresh_token: str
    token_type: str = "bearer"
