import pytest
from pydantic import ValidationError
from datetime import datetime, UTC

from app.schemas.user import (
    UserBase,
    UserCreate,
    UserUpdate,
    UserInDB,
    User,
    UserWithStats,
    Token,
    TokenData
)


def test_user_base():
    """Test du schéma UserBase"""
    # Test avec des données valides
    user_data = {
        "username": "testuser",
        "email": "test@example.com",
        "is_active": True,
        "is_superuser": False
    }
    user = UserBase(**user_data)
    
    assert user.username == "testuser"
    assert user.email == "test@example.com"
    assert user.is_active is True
    assert user.is_superuser is False
    
    # Test avec des données invalides (email invalide)
    with pytest.raises(ValidationError):
        UserBase(username="testuser", email="invalid-email")


def test_user_create():
    """Test du schéma UserCreate"""
    # Test avec des données valides
    user_data = {
        "username": "testuser",
        "email": "test@example.com",
        "password": "password123",
        "is_active": True,
        "is_superuser": False
    }
    user = UserCreate(**user_data)
    
    assert user.username == "testuser"
    assert user.email == "test@example.com"
    assert user.password == "password123"
    assert user.is_active is True
    assert user.is_superuser is False
    
    # Test avec un mot de passe trop court
    with pytest.raises(ValidationError) as excinfo:
        UserCreate(
            username="testuser",
            email="test@example.com",
            password="short"
        )
    
    assert "Le mot de passe doit contenir au moins 8 caractères" in str(excinfo.value)


def test_user_update():
    """Test du schéma UserUpdate"""
    # Test avec des données valides
    user_data = {
        "username": "newusername",
        "email": "newemail@example.com",
        "password": "newpassword123",
        "is_active": False,
        "is_superuser": True
    }
    user = UserUpdate(**user_data)
    
    assert user.username == "newusername"
    assert user.email == "newemail@example.com"
    assert user.password == "newpassword123"
    assert user.is_active is False
    assert user.is_superuser is True
    
    # Test avec des données partielles
    user = UserUpdate(username="newusername")
    assert user.username == "newusername"
    assert user.email is None
    assert user.password is None
    assert user.is_active is None
    assert user.is_superuser is None
    
    # Test avec un mot de passe trop court
    with pytest.raises(ValidationError) as excinfo:
        UserUpdate(password="short")
    
    assert "Le mot de passe doit contenir au moins 8 caractères" in str(excinfo.value)


def test_user_in_db():
    """Test du schéma UserInDB"""
    # Test avec des données valides
    user_data = {
        "id": 1,
        "username": "testuser",
        "email": "test@example.com",
        "hashed_password": "hashed_password",
        "is_active": True,
        "is_superuser": False,
        "created_at": datetime.now(UTC),
        "updated_at": datetime.now(UTC),
        "total_tokens_used": 100,
        "total_game_time": 5.5,
        "total_sessions": 10,
        "total_characters": 3
    }
    user = UserInDB(**user_data)
    
    assert user.id == 1
    assert user.username == "testuser"
    assert user.email == "test@example.com"
    assert user.hashed_password == "hashed_password"
    assert user.is_active is True
    assert user.is_superuser is False
    assert isinstance(user.created_at, datetime)
    assert isinstance(user.updated_at, datetime)
    assert user.total_tokens_used == 100
    assert user.total_game_time == 5.5
    assert user.total_sessions == 10
    assert user.total_characters == 3


def test_user():
    """Test du schéma User"""
    # Test avec des données valides
    user_data = {
        "id": 1,
        "username": "testuser",
        "email": "test@example.com",
        "is_active": True,
        "is_superuser": False,
        "created_at": datetime.now(UTC),
        "updated_at": datetime.now(UTC),
        "total_tokens_used": 100,
        "total_game_time": 5.5,
        "total_sessions": 10,
        "total_characters": 3
    }
    user = User(**user_data)
    
    assert user.id == 1
    assert user.username == "testuser"
    assert user.email == "test@example.com"
    assert user.is_active is True
    assert user.is_superuser is False
    assert isinstance(user.created_at, datetime)
    assert isinstance(user.updated_at, datetime)
    assert user.total_tokens_used == 100
    assert user.total_game_time == 5.5
    assert user.total_sessions == 10
    assert user.total_characters == 3


def test_user_with_stats():
    """Test du schéma UserWithStats"""
    # Test avec des données valides
    user_data = {
        "id": 1,
        "username": "testuser",
        "email": "test@example.com",
        "is_active": True,
        "is_superuser": False,
        "created_at": datetime.now(UTC),
        "updated_at": datetime.now(UTC),
        "total_tokens_used": 100,
        "total_game_time": 5.5,
        "total_sessions": 10,
        "total_characters": 3,
        "average_session_time": 0.55,
        "favorite_character_class": "Warrior",
        "total_actions": 150
    }
    user = UserWithStats(**user_data)
    
    assert user.id == 1
    assert user.username == "testuser"
    assert user.email == "test@example.com"
    assert user.is_active is True
    assert user.is_superuser is False
    assert isinstance(user.created_at, datetime)
    assert isinstance(user.updated_at, datetime)
    assert user.total_tokens_used == 100
    assert user.total_game_time == 5.5
    assert user.total_sessions == 10
    assert user.total_characters == 3
    assert user.average_session_time == 0.55
    assert user.favorite_character_class == "Warrior"
    assert user.total_actions == 150


def test_token():
    """Test du schéma Token"""
    # Test avec des données valides
    token_data = {
        "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
        "token_type": "bearer"
    }
    token = Token(**token_data)
    
    assert token.access_token == "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
    assert token.token_type == "bearer"
    
    # Test avec seulement le token (token_type par défaut)
    token = Token(access_token="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
    assert token.access_token == "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
    assert token.token_type == "bearer"


def test_token_data():
    """Test du schéma TokenData"""
    # Test avec des données valides
    token_data = {
        "username": "testuser",
        "user_id": 1,
        "scopes": ["user", "admin"]
    }
    data = TokenData(**token_data)
    
    assert data.username == "testuser"
    assert data.user_id == 1
    assert data.scopes == ["user", "admin"]
    
    # Test avec des données partielles
    data = TokenData(username="testuser")
    assert data.username == "testuser"
    assert data.user_id is None
    assert data.scopes == []