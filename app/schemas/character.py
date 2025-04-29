from pydantic import BaseModel, Field, field_validator
from typing import Optional, Dict, List, Any, ForwardRef
from enum import Enum
from app.schemas.base import BaseSchema
from app.models.character import CharacterClass

# Références circulaires
UserRef = ForwardRef('User')
GameSessionRef = ForwardRef('GameSession')
ActionLogRef = ForwardRef('ActionLog')

class CharacterBase(BaseModel):
    """Schéma de base pour les personnages"""
    name: str
    character_class: CharacterClass
    level: int = 1
    experience: int = 0
    
    # Caractéristiques OSE
    strength: int
    intelligence: int
    wisdom: int
    dexterity: int
    constitution: int
    charisma: int
    
    # Points de vie et armure
    max_hp: int
    current_hp: int
    armor_class: int = 10
    
    # Relations
    user_id: int
    game_session_id: int

class CharacterCreate(CharacterBase):
    """Schéma pour la création de personnages"""
    background: Optional[str] = None
    appearance: Optional[str] = None
    
    @field_validator('strength', 'intelligence', 'wisdom', 'dexterity', 'constitution', 'charisma')
    def validate_ability_scores(cls, v):
        if v < 3 or v > 18:
            raise ValueError("Les caractéristiques doivent être comprises entre 3 et 18")
        return v
    
    @field_validator('max_hp', 'current_hp')
    def validate_hp(cls, v):
        if v < 1:
            raise ValueError("Les points de vie doivent être positifs")
        return v

class CharacterUpdate(BaseModel):
    """Schéma pour la mise à jour de personnages"""
    name: Optional[str] = None
    level: Optional[int] = None
    experience: Optional[int] = None
    current_hp: Optional[int] = None
    armor_class: Optional[int] = None
    equipment: Optional[List[Dict[str, Any]]] = None
    inventory: Optional[List[Dict[str, Any]]] = None
    gold: Optional[int] = None
    skills: Optional[List[Dict[str, Any]]] = None
    spells: Optional[List[Dict[str, Any]]] = None
    background: Optional[str] = None
    appearance: Optional[str] = None
    is_alive: Optional[bool] = None
    
    @field_validator('level')
    def validate_level(cls, v):
        if v is not None and (v < 1 or v > 20):
            raise ValueError("Le niveau doit être compris entre 1 et 20")
        return v
    
    @field_validator('current_hp')
    def validate_hp(cls, v):
        if v is not None and v < 0:
            raise ValueError("Les points de vie ne peuvent pas être négatifs")
        return v

class CharacterInDB(CharacterBase, BaseSchema):
    """Schéma pour les personnages en base de données"""
    equipment: List[Dict[str, Any]] = Field(default_factory=list)
    inventory: List[Dict[str, Any]] = Field(default_factory=list)
    gold: int = 0
    skills: List[Dict[str, Any]] = Field(default_factory=list)
    spells: List[Dict[str, Any]] = Field(default_factory=list)
    background: Optional[str] = None
    appearance: Optional[str] = None
    is_alive: bool = True

class Character(CharacterInDB):
    """Schéma pour les personnages renvoyés par l'API"""
    # Calculer les modificateurs
    strength_mod: int = 0
    intelligence_mod: int = 0
    wisdom_mod: int = 0
    dexterity_mod: int = 0
    constitution_mod: int = 0
    charisma_mod: int = 0
    
    def __init__(self, **data):
        super().__init__(**data)
        # Calculer les modificateurs
        self.strength_mod = self._get_ability_modifier(self.strength)
        self.intelligence_mod = self._get_ability_modifier(self.intelligence)
        self.wisdom_mod = self._get_ability_modifier(self.wisdom)
        self.dexterity_mod = self._get_ability_modifier(self.dexterity)
        self.constitution_mod = self._get_ability_modifier(self.constitution)
        self.charisma_mod = self._get_ability_modifier(self.charisma)
    
    def _get_ability_modifier(self, score):
        """Calcule le modificateur selon les règles OSE"""
        if score <= 3:
            return -3
        elif score <= 5:
            return -2
        elif score <= 8:
            return -1
        elif score <= 12:
            return 0
        elif score <= 15:
            return 1
        elif score <= 17:
            return 2
        else:
            return 3

class CharacterWithDetails(Character):
    """Schéma pour les personnages avec détails supplémentaires"""
    user: Optional[Dict[str, Any]] = None
    game_session: Optional[Dict[str, Any]] = None
    action_logs: List[Dict[str, Any]] = []