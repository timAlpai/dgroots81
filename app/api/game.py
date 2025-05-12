from fastapi import APIRouter
from pydantic import BaseModel, Field
from random import randint
from typing import List, Literal, Optional

router = APIRouter(prefix="/game", tags=["Game"])

class JetDesRequest(BaseModel):
    type_de: Literal["d4", "d6", "d8", "d10", "d12", "d20", "d100"]
    nombre: int = Field(default=1, ge=1, le=100)
    modificateur: Optional[int] = 0

class ModificateurRequest(BaseModel):
    modificateur: int = 0

class SauvegardeRequest(BaseModel):
    seuil: int
    modificateur: int = 0

class CaracCheckRequest(BaseModel):
    seuil: int
    modificateur: int = 0

class ChanceRequest(BaseModel):
    seuil: int = 1


def roll_dice(nb: int, faces: int, mod: int = 0):
    resultats = [randint(1, faces) for _ in range(nb)]
    total = sum(resultats) + mod
    return {"dés": resultats, "modificateur": mod, "total": total}

@router.post("/jet-de")
def jet_de(request: JetDesRequest):
    faces = int(request.type_de[1:])
    return roll_dice(request.nombre, faces, request.modificateur)

@router.post("/attaque")
def jet_attaque(data: ModificateurRequest):
    return roll_dice(1, 20, data.modificateur)

@router.post("/initiative")
def jet_initiative():
    return roll_dice(1, 6)

@router.post("/sauvegarde")
def jet_sauvegarde(data: SauvegardeRequest):
    jet = randint(1, 20)
    total = jet + data.modificateur
    return {
        "jet": jet,
        "modificateur": data.modificateur,
        "total": total,
        "réussi": total >= data.seuil
    }

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
