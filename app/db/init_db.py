import asyncio
from app.core.database import engine
from app.db.base import Base
# from app.db.models import user, credit_transaction
from app.db import models 
async def init_models():
    async with engine.begin() as conn:
        await conn.run_sync(Base.metadata.create_all)

if __name__ == "__main__":
    asyncio.run(init_models())

