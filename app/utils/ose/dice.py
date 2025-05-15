import random

def roll_dice(nb_dés: int, faces: int, modificateur: int = 0) -> dict:
    jets = [random.randint(1, faces) for _ in range(nb_dés)]
    total = sum(jets) + modificateur
    return {
        "jets": jets,
        "modificateur": modificateur,
        "total": total
    }
