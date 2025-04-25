# app/services/personnage_service.py

from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from typing import List, Optional

from app.db.models import Personnage, Joueur
from app.schemas.personnage_joueur import PersonnageCreate, PersonnageUpdate
from app.utils.auth import is_admin_or_mj_for_personnage # Import de la fonction utilitaire

class PersonnageService:
    def __init__(self, db: AsyncSession):
        self.db = db

    async def create_personnage(self, data: PersonnageCreate, joueur_id: int) -> Personnage:
        personnage = Personnage(**data.dict(), joueur_id=joueur_id)
        self.db.add(personnage)
        await self.db.commit()
        await self.db.refresh(personnage)
        return personnage

    async def get_personnage_by_id(self, personnage_id: int) -> Optional[Personnage]:
        result = await self.db.execute(select(Personnage).where(Personnage.id == personnage_id))
        return result.scalars().first()

    async def update_personnage(self, personnage: Personnage, data: PersonnageUpdate) -> Personnage:
        for attr, value in data.dict(exclude_unset=True).items():
            setattr(personnage, attr, value)
        await self.db.commit()
        await self.db.refresh(personnage)
        return personnage

    async def list_personnages_by_joueur_id(self, joueur_id: int) -> List[Personnage]:
        result = await self.db.execute(select(Personnage).where(Personnage.joueur_id == joueur_id))
        return result.scalars().all()

    async def delete_personnage(self, personnage: Personnage):
        await self.db.delete(personnage)
        await self.db.commit()

    async def can_user_access_personnage(self, user: Joueur, personnage: Personnage) -> bool:
        # Réutilise la logique de vérification des droits existante
        return personnage.joueur_id == user.id or await is_admin_or_mj_for_personnage(user, personnage, self.db)