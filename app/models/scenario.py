from sqlalchemy import Column, String, Integer, ForeignKey, JSON, Text, Boolean
from sqlalchemy.orm import relationship
from app.models.base import BaseModel

class Scenario(BaseModel):
    """Modèle pour les scénarios de jeu"""
    __tablename__ = "scenarios"
    
    title = Column(String, nullable=False)
    description = Column(Text, nullable=False)
    
    # Niveau recommandé et difficulté
    recommended_level = Column(Integer, default=1)
    difficulty = Column(String, default="standard")  # easy, standard, hard
    
    # Contenu narratif
    introduction = Column(Text)
    conclusion = Column(Text)
    
    # Métadonnées
    tags = Column(JSON, default=list)  # ["fantasy", "dungeon", "horror", etc.]
    is_published = Column(Boolean, default=False)
    
    # Créateur du scénario
    creator_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    
    # Relations ORM
    creator = relationship("User", backref="created_scenarios")
    scenes = relationship("Scene", back_populates="scenario", cascade="all, delete-orphan")
    
    # Données structurées pour le LLM
    context_data = Column(JSON, default=dict)
    
    # Ressources (images, cartes, etc.)
    resources = Column(JSON, default=dict)