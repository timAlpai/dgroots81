from app.utils.ose.dice import roll_dice
from app.utils.ose.thac0 import THAC0_TABLE

def get_thac0(character_class: str, level: int) -> int:
    character_class = character_class.upper()
    if character_class not in THAC0_TABLE:
        raise ValueError(f"Classe ou race non reconnue pour THAC0 : {character_class}")
    level = max(1, min(level, max(THAC0_TABLE[character_class].keys())))
    return THAC0_TABLE[character_class][level]

def jet_attaque(character_class: str, level: int, classe_armure: int, modificateur: int = 0) -> dict:
    thac0 = get_thac0(character_class, level)
    cible = thac0 - classe_armure
    resultat = roll_dice(1, 20, modificateur)
    return {
        **resultat,
        "thac0": thac0,
        "classe_armure": classe_armure,
        "cible": cible,
        "réussi": resultat["total"] >= cible
    }
