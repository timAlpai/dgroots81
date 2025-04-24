from fastapi import APIRouter, Depends, HTTPException, Request
from sqlalchemy.ext.asyncio import AsyncSession
from starlette import status
from sqlalchemy import select

from app.schemas.joueur import JoueurCreate, JoueurOut, JoueurLogin, TokenOut
from app.core.database import get_db

from app.core.security import hash_password, verify_password, create_tokens
from app.db.models.joueur import Joueur
from app.dependencies.joueur import get_current_joueur

router = APIRouter(prefix="/joueur", tags=["joueur"])

@router.post("/register", response_model=JoueurOut)
async def register(data: JoueurCreate, db: AsyncSession = Depends(get_db)):
    existing = await db.execute(
        select(Joueur).where((Joueur.email == data.email) | (Joueur.username == data.username))
    )
    if existing.scalar_one_or_none():
        raise HTTPException(status_code=400, detail="Email ou nom d'utilisateur déjà utilisé")

    joueur = Joueur(
        email=data.email,
        username=data.username,
        hashed_password=hash_password(data.password),
        email_confirmed=False,
    )
    db.add(joueur)
    await db.commit()
    await db.refresh(joueur)

    return joueur

@router.post("/login", response_model=TokenOut)
async def login(data: JoueurLogin, db: AsyncSession = Depends(get_db), request: Request = None):
    joueur = await db.scalar(select(Joueur).where(Joueur.email == data.email))
    if not joueur or not verify_password(data.password, joueur.hashed_password):
        raise HTTPException(status_code=401, detail="Identifiants invalides")

    if not joueur.is_active or joueur.is_banned:
        raise HTTPException(status_code=403, detail="Compte inactif ou banni")

    # Mise à jour IP / UA
    joueur.last_login_ip = request.client.host if request else None
    joueur.last_login_ua = request.headers.get("user-agent")
    await db.commit()

    access_token, refresh_token  = create_tokens({"sub": str(joueur.id)})

    return {
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "bearer"
    }


@router.get("/me", response_model=JoueurOut)
async def get_me(current_user: Joueur = Depends(get_current_joueur)):
    return current_user
