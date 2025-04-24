from pydantic import BaseModel, EmailStr
from datetime import datetime

class UserCreate(BaseModel):
    email: EmailStr
    password: str

class UserOut(BaseModel):
    id: int
    email: EmailStr
    credit_balance: int
    is_admin: bool
    is_banned: bool
    created_at: datetime

    class Config:
        from_attributes = True
