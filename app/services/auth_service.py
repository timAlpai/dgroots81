# app/services/auth_service.py

from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from typing import Optional

from app.db.models.user import User
from app.schemas.user import UserCreate
from app.core.security import verify_password, create_access_token
from passlib.context import CryptContext

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

class AuthService:
    def __init__(self, db: AsyncSession):
        self.db = db

    def get_password_hash(self, password: str):
        return pwd_context.hash(password)

    async def create_user(self, user_data: UserCreate) -> Optional[User]:
        result = await self.db.execute(select(User).where(User.email == user_data.email))
        existing_user = result.scalar_one_or_none()
        if existing_user:
            return None # Indique que l'email est déjà enregistré

        new_user = User(
            email=user_data.email,
            password_hash=self.get_password_hash(user_data.password),
            credit_balance=0
        )
        self.db.add(new_user)
        await self.db.commit()
        await self.db.refresh(new_user)
        return new_user

    async def authenticate_user(self, email: str, password: str) -> Optional[User]:
        result = await self.db.execute(select(User).where(User.email == email))
        user = result.scalar_one_or_none()
        if not user or not verify_password(password, user.password_hash):
            return None
        return user

    def create_access_token_for_user(self, user_id: int) -> str:
        return create_access_token(data={"sub": str(user_id)})