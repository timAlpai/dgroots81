from pydantic import BaseModel, Field
from typing import Optional, Dict, Any, List, ForwardRef
from datetime import datetime, UTC
from app.schemas.base import BaseSchema
from app.models.action_log import ActionType

# Références circulaires
GameSessionRef = ForwardRef('GameSession')
CharacterRef = ForwardRef('Character')
SceneRef = ForwardRef('Scene')

class ActionLogBase(BaseModel):
    """Schéma de base pour les logs d'action"""
    action_type: ActionType
    description: str
    game_session_id: int
    character_id: int
    scene_id: Optional[int] = None

class ActionLogCreate(ActionLogBase):
    """Schéma pour la création de logs d'action"""
    game_data: Optional[Dict[str, Any]] = Field(default_factory=dict)
    action_timestamp: Optional[datetime] = Field(default_factory=lambda: datetime.now(UTC))

class ActionLogUpdate(BaseModel):
    """Schéma pour la mise à jour de logs d'action"""
    result: Optional[str] = None
    tokens_used: Optional[int] = None
    processing_time: Optional[float] = None

class ActionLogInDB(ActionLogBase, BaseSchema):
    """Schéma pour les logs d'action en base de données"""
    result: Optional[str] = None
    tokens_used: int = 0
    processing_time: float = 0.0
    game_data: Dict[str, Any] = Field(default_factory=dict)
    action_timestamp: datetime = Field(default_factory=lambda: datetime.now(UTC))
    action_date: str  # Format YYYY-MM-DD

class ActionLog(ActionLogInDB):
    """Schéma pour les logs d'action renvoyés par l'API"""
    pass

class ActionLogWithDetails(ActionLog):
    """Schéma pour les logs d'action avec détails supplémentaires"""
    game_session: Optional[Dict[str, Any]] = None
    character: Optional[Dict[str, Any]] = None
    scene: Optional[Dict[str, Any]] = None

class ActionRequest(BaseModel):
    """Schéma pour les requêtes d'action des joueurs"""
    action_type: ActionType
    description: str
    game_data: Optional[Dict[str, Any]] = Field(default_factory=dict)
    character_id: int
    scene_id: Optional[int] = None

class ActionResponse(BaseModel):
    """Schéma pour les réponses aux actions des joueurs"""
    action_id: int
    result: str
    game_data: Dict[str, Any] = Field(default_factory=dict)
    tokens_used: int
    processing_time: float
    timestamp: datetime
    
    # Données de jeu mises à jour
    character_updates: Optional[Dict[str, Any]] = None
    scene_updates: Optional[Dict[str, Any]] = None
    
    # Métadonnées pour le client
    next_possible_actions: List[Dict[str, Any]] = Field(default_factory=list)
    narrative_context: Optional[str] = None