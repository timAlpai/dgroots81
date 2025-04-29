from sqlalchemy import Column, String, Boolean, Integer, Float, ForeignKey, JSON, Text
from sqlalchemy.orm import relationship
from app.models.base import BaseModel

class GameSession(BaseModel):
    """Modèle pour les sessions de jeu"""
    __tablename__ = "game_sessions"
    
    name = Column(String, nullable=False)
    description = Column(Text)
    is_active = Column(Boolean, default=True)
    
    # Relation avec l'utilisateur qui a créé la session (Game Master)
    game_master_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    game_master = relationship("User", backref="game_sessions")
    
    # Métriques de session
    total_tokens_used = Column(Integer, default=0)
    total_game_time = Column(Float, default=0.0)  # En heures
    total_actions = Column(Integer, default=0)
    
    # État de la session
    current_scenario_id = Column(Integer, ForeignKey("scenarios.id"), nullable=True)
    current_scene_id = Column(Integer, ForeignKey("scenes.id"), nullable=True)
    
    # Données de contexte pour le LLM
    context_data = Column(JSON, default=dict)
    
    # Paramètres de jeu
    game_rules = Column(String, default="OSE")  # Old-School Essentials par défaut
    difficulty_level = Column(String, default="standard")  # standard, easy, hard
    
    # Relations
    characters = relationship("Character", back_populates="game_session", cascade="all, delete-orphan")
    action_logs = relationship("ActionLog", back_populates="game_session", cascade="all, delete-orphan")
    current_scenario = relationship("Scenario", foreign_keys=[current_scenario_id])
    current_scene = relationship("Scene", foreign_keys=[current_scene_id])