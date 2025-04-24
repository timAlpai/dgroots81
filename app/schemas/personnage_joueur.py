from pydantic import BaseModel, Field
from typing import Optional, List
from datetime import datetime

class Caractéristiques(BaseModel):
    force: int
    mod_force: int
    intelligence: int
    mod_intelligence: int
    sagesse: int
    mod_sagesse: int
    dexterite: int
    mod_dexterite: int
    constitution: int
    mod_constitution: int
    charisme: int
    mod_charisme: int

class Sauvegardes(BaseModel):
    mort_poison: int
    baguettes: int
    paralysie_petrefaction: int
    souffles: int
    sorts_batons: int

class SortMemorise(BaseModel):
    niveau: int
    sorts: List[str]

class Suivant(BaseModel):
    nom: str
    classe: Optional[str]
    niveau: Optional[int]
    loyauté: int
    paie: str
    part_du_butin: str

class PersonnageJoueur(BaseModel):
    # 1. Informations de base
    nom: str
    classe: str
    race: Optional[str] = "Humain"
    alignement: str
    niveau: int = 1
    experience: int = 0

    # 2. Caractéristiques
    stats: Caractéristiques

    # 3. Combat & Sauvegardes
    points_de_vie_max: int
    points_de_vie: int
    des_de_vie: str
    classe_armure: int
    thac0: int
    sauvegardes: Sauvegardes

    # 4. Déplacement
    deplacement_base: int
    deplacement_rencontre: int

    # 5. Capacités magiques
    sorts_memorises: Optional[List[SortMemorise]] = []
    livre_de_sorts: Optional[List[str]] = []

    # 6. Inventaire
    or_possede: int
    monnaie: Optional[dict] = Field(default_factory=dict)
    equipement: List[str] = Field(default_factory=list)
    objets_magiques: Optional[List[str]] = []

    # 7. Divers
    langues_connues: List[str]
    suivants: Optional[List[Suivant]] = []
    domaine: Optional[str] = None
    date_creation: datetime = Field(default_factory=datetime.utcnow)
class PersonnageCreate(PersonnageJoueur):
    """Tout est requis à la création sauf la date de création (auto)"""
    pass

class PersonnageRead(PersonnageJoueur):
    id: int
    joueur_id: int

    class Config:
        orm_mode = True

class PersonnageUpdate(BaseModel):
    nom: Optional[str] = None
    classe: Optional[str] = None
    race: Optional[str] = None
    alignement: Optional[str] = None
    niveau: Optional[int] = None
    experience: Optional[int] = None
    stats: Optional[Caractéristiques] = None
    points_de_vie_max: Optional[int] = None
    points_de_vie: Optional[int] = None
    des_de_vie: Optional[str] = None
    classe_armure: Optional[int] = None
    thac0: Optional[int] = None
    sauvegardes: Optional[Sauvegardes] = None
    deplacement_base: Optional[int] = None
    deplacement_rencontre: Optional[int] = None
    sorts_memorises: Optional[List[SortMemorise]] = None
    livre_de_sorts: Optional[List[str]] = None
    or_possede: Optional[int] = None
    monnaie: Optional[dict] = None
    equipement: Optional[List[str]] = None
    objets_magiques: Optional[List[str]] = None
    langues_connues: Optional[List[str]] = None
    suivants: Optional[List[Suivant]] = None
    domaine: Optional[str] = None
