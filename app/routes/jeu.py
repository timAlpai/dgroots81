from fastapi import APIRouter, Query
from random import randint

router = APIRouter(prefix="/jeu", tags=["jeu"])

@router.get("/jet-de", summary="Effectuer un jet de dé")
async def jet_de(
    faces: int = Query(..., gt=1),
    nombre: int = Query(1, ge=1, le=100),
    modificateur: int = Query(0)
):
    resultats = [randint(1, faces) for _ in range(nombre)]
    total = sum(resultats) + modificateur
    return {
        "jet": f"{nombre}d{faces}+{modificateur}",
        "resultats": resultats,
        "modificateur": modificateur,
        "total": total
    }
