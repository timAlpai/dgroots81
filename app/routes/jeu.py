from fastapi import APIRouter, Depends
from pydantic import BaseModel, Field
from typing import Optional, Literal

from app.schemas.jeu import JetDesRequest, ModificateurRequest, SauvegardeRequest, CaracCheckRequest, ChanceRequest
from app.services.jeu_service import JeuService # Import du service

router = APIRouter(prefix="/jeu", tags=["jeu"])

# Dépendance pour obtenir le JeuService
def get_jeu_service() -> JeuService:
    return JeuService()

@router.post("/jet-de")
def jet_de(
    request: JetDesRequest,
    jeu_service: JeuService = Depends(get_jeu_service) # Injection du service
):
    return jeu_service.jet_de(request)

@router.post("/attaque")
def jet_attaque(
    data: ModificateurRequest,
    jeu_service: JeuService = Depends(get_jeu_service) # Injection du service
):
    return jeu_service.jet_attaque(data)

@router.post("/initiative")
def jet_initiative(
    jeu_service: JeuService = Depends(get_jeu_service) # Injection du service
):
    return jeu_service.jet_initiative()

@router.post("/sauvegarde")
def jet_sauvegarde(
    data: SauvegardeRequest,
    jeu_service: JeuService = Depends(get_jeu_service) # Injection du service
):
    return jeu_service.jet_sauvegarde(data)

@router.post("/moral")
def jet_moral(
    data: ModificateurRequest,
    jeu_service: JeuService = Depends(get_jeu_service) # Injection du service
):
    return jeu_service.jet_moral(data)

@router.post("/caracteristique")
def test_caracteristique(
    data: CaracCheckRequest,
    jeu_service: JeuService = Depends(get_jeu_service) # Injection du service
):
    return jeu_service.test_caracteristique(data)

@router.post("/chance")
def jet_chance(
    data: ChanceRequest,
    jeu_service: JeuService = Depends(get_jeu_service) # Injection du service
):
    return jeu_service.jet_chance(data)
