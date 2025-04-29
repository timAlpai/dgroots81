from fastapi import Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from typing import Optional, Tuple

from app.core.database import get_db
from app.core.security import get_current_active_user
from app.models.user import User
from app.models.game_session import GameSession
from app.models.character import Character
from app.models.scenario import Scenario
from app.models.scene import Scene

async def get_game_session(
    session_id: int,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
) -> GameSession:
    """Récupère une session de jeu par son ID et vérifie les permissions"""
    result = await db.execute(select(GameSession).filter(GameSession.id == session_id))
    session = result.scalars().first()
    
    if not session:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Session de jeu avec l'ID {session_id} non trouvée"
        )
    
    # Vérifier si l'utilisateur est le maître de jeu ou un joueur de la session
    is_game_master = session.game_master_id == current_user.id
    
    # Vérifier si l'utilisateur est un joueur dans cette session
    result = await db.execute(
        select(Character).filter(
            Character.game_session_id == session_id,
            Character.user_id == current_user.id
        )
    )
    is_player = result.scalars().first() is not None
    
    if not (is_game_master or is_player or current_user.is_superuser):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Accès non autorisé à cette session de jeu"
        )
    
    return session

async def get_character(
    character_id: int,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
) -> Tuple[Character, bool]:
    """
    Récupère un personnage par son ID et vérifie les permissions
    Retourne le personnage et un booléen indiquant si l'utilisateur est le maître de jeu
    """
    result = await db.execute(select(Character).filter(Character.id == character_id))
    character = result.scalars().first()
    
    if not character:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Personnage avec l'ID {character_id} non trouvé"
        )
    
    # Vérifier si l'utilisateur est le propriétaire du personnage
    is_owner = character.user_id == current_user.id
    
    # Vérifier si l'utilisateur est le maître de jeu de la session
    result = await db.execute(select(GameSession).filter(GameSession.id == character.game_session_id))
    session = result.scalars().first()
    is_game_master = session and session.game_master_id == current_user.id
    
    if not (is_owner or is_game_master or current_user.is_superuser):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Accès non autorisé à ce personnage"
        )
    
    return character, is_game_master

async def get_scenario(
    scenario_id: int,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
) -> Scenario:
    """Récupère un scénario par son ID et vérifie les permissions"""
    result = await db.execute(select(Scenario).filter(Scenario.id == scenario_id))
    scenario = result.scalars().first()
    
    if not scenario:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Scénario avec l'ID {scenario_id} non trouvé"
        )
    
    # Vérifier si l'utilisateur est le créateur du scénario ou si le scénario est publié
    is_creator = scenario.creator_id == current_user.id
    
    if not (is_creator or scenario.is_published or current_user.is_superuser):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Accès non autorisé à ce scénario"
        )
    
    return scenario

async def get_scene(
    scene_id: int,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
) -> Tuple[Scene, Scenario]:
    """
    Récupère une scène par son ID et vérifie les permissions
    Retourne la scène et le scénario associé
    """
    result = await db.execute(select(Scene).filter(Scene.id == scene_id))
    scene = result.scalars().first()
    
    if not scene:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Scène avec l'ID {scene_id} non trouvée"
        )
    
    # Récupérer le scénario associé
    result = await db.execute(select(Scenario).filter(Scenario.id == scene.scenario_id))
    scenario = result.scalars().first()
    
    if not scenario:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Scénario associé non trouvé"
        )
    
    # Vérifier si l'utilisateur est le créateur du scénario ou si le scénario est publié
    is_creator = scenario.creator_id == current_user.id
    
    if not (is_creator or scenario.is_published or current_user.is_superuser):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Accès non autorisé à cette scène"
        )
    
    return scene, scenario