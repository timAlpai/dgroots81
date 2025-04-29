from fastapi import APIRouter, Depends, HTTPException, status, BackgroundTasks
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from typing import List, Optional, Dict, Any
from datetime import datetime, UTC
import json
import time

from app.core.database import get_db
from app.core.security import get_current_active_user
from app.core.dependencies import get_game_session, get_character
from app.core import redis, llm_client
from app.models.user import User
from app.models.game_session import GameSession
from app.models.character import Character
from app.models.action_log import ActionLog, ActionType
from app.models.scene import Scene
from app.schemas.action_log import (
    ActionLogCreate,
    ActionLog as ActionLogSchema,
    ActionRequest,
    ActionResponse
)
from app.services.llm_service import generate_action_response

router = APIRouter(prefix="/actions", tags=["actions"])

@router.get("/", response_model=List[ActionLogSchema])
async def read_actions(
    skip: int = 0,
    limit: int = 100,
    game_session_id: Optional[int] = None,
    character_id: Optional[int] = None,
    action_date: Optional[str] = None,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """
    Récupère les logs d'action avec filtres optionnels.
    """
    query = select(ActionLog)
    
    # Appliquer les filtres
    if game_session_id:
        # Vérifier l'accès à la session
        result = await db.execute(select(GameSession).filter(GameSession.id == game_session_id))
        session = result.scalars().first()
        
        if not session:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail=f"Session de jeu avec l'ID {game_session_id} non trouvée"
            )
        
        # Vérifier si l'utilisateur est le maître de jeu ou un joueur de la session
        is_game_master = session.game_master_id == current_user.id
        
        if not (is_game_master or current_user.is_superuser):
            # Vérifier si l'utilisateur est un joueur dans cette session
            result = await db.execute(
                select(Character).filter(
                    Character.game_session_id == game_session_id,
                    Character.user_id == current_user.id
                )
            )
            if not result.scalars().first():
                raise HTTPException(
                    status_code=status.HTTP_403_FORBIDDEN,
                    detail="Accès non autorisé à cette session de jeu"
                )
        
        query = query.filter(ActionLog.game_session_id == game_session_id)
    
    if character_id:
        # Vérifier l'accès au personnage
        result = await db.execute(select(Character).filter(Character.id == character_id))
        character = result.scalars().first()
        
        if not character:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail=f"Personnage avec l'ID {character_id} non trouvé"
            )
        
        # Vérifier si l'utilisateur est le propriétaire du personnage ou le maître de jeu
        is_owner = character.user_id == current_user.id
        
        result = await db.execute(
            select(GameSession).filter(GameSession.id == character.game_session_id)
        )
        session = result.scalars().first()
        is_game_master = session and session.game_master_id == current_user.id
        
        if not (is_owner or is_game_master or current_user.is_superuser):
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Accès non autorisé à ce personnage"
            )
        
        query = query.filter(ActionLog.character_id == character_id)
    
    if action_date:
        query = query.filter(ActionLog.action_date == action_date)
    
    # Appliquer pagination et tri par date
    query = query.order_by(ActionLog.action_timestamp.desc()).offset(skip).limit(limit)
    
    result = await db.execute(query)
    actions = result.scalars().all()
    
    return actions

@router.post("/", response_model=ActionResponse)
async def create_action(
    action_request: ActionRequest,
    background_tasks: BackgroundTasks,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """
    Crée une nouvelle action et génère une réponse via le LLM.
    """
    # Vérifier l'accès au personnage
    result = await db.execute(select(Character).filter(Character.id == action_request.character_id))
    character = result.scalars().first()
    
    if not character:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Personnage avec l'ID {action_request.character_id} non trouvé"
        )
    
    # Vérifier si l'utilisateur est le propriétaire du personnage
    if character.user_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Vous ne pouvez créer une action que pour votre propre personnage"
        )
    
    # Récupérer la session de jeu
    result = await db.execute(select(GameSession).filter(GameSession.id == character.game_session_id))
    session = result.scalars().first()
    
    if not session:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Session de jeu associée non trouvée"
        )
    
    # Vérifier si la session est active
    if not session.is_active:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="La session de jeu n'est pas active"
        )
    
    # Récupérer la scène actuelle
    scene = None
    if action_request.scene_id:
        result = await db.execute(select(Scene).filter(Scene.id == action_request.scene_id))
        scene = result.scalars().first()
    elif session.current_scene_id:
        result = await db.execute(select(Scene).filter(Scene.id == session.current_scene_id))
        scene = result.scalars().first()
    
    # Créer le log d'action
    action_timestamp = datetime.now(UTC)
    action_date = action_timestamp.strftime("%Y-%m-%d")
    
    action_log = ActionLog(
        action_type=action_request.action_type,
        description=action_request.description,
        game_data=action_request.game_data or {},
        game_session_id=character.game_session_id,
        character_id=character.id,
        scene_id=scene.id if scene else None,
        action_timestamp=action_timestamp,
        action_date=action_date
    )
    
    db.add(action_log)
    await db.commit()
    await db.refresh(action_log)
    
    # Mettre à jour les statistiques de la session
    session.total_actions += 1
    await db.commit()
    
    # Récupérer l'état de la session depuis Redis
    session_state_json = await redis.redis_client.get(f"session:{session.id}")
    
    if not session_state_json:
        # Initialiser l'état si non existant
        from app.api.game_sessions import initialize_session_state_in_redis
        await initialize_session_state_in_redis(session.id, session)
        session_state_json = await redis.redis_client.get(f"session:{session.id}")
    
    session_state = json.loads(session_state_json)
    
    # Mettre à jour le contexte de la session
    context_window = session_state.get("context_window", [])
    
    # Ajouter l'action au contexte
    context_window.append({
        "action_id": action_log.id,
        "character_name": character.name,
        "action_type": action_request.action_type,
        "description": action_request.description,
        "timestamp": action_timestamp.isoformat()
    })
    
    # Limiter la taille du contexte (garder les 10 dernières actions)
    if len(context_window) > 10:
        context_window = context_window[-10:]
    
    session_state["context_window"] = context_window
    session_state["last_action_id"] = action_log.id
    session_state["last_activity_time"] = datetime.now(UTC).isoformat()
    
    # Mettre à jour l'état dans Redis
    await redis.redis_client.set(
        f"session:{session.id}",
        json.dumps(session_state),
        ex=86400  # 24 heures
    )
    
    # Générer la réponse via le LLM
    start_time = time.time()
    
    # Construire le contexte pour le LLM
    llm_context = await build_llm_context(db, session, character, scene, context_window)
    
    # Générer la réponse
    response_data = await generate_action_response(
        action_request.action_type,
        action_request.description,
        action_request.game_data or {},
        llm_context
    )
    
    end_time = time.time()
    processing_time = end_time - start_time
    
    # Mettre à jour le log d'action avec la réponse
    action_log.result = response_data["result"]
    action_log.tokens_used = response_data["tokens_used"]
    action_log.processing_time = processing_time
    
    await db.commit()
    
    # Mettre à jour les statistiques de tokens
    session.total_tokens_used += response_data["tokens_used"]
    current_user.total_tokens_used += response_data["tokens_used"]
    
    # Mettre à jour le temps de jeu
    # Cette partie serait normalement plus complexe pour calculer le temps de jeu réel
    
    await db.commit()
    
    # Mettre à jour l'état du jeu en arrière-plan
    background_tasks.add_task(
        update_game_state,
        session.id,
        character.id,
        action_log.id,
        response_data
    )
    
    # Construire la réponse
    action_response = ActionResponse(
        action_id=action_log.id,
        result=response_data["result"],
        game_data=response_data.get("game_data", {}),
        tokens_used=response_data["tokens_used"],
        processing_time=processing_time,
        timestamp=action_timestamp,
        character_updates=response_data.get("character_updates"),
        scene_updates=response_data.get("scene_updates"),
        next_possible_actions=response_data.get("next_possible_actions", []),
        narrative_context=response_data.get("narrative_context")
    )
    
    return action_response

@router.get("/{action_id}", response_model=ActionLogSchema)
async def read_action(
    action_id: int,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """
    Récupère un log d'action par son ID.
    """
    result = await db.execute(select(ActionLog).filter(ActionLog.id == action_id))
    action = result.scalars().first()
    
    if not action:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Action avec l'ID {action_id} non trouvée"
        )
    
    # Vérifier l'accès à l'action
    result = await db.execute(select(Character).filter(Character.id == action.character_id))
    character = result.scalars().first()
    
    result = await db.execute(select(GameSession).filter(GameSession.id == action.game_session_id))
    session = result.scalars().first()
    
    is_owner = character and character.user_id == current_user.id
    is_game_master = session and session.game_master_id == current_user.id
    
    if not (is_owner or is_game_master or current_user.is_superuser):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Accès non autorisé à cette action"
        )
    
    return action

# Fonctions utilitaires

async def build_llm_context(
    db: AsyncSession,
    session: GameSession,
    character: Character,
    scene: Optional[Scene],
    context_window: List[Dict[str, Any]]
) -> Dict[str, Any]:
    """
    Construit le contexte pour le LLM.
    """
    # Récupérer les données du personnage
    character_data = {
        "id": character.id,
        "name": character.name,
        "class": character.character_class,
        "level": character.level,
        "strength": character.strength,
        "intelligence": character.intelligence,
        "wisdom": character.wisdom,
        "dexterity": character.dexterity,
        "constitution": character.constitution,
        "charisma": character.charisma,
        "max_hp": character.max_hp,
        "current_hp": character.current_hp,
        "armor_class": character.armor_class,
        "equipment": character.equipment,
        "inventory": character.inventory,
        "gold": character.gold,
        "skills": character.skills,
        "spells": character.spells
    }
    
    # Récupérer les données de la scène
    scene_data = {}
    if scene:
        scene_data = {
            "id": scene.id,
            "title": scene.title,
            "description": scene.description,
            "scene_type": scene.scene_type,
            "narrative_content": scene.narrative_content,
            "npcs": scene.npcs,
            "monsters": scene.monsters,
            "items": scene.items
        }
    
    # Récupérer les autres personnages de la session
    result = await db.execute(
        select(Character)
        .filter(Character.game_session_id == session.id)
        .filter(Character.id != character.id)
    )
    other_characters = result.scalars().all()
    
    other_characters_data = [{
        "id": char.id,
        "name": char.name,
        "class": char.character_class,
        "level": char.level
    } for char in other_characters]
    
    # Construire le contexte complet
    llm_context = {
        "session": {
            "id": session.id,
            "name": session.name,
            "description": session.description,
            "game_rules": session.game_rules,
            "difficulty_level": session.difficulty_level,
            "context_data": session.context_data
        },
        "character": character_data,
        "other_characters": other_characters_data,
        "scene": scene_data,
        "context_window": context_window,
        "game_state": {}  # Sera rempli avec l'état du jeu depuis Redis
    }
    
    return llm_context

async def update_game_state(
    session_id: int,
    character_id: int,
    action_id: int,
    response_data: Dict[str, Any]
):
    """
    Met à jour l'état du jeu dans Redis et applique les mises à jour au personnage et à la scène.
    """
    # Récupérer l'état de la session depuis Redis
    session_state_json = await redis.redis_client.get(f"session:{session_id}")
    
    if not session_state_json:
        return
    
    session_state = json.loads(session_state_json)
    
    # Mettre à jour l'état du jeu
    game_state = session_state.get("game_state", {})
    
    # Appliquer les mises à jour du personnage
    character_updates = response_data.get("character_updates")
    if character_updates:
        # Cette partie serait normalement implémentée pour mettre à jour le personnage
        # dans la base de données
        pass
    
    # Appliquer les mises à jour de la scène
    scene_updates = response_data.get("scene_updates")
    if scene_updates:
        # Cette partie serait normalement implémentée pour mettre à jour la scène
        # dans la base de données
        pass
    
    # Mettre à jour l'état du jeu
    game_state.update(response_data.get("game_data", {}))
    session_state["game_state"] = game_state
    
    # Mettre à jour l'état dans Redis
    await redis.redis_client.set(
        f"session:{session_id}",
        json.dumps(session_state),
        ex=86400  # 24 heures
    )