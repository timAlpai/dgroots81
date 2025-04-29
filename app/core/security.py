from datetime import datetime, timedelta, UTC
from typing import Optional, List, Union
from jose import JWTError, jwt
from passlib.context import CryptContext
from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer, SecurityScopes
from pydantic import ValidationError
from sqlalchemy.ext.asyncio import AsyncSession

from app.core.config import settings
from app.core.database import get_db
from app.schemas.user import TokenData
from app.models.user import User
from sqlalchemy.future import select

# Configuration du hachage de mot de passe
pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

# Configuration de l'authentification OAuth2
oauth2_scheme = OAuth2PasswordBearer(
    tokenUrl="api/auth/token",
    scopes={
        "user": "Accès utilisateur standard",
        "admin": "Accès administrateur",
        "game_master": "Accès maître de jeu",
    },
)

def verify_password(plain_password: str, hashed_password: str) -> bool:
    """Vérifie si le mot de passe en clair correspond au mot de passe haché"""
    return pwd_context.verify(plain_password, hashed_password)

def get_password_hash(password: str) -> str:
    """Génère un hash pour le mot de passe"""
    return pwd_context.hash(password)

async def authenticate_user(db: AsyncSession, username: str, password: str) -> Optional[User]:
    """Authentifie un utilisateur par son nom d'utilisateur et son mot de passe"""
    result = await db.execute(select(User).filter(User.username == username))
    user = result.scalars().first()
    
    if not user:
        return None
    if not verify_password(password, user.hashed_password):
        return None
    return user

def create_access_token(
    data: dict, 
    expires_delta: Optional[timedelta] = None,
    scopes: List[str] = ["user"]
) -> str:
    """Crée un token JWT"""
    to_encode = data.copy()
    
    if expires_delta:
        expire = datetime.now(UTC) + expires_delta
    else:
        expire = datetime.now(UTC) + timedelta(minutes=30)
    
    to_encode.update({"exp": expire, "scopes": scopes})
    encoded_jwt = jwt.encode(to_encode, settings.secret_key, algorithm=settings.algorithm)
    return encoded_jwt

async def get_current_user(
    security_scopes: SecurityScopes,
    token: str = Depends(oauth2_scheme),
    db: AsyncSession = Depends(get_db)
) -> User:
    """Récupère l'utilisateur actuel à partir du token JWT"""
    if security_scopes.scopes:
        authenticate_value = f'Bearer scope="{security_scopes.scope_str}"'
    else:
        authenticate_value = "Bearer"
    
    credentials_exception = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Impossible de valider les informations d'identification",
        headers={"WWW-Authenticate": authenticate_value},
    )
    
    try:
        payload = jwt.decode(token, settings.secret_key, algorithms=[settings.algorithm])
        username: str = payload.get("sub")
        if username is None:
            raise credentials_exception
        
        token_scopes = payload.get("scopes", [])
        user_id: int = payload.get("user_id")
        token_data = TokenData(username=username, user_id=user_id, scopes=token_scopes)
    except (JWTError, ValidationError):
        raise credentials_exception
    
    result = await db.execute(select(User).filter(User.id == user_id))
    user = result.scalars().first()
    
    if user is None:
        raise credentials_exception
    
    # Vérifier les scopes
    for scope in security_scopes.scopes:
        if scope not in token_data.scopes:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail=f"Permissions insuffisantes. Scope requis: {scope}",
                headers={"WWW-Authenticate": authenticate_value},
            )
    
    return user

async def get_current_active_user(
    current_user: User = Depends(get_current_user),
) -> User:
    """Vérifie si l'utilisateur actuel est actif"""
    if not current_user.is_active:
        raise HTTPException(status_code=400, detail="Utilisateur inactif")
    return current_user

async def get_current_superuser(
    current_user: User = Depends(get_current_active_user),
) -> User:
    """Vérifie si l'utilisateur actuel est un superutilisateur"""
    if not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN, 
            detail="Permissions insuffisantes"
        )
    return current_user