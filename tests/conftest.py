import asyncio
import pytest
import pytest_asyncio
from typing import AsyncGenerator, Generator
from sqlalchemy.ext.asyncio import create_async_engine, AsyncSession
from sqlalchemy.orm import sessionmaker
from fastapi.testclient import TestClient
from datetime import datetime, timedelta
from jose import jwt

from app.main import app
from app.core.config import settings
from app.core.database import get_db
from app.models.base import Base
from app.models.user import User
from app.core.security import get_password_hash, create_access_token

# Base de données de test SQLite en mémoire
TEST_DATABASE_URL = "sqlite+aiosqlite:///:memory:"

# Créer le moteur et la session pour la base de données de test
engine_test = create_async_engine(TEST_DATABASE_URL, echo=False)
TestingSessionLocal = sessionmaker(bind=engine_test, class_=AsyncSession, expire_on_commit=False)


@pytest.fixture(scope="session")
def event_loop():
    """Créer une boucle d'événements pour les tests asyncio"""
    loop = asyncio.get_event_loop_policy().new_event_loop()
    yield loop
    loop.close()


@pytest_asyncio.fixture(scope="function")
async def db_session() -> AsyncGenerator[AsyncSession, None]:
    """Créer une session de base de données de test"""
    # Créer les tables
    async with engine_test.begin() as conn:
        await conn.run_sync(Base.metadata.create_all)
    
    # Créer une session
    async with TestingSessionLocal() as session:
        yield session
    
    # Supprimer les tables
    async with engine_test.begin() as conn:
        await conn.run_sync(Base.metadata.drop_all)


@pytest_asyncio.fixture(scope="function")
async def client(db_session: AsyncSession) -> AsyncGenerator[TestClient, None]:
    """Créer un client de test FastAPI avec une base de données de test"""
    
    async def override_get_db():
        yield db_session
    
    app.dependency_overrides[get_db] = override_get_db
    
    with TestClient(app) as test_client:
        yield test_client
    
    app.dependency_overrides.clear()


@pytest_asyncio.fixture(scope="function")
async def test_user(db_session: AsyncSession) -> User:
    """Créer un utilisateur de test"""
    user = User(
        username="testuser",
        email="test@example.com",
        hashed_password=get_password_hash("password123"),
        is_active=True,
        is_superuser=False
    )
    
    db_session.add(user)
    await db_session.commit()
    await db_session.refresh(user)
    
    return user


@pytest_asyncio.fixture(scope="function")
async def test_superuser(db_session: AsyncSession) -> User:
    """Créer un superutilisateur de test"""
    user = User(
        username="admin",
        email="admin@example.com",
        hashed_password=get_password_hash("adminpass"),
        is_active=True,
        is_superuser=True
    )
    
    db_session.add(user)
    await db_session.commit()
    await db_session.refresh(user)
    
    return user


@pytest_asyncio.fixture(scope="function")
async def test_token(test_user: User) -> str:
    """Créer un token JWT de test"""
    access_token_expires = timedelta(minutes=30)
    return create_access_token(
        data={"sub": test_user.username, "user_id": test_user.id},
        expires_delta=access_token_expires,
        scopes=["user"]
    )


@pytest_asyncio.fixture(scope="function")
async def test_admin_token(test_superuser: User) -> str:
    """Créer un token JWT de test pour un administrateur"""
    access_token_expires = timedelta(minutes=30)
    return create_access_token(
        data={"sub": test_superuser.username, "user_id": test_superuser.id},
        expires_delta=access_token_expires,
        scopes=["user", "admin"]
    )