# app/services/jeu_service.py

from random import randint
from typing import Optional, Literal, List, Dict, Any

from app.schemas.jeu import JetDesRequest, ModificateurRequest, SauvegardeRequest, CaracCheckRequest, ChanceRequest

class JeuService:
    def __init__(self):
        # Ce service n'a pas besoin d'accès à la base de données pour l'instant
        pass

    def roll_dice(self, nb: int, faces: int, mod: int = 0) -> Dict[str, Any]:
        resultats = [randint(1, faces) for _ in range(nb)]
        total = sum(resultats) + mod
        return {"dés": resultats, "modificateur": mod, "total": total}

    def jet_de(self, request: JetDesRequest) -> Dict[str, Any]:
        faces = int(request.type_de[1:])
        return self.roll_dice(request.nombre, faces, request.modificateur)

    def jet_attaque(self, data: ModificateurRequest) -> Dict[str, Any]:
        return self.roll_dice(1, 20, data.modificateur)

    def jet_initiative(self) -> Dict[str, Any]:
        return self.roll_dice(1, 6)

    def jet_sauvegarde(self, data: SauvegardeRequest) -> Dict[str, Any]:
        jet = randint(1, 20)
        total = jet + data.modificateur
        return {
            "jet": jet,
            "modificateur": data.modificateur,
            "total": total,
            "réussi": total >= data.seuil
        }

    def jet_moral(self, data: ModificateurRequest) -> Dict[str, Any]:
        return self.roll_dice(2, 6, data.modificateur)

    def test_caracteristique(self, data: CaracCheckRequest) -> Dict[str, Any]:
        jet = randint(1, 20)
        total = jet + data.modificateur
        return {
            "jet": jet,
            "modificateur": data.modificateur,
            "total": total,
            "réussi": total <= data.seuil
        }

    def jet_chance(self, data: ChanceRequest) -> Dict[str, Any]:
        jet = randint(1, 6)
        return {
            "jet": jet,
            "réussi": jet <= data.seuil
        }