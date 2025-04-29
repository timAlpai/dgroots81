from fastapi import APIRouter, Depends, HTTPException, status, UploadFile, File
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from sqlalchemy.orm import selectinload
from typing import List, Optional

from app.core.database import get_db
from app.core.security import get_current_active_user
from app.core.dependencies import get_scenario
from app.models.user import User
from app.models.scenario import Scenario
from app.models.scene import Scene
from app.schemas.scenario import (
    ScenarioCreate,
    Scenario as ScenarioSchema,
    ScenarioUpdate,
    ScenarioWithDetails
)
from app.services.markdown_parser import markdown_parser

router = APIRouter(prefix="/scenarios", tags=["scenarios"])

@router.get("/", response_model=List[ScenarioSchema])
async def read_scenarios(
    skip: int = 0,
    limit: int = 100,
    published_only: bool = False,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """
    Récupère tous les scénarios.
    Si published_only est True, récupère uniquement les scénarios publiés.
    Sinon, récupère les scénarios publiés et ceux créés par l'utilisateur actuel.
    """
    query = select(Scenario)
    
    if published_only:
        query = query.filter(Scenario.is_published == True)
    else:
        # Récupérer les scénarios publiés et ceux créés par l'utilisateur
        query = query.filter(
            (Scenario.is_published == True) | (Scenario.creator_id == current_user.id)
        )
    
    # Si l'utilisateur est un administrateur, récupérer tous les scénarios
    if current_user.is_superuser:
        query = select(Scenario)
    
    # Appliquer pagination
    query = query.offset(skip).limit(limit)
    
    result = await db.execute(query)
    scenarios = result.scalars().all()
    
    return scenarios

@router.post("/", response_model=ScenarioSchema)
async def create_scenario(
    scenario: ScenarioCreate,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """Crée un nouveau scénario"""
    # Vérifier si l'utilisateur est le créateur du scénario
    if scenario.creator_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Vous ne pouvez créer un scénario qu'avec vous-même comme créateur"
        )
    
    # Créer le scénario
    db_scenario = Scenario(**scenario.model_dump())
    
    db.add(db_scenario)
    await db.commit()
    await db.refresh(db_scenario)
    
    return db_scenario

@router.get("/{scenario_id}", response_model=ScenarioWithDetails)
async def read_scenario(
    scenario_id: int,
    db: AsyncSession = Depends(get_db),
    scenario: Scenario = Depends(get_scenario)
):
    """Récupère un scénario par son ID avec tous les détails"""
    # Charger les relations
    result = await db.execute(
        select(Scenario)
        .filter(Scenario.id == scenario_id)
        .options(
            selectinload(Scenario.creator),
            selectinload(Scenario.scenes)
        )
    )
    
    scenario_with_details = result.scalars().first()
    
    if not scenario_with_details:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Scénario avec l'ID {scenario_id} non trouvé"
        )
    
    return scenario_with_details

@router.put("/{scenario_id}", response_model=ScenarioSchema)
async def update_scenario(
    scenario_id: int,
    scenario_update: ScenarioUpdate,
    db: AsyncSession = Depends(get_db),
    scenario: Scenario = Depends(get_scenario)
):
    """Met à jour un scénario"""
    # Vérifier si l'utilisateur est le créateur du scénario
    if scenario.creator_id != scenario.creator.id and not scenario.creator.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le créateur peut modifier le scénario"
        )
    
    # Mettre à jour les champs
    update_data = scenario_update.model_dump(exclude_unset=True)
    
    # Appliquer les mises à jour
    for key, value in update_data.items():
        setattr(scenario, key, value)
    
    await db.commit()
    await db.refresh(scenario)
    
    return scenario

@router.delete("/{scenario_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_scenario(
    scenario_id: int,
    db: AsyncSession = Depends(get_db),
    scenario: Scenario = Depends(get_scenario)
):
    """Supprime un scénario"""
    # Vérifier si l'utilisateur est le créateur du scénario
    if scenario.creator_id != scenario.creator.id and not scenario.creator.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le créateur peut supprimer le scénario"
        )
    
    # Supprimer le scénario
    await db.delete(scenario)
    await db.commit()
    
    return None

@router.post("/{scenario_id}/publish", response_model=ScenarioSchema)
async def publish_scenario(
    scenario_id: int,
    db: AsyncSession = Depends(get_db),
    scenario: Scenario = Depends(get_scenario)
):
    """Publie un scénario"""
    # Vérifier si l'utilisateur est le créateur du scénario
    if scenario.creator_id != scenario.creator.id and not scenario.creator.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le créateur peut publier le scénario"
        )
    
    # Vérifier si le scénario a au moins une scène
    result = await db.execute(select(Scene).filter(Scene.scenario_id == scenario_id))
    scenes = result.scalars().all()
    
    if not scenes:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Le scénario doit avoir au moins une scène pour être publié"
        )
    
    # Publier le scénario
    scenario.is_published = True
    
    await db.commit()
    await db.refresh(scenario)
    
    return scenario

@router.post("/{scenario_id}/unpublish", response_model=ScenarioSchema)
async def unpublish_scenario(
    scenario_id: int,
    db: AsyncSession = Depends(get_db),
    scenario: Scenario = Depends(get_scenario)
):
    """Dépublie un scénario"""
    # Vérifier si l'utilisateur est le créateur du scénario
    if scenario.creator_id != scenario.creator.id and not scenario.creator.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Seul le créateur peut dépublier le scénario"
        )
    
    # Dépublier le scénario
    scenario.is_published = False
    
    await db.commit()
    await db.refresh(scenario)
    
    return scenario

@router.post("/import", response_model=ScenarioSchema)
async def import_scenario_from_markdown(
    file: UploadFile = File(...),
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """Importe un scénario à partir d'un fichier markdown"""
    # Vérifier le type de fichier
    if not file.filename.endswith(('.md', '.markdown')):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Le fichier doit être au format markdown (.md ou .markdown)"
        )
    
    # Lire le contenu du fichier
    content = await file.read()
    
    # Sauvegarder temporairement le fichier
    import tempfile
    import os
    
    with tempfile.NamedTemporaryFile(delete=False, suffix='.md') as temp_file:
        temp_file.write(content)
        temp_file_path = temp_file.name
    
    try:
        # Parser le fichier markdown
        scenario_data = markdown_parser.parse_scenario_file(temp_file_path)
        
        # Extraire les métadonnées
        metadata = scenario_data.get("metadata", {})
        
        # Créer le scénario
        scenario = Scenario(
            title=metadata.get("title", os.path.splitext(file.filename)[0]),
            description=metadata.get("description", ""),
            introduction=metadata.get("introduction", ""),
            conclusion=metadata.get("conclusion", ""),
            recommended_level=metadata.get("level", 1),
            difficulty=metadata.get("difficulty", "standard"),
            tags=metadata.get("tags", []),
            is_published=False,
            creator_id=current_user.id,
            context_data=metadata.get("context", {}),
            resources=metadata.get("resources", {})
        )
        
        db.add(scenario)
        await db.commit()
        await db.refresh(scenario)
        
        # Créer les scènes
        sections = scenario_data.get("sections", [])
        
        for i, section in enumerate(sections):
            scene = Scene(
                title=section.get("title", f"Scène {i+1}"),
                description=section.get("content", "")[:200] + "..." if len(section.get("content", "")) > 200 else section.get("content", ""),
                scene_type="EXPLORATION",  # Type par défaut
                order=i,
                narrative_content=section.get("content", ""),
                markdown_content=section.get("content", ""),
                scenario_id=scenario.id
            )
            
            db.add(scene)
        
        await db.commit()
        
        # Récupérer le scénario avec ses scènes
        result = await db.execute(
            select(Scenario)
            .filter(Scenario.id == scenario.id)
            .options(selectinload(Scenario.scenes))
        )
        
        scenario_with_scenes = result.scalars().first()
        
        return scenario_with_scenes
    
    finally:
        # Supprimer le fichier temporaire
        os.unlink(temp_file_path)