from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select

from app.core.database import get_db
from app.db.models import Personnage, Joueur
from app.schemas.personnage_joueur import PersonnageCreate, PersonnageRead, PersonnageUpdate
from app.dependencies.joueur import get_current_joueur
from app.utils.auth import is_admin_or_mj_for_personnage

router = APIRouter(prefix="/personnages", tags=["personnages"])

@router.post("/", response_model=PersonnageRead)
async def create_personnage(
    data: PersonnageCreate,
    current_user: Joueur = Depends(get_current_joueur),
    db: AsyncSession = Depends(get_db)
):
    personnage = Personnage(**data.dict(), joueur_id=current_user.id)
    db.add(personnage)
    await db.commit()
    await db.refresh(personnage)
    return personnage

@router.get("/{personnage_id}", response_model=PersonnageRead)
async def read_personnage(
    personnage_id: int,
    current_user: Joueur = Depends(get_current_joueur),
    db: AsyncSession = Depends(get_db)
):
    result = await db.execute(select(Personnage).where(Personnage.id == personnage_id))
    personnage = result.scalars().first()
    if not personnage:
        raise HTTPException(status_code=404, detail="Personnage introuvable")

    if personnage.joueur_id != current_user.id and not await is_admin_or_mj_for_personnage(current_user, personnage, db):
        raise HTTPException(status_code=403, detail="Accès interdit")

    return personnage

@router.put("/{personnage_id}", response_model=PersonnageRead)
async def update_personnage(
    personnage_id: int,
    data: PersonnageUpdate,
    current_user: Joueur = Depends(get_current_joueur),
    db: AsyncSession = Depends(get_db)
):
    result = await db.execute(select(Personnage).where(Personnage.id == personnage_id))
    personnage = result.scalars().first()
    if not personnage:
        raise HTTPException(status_code=404, detail="Personnage introuvable")

    if personnage.joueur_id != current_user.id and not await is_admin_or_mj_for_personnage(current_user, personnage, db):
        raise HTTPException(status_code=403, detail="Modification interdite")

    for attr, value in data.dict(exclude_unset=True).items():
        setattr(personnage, attr, value)

    await db.commit()
    await db.refresh(personnage)
    return personnage

@router.get("/", response_model=list[PersonnageRead])
async def list_personnages(
    current_user: Joueur = Depends(get_current_joueur),
    db: AsyncSession = Depends(get_db)
):
    result = await db.execute(select(Personnage).where(Personnage.joueur_id == current_user.id))
    return result.scalars().all()

@router.delete("/{personnage_id}", status_code=204)
async def delete_personnage(
    personnage_id: int,
    current_user: Joueur = Depends(get_current_joueur),
    db: AsyncSession = Depends(get_db)
):
    result = await db.execute(select(Personnage).where(Personnage.id == personnage_id))
    personnage = result.scalars().first()

    if not personnage:
        raise HTTPException(status_code=404, detail="Personnage introuvable")

    if personnage.joueur_id != current_user.id and not await is_admin_or_mj_for_personnage(current_user, personnage, db):
        raise HTTPException(status_code=403, detail="Suppression interdite")

    await db.delete(personnage)
    await db.commit()
