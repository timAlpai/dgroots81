from fastapi import Depends, HTTPException, status
from jose import JWTError, jwt
from sqlalchemy.ext.asyncio import AsyncSession

from app.core.config import settings
from app.db.models.joueur import Joueur
from app.core.database import get_db

from app.schemas.token import TokenPayload  # On va le créer juste après
from fastapi.security import OAuth2PasswordBearer

oauth2_scheme = OAuth2PasswordBearer(tokenUrl="/api/v1/joueur/login")

async def get_current_joueur(
    token: str = Depends(oauth2_scheme),
    db: AsyncSession = Depends(get_db),
) -> Joueur:
    try:
        payload = jwt.decode(token, settings.secret_key, algorithms=[settings.algorithm])
        token_data = TokenPayload(**payload)
    except JWTError:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token invalide")

    joueur_service = JoueurService(db)
    joueur = await joueur_service.get_joueur_by_id(token_data.sub)

    if not joueur:
        raise HTTPException(status_code=404, detail="Joueur introuvable")
    if not joueur.is_active:
        raise HTTPException(status_code=403, detail="Compte inactif")
    if joueur.is_banned:
        raise HTTPException(status_code=403, detail="Compte banni")

    return joueur
