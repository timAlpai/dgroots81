from app.models.character import CharacterClass




MAX_LEVEL_BY_CLASS = {
    "GUERRIER": 14,
    "CLERC": 14,
    "MAGICIEN": 14,
    "VOLEUR": 14,
    "NAIN": 12,
    "ELFE": 10,
    "HALFELIN": 8,
}

def get_max_level(character_class: CharacterClass) -> int:
    return MAX_LEVEL_BY_CLASS.get(character_class.name.upper(), 14)
