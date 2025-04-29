import pytest
from datetime import datetime, timedelta
from jose import jwt
from fastapi import HTTPException
from fastapi.security import SecurityScopes

from app.core.security import (
    verify_password,
    get_password_hash,
    authenticate_user,
    create_access_token,
    get_current_user,
    get_current_active_user,
    get_current_superuser
)
from app.core.config import settings
from app.models.user import User


def test_verify_password():
    """Test de la fonction verify_password"""
    password = "testpassword"
    hashed_password = get_password_hash(password)
    
    assert verify_password(password, hashed_password)
    assert not verify_password("wrongpassword", hashed_password)


def test_get_password_hash():
    """Test de la fonction get_password_hash"""
    password = "testpassword"
    hashed_password = get_password_hash(password)
    
    assert hashed_password != password
    assert isinstance(hashed_password, str)
    assert len(hashed_password) > 0


@pytest.mark.asyncio
async def test_authenticate_user(db_session, test_user):
    """Test de la fonction authenticate_user"""
    # Test avec des identifiants valides
    user = await authenticate_user(db_session, "testuser", "password123")
    assert user is not None
    assert user.username == "testuser"
    
    # Test avec un nom d'utilisateur invalide
    user = await authenticate_user(db_session, "nonexistentuser", "password123")
    assert user is None
    
    # Test avec un mot de passe invalide
    user = await authenticate_user(db_session, "testuser", "wrongpassword")
    assert user is None


def test_create_access_token():
    """Test de la fonction create_access_token"""
    data = {"sub": "testuser", "user_id": 1}
    expires_delta = timedelta(minutes=30)
    scopes = ["user"]
    
    token = create_access_token(data, expires_delta, scopes)
    
    assert isinstance(token, str)
    
    # Décodage du token pour vérifier son contenu
    payload = jwt.decode(token, settings.secret_key, algorithms=[settings.algorithm])
    
    assert payload["sub"] == "testuser"
    assert payload["user_id"] == 1
    assert payload["scopes"] == ["user"]
    assert "exp" in payload
    
    # Test sans expires_delta
    token = create_access_token(data, scopes=scopes)
    payload = jwt.decode(token, settings.secret_key, algorithms=[settings.algorithm])
    
    assert "exp" in payload


@pytest.mark.asyncio
async def test_get_current_user(db_session, test_user, test_token):
    """Test de la fonction get_current_user"""
    # Test avec un token valide
    security_scopes = SecurityScopes(scopes=["user"])
    user = await get_current_user(security_scopes, test_token, db_session)
    
    assert user is not None
    assert user.id == test_user.id
    assert user.username == test_user.username
    
    # Test avec un token invalide
    with pytest.raises(HTTPException) as excinfo:
        await get_current_user(security_scopes, "invalid_token", db_session)
    
    assert excinfo.value.status_code == 401
    
    # Test avec un scope invalide
    security_scopes = SecurityScopes(scopes=["admin"])
    with pytest.raises(HTTPException) as excinfo:
        await get_current_user(security_scopes, test_token, db_session)
    
    assert excinfo.value.status_code == 403


@pytest.mark.asyncio
async def test_get_current_active_user(test_user):
    """Test de la fonction get_current_active_user"""
    # Test avec un utilisateur actif
    user = await get_current_active_user(test_user)
    assert user is not None
    assert user.id == test_user.id
    
    # Test avec un utilisateur inactif
    test_user.is_active = False
    with pytest.raises(HTTPException) as excinfo:
        await get_current_active_user(test_user)
    
    assert excinfo.value.status_code == 400
    assert excinfo.value.detail == "Utilisateur inactif"


@pytest.mark.asyncio
async def test_get_current_superuser(test_user, test_superuser):
    """Test de la fonction get_current_superuser"""
    # Test avec un superutilisateur
    user = await get_current_superuser(test_superuser)
    assert user is not None
    assert user.id == test_superuser.id
    
    # Test avec un utilisateur normal
    with pytest.raises(HTTPException) as excinfo:
        await get_current_superuser(test_user)
    
    assert excinfo.value.status_code == 403
    assert excinfo.value.detail == "Permissions insuffisantes"