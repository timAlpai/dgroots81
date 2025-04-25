# app/services/joueur_service.py

from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select

from app.db.models.joueur import Joueur
from app.schemas.joueur import JoueurCreate, JoueurLogin
from app.core.security import hash_password, verify_password, create_tokens

class JoueurService:
    def __init__(self, db: AsyncSession):
        self.db = db

    async def create_joueur(self, data: JoueurCreate) -> Joueur:
        existing = await self.db.execute(
            select(Joueur).where((Joueur.email == data.email) | (Joueur.username == data.username))
        )
        if existing.scalar_one_or_none():
            return None # Indique que le joueur existe déjà

        joueur = Joueur(
            email=data.email,
            username=data.username,
            hashed_password=hash_password(data.password),
            email_confirmed=False,
        )
        self.db.add(joueur)
        await self.db.commit()
        await self.db.refresh(joueur)
        return joueur

    async def authenticate_joueur(self, data: JoueurLogin) -> Joueur | None:
        joueur = await self.db.scalar(select(Joueur).where(Joueur.email == data.email))
        if not joueur or not verify_password(data.password, joueur.hashed_password):
            return None

        if not joueur.is_active or joueur.is_banned:
             return None # Indique que le compte est inactif ou banni

        return joueur

    async def update_last_login(self, joueur: Joueur, ip: str | None, ua: str | None):
        joueur.last_login_ip = ip
        joueur.last_login_ua = ua
        await self.db.commit()
        await self.db.refresh(joueur)

    def create_auth_tokens(self, joueur_id: int) -> tuple[str, str]:
        return create_tokens({"sub": str(joueur_id)})