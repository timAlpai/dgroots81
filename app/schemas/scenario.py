from pydantic import BaseModel, Field, field_validator
from typing import Optional, Dict, List, Any, ForwardRef
from app.schemas.base import BaseSchema

# Références circulaires
UserRef = ForwardRef('User')
SceneRef = ForwardRef('Scene')

class ScenarioBase(BaseModel):
    """Schéma de base pour les scénarios"""
    title: str
    description: str
    recommended_level: int = 1
    difficulty: str = "standard"
    creator_id: int

class ScenarioCreate(ScenarioBase):
    """Schéma pour la création de scénarios"""
    introduction: Optional[str] = None
    conclusion: Optional[str] = None
    tags: Optional[List[str]] = Field(default_factory=list)
    is_published: bool = False
    context_data: Optional[Dict[str, Any]] = Field(default_factory=dict)
    resources: Optional[Dict[str, Any]] = Field(default_factory=dict)
    
    @field_validator('difficulty')
    def validate_difficulty(cls, v):
        allowed = ["easy", "standard", "hard"]
        if v not in allowed:
            raise ValueError(f"La difficulté doit être l'une des suivantes: {', '.join(allowed)}")
        return v
    
    @field_validator('recommended_level')
    def validate_level(cls, v):
        if v < 1 or v > 20:
            raise ValueError("Le niveau recommandé doit être compris entre 1 et 20")
        return v

class ScenarioUpdate(BaseModel):
    """Schéma pour la mise à jour de scénarios"""
    title: Optional[str] = None
    description: Optional[str] = None
    recommended_level: Optional[int] = None
    difficulty: Optional[str] = None
    introduction: Optional[str] = None
    conclusion: Optional[str] = None
    tags: Optional[List[str]] = None
    is_published: Optional[bool] = None
    context_data: Optional[Dict[str, Any]] = None
    resources: Optional[Dict[str, Any]] = None
    
    @field_validator('difficulty')
    def validate_difficulty(cls, v):
        if v is not None:
            allowed = ["easy", "standard", "hard"]
            if v not in allowed:
                raise ValueError(f"La difficulté doit être l'une des suivantes: {', '.join(allowed)}")
        return v
    
    @field_validator('recommended_level')
    def validate_level(cls, v):
        if v is not None and (v < 1 or v > 20):
            raise ValueError("Le niveau recommandé doit être compris entre 1 et 20")
        return v

class ScenarioInDB(ScenarioBase, BaseSchema):
    """Schéma pour les scénarios en base de données"""
    introduction: Optional[str] = None
    conclusion: Optional[str] = None
    tags: List[str] = Field(default_factory=list)
    is_published: bool = False
    context_data: Dict[str, Any] = Field(default_factory=dict)
    resources: Dict[str, Any] = Field(default_factory=dict)

class Scenario(ScenarioInDB):
    """Schéma pour les scénarios renvoyés par l'API"""
    pass

class ScenarioWithDetails(Scenario):
    """Schéma pour les scénarios avec détails supplémentaires"""
    creator: Optional[Dict[str, Any]] = None
    scenes: List[Dict[str, Any]] = []
    scene_count: int = 0
    
    def __init__(self, **data):
        super().__init__(**data)
        if hasattr(self, 'scenes'):
            self.scene_count = len(self.scenes)