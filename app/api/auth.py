from datetime import timedelta
from fastapi import APIRouter, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select

from app.core.database import get_db
from app.core.security import (
    authenticate_user,
    create_access_token,
    get_current_active_user,
    get_password_hash,
)
from app.models.user import User
from app.schemas.user import User as UserSchema, UserCreate, Token

router = APIRouter(prefix="/auth", tags=["auth"])

@router.post("/token", response_model=Token)
async def login_for_access_token(
    form_data: OAuth2PasswordRequestForm = Depends(),
    db: AsyncSession = Depends(get_db)
):
    """Endpoint pour obtenir un token JWT"""
    user = await authenticate_user(db, form_data.username, form_data.password)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Nom d'utilisateur ou mot de passe incorrect",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    # Déterminer les scopes en fonction du rôle de l'utilisateur
    scopes = ["user"]
    if user.is_superuser:
        scopes.append("admin")
    
    # Vérifier si l'utilisateur est maître de jeu d'une session
    result = await db.execute(
        select(User).filter(User.id == user.id).filter(User.game_sessions.any())
    )
    if result.scalars().first():
        scopes.append("game_master")
    
    access_token_expires = timedelta(minutes=30)
    access_token = create_access_token(
        data={"sub": user.username, "user_id": user.id},
        expires_delta=access_token_expires,
        scopes=scopes
    )
    
    return {"access_token": access_token, "token_type": "bearer"}

@router.post("/register", response_model=UserSchema)
async def register_user(user: UserCreate, db: AsyncSession = Depends(get_db)):
    """Endpoint pour créer un nouvel utilisateur"""
    # Vérifier si le nom d'utilisateur existe déjà
    result = await db.execute(select(User).filter(User.username == user.username))
    if result.scalars().first():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Ce nom d'utilisateur est déjà utilisé"
        )
    
    # Vérifier si l'email existe déjà
    result = await db.execute(select(User).filter(User.email == user.email))
    if result.scalars().first():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Cet email est déjà utilisé"
        )
    
    # Créer le nouvel utilisateur
    hashed_password = get_password_hash(user.password)
    db_user = User(
        username=user.username,
        email=user.email,
        hashed_password=hashed_password,
        is_active=user.is_active,
        is_superuser=user.is_superuser
    )
    
    db.add(db_user)
    await db.commit()
    await db.refresh(db_user)
    
    return db_user

@router.get("/me", response_model=UserSchema)
async def read_users_me(current_user: User = Depends(get_current_active_user)):
    """Endpoint pour obtenir les informations de l'utilisateur actuel"""
    return current_user

@router.post("/refresh", response_model=Token)
async def refresh_token(current_user: User = Depends(get_current_active_user)):
    """Endpoint pour rafraîchir le token JWT"""
    # Déterminer les scopes en fonction du rôle de l'utilisateur
    scopes = ["user"]
    if current_user.is_superuser:
        scopes.append("admin")
    
    # Vérifier si l'utilisateur est maître de jeu d'une session
    if hasattr(current_user, 'game_sessions') and current_user.game_sessions:
        scopes.append("game_master")
    
    access_token_expires = timedelta(minutes=30)
    access_token = create_access_token(
        data={"sub": current_user.username, "user_id": current_user.id},
        expires_delta=access_token_expires,
        scopes=scopes
    )
    
    return {"access_token": access_token, "token_type": "bearer"}