from pydantic import BaseModel, EmailStr, Field, field_validator
from typing import Optional, List
from app.schemas.base import BaseSchema

class UserBase(BaseModel):
    """Schéma de base pour les utilisateurs"""
    username: str
    email: EmailStr
    is_active: Optional[bool] = True
    is_superuser: Optional[bool] = False

class UserCreate(UserBase):
    """Schéma pour la création d'utilisateurs"""
    password: str
    
    @field_validator('password')
    def password_strength(cls, v):
        """Valide la force du mot de passe"""
        if len(v) < 8:
            raise ValueError('Le mot de passe doit contenir au moins 8 caractères')
        return v

class UserUpdate(BaseModel):
    """Schéma pour la mise à jour d'utilisateurs"""
    username: Optional[str] = None
    email: Optional[EmailStr] = None
    password: Optional[str] = None
    is_active: Optional[bool] = None
    is_superuser: Optional[bool] = None
    
    @field_validator('password')
    def password_strength(cls, v):
        """Valide la force du mot de passe"""
        if v is not None and len(v) < 8:
            raise ValueError('Le mot de passe doit contenir au moins 8 caractères')
        return v

class UserInDB(UserBase, BaseSchema):
    """Schéma pour les utilisateurs en base de données"""
    hashed_password: str
    total_tokens_used: int = 0
    total_game_time: float = 0.0
    total_sessions: int = 0
    total_characters: int = 0

class User(UserBase, BaseSchema):
    """Schéma pour les utilisateurs renvoyés par l'API"""
    total_tokens_used: int = 0
    total_game_time: float = 0.0
    total_sessions: int = 0
    total_characters: int = 0

class UserWithStats(User):
    """Schéma pour les utilisateurs avec statistiques détaillées"""
    # Statistiques supplémentaires qui pourraient être calculées
    average_session_time: Optional[float] = None
    favorite_character_class: Optional[str] = None
    total_actions: Optional[int] = None
    
class Token(BaseModel):
    """Schéma pour les tokens d'authentification"""
    access_token: str
    token_type: str = "bearer"

class TokenData(BaseModel):
    """Schéma pour les données contenues dans le token"""
    username: Optional[str] = None
    user_id: Optional[int] = None
    scopes: List[str] = []