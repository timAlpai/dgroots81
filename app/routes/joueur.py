from fastapi import APIRouter, Depends, HTTPException, Request
from sqlalchemy.ext.asyncio import AsyncSession
from starlette import status

from app.schemas.joueur import JoueurCreate, JoueurOut, JoueurLogin, TokenOut
from app.core.database import get_db
from app.db.models.joueur import Joueur
from app.dependencies.joueur import get_current_joueur
from app.services.joueur_service import JoueurService # Import du service

router = APIRouter(prefix="/joueur", tags=["joueur"])

# Dépendance pour obtenir le JoueurService
async def get_joueur_service(db: AsyncSession = Depends(get_db)) -> JoueurService:
    return JoueurService(db)

@router.post("/register", response_model=JoueurOut)
async def register(
    data: JoueurCreate,
    joueur_service: JoueurService = Depends(get_joueur_service) # Injection du service
):
    joueur = await joueur_service.create_joueur(data)
    if joueur is None:
        raise HTTPException(status_code=400, detail="Email ou nom d'utilisateur déjà utilisé")

    return joueur

@router.post("/login", response_model=TokenOut)
async def login(
    data: JoueurLogin,
    request: Request = None,
    joueur_service: JoueurService = Depends(get_joueur_service) # Injection du service
):
    joueur = await joueur_service.authenticate_joueur(data)
    if joueur is None:
        # Le service retourne None si identifiants invalides ou compte inactif/banni
        # On lève une exception générique pour ne pas donner d'indices sur la raison exacte
        raise HTTPException(status_code=401, detail="Identifiants invalides")

    # Mise à jour IP / UA via le service
    await joueur_service.update_last_login(
        joueur,
        request.client.host if request else None,
        request.headers.get("user-agent")
    )

    # Création des tokens via le service
    access_token, refresh_token  = joueur_service.create_auth_tokens(joueur.id)

    return {
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "bearer"
    }


@router.get("/me", response_model=JoueurOut)
async def get_me(current_user: Joueur = Depends(get_current_joueur)):
    return current_user
