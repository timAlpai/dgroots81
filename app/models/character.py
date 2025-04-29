from sqlalchemy import Column, String, Integer, ForeignKey, JSON, Text, Enum, Boolean
from sqlalchemy.orm import relationship
import enum
from app.models.base import BaseModel

class CharacterClass(str, enum.Enum):
    """Classes de personnage selon OSE"""
    CLERC = "clerc"
    GUERRIER = "guerrier"
    MAGICIEN = "magicien"
    VOLEUR = "voleur"
    NAIN = "nain"
    ELFE = "elfe"
    HALFELIN = "halfelin"

class Character(BaseModel):
    """Modèle pour les personnages des joueurs"""
    __tablename__ = "characters"
    
    name = Column(String, nullable=False)
    character_class = Column(Enum(CharacterClass), nullable=False)
    level = Column(Integer, default=1)
    experience = Column(Integer, default=0)
    
    # Caractéristiques OSE
    strength = Column(Integer, nullable=False)
    intelligence = Column(Integer, nullable=False)
    wisdom = Column(Integer, nullable=False)
    dexterity = Column(Integer, nullable=False)
    constitution = Column(Integer, nullable=False)
    charisma = Column(Integer, nullable=False)
    
    # Points de vie et armure
    max_hp = Column(Integer, nullable=False)
    current_hp = Column(Integer, nullable=False)
    armor_class = Column(Integer, default=10)
    
    # Équipement et inventaire
    equipment = Column(JSON, default=list)
    inventory = Column(JSON, default=list)
    gold = Column(Integer, default=0)
    
    # Compétences et sorts
    skills = Column(JSON, default=list)
    spells = Column(JSON, default=list)
    
    # Biographie et apparence
    background = Column(Text)
    appearance = Column(Text)
    
    # État du personnage
    is_alive = Column(Boolean, default=True)
    
    # Relations
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    game_session_id = Column(Integer, ForeignKey("game_sessions.id"), nullable=False)
    
    # Relations ORM
    user = relationship("User", backref="characters")
    game_session = relationship("GameSession", back_populates="characters")
    action_logs = relationship("ActionLog", back_populates="character", cascade="all, delete-orphan")
    
    # Méthodes pour calculer les modificateurs selon les règles OSE
    def get_strength_modifier(self):
        return self._get_ability_modifier(self.strength)
    
    def get_intelligence_modifier(self):
        return self._get_ability_modifier(self.intelligence)
    
    def get_wisdom_modifier(self):
        return self._get_ability_modifier(self.wisdom)
    
    def get_dexterity_modifier(self):
        return self._get_ability_modifier(self.dexterity)
    
    def get_constitution_modifier(self):
        return self._get_ability_modifier(self.constitution)
    
    def get_charisma_modifier(self):
        return self._get_ability_modifier(self.charisma)
    
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