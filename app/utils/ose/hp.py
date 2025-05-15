from app.utils.ose.dice import roll_dice
from app.utils.ose_rules import get_ability_modifier
from app.models.character import CharacterClass
from app.utils.ose_rules import get_hit_dice

def calculate_hp_for_level_up(character_class: CharacterClass, level: int, constitution: int) -> int:
    hit_dice = get_hit_dice(character_class)
    dice_num = hit_dice["num"]
    dice_type = hit_dice["type"]
    max_dv_level = hit_dice.get("max_level_dv", 9)
    con_mod = get_ability_modifier(constitution)

    if level <= max_dv_level:
        result = roll_dice(dice_num, dice_type)["total"]
        return max(1, result + con_mod)
    else:
        return max(1, con_mod)  # Pas de DV mais CON peut encore apporter des PV
