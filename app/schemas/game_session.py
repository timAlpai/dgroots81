from pydantic import BaseModel, Field, field_validator
from typing import Optional, Dict, List, Any, ForwardRef
from datetime import datetime
from app.schemas.base import BaseSchema

# Références circulaires
CharacterRef = ForwardRef('Character')
ScenarioRef = ForwardRef('Scenario')
SceneRef = ForwardRef('Scene')
UserRef = ForwardRef('User')

class GameSessionBase(BaseModel):
    """Schéma de base pour les sessions de jeu"""
    name: str
    description: Optional[str] = None
    is_active: Optional[bool] = True
    game_master_id: int
    game_rules: Optional[str] = "OSE"
    difficulty_level: Optional[str] = "standard"

class GameSessionCreate(GameSessionBase):
    """Schéma pour la création de sessions de jeu"""
    @field_validator('difficulty_level')
    def validate_difficulty(cls, v):
        allowed = ["easy", "standard", "hard"]
        if v not in allowed:
            raise ValueError(f"La difficulté doit être l'une des suivantes: {', '.join(allowed)}")
        return v
    
    @field_validator('game_rules')
    def validate_rules(cls, v):
        allowed = ["OSE", "DnD5e", "Custom"]
        if v not in allowed:
            raise ValueError(f"Les règles doivent être l'une des suivantes: {', '.join(allowed)}")
        return v

class GameSessionUpdate(BaseModel):
    """Schéma pour la mise à jour de sessions de jeu"""
    name: Optional[str] = None
    description: Optional[str] = None
    is_active: Optional[bool] = None
    current_scenario_id: Optional[int] = None
    current_scene_id: Optional[int] = None
    game_rules: Optional[str] = None
    difficulty_level: Optional[str] = None
    context_data: Optional[Dict[str, Any]] = None
    
    @field_validator('difficulty_level')
    def validate_difficulty(cls, v):
        if v is not None:
            allowed = ["easy", "standard", "hard"]
            if v not in allowed:
                raise ValueError(f"La difficulté doit être l'une des suivantes: {', '.join(allowed)}")
        return v
    
    @field_validator('game_rules')
    def validate_rules(cls, v):
        if v is not None:
            allowed = ["OSE", "DnD5e", "Custom"]
            if v not in allowed:
                raise ValueError(f"Les règles doivent être l'une des suivantes: {', '.join(allowed)}")
        return v

class GameSessionInDB(GameSessionBase, BaseSchema):
    """Schéma pour les sessions de jeu en base de données"""
    total_tokens_used: int = 0
    total_game_time: float = 0.0
    total_actions: int = 0
    current_scenario_id: Optional[int] = None
    current_scene_id: Optional[int] = None
    context_data: Dict[str, Any] = Field(default_factory=dict)

class GameSession(GameSessionInDB):
    """Schéma pour les sessions de jeu renvoyées par l'API"""
    pass

class GameSessionWithDetails(GameSession):
    """Schéma pour les sessions de jeu avec détails supplémentaires"""
    characters: List[Dict[str, Any]] = []
    current_scenario: Optional[Dict[str, Any]] = None
    current_scene: Optional[Dict[str, Any]] = None
    game_master: Optional[Dict[str, Any]] = None

class GameSessionState(BaseModel):
    """Schéma pour l'état d'une session de jeu en temps réel (stocké dans Redis)"""
    session_id: int
    name: str
    active_characters: List[int] = []  # IDs des personnages actifs
    current_scenario_id: Optional[int] = None
    current_scene_id: Optional[int] = None
    last_action_id: Optional[int] = None
    context_window: List[Dict[str, Any]] = []  # Historique récent pour le contexte LLM
    session_start_time: datetime = Field(default_factory=datetime.utcnow)
    last_activity_time: datetime = Field(default_factory=datetime.utcnow)
    game_state: Dict[str, Any] = Field(default_factory=dict)  # État du jeu (position, inventaire, etc.)