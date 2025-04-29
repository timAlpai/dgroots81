import pytest
from fastapi.testclient import TestClient
from sqlalchemy.ext.asyncio import AsyncSession

from app.models.user import User
from app.models.game_session import GameSession
from app.models.character import Character, CharacterClass


@pytest.mark.asyncio
async def test_read_characters(client: TestClient, test_user: User, test_token: str, db_session: AsyncSession):
    """Test de l'endpoint GET /characters/"""
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
    
    # Test de récupération de tous les personnages
    response = client.get(
        "/api/characters/",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 200
    characters = response.json()
    assert isinstance(characters, list)
    assert len(characters) > 0
    assert characters[0]["name"] == "Test Character"
    
    # Test de récupération des personnages d'une session spécifique
    response = client.get(
        f"/api/characters/?game_session_id={game_session.id}",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 200
    characters = response.json()
    assert isinstance(characters, list)
    assert len(characters) > 0
    assert characters[0]["name"] == "Test Character"
    assert characters[0]["game_session_id"] == game_session.id
    
    # Test sans token
    response = client.get("/api/characters/")
    assert response.status_code == 401


@pytest.mark.asyncio
async def test_create_character(client: TestClient, test_user: User, test_token: str, db_session: AsyncSession):
    """Test de l'endpoint POST /characters/"""
    # Créer d'abord une session de jeu
    game_session = GameSession(
        name="Test Session",
        description="Test Description",
        game_master_id=test_user.id
    )
    
    db_session.add(game_session)
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Test de création d'un personnage
    response = client.post(
        "/api/characters/",
        headers={"Authorization": f"Bearer {test_token}"},
        json={
            "name": "New Character",
            "character_class": "guerrier",
            "level": 1,
            "experience": 0,
            "strength": 14,
            "intelligence": 10,
            "wisdom": 8,
            "dexterity": 12,
            "constitution": 15,
            "charisma": 9,
            "max_hp": 12,
            "current_hp": 12,
            "armor_class": 14,
            "user_id": test_user.id,
            "game_session_id": game_session.id
        }
    )
    
    assert response.status_code == 200
    character_data = response.json()
    assert character_data["name"] == "New Character"
    assert character_data["character_class"] == "guerrier"
    assert character_data["level"] == 1
    assert character_data["strength"] == 14
    assert character_data["user_id"] == test_user.id
    assert character_data["game_session_id"] == game_session.id
    
    # Test avec des caractéristiques invalides
    response = client.post(
        "/api/characters/",
        headers={"Authorization": f"Bearer {test_token}"},
        json={
            "name": "Invalid Character",
            "character_class": "guerrier",
            "level": 1,
            "experience": 0,
            "strength": 20,  # Trop élevé
            "intelligence": 10,
            "wisdom": 8,
            "dexterity": 12,
            "constitution": 15,
            "charisma": 9,
            "max_hp": 12,
            "current_hp": 12,
            "armor_class": 14,
            "user_id": test_user.id,
            "game_session_id": game_session.id
        }
    )
    
    assert response.status_code == 422
    
    # Test avec une session de jeu inexistante
    response = client.post(
        "/api/characters/",
        headers={"Authorization": f"Bearer {test_token}"},
        json={
            "name": "Invalid Character",
            "character_class": "guerrier",
            "level": 1,
            "experience": 0,
            "strength": 14,
            "intelligence": 10,
            "wisdom": 8,
            "dexterity": 12,
            "constitution": 15,
            "charisma": 9,
            "max_hp": 12,
            "current_hp": 12,
            "armor_class": 14,
            "user_id": test_user.id,
            "game_session_id": 9999  # ID inexistant
        }
    )
    
    assert response.status_code == 404


@pytest.mark.asyncio
async def test_generate_character(client: TestClient, test_user: User, test_token: str, db_session: AsyncSession):
    """Test de l'endpoint POST /characters/generate"""
    # Créer d'abord une session de jeu
    game_session = GameSession(
        name="Test Session",
        description="Test Description",
        game_master_id=test_user.id
    )
    
    db_session.add(game_session)
    await db_session.commit()
    await db_session.refresh(game_session)
    
    # Test de génération d'un personnage
    response = client.post(
        "/api/characters/generate",
        headers={"Authorization": f"Bearer {test_token}"},
        params={
            "name": "Generated Character",
            "character_class": "guerrier",
            "game_session_id": game_session.id
        }
    )
    
    assert response.status_code == 200
    character_data = response.json()
    assert character_data["name"] == "Generated Character"
    assert character_data["character_class"] == "guerrier"
    assert character_data["user_id"] == test_user.id
    assert character_data["game_session_id"] == game_session.id
    assert character_data["level"] == 1
    assert 3 <= character_data["strength"] <= 18
    assert 3 <= character_data["intelligence"] <= 18
    assert 3 <= character_data["wisdom"] <= 18
    assert 3 <= character_data["dexterity"] <= 18
    assert 3 <= character_data["constitution"] <= 18
    assert 3 <= character_data["charisma"] <= 18
    assert character_data["max_hp"] > 0
    assert character_data["current_hp"] == character_data["max_hp"]


@pytest.mark.asyncio
async def test_read_character(client: TestClient, test_user: User, test_token: str, db_session: AsyncSession):
    """Test de l'endpoint GET /characters/{character_id}"""
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
    
    # Test de récupération d'un personnage par ID
    response = client.get(
        f"/api/characters/{character.id}",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 200
    character_data = response.json()
    assert character_data["id"] == character.id
    assert character_data["name"] == "Test Character"
    assert character_data["character_class"] == "guerrier"
    assert character_data["level"] == 1
    assert character_data["strength"] == 12
    assert character_data["user_id"] == test_user.id
    assert character_data["game_session_id"] == game_session.id
    
    # Test avec un ID inexistant
    response = client.get(
        "/api/characters/9999",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 404


@pytest.mark.asyncio
async def test_update_character(client: TestClient, test_user: User, test_token: str, db_session: AsyncSession):
    """Test de l'endpoint PUT /characters/{character_id}"""
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
    
    # Test de mise à jour d'un personnage
    response = client.put(
        f"/api/characters/{character.id}",
        headers={"Authorization": f"Bearer {test_token}"},
        json={
            "name": "Updated Character",
            "level": 2,
            "experience": 2000,
            "current_hp": 8,
            "equipment": [{"name": "Épée longue", "damage": "1d8"}],
            "gold": 100
        }
    )
    
    assert response.status_code == 200
    character_data = response.json()
    assert character_data["name"] == "Updated Character"
    assert character_data["level"] == 2
    assert character_data["experience"] == 2000
    assert character_data["current_hp"] == 8
    assert character_data["equipment"] == [{"name": "Épée longue", "damage": "1d8"}]
    assert character_data["gold"] == 100
    
    # Test avec un niveau invalide
    response = client.put(
        f"/api/characters/{character.id}",
        headers={"Authorization": f"Bearer {test_token}"},
        json={
            "level": 0  # Trop bas
        }
    )
    
    assert response.status_code == 422
    
    # Test avec un ID inexistant
    response = client.put(
        "/api/characters/9999",
        headers={"Authorization": f"Bearer {test_token}"},
        json={
            "name": "Nonexistent Character"
        }
    )
    
    assert response.status_code == 404


@pytest.mark.asyncio
async def test_delete_character(client: TestClient, test_user: User, test_token: str, db_session: AsyncSession):
    """Test de l'endpoint DELETE /characters/{character_id}"""
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
    
    # Test de suppression d'un personnage
    response = client.delete(
        f"/api/characters/{character.id}",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 204
    
    # Vérifier que le personnage a été supprimé
    response = client.get(
        f"/api/characters/{character.id}",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 404
    
    # Test avec un ID inexistant
    response = client.delete(
        "/api/characters/9999",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 404


@pytest.mark.asyncio
async def test_level_up_character(client: TestClient, test_user: User, test_token: str, db_session: AsyncSession):
    """Test de l'endpoint POST /characters/{character_id}/level-up"""
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
    
    # Test de montée de niveau d'un personnage
    response = client.post(
        f"/api/characters/{character.id}/level-up",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 200
    character_data = response.json()
    assert character_data["level"] == 2
    assert character_data["max_hp"] > 10  # Les points de vie ont augmenté
    
    # Test avec un ID inexistant
    response = client.post(
        "/api/characters/9999/level-up",
        headers={"Authorization": f"Bearer {test_token}"}
    )
    
    assert response.status_code == 404