import asyncio
from app.core.database import engine
from app.db.base import Base
from app.db.models import user, credit_transaction

async def reset_database():
    print("⚠️  Suppression des tables…")
    async with engine.begin() as conn:
        await conn.run_sync(Base.metadata.drop_all)
        print("✅ Tables supprimées.")
        await conn.run_sync(Base.metadata.create_all)
        print("✅ Tables recréées.")

if __name__ == "__main__":
    asyncio.run(reset_database())
