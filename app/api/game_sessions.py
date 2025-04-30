from fastapi import APIRouter, Depends, HTTPException, status, BackgroundTasks
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from sqlalchemy.orm import selectinload
from typing import List, Optional, Dict, Any

from app.core.database import get_db
from app.core.security import get_current_active_user
from app.core.dependencies import get_game_session
from app.models.user import User
from app.models.game_session import GameSession
from app.models.character import Character
from app.models.scenario import Scenario
from app.models.scene import Scene
from app.models.action_log import ActionLog
from app.schemas.game_session import (
    GameSessionCreate,
    GameSession as GameSessionSchema,
    GameSessionUpdate,
    GameSessionWithDetails,
    GameSessionState
)
from app.schemas.user import User as UserSchema
from app.schemas.action_log import ActionLog as ActionLogSchema
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
    # Sélectionner explicitement les colonnes pour éviter les problèmes avec les champs JSON
    columns = [
        GameSession.id, GameSession.name, GameSession.description,
        GameSession.is_active, GameSession.game_master_id,
        GameSession.total_tokens_used, GameSession.total_game_time,
        GameSession.total_actions, GameSession.current_scenario_id,
        GameSession.current_scene_id, GameSession.difficulty_level,
        GameSession.created_at, GameSession.updated_at
    ]
    
    query = select(*columns)
    
    # Filtrer par utilisateur si ce n'est pas un administrateur
    if not current_user.is_superuser:
        # Sessions où l'utilisateur est le maître de jeu
        gm_query = query.filter(GameSession.game_master_id == current_user.id)
        
        # Sessions où l'utilisateur est un joueur
        player_query = select(*columns).join(
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
    sessions = result.all()
    
    # Récupérer les champs JSON séparément pour chaque session
    complete_sessions = []
    for session_tuple in sessions:
        # Convertir le tuple en dictionnaire
        session_dict = {col.name: session_tuple[i]
                        for i, col in enumerate(columns)}
        
        # Récupérer les champs JSON séparément
        json_result = await db.execute(
            select(GameSession.context_data, GameSession.game_rules)
            .filter(GameSession.id == session_dict["id"])
        )
        json_data = json_result.first()
        
        if json_data:
            session_dict["context_data"] = json_data[0]
            session_dict["game_rules"] = json_data[1]
        
        # Créer un objet GameSession complet
        complete_session = GameSession(**session_dict)
        complete_sessions.append(complete_session)
    
    return complete_sessions

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
    
    # Convertir les objets en dictionnaires pour respecter le schéma GameSessionWithDetails
    response_data = {}
    
    # Copier les attributs de base de la session
    for key, value in session_with_details.__dict__.items():
        if not key.startswith('_'):  # Ignorer les attributs privés de SQLAlchemy
            response_data[key] = value
    
    # Convertir game_master en dictionnaire s'il existe
    if session_with_details.game_master:
        game_master_dict = {}
        for key, value in session_with_details.game_master.__dict__.items():
            if not key.startswith('_'):  # Ignorer les attributs privés de SQLAlchemy
                game_master_dict[key] = value
        response_data["game_master"] = game_master_dict
    else:
        response_data["game_master"] = None
    
    # Convertir characters en liste de dictionnaires
    if session_with_details.characters:
        characters_list = []
        for char in session_with_details.characters:
            char_dict = {}
            for key, value in char.__dict__.items():
                if not key.startswith('_'):  # Ignorer les attributs privés de SQLAlchemy
                    char_dict[key] = value
            characters_list.append(char_dict)
        response_data["characters"] = characters_list
    else:
        response_data["characters"] = []
    
    # Convertir current_scenario en dictionnaire s'il existe
    if session_with_details.current_scenario:
        scenario_dict = {}
        for key, value in session_with_details.current_scenario.__dict__.items():
            if not key.startswith('_'):  # Ignorer les attributs privés de SQLAlchemy
                scenario_dict[key] = value
        response_data["current_scenario"] = scenario_dict
    else:
        response_data["current_scenario"] = None
    
    # Convertir current_scene en dictionnaire s'il existe
    if session_with_details.current_scene:
        scene_dict = {}
        for key, value in session_with_details.current_scene.__dict__.items():
            if not key.startswith('_'):  # Ignorer les attributs privés de SQLAlchemy
                scene_dict[key] = value
        response_data["current_scene"] = scene_dict
    else:
        response_data["current_scene"] = None
    
    return response_data

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

@router.get("/{session_id}/actions", response_model=List[ActionLogSchema])
async def read_session_actions(
    session_id: int,
    timestamp: Optional[int] = None,
    db: AsyncSession = Depends(get_db),
    session: GameSession = Depends(get_game_session)
):
    """
    Récupère les actions d'une session de jeu.
    Si un timestamp est fourni, ne récupère que les actions après ce timestamp.
    """
    # Vérifier si l'utilisateur a accès à la session
    # Cette vérification est déjà faite par la dépendance get_game_session
    
    # Construire la requête pour récupérer les actions
    query = select(ActionLog).filter(ActionLog.game_session_id == session_id)
    
    # Filtrer par timestamp si fourni
    if timestamp:
        from datetime import datetime, UTC
        timestamp_date = datetime.fromtimestamp(timestamp, UTC)
        query = query.filter(ActionLog.action_timestamp > timestamp_date)
    
    # Trier par timestamp
    query = query.order_by(ActionLog.action_timestamp)
    
    # Exécuter la requête
    result = await db.execute(query)
    actions = result.scalars().all()
    
    return actions