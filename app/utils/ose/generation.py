from enum import Enum
from app.utils.ose.dice import roll_dice
import random
from app.models.character import CharacterClass
from typing import Dict, Any, List


class StatGenMethod(str, Enum):
    CLASSIC = "3d6"
    HEROIC = "4d6-drop-lowest"
    REROLL_LOW = "3d6-reroll-below-6"


def adjust_primary_stat(stats: Dict[str, int], character_class: CharacterClass) -> Dict[str, int]:
    """
    Applique la règle OSE : un joueur peut baisser FOR, SAG, ou INT (>=9) par -2 pour +1 dans la carac principale.
    La carac principale dépend de la classe, et ne peut excéder 18.
    """
    primary_map = {
        CharacterClass.GUERRIER: "strength",
        CharacterClass.CLERC: "wisdom",
        CharacterClass.MAGICIEN: "intelligence",
        CharacterClass.VOLEUR: "dexterity",
        CharacterClass.NAIN: "strength",
        CharacterClass.ELFE: "intelligence",
        CharacterClass.HALFELIN: "dexterity",
    }

    primary = primary_map[character_class]
    exchangeable = ["strength", "intelligence", "wisdom"]

    for stat in exchangeable:
        if stat == primary:
            continue
        while stats[stat] > 9 and stats[primary] < 18:
            stats[stat] -= 2
            stats[primary] += 1

    return stats



def generate_abilities(method: StatGenMethod) -> Dict[str, int]:
    if method == StatGenMethod.CLASSIC:
        stats = [roll_dice(3, 6) for _ in range(6)]
    elif method == StatGenMethod.HEROIC:
        stats = [sum(sorted([random.randint(1, 6) for _ in range(4)])[1:]) for _ in range(6)]
    elif method == StatGenMethod.REROLL_LOW:
        stats = []
        for _ in range(6):
            val = roll_dice(3, 6)
            while val < 6:
                val = roll_dice(3, 6)
            stats.append(val)
    else:
        raise ValueError("Méthode de génération inconnue")

    keys = ["strength", "intelligence", "wisdom", "dexterity", "constitution", "charisma"]
    return dict(zip(keys, stats))



def generate_character_stats(character_class: CharacterClass, method: StatGenMethod = StatGenMethod.CLASSIC ) -> Dict[str, Any]:
    """
    Génère les statistiques complètes pour un nouveau personnage.
    
    Args:
        character_class: Classe du personnage
    
    Returns:
        Dictionnaire contenant toutes les statistiques du personnage
    """


    raw_stats = generate_abilities(method)
    raw_stats = adjust_primary_stat(raw_stats, character_class)

    strength = raw_stats["strength"]
    intelligence = raw_stats["intelligence"]
    wisdom = raw_stats["wisdom"]
    dexterity = raw_stats["dexterity"]
    constitution = raw_stats["constitution"]
    charisma = raw_stats["charisma"]
    
    # Obtenir le type de dé de vie
    hit_dice = get_hit_dice(character_class)
    
    # Calculer les points de vie (avec modificateur de constitution)
    con_mod = get_ability_modifier(constitution)
    max_hp = max(1, roll_dice(hit_dice["num"], hit_dice["type"]) + con_mod)
    
    # Générer l'or de départ
    gold = get_starting_gold(character_class)
    
    # Générer l'équipement de départ
    equipment = get_starting_equipment(character_class)
    
    # Générer les sorts de départ
    spells = get_starting_spells(character_class)
    
    # Générer les compétences de départ
    skills = get_starting_skills(character_class)
    
    # Calculer la classe d'armure (CA de base 10)
    armor_class = 10
    
    # Ajouter le bonus d'armure de l'équipement
    for item in equipment:
        if item.get("type") == "armor":
            armor_class += item.get("ac_bonus", 0)
        elif item.get("type") == "shield":
            armor_class += item.get("ac_bonus", 0)
    
    # Ajouter le modificateur de dextérité
    armor_class += get_ability_modifier(dexterity)
    
    return {
        "strength": strength,
        "intelligence": intelligence,
        "wisdom": wisdom,
        "dexterity": dexterity,
        "constitution": constitution,
        "charisma": charisma,
        "max_hp": max_hp,
        "current_hp": max_hp,
        "armor_class": armor_class,
        "gold": gold,
        "equipment": equipment,
        "spells": spells,
        "skills": skills
    }