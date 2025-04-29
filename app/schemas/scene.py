from pydantic import BaseModel, Field, field_validator
from typing import Optional, Dict, List, Any, ForwardRef
from app.schemas.base import BaseSchema
from app.models.scene import SceneType

# Références circulaires
ScenarioRef = ForwardRef('Scenario')
ActionLogRef = ForwardRef('ActionLog')

class SceneBase(BaseModel):
    """Schéma de base pour les scènes"""
    title: str
    description: str
    scene_type: SceneType
    order: int = 0
    scenario_id: int

class SceneCreate(SceneBase):
    """Schéma pour la création de scènes"""
    narrative_content: Optional[str] = None
    markdown_content: Optional[str] = None
    context_data: Optional[Dict[str, Any]] = Field(default_factory=dict)
    npcs: Optional[List[Dict[str, Any]]] = Field(default_factory=list)
    monsters: Optional[List[Dict[str, Any]]] = Field(default_factory=list)
    items: Optional[List[Dict[str, Any]]] = Field(default_factory=list)
    entry_conditions: Optional[Dict[str, Any]] = Field(default_factory=dict)
    exit_conditions: Optional[Dict[str, Any]] = Field(default_factory=dict)
    next_scenes: Optional[List[int]] = Field(default_factory=list)
    resources: Optional[Dict[str, Any]] = Field(default_factory=dict)

class SceneUpdate(BaseModel):
    """Schéma pour la mise à jour de scènes"""
    title: Optional[str] = None
    description: Optional[str] = None
    scene_type: Optional[SceneType] = None
    order: Optional[int] = None
    narrative_content: Optional[str] = None
    markdown_content: Optional[str] = None
    context_data: Optional[Dict[str, Any]] = None
    npcs: Optional[List[Dict[str, Any]]] = None
    monsters: Optional[List[Dict[str, Any]]] = None
    items: Optional[List[Dict[str, Any]]] = None
    entry_conditions: Optional[Dict[str, Any]] = None
    exit_conditions: Optional[Dict[str, Any]] = None
    next_scenes: Optional[List[int]] = None
    resources: Optional[Dict[str, Any]] = None

class SceneInDB(SceneBase, BaseSchema):
    """Schéma pour les scènes en base de données"""
    narrative_content: Optional[str] = None
    markdown_content: Optional[str] = None
    context_data: Dict[str, Any] = Field(default_factory=dict)
    npcs: List[Dict[str, Any]] = Field(default_factory=list)
    monsters: List[Dict[str, Any]] = Field(default_factory=list)
    items: List[Dict[str, Any]] = Field(default_factory=list)
    entry_conditions: Dict[str, Any] = Field(default_factory=dict)
    exit_conditions: Dict[str, Any] = Field(default_factory=dict)
    next_scenes: List[int] = Field(default_factory=list)
    resources: Dict[str, Any] = Field(default_factory=dict)

class Scene(SceneInDB):
    """Schéma pour les scènes renvoyées par l'API"""
    pass

class SceneWithDetails(Scene):
    """Schéma pour les scènes avec détails supplémentaires"""
    scenario: Optional[Dict[str, Any]] = None
    action_logs: List[Dict[str, Any]] = []
    
    # Données calculées
    action_count: int = 0
    
    def __init__(self, **data):
        super().__init__(**data)
        if hasattr(self, 'action_logs'):
            self.action_count = len(self.action_logs)

class SceneTransition(BaseModel):
    """Schéma pour les transitions entre scènes"""
    from_scene_id: int
    to_scene_id: int
    transition_type: str  # "automatic", "conditional", "player_choice"
    condition: Optional[Dict[str, Any]] = None
    description: Optional[str] = None