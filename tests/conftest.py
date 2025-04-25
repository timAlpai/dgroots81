import pytest
import pytest_asyncio
from sqlalchemy.ext.asyncio import create_async_engine, AsyncSession
from sqlalchemy.orm import sessionmaker
from app.db.base import Base

# Configuration de la base de données SQLite en mémoire pour les tests
DATABASE_URL = "sqlite+aiosqlite:///:memory:"

@pytest_asyncio.fixture(scope="session")
async def async_engine():
    """Fixture pour un moteur de base de données asynchrone en mémoire."""
    engine = create_async_engine(DATABASE_URL, echo=False)
    yield engine
    await engine.dispose()

@pytest_asyncio.fixture(scope="function")
async def async_session(async_engine):
    """Fixture pour une session de base de données asynchrone pour chaque test."""
    # Créer les tables
    async with async_engine.begin() as conn:
        await conn.run_sync(Base.metadata.create_all)
    
    # Créer une session
    async_session_maker = sessionmaker(
        async_engine, class_=AsyncSession, expire_on_commit=False
    )
    async with async_session_maker() as session:
        yield session
    
    # Supprimer les tables
    async with async_engine.begin() as conn:
        await conn.run_sync(Base.metadata.drop_all)