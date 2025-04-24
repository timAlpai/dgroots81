from sqlalchemy.ext.asyncio import create_async_engine, AsyncSession
from sqlalchemy.orm import sessionmaker
from app.core.config import settings

DATABASE_URL = (
    f"postgresql+asyncpg://{settings.postgres_user}:"
    f"{settings.postgres_password}@{settings.postgres_host}:"
    f"{settings.postgres_port}/{settings.postgres_db}"
)

engine = create_async_engine(DATABASE_URL, echo=False)
AsyncSessionLocal = sessionmaker(bind=engine, class_=AsyncSession, expire_on_commit=False)

async def get_db():
    async with AsyncSessionLocal() as session:
        yield session
