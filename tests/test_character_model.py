import pytest
from sqlalchemy.future import select
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.exc import IntegrityError

from app.models.character import Character, CharacterClass
from app.models.user import User
from app.models.game_session import GameSession
from app.core.security import get_password_hash


@pytest.mark.asyncio
async def test_create_character(db_session: AsyncSession, test_user: User):
    """Test de la création d'un personnage"""
    # Créer d'abord une session de jeu
    game_session = GameSession(
        name="Test Session",
        description="Test Description",
        game_master_id=test_user.id
    )
    
    db_session.add(game_session)
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Créer un personnage
    character = Character(
        name="Test Character",
        character_class=CharacterClass.GUERRIER,
        level=1,
        experience=0,
        strength=12,
        intelligence=10,
        wisdom=8,
        dexterity=14,
        constitution=13,
        charisma=9,
        max_hp=10,
        current_hp=10,
        armor_class=12,
        user_id=test_user.id,
        game_session_id=game_session.id
    )
    
    db_session.add(character)
    await db_session.commit()
    await db_session.refresh(character)
    
    # Vérifier que le personnage a été créé avec un ID
    assert character.id is not None
    assert character.name == "Test Character"
    assert character.character_class == CharacterClass.GUERRIER
    assert character.level == 1
    assert character.experience == 0
    assert character.strength == 12
    assert character.intelligence == 10
    assert character.wisdom == 8
    assert character.dexterity == 14
    assert character.constitution == 13
    assert character.charisma == 9
    assert character.max_hp == 10
    assert character.current_hp == 10
    assert character.armor_class == 12
    assert character.user_id == test_user.id
    assert character.game_session_id == game_session.id
    assert character.created_at is not None
    assert character.updated_at is not None
    assert character.is_alive is True
    assert character.equipment == []
    assert character.inventory == []
    assert character.gold == 0
    assert character.skills == []
    assert character.spells == []


@pytest.mark.asyncio
async def test_read_character(db_session: AsyncSession, test_user: User):
    """Test de la lecture d'un personnage"""
    # Créer d'abord une session de jeu
    game_session = GameSession(
        name="Test Session",
        description="Test Description",
        game_master_id=test_user.id
    )
    
    db_session.add(game_session)
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Créer un personnage
    character = Character(
        name="Test Character",
        character_class=CharacterClass.GUERRIER,
        level=1,
        experience=0,
        strength=12,
        intelligence=10,
        wisdom=8,
        dexterity=14,
        constitution=13,
        charisma=9,
        max_hp=10,
        current_hp=10,
        armor_class=12,
        user_id=test_user.id,
        game_session_id=game_session.id
    )
    
    db_session.add(character)
    await db_session.commit()
    await db_session.refresh(character)
    
    # Récupérer le personnage par ID
    result = await db_session.execute(select(Character).filter(Character.id == character.id))
    db_character = result.scalars().first()
    
    assert db_character is not None
    assert db_character.id == character.id
    assert db_character.name == character.name
    assert db_character.character_class == character.character_class
    assert db_character.level == character.level
    assert db_character.user_id == character.user_id
    assert db_character.game_session_id == character.game_session_id


@pytest.mark.asyncio
async def test_update_character(db_session: AsyncSession, test_user: User):
    """Test de la mise à jour d'un personnage"""
    # Créer d'abord une session de jeu
    game_session = GameSession(
        name="Test Session",
        description="Test Description",
        game_master_id=test_user.id
    )
    
    db_session.add(game_session)
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Créer un personnage
    character = Character(
        name="Test Character",
        character_class=CharacterClass.GUERRIER,
        level=1,
        experience=0,
        strength=12,
        intelligence=10,
        wisdom=8,
        dexterity=14,
        constitution=13,
        charisma=9,
        max_hp=10,
        current_hp=10,
        armor_class=12,
        user_id=test_user.id,
        game_session_id=game_session.id
    )
    
    db_session.add(character)
    await db_session.commit()
    await db_session.refresh(character)
    
    # Mettre à jour le personnage
    character.name = "Updated Character"
    character.level = 2
    character.experience = 2000
    character.current_hp = 8
    character.equipment = [{"name": "Épée longue", "damage": "1d8"}]
    character.gold = 100
    
    await db_session.commit()
    await db_session.refresh(character)
    
    # Vérifier que le personnage a été mis à jour
    assert character.name == "Updated Character"
    assert character.level == 2
    assert character.experience == 2000
    assert character.current_hp == 8
    assert character.equipment == [{"name": "Épée longue", "damage": "1d8"}]
    assert character.gold == 100
    
    # Récupérer le personnage mis à jour
    result = await db_session.execute(select(Character).filter(Character.id == character.id))
    db_character = result.scalars().first()
    
    assert db_character is not None
    assert db_character.name == "Updated Character"
    assert db_character.level == 2
    assert db_character.experience == 2000
    assert db_character.current_hp == 8
    assert db_character.equipment == [{"name": "Épée longue", "damage": "1d8"}]
    assert db_character.gold == 100


@pytest.mark.asyncio
async def test_delete_character(db_session: AsyncSession, test_user: User):
    """Test de la suppression d'un personnage"""
    # Créer d'abord une session de jeu
    game_session = GameSession(
        name="Test Session",
        description="Test Description",
        game_master_id=test_user.id
    )
    
    db_session.add(game_session)
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Créer un personnage
    character = Character(
        name="Test Character",
        character_class=CharacterClass.GUERRIER,
        level=1,
        experience=0,
        strength=12,
        intelligence=10,
        wisdom=8,
        dexterity=14,
        constitution=13,
        charisma=9,
        max_hp=10,
        current_hp=10,
        armor_class=12,
        user_id=test_user.id,
        game_session_id=game_session.id
    )
    
    db_session.add(character)
    await db_session.commit()
    await db_session.refresh(character)
    
    # Supprimer le personnage
    await db_session.delete(character)
    await db_session.commit()
    
    # Vérifier que le personnage a été supprimé
    result = await db_session.execute(select(Character).filter(Character.id == character.id))
    db_character = result.scalars().first()
    
    assert db_character is None


@pytest.mark.asyncio
async def test_character_ability_modifiers(db_session: AsyncSession, test_user: User):
    """Test des modificateurs de caractéristiques du personnage"""
    # Créer d'abord une session de jeu
    game_session = GameSession(
        name="Test Session",
        description="Test Description",
        game_master_id=test_user.id
    )
    
    db_session.add(game_session)
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Créer un personnage avec différentes valeurs de caractéristiques
    character = Character(
        name="Test Character",
        character_class=CharacterClass.GUERRIER,
        level=1,
        experience=0,
        strength=18,  # Modificateur +3
        intelligence=16,  # Modificateur +2
        wisdom=13,  # Modificateur +1
        dexterity=10,  # Modificateur 0
        constitution=6,  # Modificateur -1
        charisma=3,  # Modificateur -3
        max_hp=10,
        current_hp=10,
        armor_class=12,
        user_id=test_user.id,
        game_session_id=game_session.id
    )
    
    db_session.add(character)
    await db_session.commit()
    await db_session.refresh(character)
    
    # Vérifier les modificateurs
    assert character.get_strength_modifier() == 3
    assert character.get_intelligence_modifier() == 2
    assert character.get_wisdom_modifier() == 1
    assert character.get_dexterity_modifier() == 0
    assert character.get_constitution_modifier() == -1
    assert character.get_charisma_modifier() == -3