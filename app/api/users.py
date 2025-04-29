from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.future import select
from typing import List

from app.core.database import get_db
from app.core.security import get_current_active_user, get_current_superuser, get_password_hash
from app.models.user import User
from app.schemas.user import User as UserSchema, UserCreate, UserUpdate, UserWithStats

router = APIRouter(prefix="/users", tags=["users"])

@router.get("/", response_model=List[UserSchema])
async def read_users(
    skip: int = 0,
    limit: int = 100,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_superuser)
):
    """
    Récupère tous les utilisateurs.
    Nécessite des droits d'administrateur.
    """
    result = await db.execute(select(User).offset(skip).limit(limit))
    users = result.scalars().all()
    return users

@router.post("/", response_model=UserSchema)
async def create_user(
    user: UserCreate,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_superuser)
):
    """
    Crée un nouvel utilisateur.
    Nécessite des droits d'administrateur.
    """
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

@router.get("/{user_id}", response_model=UserWithStats)
async def read_user(
    user_id: int,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """
    Récupère un utilisateur par son ID.
    Les utilisateurs normaux ne peuvent voir que leur propre profil.
    Les administrateurs peuvent voir tous les profils.
    """
    if user_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Accès non autorisé"
        )
    
    result = await db.execute(select(User).filter(User.id == user_id))
    user = result.scalars().first()
    
    if user is None:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Utilisateur avec l'ID {user_id} non trouvé"
        )
    
    # Calculer des statistiques supplémentaires
    user_stats = UserWithStats.model_validate(user)
    
    # Calculer le temps moyen par session
    if user.total_sessions > 0:
        user_stats.average_session_time = user.total_game_time / user.total_sessions
    
    # Récupérer la classe de personnage préférée (à implémenter plus tard)
    # user_stats.favorite_character_class = ...
    
    # Récupérer le nombre total d'actions (à implémenter plus tard)
    # user_stats.total_actions = ...
    
    return user_stats

@router.put("/{user_id}", response_model=UserSchema)
async def update_user(
    user_id: int,
    user_update: UserUpdate,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_active_user)
):
    """
    Met à jour un utilisateur.
    Les utilisateurs normaux ne peuvent mettre à jour que leur propre profil.
    Les administrateurs peuvent mettre à jour tous les profils.
    """
    if user_id != current_user.id and not current_user.is_superuser:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Accès non autorisé"
        )
    
    result = await db.execute(select(User).filter(User.id == user_id))
    db_user = result.scalars().first()
    
    if db_user is None:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Utilisateur avec l'ID {user_id} non trouvé"
        )
    
    # Mettre à jour les champs
    update_data = user_update.model_dump(exclude_unset=True)
    
    # Hacher le mot de passe si fourni
    if "password" in update_data:
        update_data["hashed_password"] = get_password_hash(update_data.pop("password"))
    
    # Vérifier si le nom d'utilisateur existe déjà
    if "username" in update_data and update_data["username"] != db_user.username:
        result = await db.execute(select(User).filter(User.username == update_data["username"]))
        if result.scalars().first():
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Ce nom d'utilisateur est déjà utilisé"
            )
    
    # Vérifier si l'email existe déjà
    if "email" in update_data and update_data["email"] != db_user.email:
        result = await db.execute(select(User).filter(User.email == update_data["email"]))
        if result.scalars().first():
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Cet email est déjà utilisé"
            )
    
    # Appliquer les mises à jour
    for key, value in update_data.items():
        setattr(db_user, key, value)
    
    await db.commit()
    await db.refresh(db_user)
    
    return db_user

@router.delete("/{user_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_user(
    user_id: int,
    db: AsyncSession = Depends(get_db),
    current_user: User = Depends(get_current_superuser)
):
    """
    Supprime un utilisateur.
    Nécessite des droits d'administrateur.
    """
    result = await db.execute(select(User).filter(User.id == user_id))
    db_user = result.scalars().first()
    
    if db_user is None:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Utilisateur avec l'ID {user_id} non trouvé"
        )
    
    await db.delete(db_user)
    await db.commit()
    
    return None