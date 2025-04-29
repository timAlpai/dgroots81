from sqlalchemy import Column, String, Integer, ForeignKey, JSON, Text, Boolean, Enum
from sqlalchemy.orm import relationship
import enum
from app.models.base import BaseModel

class SceneType(str, enum.Enum):
    """Types de scènes possibles"""
    INTRODUCTION = "introduction"
    EXPLORATION = "exploration"
    COMBAT = "combat"
    DIALOGUE = "dialogue"
    PUZZLE = "puzzle"
    REPOS = "repos"
    CONCLUSION = "conclusion"

class Scene(BaseModel):
    """Modèle pour les scènes de jeu"""
    __tablename__ = "scenes"
    
    title = Column(String, nullable=False)
    description = Column(Text, nullable=False)
    
    # Type de scène
    scene_type = Column(Enum(SceneType), nullable=False)
    
    # Ordre dans le scénario
    order = Column(Integer, default=0)
    
    # Contenu narratif
    narrative_content = Column(Text)
    
    # Contenu markdown avec codes couleur
    markdown_content = Column(Text)
    
    # Données structurées pour le LLM
    context_data = Column(JSON, default=dict)
    
    # Personnages non-joueurs (PNJ)
    npcs = Column(JSON, default=list)
    
    # Monstres et ennemis
    monsters = Column(JSON, default=list)
    
    # Objets et trésors
    items = Column(JSON, default=list)
    
    # Conditions d'entrée et de sortie
    entry_conditions = Column(JSON, default=dict)
    exit_conditions = Column(JSON, default=dict)
    
    # Scènes suivantes possibles (pour les scénarios non-linéaires)
    next_scenes = Column(JSON, default=list)
    
    # Ressources (images, cartes, etc.)
    resources = Column(JSON, default=dict)
    
    # Relations
    scenario_id = Column(Integer, ForeignKey("scenarios.id"), nullable=False)
    
    # Relations ORM
    scenario = relationship("Scenario", back_populates="scenes")
    action_logs = relationship("ActionLog", back_populates="scene")