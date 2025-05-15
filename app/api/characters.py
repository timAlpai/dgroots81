from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from sqlalchemy.orm import selectinload
from typing import List, Tuple

from app.core.database import get_db
from app.core.security import get_current_active_user
from app.core.dependencies import get_game_session, get_character
from app.models.user import User
from app.models.game_session import GameSession
from app.models.character import Character, CharacterClass
from app.schemas.character import (
    CharacterCreate,
    Character as CharacterSchema,
    CharacterUpdate,
    CharacterWithDetails
)
from app.utils.ose.generation import generate_character_stats, StatGenMethod
from app.utils.ose.hp import calculate_hp_for_level_up
from app.utils.ose.core import get_max_level

router = APIRouter(prefix="/characters", tags=["characters"])

@router.get("/", response_model=List[CharacterSchema])
async def read_characters(
    skip: int = 0,
    limit: int = 100,
    game_session_id: int = None,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """
    Récupère tous les personnages de l'utilisateur.
    Si game_session_id est fourni, récupère tous les personnages de cette session.
    """
    query = select(Character)
    
    # Filtrer par utilisateur si ce n'est pas un administrateur
    if not current_user.is_superuser:
        query = query.filter(Character.user_id == current_user.id)
    
    # Filtrer par session de jeu si fourni
    if game_session_id:
        # Vérifier si l'utilisateur a accès à cette session
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
        
        query = query.filter(Character.game_session_id == game_session_id)
    
    # Appliquer pagination
    query = query.offset(skip).limit(limit)
    
    result = await db.execute(query)
    characters = result.scalars().all()
    
    return characters

@router.post("/", response_model=CharacterSchema)
async def create_character(
    character: CharacterCreate,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """Crée un nouveau personnage"""
    # Vérifier si l'utilisateur est le propriétaire du personnage
    if character.user_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Vous ne pouvez créer un personnage que pour vous-même"
        )
    
    # Vérifier si la session de jeu existe
    result = await db.execute(select(GameSession).filter(GameSession.id == character.game_session_id))
    session = result.scalars().first()
    
    if not session:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Session de jeu avec l'ID {character.game_session_id} non trouvée"
        )
    
    # Créer le personnage
    db_character = Character(**character.model_dump())
    
    db.add(db_character)
    await db.commit()
    await db.refresh(db_character)
    
    # Mettre à jour les statistiques de l'utilisateur
    current_user.total_characters += 1
    await db.commit()
    
    return db_character

@router.post("/generate", response_model=CharacterSchema)
async def generate_character(
    name: str,
    character_class: CharacterClass,    
    game_session_id: int,
    method: StatGenMethod = StatGenMethod.CLASSIC,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """Génère un nouveau personnage avec des statistiques aléatoires selon les règles OSE"""
    # Vérifier si la session de jeu existe
    result = await db.execute(select(GameSession).filter(GameSession.id == game_session_id))
    session = result.scalars().first()
    
    if not session:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Session de jeu avec l'ID {game_session_id} non trouvée"
        )
    
    # Générer les statistiques du personnage
    character_data = generate_character_stats(character_class, method=method)
    
    # Créer le personnage
    db_character = Character(
        name=name,
        character_class=character_class,
        user_id=current_user.id,
        game_session_id=game_session_id,
        **character_data
    )
    
    db.add(db_character)
    await db.commit()
    await db.refresh(db_character)
    
    # Mettre à jour les statistiques de l'utilisateur
    current_user.total_characters += 1
    await db.commit()
    
    return db_character

@router.get("/{character_id}", response_model=CharacterWithDetails)
async def read_character(
    character_id: int,
    db: AsyncSession = Depends(get_db),
    character_and_is_gm: Tuple[Character, bool] = Depends(get_character)
):
    """Récupère un personnage par son ID avec tous les détails"""
    character, _ = character_and_is_gm
    
    # Charger les relations
    result = await db.execute(
        select(Character)
        .filter(Character.id == character_id)
        .options(
            selectinload(Character.user),
            selectinload(Character.game_session),
            selectinload(Character.action_logs)
        )
    )
    
    character_with_details = result.scalars().first()
    
    if not character_with_details:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Personnage avec l'ID {character_id} non trouvé"
        )
    
    # Convertir les objets SQLAlchemy en dictionnaires
    from sqlalchemy.orm import class_mapper
    
    def object_as_dict(obj):
        if obj is None:
            return None
        return {c.key: getattr(obj, c.key) for c in class_mapper(obj.__class__).columns}
    
    # Créer un dictionnaire pour le personnage
    character_dict = object_as_dict(character_with_details)
    
    # Ajouter les relations sous forme de dictionnaires
    character_dict['user'] = object_as_dict(character_with_details.user) if character_with_details.user else None
    character_dict['game_session'] = object_as_dict(character_with_details.game_session) if character_with_details.game_session else None
    character_dict['action_logs'] = [object_as_dict(log) for log in character_with_details.action_logs] if character_with_details.action_logs else []
    
    # Créer un objet CharacterWithDetails à partir du dictionnaire
    from app.schemas.character import CharacterWithDetails as CharacterWithDetailsSchema
    return CharacterWithDetailsSchema(**character_dict)

@router.put("/{character_id}", response_model=CharacterSchema)
async def update_character(
    character_id: int,
    character_update: CharacterUpdate,
    db: AsyncSession = Depends(get_db),
    character_and_is_gm: Tuple[Character, bool] = Depends(get_character)
):
    """Met à jour un personnage"""
    character, is_game_master = character_and_is_gm
    
    # Mettre à jour les champs
    update_data = character_update.model_dump(exclude_unset=True)
    
    # Appliquer les mises à jour
    for key, value in update_data.items():
        setattr(character, key, value)
    
    await db.commit()
    await db.refresh(character)
    
    return character

@router.delete("/{character_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_character(
    character_id: int,
    db: AsyncSession = Depends(get_db),
    character_and_is_gm: Tuple[Character, bool] = Depends(get_character)
):
    """Supprime un personnage"""
    character, is_game_master = character_and_is_gm
    
    # Vérifier si l'utilisateur est le propriétaire du personnage ou le maître de jeu
    if not (character.user_id == character.user.id or is_game_master):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le propriétaire du personnage ou le maître de jeu peut le supprimer"
        )
    
    # Supprimer le personnage
    await db.delete(character)
    await db.commit()
    
    return None



@router.post("/{character_id}/level-up", response_model=CharacterSchema)
async def level_up_character(
    character_id: int,
    db: AsyncSession = Depends(get_db),
    character_and_is_gm: Tuple[Character, bool] = Depends(get_character)
):
    """Augmente le niveau d'un personnage OU seulement ses PV s'il est au max"""
    character, is_game_master = character_and_is_gm
    
    # Vérifie permissions
    if not (character.user_id == character.user.id or is_game_master):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le propriétaire du personnage ou le maître de jeu peut augmenter son niveau"
        )
    
    max_level = get_max_level(character.character_class)
    is_level_up = character.level < max_level

    # Monte de niveau si possible
    if is_level_up:
        character.level += 1

    # Gagne des PV dans tous les cas
    hp_increase = calculate_hp_for_level_up(character.character_class, character.level, character.constitution)
    character.max_hp += hp_increase
    character.current_hp = character.max_hp

    await db.commit()
    await db.refresh(character)
    
    return character
