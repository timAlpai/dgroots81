import pytest
from sqlalchemy.future import select
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.exc import IntegrityError

from app.models.user import User
from app.core.security import get_password_hash


@pytest.mark.asyncio
async def test_create_user(db_session: AsyncSession):
    """Test de la création d'un utilisateur"""
    # Créer un nouvel utilisateur
    user = User(
        username="newuser",
        email="newuser@example.com",
        hashed_password=get_password_hash("password123"),
        is_active=True,
        is_superuser=False
    )
    
    db_session.add(user)
    await db_session.commit()
    await db_session.refresh(user)
    
    # Vérifier que l'utilisateur a été créé avec un ID
    assert user.id is not None
    assert user.username == "newuser"
    assert user.email == "newuser@example.com"
    assert user.is_active is True
    assert user.is_superuser is False
    assert user.created_at is not None
    assert user.updated_at is not None
    assert user.total_tokens_used == 0
    assert user.total_game_time == 0.0
    assert user.total_sessions == 0
    assert user.total_characters == 0


@pytest.mark.asyncio
async def test_read_user(db_session: AsyncSession, test_user: User):
    """Test de la lecture d'un utilisateur"""
    # Récupérer l'utilisateur par ID
    result = await db_session.execute(select(User).filter(User.id == test_user.id))
    user = result.scalars().first()
    
    assert user is not None
    assert user.id == test_user.id
    assert user.username == test_user.username
    assert user.email == test_user.email
    assert user.is_active == test_user.is_active
    assert user.is_superuser == test_user.is_superuser
    
    # Récupérer l'utilisateur par nom d'utilisateur
    result = await db_session.execute(select(User).filter(User.username == test_user.username))
    user = result.scalars().first()
    
    assert user is not None
    assert user.id == test_user.id
    
    # Récupérer l'utilisateur par email
    result = await db_session.execute(select(User).filter(User.email == test_user.email))
    user = result.scalars().first()
    
    assert user is not None
    assert user.id == test_user.id
    
    # Récupérer un utilisateur inexistant
    result = await db_session.execute(select(User).filter(User.username == "nonexistentuser"))
    user = result.scalars().first()
    
    assert user is None


@pytest.mark.asyncio
async def test_update_user(db_session: AsyncSession, test_user: User):
    """Test de la mise à jour d'un utilisateur"""
    # Mettre à jour l'utilisateur
    test_user.username = "updateduser"
    test_user.email = "updated@example.com"
    test_user.is_active = False
    test_user.is_superuser = True
    test_user.total_tokens_used = 100
    test_user.total_game_time = 5.5
    test_user.total_sessions = 10
    test_user.total_characters = 3
    
    await db_session.commit()
    await db_session.refresh(test_user)
    
    # Vérifier que l'utilisateur a été mis à jour
    assert test_user.username == "updateduser"
    assert test_user.email == "updated@example.com"
    assert test_user.is_active is False
    assert test_user.is_superuser is True
    assert test_user.total_tokens_used == 100
    assert test_user.total_game_time == 5.5
    assert test_user.total_sessions == 10
    assert test_user.total_characters == 3
    
    # Récupérer l'utilisateur mis à jour
    result = await db_session.execute(select(User).filter(User.id == test_user.id))
    user = result.scalars().first()
    
    assert user is not None
    assert user.username == "updateduser"
    assert user.email == "updated@example.com"
    assert user.is_active is False
    assert user.is_superuser is True
    assert user.total_tokens_used == 100
    assert user.total_game_time == 5.5
    assert user.total_sessions == 10
    assert user.total_characters == 3


@pytest.mark.asyncio
async def test_delete_user(db_session: AsyncSession, test_user: User):
    """Test de la suppression d'un utilisateur"""
    # Supprimer l'utilisateur
    await db_session.delete(test_user)
    await db_session.commit()
    
    # Vérifier que l'utilisateur a été supprimé
    result = await db_session.execute(select(User).filter(User.id == test_user.id))
    user = result.scalars().first()
    
    assert user is None


@pytest.mark.asyncio
async def test_user_unique_constraints(db_session: AsyncSession):
    """Test des contraintes d'unicité sur le modèle User"""
    # Créer un premier utilisateur
    user1 = User(
        username="uniqueuser",
        email="unique@example.com",
        hashed_password=get_password_hash("password123"),
        is_active=True,
        is_superuser=False
    )
    
    db_session.add(user1)
    await db_session.commit()
    
    # Tenter de créer un utilisateur avec un nom d'utilisateur déjà utilisé
    user2 = User(
        username="uniqueuser",  # Nom d'utilisateur déjà utilisé
        email="another@example.com",
        hashed_password=get_password_hash("password123"),
        is_active=True,
        is_superuser=False
    )
    
    db_session.add(user2)
    
    # Vérifier que la contrainte d'unicité est appliquée
    with pytest.raises(IntegrityError):
        await db_session.commit()
    
    await db_session.rollback()
    
    # Tenter de créer un utilisateur avec un email déjà utilisé
    user3 = User(
        username="anotheruser",
        email="unique@example.com",  # Email déjà utilisé
        hashed_password=get_password_hash("password123"),
        is_active=True,
        is_superuser=False
    )
    
    db_session.add(user3)
    
    # Vérifier que la contrainte d'unicité est appliquée
    with pytest.raises(IntegrityError):
        await db_session.commit()
    
    await db_session.rollback()