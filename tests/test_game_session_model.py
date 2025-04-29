import pytest
from sqlalchemy.future import select
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.exc import IntegrityError

from app.models.game_session import GameSession
from app.models.user import User
from app.core.security import get_password_hash


@pytest.mark.asyncio
async def test_create_game_session(db_session: AsyncSession, test_user: User):
    """Test de la création d'une session de jeu"""
    # Créer une session de jeu
    game_session = GameSession(
        name="Test Session",
        description="Test Description",
        game_master_id=test_user.id,
        game_rules="OSE",
        difficulty_level="standard"
    )
    
    db_session.add(game_session)
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Vérifier que la session a été créée avec un ID
    assert game_session.id is not None
    assert game_session.name == "Test Session"
    assert game_session.description == "Test Description"
    assert game_session.game_master_id == test_user.id
    assert game_session.game_rules == "OSE"
    assert game_session.difficulty_level == "standard"
    assert game_session.is_active is True
    assert game_session.created_at is not None
    assert game_session.updated_at is not None
    assert game_session.total_tokens_used == 0
    assert game_session.total_game_time == 0.0
    assert game_session.total_actions == 0
    assert game_session.current_scenario_id is None
    assert game_session.current_scene_id is None
    assert game_session.context_data == {}


@pytest.mark.asyncio
async def test_read_game_session(db_session: AsyncSession, test_user: User):
    """Test de la lecture d'une session de jeu"""
    # Créer une session de jeu
    game_session = GameSession(
        name="Test Session",
        description="Test Description",
        game_master_id=test_user.id
    )
    
    db_session.add(game_session)
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Récupérer la session par ID
    result = await db_session.execute(select(GameSession).filter(GameSession.id == game_session.id))
    db_game_session = result.scalars().first()
    
    assert db_game_session is not None
    assert db_game_session.id == game_session.id
    assert db_game_session.name == game_session.name
    assert db_game_session.description == game_session.description
    assert db_game_session.game_master_id == game_session.game_master_id
    
    # Récupérer les sessions d'un maître de jeu
    result = await db_session.execute(select(GameSession).filter(GameSession.game_master_id == test_user.id))
    game_sessions = result.scalars().all()
    
    assert len(game_sessions) > 0
    assert any(session.id == game_session.id for session in game_sessions)


@pytest.mark.asyncio
async def test_update_game_session(db_session: AsyncSession, test_user: User):
    """Test de la mise à jour d'une session de jeu"""
    # Créer une session de jeu
    game_session = GameSession(
        name="Test Session",
        description="Test Description",
        game_master_id=test_user.id
    )
    
    db_session.add(game_session)
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Mettre à jour la session
    game_session.name = "Updated Session"
    game_session.description = "Updated Description"
    game_session.is_active = False
    game_session.difficulty_level = "hard"
    game_session.total_tokens_used = 100
    game_session.total_game_time = 2.5
    game_session.total_actions = 50
    game_session.context_data = {"last_action": "combat", "current_location": "donjon"}
    
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Vérifier que la session a été mise à jour
    assert game_session.name == "Updated Session"
    assert game_session.description == "Updated Description"
    assert game_session.is_active is False
    assert game_session.difficulty_level == "hard"
    assert game_session.total_tokens_used == 100
    assert game_session.total_game_time == 2.5
    assert game_session.total_actions == 50
    assert game_session.context_data == {"last_action": "combat", "current_location": "donjon"}
    
    # Récupérer la session mise à jour
    result = await db_session.execute(select(GameSession).filter(GameSession.id == game_session.id))
    db_game_session = result.scalars().first()
    
    assert db_game_session is not None
    assert db_game_session.name == "Updated Session"
    assert db_game_session.description == "Updated Description"
    assert db_game_session.is_active is False
    assert db_game_session.difficulty_level == "hard"
    assert db_game_session.total_tokens_used == 100
    assert db_game_session.total_game_time == 2.5
    assert db_game_session.total_actions == 50
    assert db_game_session.context_data == {"last_action": "combat", "current_location": "donjon"}


@pytest.mark.asyncio
async def test_delete_game_session(db_session: AsyncSession, test_user: User):
    """Test de la suppression d'une session de jeu"""
    # Créer une session de jeu
    game_session = GameSession(
        name="Test Session",
        description="Test Description",
        game_master_id=test_user.id
    )
    
    db_session.add(game_session)
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Supprimer la session
    await db_session.delete(game_session)
    await db_session.commit()
    
    # Vérifier que la session a été supprimée
    result = await db_session.execute(select(GameSession).filter(GameSession.id == game_session.id))
    db_game_session = result.scalars().first()
    
    assert db_game_session is None


@pytest.mark.asyncio
async def test_game_session_relationships(db_session: AsyncSession, test_user: User):
    """Test des relations de la session de jeu"""
    # Créer une session de jeu
    game_session = GameSession(
        name="Test Session",
        description="Test Description",
        game_master_id=test_user.id
    )
    
    db_session.add(game_session)
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Vérifier la relation avec le maître de jeu
    assert game_session.game_master is not None
    assert game_session.game_master.id == test_user.id
    
    # Vérifier que la session apparaît dans les sessions du maître de jeu
    from sqlalchemy.orm import selectinload
    result = await db_session.execute(
        select(User)
        .filter(User.id == test_user.id)
        .options(selectinload(User.game_sessions))
    )
    user = result.scalars().first()
    
    assert user is not None
    assert hasattr(user, 'game_sessions')
    assert any(session.id == game_session.id for session in user.game_sessions)