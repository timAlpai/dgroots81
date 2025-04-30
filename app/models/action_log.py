from sqlalchemy import Column, String, Integer, Float, ForeignKey, JSON, Text, DateTime, Enum
from sqlalchemy.orm import relationship
import enum
from datetime import datetime, UTC

def utc_now():
    """Retourne la date et l'heure actuelles."""
    return datetime.now()
from app.models.base import BaseModel

class ActionType(str, enum.Enum):
    """Types d'actions possibles dans le jeu"""
    DIALOGUE = "dialogue"
    COMBAT = "combat"
    MOUVEMENT = "mouvement"
    INTERACTION = "interaction"
    SORT = "sort"
    COMPETENCE = "competence"
    REPOS = "repos"
    AUTRE = "autre"

class ActionLog(BaseModel):
    """Modèle pour l'historique des actions des joueurs"""
    __tablename__ = "action_logs"
    
    # Type d'action
    action_type = Column(Enum(ActionType), nullable=False)
    
    # Description de l'action
    description = Column(Text, nullable=False)
    
    # Résultat de l'action (texte généré par l'IA)
    result = Column(Text)
    
    # Données techniques
    tokens_used = Column(Integer, default=0)
    processing_time = Column(Float, default=0.0)  # En secondes
    
    # Données de jeu
    game_data = Column(JSON, default=dict)  # Données spécifiques à l'action (dés, modificateurs, etc.)
    
    # Horodatage spécifique à l'action
    action_timestamp = Column(DateTime, default=utc_now)
    
    # Partition par date (pour optimisation des requêtes)
    action_date = Column(String, index=True)  # Format YYYY-MM-DD
    
    # Relations
    game_session_id = Column(Integer, ForeignKey("game_sessions.id"), nullable=False)
    character_id = Column(Integer, ForeignKey("characters.id"), nullable=False)
    scene_id = Column(Integer, ForeignKey("scenes.id"), nullable=True)
    
    # Relations ORM
    game_session = relationship("GameSession", back_populates="action_logs")
    character = relationship("Character", back_populates="action_logs")
    scene = relationship("Scene", back_populates="action_logs")
    
    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        # Définir automatiquement la date de l'action pour le partitionnement
        if not self.action_date and self.action_timestamp:
            self.action_date = self.action_timestamp.strftime("%Y-%m-%d")
        elif not self.action_date:
            self.action_date = datetime.now().strftime("%Y-%m-%d")