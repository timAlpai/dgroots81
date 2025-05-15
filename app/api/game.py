from fastapi import APIRouter
from pydantic import BaseModel, Field, conint
from typing_extensions import Annotated
from random import randint
from typing import List, Literal, Optional
from app.models.character import Character
from app.utils.ose_rules import jet_sauvegarde as ose_jet_sauvegarde

from app.utils.ose.combat import jet_attaque

from app.utils.ose.dice import roll_dice

from sqlalchemy.ext.asyncio import AsyncSession
from fastapi import Depends, HTTPException
from app.core.database import get_db
router = APIRouter(prefix="/game", tags=["Game"])

class JetDesRequest(BaseModel):
    type_de: Literal["d4", "d6", "d8", "d10", "d12", "d20", "d100"]
    nombre: int = Field(default=1, ge=1, le=100)
    modificateur: Optional[int] = 0

class ModificateurRequest(BaseModel):
    modificateur: int = 0

class SauvegardeRequest(BaseModel):
    character_class: Literal[
        "GUERRIER", "CLERC", "MAGICIEN", "VOLEUR",
        "NAIN", "ELFE", "HALFELIN"
    ]
    level: int
    level: Annotated[int, Field(ge=1, le=14)]
    type: Literal["MP", "B", "PP", "S", "SBB"]
    modificateur: int = 0

class AttaqueRequest(BaseModel):
    character_class: Literal["GUERRIER", "CLERC", "MAGICIEN", "VOLEUR", "NAIN", "ELFE", "HALFELIN"]
    level: Annotated[int, Field(ge=1, le=14)]
    classe_armure: int  # La CA de la cible (positive ou négative)
    modificateur: int = 0  # Bonus d'attaque, force, magie, etc.


class CaracCheckRequest(BaseModel):
    seuil: int
    modificateur: int = 0

class ChanceRequest(BaseModel):
    seuil: int = 1




@router.post("/jet-de")
def jet_de(request: JetDesRequest):
    faces = int(request.type_de[1:])
    return roll_dice(request.nombre, faces, request.modificateur)

@router.post("/attaque")
def attaque(data: AttaqueRequest):
    return jet_attaque(
        character_class=data.character_class,
        level=data.level,
        classe_armure=data.classe_armure,
        modificateur=data.modificateur
    )
@router.post("/initiative")
def jet_initiative():
    return roll_dice(1, 6)


@router.post("/sauvegarde")
def jet_sauvegarde(data: SauvegardeRequest):
    return ose_jet_sauvegarde(
        character_class=data.character_class,
        level=data.level,
        save_type=data.type,
        modificateur=data.modificateur
    )




@router.post("/moral")
def jet_moral(data: ModificateurRequest):
    return roll_dice(2, 6, data.modificateur)

@router.post("/caracteristique")
def test_caracteristique(data: CaracCheckRequest):
    jet = randint(1, 20)
    total = jet + data.modificateur
    return {
        "jet": jet,
        "modificateur": data.modificateur,
        "total": total,
        "réussi": total <= data.seuil
    }

@router.post("/chance")
def jet_chance(data: ChanceRequest):
    jet = randint(1, 6)
    return {
        "jet": jet,
        "réussi": jet <= data.seuil
    }
