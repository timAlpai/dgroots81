from fastapi import APIRouter, Depends, HTTPException, status, BackgroundTasks
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from sqlalchemy.orm import selectinload
from typing import List, Optional

from app.core.database import get_db
from app.core.security import get_current_active_user
from app.core.dependencies import get_game_session
from app.models.user import User
from app.models.game_session import GameSession
from app.models.character import Character
from app.models.scenario import Scenario
from app.models.scene import Scene
from app.schemas.game_session import (
    GameSessionCreate,
    GameSession as GameSessionSchema,
    GameSessionUpdate,
    GameSessionWithDetails,
    GameSessionState
)
from app.core import redis

router = APIRouter(prefix="/game-sessions", tags=["game_sessions"])

@router.get("/", response_model=List[GameSessionSchema])
async def read_game_sessions(
    skip: int = 0,
    limit: int = 100,
    active_only: bool = False,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """
    Récupère toutes les sessions de jeu de l'utilisateur.
    Si l'utilisateur est un administrateur, récupère toutes les sessions.
    """
    query = select(GameSession)
    
    # Filtrer par utilisateur si ce n'est pas un administrateur
    if not current_user.is_superuser:
        # Sessions où l'utilisateur est le maître de jeu
        gm_query = query.filter(GameSession.game_master_id == current_user.id)
        
        # Sessions où l'utilisateur est un joueur
        player_query = select(GameSession).join(
            Character, 
            (Character.game_session_id == GameSession.id) & 
            (Character.user_id == current_user.id)
        )
        
        # Combiner les deux requêtes
        query = gm_query.union(player_query)
    
    # Filtrer par sessions actives si demandé
    if active_only:
        query = query.filter(GameSession.is_active == True)
    
    # Appliquer pagination
    query = query.offset(skip).limit(limit)
    
    result = await db.execute(query)
    sessions = result.scalars().all()
    
    return sessions

@router.post("/", response_model=GameSessionSchema)
async def create_game_session(
    session: GameSessionCreate,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """Crée une nouvelle session de jeu"""
    # Vérifier si l'utilisateur est le maître de jeu
    if session.game_master_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Vous ne pouvez créer une session qu'avec vous-même comme maître de jeu"
        )
    
    # Créer la session
    db_session = GameSession(**session.model_dump())
    
    db.add(db_session)
    await db.commit()
    await db.refresh(db_session)
    
    # Mettre à jour les statistiques de l'utilisateur
    current_user.total_sessions += 1
    await db.commit()
    
    return db_session

@router.get("/{session_id}", response_model=GameSessionWithDetails)
async def read_game_session(
    session_id: int,
    db: AsyncSession = Depends(get_db),
    session: GameSession = Depends(get_game_session)
):
    """Récupère une session de jeu par son ID avec tous les détails"""
    # Charger les relations
    result = await db.execute(
        select(GameSession)
        .filter(GameSession.id == session_id)
        .options(
            selectinload(GameSession.characters),
            selectinload(GameSession.game_master),
            selectinload(GameSession.current_scenario),
            selectinload(GameSession.current_scene)
        )
    )
    
    session_with_details = result.scalars().first()
    
    if not session_with_details:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Session de jeu avec l'ID {session_id} non trouvée"
        )
    
    return session_with_details

@router.put("/{session_id}", response_model=GameSessionSchema)
async def update_game_session(
    session_id: int,
    session_update: GameSessionUpdate,
    db: AsyncSession = Depends(get_db),
    session: GameSession = Depends(get_game_session)
):
    """Met à jour une session de jeu"""
    # Vérifier si l'utilisateur est le maître de jeu
    if session.game_master_id != session.game_master.id:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le maître de jeu peut modifier la session"
        )
    
    # Mettre à jour les champs
    update_data = session_update.model_dump(exclude_unset=True)
    
    # Vérifier si le scénario existe si fourni
    if "current_scenario_id" in update_data and update_data["current_scenario_id"]:
        result = await db.execute(select(Scenario).filter(Scenario.id == update_data["current_scenario_id"]))
        if not result.scalars().first():
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail=f"Scénario avec l'ID {update_data['current_scenario_id']} non trouvé"
            )
    
    # Vérifier si la scène existe si fournie
    if "current_scene_id" in update_data and update_data["current_scene_id"]:
        result = await db.execute(select(Scene).filter(Scene.id == update_data["current_scene_id"]))
        if not result.scalars().first():
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail=f"Scène avec l'ID {update_data['current_scene_id']} non trouvée"
            )
    
    # Appliquer les mises à jour
    for key, value in update_data.items():
        setattr(session, key, value)
    
    await db.commit()
    await db.refresh(session)
    
    # Si la session est active, mettre à jour l'état dans Redis
    if session.is_active:
        await update_session_state_in_redis(session_id, session)
    
    return session

@router.delete("/{session_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_game_session(
    session_id: int,
    db: AsyncSession = Depends(get_db),
    session: GameSession = Depends(get_game_session)
):
    """Supprime une session de jeu"""
    # Vérifier si l'utilisateur est le maître de jeu
    if session.game_master_id != session.game_master.id:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le maître de jeu peut supprimer la session"
        )
    
    # Supprimer l'état de la session dans Redis
    await redis.redis_client.delete(f"session:{session_id}")
    
    # Supprimer la session
    await db.delete(session)
    await db.commit()
    
    return None

@router.post("/{session_id}/start", response_model=GameSessionSchema)
async def start_game_session(
    session_id: int,
    scenario_id: Optional[int] = None,
    db: AsyncSession = Depends(get_db),
    session: GameSession = Depends(get_game_session)
):
    """Démarre une session de jeu"""
    # Vérifier si l'utilisateur est le maître de jeu
    if session.game_master_id != session.game_master.id:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le maître de jeu peut démarrer la session"
        )
    
    # Vérifier si la session est déjà active
    if session.is_active:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="La session est déjà active"
        )
    
    # Mettre à jour le scénario si fourni
    if scenario_id:
        result = await db.execute(select(Scenario).filter(Scenario.id == scenario_id))
        scenario = result.scalars().first()
        
        if not scenario:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail=f"Scénario avec l'ID {scenario_id} non trouvé"
            )
        
        session.current_scenario_id = scenario_id
        
        # Définir la première scène si le scénario a des scènes
        result = await db.execute(
            select(Scene)
            .filter(Scene.scenario_id == scenario_id)
            .order_by(Scene.order)
            .limit(1)
        )
        first_scene = result.scalars().first()
        
        if first_scene:
            session.current_scene_id = first_scene.id
    
    # Activer la session
    session.is_active = True
    
    await db.commit()
    await db.refresh(session)
    
    # Initialiser l'état de la session dans Redis
    await initialize_session_state_in_redis(session_id, session)
    
    return session

@router.post("/{session_id}/stop", response_model=GameSessionSchema)
async def stop_game_session(
    session_id: int,
    background_tasks: BackgroundTasks,
    db: AsyncSession = Depends(get_db),
    session: GameSession = Depends(get_game_session)
):
    """Arrête une session de jeu"""
    # Vérifier si l'utilisateur est le maître de jeu
    if session.game_master_id != session.game_master.id:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le maître de jeu peut arrêter la session"
        )
    
    # Vérifier si la session est active
    if not session.is_active:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="La session n'est pas active"
        )
    
    # Désactiver la session
    session.is_active = False
    
    await db.commit()
    await db.refresh(session)
    
    # Sauvegarder l'état de la session dans PostgreSQL et supprimer de Redis
    background_tasks.add_task(save_session_state_to_db, session_id)
    
    return session

@router.get("/{session_id}/state", response_model=GameSessionState)
async def get_session_state(
    session_id: int,
    session: GameSession = Depends(get_game_session)
):
    """Récupère l'état actuel d'une session de jeu depuis Redis"""
    # Vérifier si la session est active
    if not session.is_active:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="La session n'est pas active"
        )
    
    # Récupérer l'état de la session depuis Redis
    session_state_json = await redis.redis_client.get(f"session:{session_id}")
    
    if not session_state_json:
        # Initialiser l'état si non existant
        await initialize_session_state_in_redis(session_id, session)
        session_state_json = await redis.redis_client.get(f"session:{session_id}")
    
    import json
    session_state = json.loads(session_state_json)
    
    return GameSessionState(**session_state)

# Fonctions utilitaires

async def initialize_session_state_in_redis(session_id: int, session: GameSession):
    """Initialise l'état d'une session dans Redis"""
    from datetime import datetime, UTC
    import json
    
    # Récupérer les personnages actifs
    # Cette partie serait normalement implémentée pour récupérer les personnages
    active_characters = []
    
    # Créer l'état initial
    session_state = {
        "session_id": session_id,
        "name": session.name,
        "active_characters": active_characters,
        "current_scenario_id": session.current_scenario_id,
        "current_scene_id": session.current_scene_id,
        "last_action_id": None,
        "context_window": [],
        "session_start_time": datetime.now(UTC).isoformat(),
        "last_activity_time": datetime.now(UTC).isoformat(),
        "game_state": {}
    }
    
    # Stocker dans Redis avec TTL de 24h
    await redis.redis_client.set(
        f"session:{session_id}",
        json.dumps(session_state),
        ex=86400  # 24 heures
    )

async def update_session_state_in_redis(session_id: int, session: GameSession):
    """Met à jour l'état d'une session dans Redis"""
    import json
    
    # Récupérer l'état actuel
    session_state_json = await redis.redis_client.get(f"session:{session_id}")
    
    if not session_state_json:
        # Initialiser si non existant
        await initialize_session_state_in_redis(session_id, session)
        return
    
    # Mettre à jour les champs pertinents
    session_state = json.loads(session_state_json)
    session_state["name"] = session.name
    session_state["current_scenario_id"] = session.current_scenario_id
    session_state["current_scene_id"] = session.current_scene_id
    
    from datetime import datetime, UTC
    session_state["last_activity_time"] = datetime.now(UTC).isoformat()
    
    # Stocker dans Redis avec TTL de 24h
    await redis.redis_client.set(
        f"session:{session_id}",
        json.dumps(session_state),
        ex=86400  # 24 heures
    )

async def save_session_state_to_db(session_id: int):
    """Sauvegarde l'état d'une session de Redis vers PostgreSQL"""
    # Cette fonction serait implémentée pour sauvegarder l'état complet
    # de la session dans PostgreSQL avant de la supprimer de Redis
    
    # Récupérer l'état depuis Redis
    session_state_json = await redis.redis_client.get(f"session:{session_id}")
    
    if session_state_json:
        # Sauvegarder dans PostgreSQL (à implémenter)
        # ...
        
        # Supprimer de Redis
        await redis.redis_client.delete(f"session:{session_id}")