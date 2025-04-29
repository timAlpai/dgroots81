from sqlalchemy import Column, String, Boolean, Integer, Float
from app.models.base import BaseModel

class User(BaseModel):
    """Modèle pour les utilisateurs du système"""
    __tablename__ = "users"
    
    username = Column(String, unique=True, index=True, nullable=False)
    email = Column(String, unique=True, index=True, nullable=False)
    hashed_password = Column(String, nullable=False)
    is_active = Column(Boolean, default=True)
    is_superuser = Column(Boolean, default=False)
    
    # Métriques d'utilisation
    total_tokens_used = Column(Integer, default=0)
    total_game_time = Column(Float, default=0.0)  # En heures
    total_sessions = Column(Integer, default=0)
    total_characters = Column(Integer, default=0)