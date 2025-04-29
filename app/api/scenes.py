from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from sqlalchemy.orm import selectinload
from typing import List, Tuple, Optional

from app.core.database import get_db
from app.core.security import get_current_active_user
from app.core.dependencies import get_scene, get_scenario
from app.models.user import User
from app.models.scenario import Scenario
from app.models.scene import Scene, SceneType
from app.schemas.scene import (
    SceneCreate,
    Scene as SceneSchema,
    SceneUpdate,
    SceneWithDetails,
    SceneTransition
)
from app.services.llm_service import generate_scene_description

router = APIRouter(prefix="/scenes", tags=["scenes"])

@router.get("/", response_model=List[SceneSchema])
async def read_scenes(
    skip: int = 0,
    limit: int = 100,
    scenario_id: Optional[int] = None,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """
    Récupère toutes les scènes.
    Si scenario_id est fourni, récupère uniquement les scènes de ce scénario.
    """
    query = select(Scene)
    
    # Filtrer par scénario si fourni
    if scenario_id:
        # Vérifier l'accès au scénario
        scenario = await get_scenario(scenario_id, db, current_user)
        
        query = query.filter(Scene.scenario_id == scenario_id)
    
    # Appliquer pagination et tri par ordre
    query = query.order_by(Scene.order).offset(skip).limit(limit)
    
    result = await db.execute(query)
    scenes = result.scalars().all()
    
    return scenes

@router.post("/", response_model=SceneSchema)
async def create_scene(
    scene: SceneCreate,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """Crée une nouvelle scène"""
    # Vérifier l'accès au scénario
    scenario = await get_scenario(scene.scenario_id, db, current_user)
    
    # Vérifier si l'utilisateur est le créateur du scénario
    if scenario.creator_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le créateur du scénario peut ajouter des scènes"
        )
    
    # Créer la scène
    db_scene = Scene(**scene.model_dump())
    
    db.add(db_scene)
    await db.commit()
    await db.refresh(db_scene)
    
    return db_scene

@router.get("/{scene_id}", response_model=SceneWithDetails)
async def read_scene(
    scene_id: int,
    db: AsyncSession = Depends(get_db),
    scene_and_scenario: Tuple[Scene, Scenario] = Depends(get_scene)
):
    """Récupère une scène par son ID avec tous les détails"""
    scene, _ = scene_and_scenario
    
    # Charger les relations
    result = await db.execute(
        select(Scene)
        .filter(Scene.id == scene_id)
        .options(
            selectinload(Scene.scenario),
            selectinload(Scene.action_logs)
        )
    )
    
    scene_with_details = result.scalars().first()
    
    if not scene_with_details:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Scène avec l'ID {scene_id} non trouvée"
        )
    
    return scene_with_details

@router.put("/{scene_id}", response_model=SceneSchema)
async def update_scene(
    scene_id: int,
    scene_update: SceneUpdate,
    db: AsyncSession = Depends(get_db),
    scene_and_scenario: Tuple[Scene, Scenario] = Depends(get_scene)
):
    """Met à jour une scène"""
    scene, scenario = scene_and_scenario
    
    # Vérifier si l'utilisateur est le créateur du scénario
    current_user = scene_and_scenario[1].creator
    if scenario.creator_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le créateur du scénario peut modifier les scènes"
        )
    
    # Mettre à jour les champs
    update_data = scene_update.model_dump(exclude_unset=True)
    
    # Appliquer les mises à jour
    for key, value in update_data.items():
        setattr(scene, key, value)
    
    await db.commit()
    await db.refresh(scene)
    
    return scene

@router.delete("/{scene_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_scene(
    scene_id: int,
    db: AsyncSession = Depends(get_db),
    scene_and_scenario: Tuple[Scene, Scenario] = Depends(get_scene)
):
    """Supprime une scène"""
    scene, scenario = scene_and_scenario
    
    # Vérifier si l'utilisateur est le créateur du scénario
    current_user = scene_and_scenario[1].creator
    if scenario.creator_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le créateur du scénario peut supprimer les scènes"
        )
    
    # Supprimer la scène
    await db.delete(scene)
    await db.commit()
    
    # Réorganiser l'ordre des scènes restantes
    result = await db.execute(
        select(Scene)
        .filter(Scene.scenario_id == scenario.id)
        .order_by(Scene.order)
    )
    
    scenes = result.scalars().all()
    
    for i, s in enumerate(scenes):
        s.order = i
    
    await db.commit()
    
    return None

@router.post("/{scene_id}/generate-description", response_model=SceneSchema)
async def generate_description(
    scene_id: int,
    db: AsyncSession = Depends(get_db),
    scene_and_scenario: Tuple[Scene, Scenario] = Depends(get_scene)
):
    """Génère une description détaillée pour une scène en utilisant le LLM"""
    scene, scenario = scene_and_scenario
    
    # Vérifier si l'utilisateur est le créateur du scénario
    current_user = scene_and_scenario[1].creator
    if scenario.creator_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le créateur du scénario peut générer des descriptions"
        )
    
    # Préparer les données de la scène
    scene_data = {
        "id": scene.id,
        "title": scene.title,
        "description": scene.description,
        "scene_type": scene.scene_type,
        "npcs": scene.npcs,
        "monsters": scene.monsters,
        "items": scene.items
    }
    
    # Préparer le contexte
    context = {
        "scenario": {
            "id": scenario.id,
            "title": scenario.title,
            "description": scenario.description
        }
    }
    
    # Générer la description
    narrative_content = await generate_scene_description(scene_data, context)
    
    # Mettre à jour la scène
    scene.narrative_content = narrative_content
    
    await db.commit()
    await db.refresh(scene)
    
    return scene

@router.post("/{scene_id}/reorder", response_model=SceneSchema)
async def reorder_scene(
    scene_id: int,
    new_order: int,
    db: AsyncSession = Depends(get_db),
    scene_and_scenario: Tuple[Scene, Scenario] = Depends(get_scene)
):
    """Change l'ordre d'une scène dans un scénario"""
    scene, scenario = scene_and_scenario
    
    # Vérifier si l'utilisateur est le créateur du scénario
    current_user = scene_and_scenario[1].creator
    if scenario.creator_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le créateur du scénario peut réorganiser les scènes"
        )
    
    # Récupérer toutes les scènes du scénario
    result = await db.execute(
        select(Scene)
        .filter(Scene.scenario_id == scenario.id)
        .order_by(Scene.order)
    )
    
    scenes = result.scalars().all()
    
    # Vérifier si le nouvel ordre est valide
    if new_order < 0 or new_order >= len(scenes):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"L'ordre doit être compris entre 0 et {len(scenes) - 1}"
        )
    
    # Ancien ordre de la scène
    old_order = scene.order
    
    # Mettre à jour l'ordre des scènes
    if new_order > old_order:
        # Déplacer vers le bas
        for s in scenes:
            if old_order < s.order <= new_order:
                s.order -= 1
    else:
        # Déplacer vers le haut
        for s in scenes:
            if new_order <= s.order < old_order:
                s.order += 1
    
    # Mettre à jour l'ordre de la scène
    scene.order = new_order
    
    await db.commit()
    await db.refresh(scene)
    
    return scene

@router.post("/transitions", response_model=SceneTransition)
async def create_scene_transition(
    transition: SceneTransition,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """Crée une transition entre deux scènes"""
    # Vérifier l'accès aux scènes
    from_scene, from_scenario = await get_scene(transition.from_scene_id, db, current_user)
    to_scene, to_scenario = await get_scene(transition.to_scene_id, db, current_user)
    
    # Vérifier si les scènes appartiennent au même scénario
    if from_scene.scenario_id != to_scene.scenario_id:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Les scènes doivent appartenir au même scénario"
        )
    
    # Vérifier si l'utilisateur est le créateur du scénario
    if from_scenario.creator_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le créateur du scénario peut créer des transitions"
        )
    
    # Ajouter la scène de destination aux scènes suivantes
    next_scenes = from_scene.next_scenes or []
    
    # Vérifier si la transition existe déjà
    if to_scene.id in next_scenes:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Cette transition existe déjà"
        )
    
    next_scenes.append(to_scene.id)
    from_scene.next_scenes = next_scenes
    
    await db.commit()
    await db.refresh(from_scene)
    
    return transition

@router.delete("/transitions", status_code=status.HTTP_204_NO_CONTENT)
async def delete_scene_transition(
    from_scene_id: int,
    to_scene_id: int,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """Supprime une transition entre deux scènes"""
    # Vérifier l'accès aux scènes
    from_scene, from_scenario = await get_scene(from_scene_id, db, current_user)
    
    # Vérifier si l'utilisateur est le créateur du scénario
    if from_scenario.creator_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le créateur du scénario peut supprimer des transitions"
        )
    
    # Supprimer la scène de destination des scènes suivantes
    next_scenes = from_scene.next_scenes or []
    
    if to_scene_id not in next_scenes:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Cette transition n'existe pas"
        )
    
    next_scenes.remove(to_scene_id)
    from_scene.next_scenes = next_scenes
    
    await db.commit()
    
    return None