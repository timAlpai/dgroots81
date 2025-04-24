import asyncio
from app.core.database import get_db, engine
from app.db.models.user import User
from sqlalchemy.future import select
from sqlalchemy.ext.asyncio import AsyncSession
from passlib.context import CryptContext

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

ADMIN_EMAIL = "dgroots81@mandragore.ai"
ADMIN_PASSWORD = "adminadmin"

async def seed_admin():
    async with AsyncSession(engine) as session:
        result = await session.execute(select(User).where(User.email == ADMIN_EMAIL))
        admin = result.scalar_one_or_none()
        if admin:
            print(f"⚠️  Admin déjà présent : {admin.email}")
            return

        new_admin = User(
            email=ADMIN_EMAIL,
            password_hash=pwd_context.hash(ADMIN_PASSWORD),
            credit_balance=999999,
            is_admin=True
        )
        session.add(new_admin)
        await session.commit()
        await session.refresh(new_admin) 
        print("✅ Admin créé :", new_admin.email)

if __name__ == "__main__":
    asyncio.run(seed_admin())
