from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession

from app.core.database import get_db
from app.db.models import Joueur # Garder Joueur pour get_current_joueur
from app.schemas.personnage_joueur import PersonnageCreate, PersonnageRead, PersonnageUpdate
from app.dependencies.joueur import get_current_joueur
from app.services.personnage_service import PersonnageService # Import du service

router = APIRouter(prefix="/personnages", tags=["personnages"])

# Dépendance pour obtenir le PersonnageService
async def get_personnage_service(db: AsyncSession = Depends(get_db)) -> PersonnageService:
    return PersonnageService(db)

@router.post("/", response_model=PersonnageRead)
async def create_personnage(
    data: PersonnageCreate,
    current_user: Joueur = Depends(get_current_joueur),
    personnage_service: PersonnageService = Depends(get_personnage_service) # Injection du service
):
    personnage = await personnage_service.create_personnage(data, current_user.id)
    return personnage

@router.get("/{personnage_id}", response_model=PersonnageRead)
async def read_personnage(
    personnage_id: int,
    current_user: Joueur = Depends(get_current_joueur),
    personnage_service: PersonnageService = Depends(get_personnage_service) # Injection du service
):
    personnage = await personnage_service.get_personnage_for_user(personnage_id, current_user)
    if not personnage:
        raise HTTPException(status_code=404, detail="Personnage introuvable ou accès interdit")
    return personnage

@router.put("/{personnage_id}", response_model=PersonnageRead)
async def update_personnage(
    personnage_id: int,
    data: PersonnageUpdate,
    current_user: Joueur = Depends(get_current_joueur),
    personnage_service: PersonnageService = Depends(get_personnage_service) # Injection du service
):
    updated_personnage = await personnage_service.update_personnage_for_user(personnage_id, data, current_user)
    if not updated_personnage:
         raise HTTPException(status_code=404, detail="Personnage introuvable ou modification interdite")
    return updated_personnage

@router.get("/", response_model=list[PersonnageRead])
async def list_personnages(
    current_user: Joueur = Depends(get_current_joueur),
    personnage_service: PersonnageService = Depends(get_personnage_service) # Injection du service
):
    return await personnage_service.list_personnages_by_joueur_id(current_user.id)

@router.delete("/{personnage_id}", status_code=204)
async def delete_personnage(
    personnage_id: int,
    current_user: Joueur = Depends(get_current_joueur),
    personnage_service: PersonnageService = Depends(get_personnage_service) # Injection du service
):
    success = await personnage_service.delete_personnage_for_user(personnage_id, current_user)
    if not success:
        raise HTTPException(status_code=404, detail="Personnage introuvable ou suppression interdite")
    return # FastAPI retourne 204 pour None
