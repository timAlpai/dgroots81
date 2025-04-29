"""
Module implémentant les règles Old-School Essentials (OSE) pour le système de jeu de rôle.
"""

import random
from typing import Dict, Any, List
from app.models.character import CharacterClass

def roll_dice(num_dice: int, dice_type: int) -> int:
    """
    Lance un nombre spécifié de dés d'un type donné.
    
    Args:
        num_dice: Nombre de dés à lancer
        dice_type: Type de dé (d4, d6, d8, d10, d12, d20, etc.)
    
    Returns:
        Somme des résultats des dés
    """
    return sum(random.randint(1, dice_type) for _ in range(num_dice))

def roll_ability_score() -> int:
    """
    Lance 3d6 pour générer une caractéristique selon les règles OSE.
    
    Returns:
        Valeur de la caractéristique (3-18)
    """
    return roll_dice(3, 6)

def get_ability_modifier(score: int) -> int:
    """
    Calcule le modificateur d'une caractéristique selon les règles OSE.
    
    Args:
        score: Valeur de la caractéristique (3-18)
    
    Returns:
        Modificateur (-3 à +3)
    """
    if score <= 3:
        return -3
    elif score <= 5:
        return -2
    elif score <= 8:
        return -1
    elif score <= 12:
        return 0
    elif score <= 15:
        return 1
    elif score <= 17:
        return 2
    else:
        return 3

def get_hit_dice(character_class: CharacterClass) -> Dict[str, int]:
    """
    Retourne le type de dé de vie pour une classe de personnage.
    
    Args:
        character_class: Classe du personnage
    
    Returns:
        Dictionnaire contenant le nombre et le type de dés de vie
    """
    hit_dice = {
        CharacterClass.CLERC: {"num": 1, "type": 6},
        CharacterClass.GUERRIER: {"num": 1, "type": 8},
        CharacterClass.MAGICIEN: {"num": 1, "type": 4},
        CharacterClass.VOLEUR: {"num": 1, "type": 4},
        CharacterClass.NAIN: {"num": 1, "type": 8},
        CharacterClass.ELFE: {"num": 1, "type": 6},
        CharacterClass.HALFELIN: {"num": 1, "type": 6}
    }
    
    return hit_dice.get(character_class, {"num": 1, "type": 6})

def get_starting_gold(character_class: CharacterClass) -> int:
    """
    Génère l'or de départ pour une classe de personnage.
    
    Args:
        character_class: Classe du personnage
    
    Returns:
        Montant d'or de départ
    """
    gold_by_class = {
        CharacterClass.CLERC: roll_dice(3, 6) * 10,
        CharacterClass.GUERRIER: roll_dice(3, 6) * 10,
        CharacterClass.MAGICIEN: roll_dice(2, 6) * 10,
        CharacterClass.VOLEUR: roll_dice(2, 6) * 10,
        CharacterClass.NAIN: roll_dice(3, 6) * 10,
        CharacterClass.ELFE: roll_dice(2, 6) * 10,
        CharacterClass.HALFELIN: roll_dice(2, 6) * 10
    }
    
    return gold_by_class.get(character_class, roll_dice(3, 6) * 10)

def get_starting_equipment(character_class: CharacterClass) -> List[Dict[str, Any]]:
    """
    Génère l'équipement de départ pour une classe de personnage.
    
    Args:
        character_class: Classe du personnage
    
    Returns:
        Liste d'équipements de départ
    """
    # Équipement commun à toutes les classes
    common_equipment = [
        {"name": "Sac à dos", "type": "container", "weight": 1},
        {"name": "Rations (1 semaine)", "type": "food", "weight": 1},
        {"name": "Gourde", "type": "container", "weight": 0.5},
        {"name": "Torches (6)", "type": "light", "weight": 1}
    ]
    
    # Équipement spécifique à chaque classe
    class_equipment = {
        CharacterClass.CLERC: [
            {"name": "Masse", "type": "weapon", "damage": "1d6", "weight": 3},
            {"name": "Armure de cuir", "type": "armor", "ac_bonus": 2, "weight": 5},
            {"name": "Symbole sacré", "type": "holy_symbol", "weight": 0.1}
        ],
        CharacterClass.GUERRIER: [
            {"name": "Épée longue", "type": "weapon", "damage": "1d8", "weight": 3},
            {"name": "Armure de mailles", "type": "armor", "ac_bonus": 4, "weight": 10},
            {"name": "Bouclier", "type": "shield", "ac_bonus": 1, "weight": 2}
        ],
        CharacterClass.MAGICIEN: [
            {"name": "Dague", "type": "weapon", "damage": "1d4", "weight": 0.5},
            {"name": "Grimoire", "type": "spellbook", "weight": 1},
            {"name": "Composantes de sorts", "type": "spell_components", "weight": 0.5}
        ],
        CharacterClass.VOLEUR: [
            {"name": "Épée courte", "type": "weapon", "damage": "1d6", "weight": 1},
            {"name": "Armure de cuir", "type": "armor", "ac_bonus": 2, "weight": 5},
            {"name": "Outils de crochetage", "type": "thieves_tools", "weight": 0.5}
        ],
        CharacterClass.NAIN: [
            {"name": "Hache de bataille", "type": "weapon", "damage": "1d8", "weight": 3},
            {"name": "Armure de mailles", "type": "armor", "ac_bonus": 4, "weight": 10},
            {"name": "Bouclier", "type": "shield", "ac_bonus": 1, "weight": 2}
        ],
        CharacterClass.ELFE: [
            {"name": "Épée longue", "type": "weapon", "damage": "1d8", "weight": 3},
            {"name": "Arc long", "type": "weapon", "damage": "1d6", "weight": 1},
            {"name": "Flèches (20)", "type": "ammunition", "weight": 0.5},
            {"name": "Armure de cuir", "type": "armor", "ac_bonus": 2, "weight": 5}
        ],
        CharacterClass.HALFELIN: [
            {"name": "Épée courte", "type": "weapon", "damage": "1d6", "weight": 1},
            {"name": "Fronde", "type": "weapon", "damage": "1d4", "weight": 0.1},
            {"name": "Billes (20)", "type": "ammunition", "weight": 0.5},
            {"name": "Armure de cuir", "type": "armor", "ac_bonus": 2, "weight": 5}
        ]
    }
    
    return common_equipment + class_equipment.get(character_class, [])

def get_starting_spells(character_class: CharacterClass) -> List[Dict[str, Any]]:
    """
    Génère les sorts de départ pour une classe de personnage.
    
    Args:
        character_class: Classe du personnage
    
    Returns:
        Liste de sorts de départ
    """
    # Sorts de niveau 1 pour les magiciens
    wizard_spells = [
        {"name": "Lecture de la magie", "level": 1, "description": "Permet de lire les parchemins et grimoires magiques."},
        {"name": "Détection de la magie", "level": 1, "description": "Détecte la présence de magie dans un rayon de 18 mètres."},
        {"name": "Lumière", "level": 1, "description": "Crée une source de lumière équivalente à une torche."},
        {"name": "Projectile magique", "level": 1, "description": "Lance un projectile d'énergie qui inflige 1d6+1 points de dégâts."},
        {"name": "Bouclier", "level": 1, "description": "Crée un bouclier invisible qui améliore la CA de 2 points."},
        {"name": "Sommeil", "level": 1, "description": "Endort 2d4 DV de créatures."}
    ]
    
    # Sorts de niveau 1 pour les clercs
    cleric_spells = [
        {"name": "Soins légers", "level": 1, "description": "Soigne 1d6+1 points de vie."},
        {"name": "Détection du mal", "level": 1, "description": "Détecte les créatures et objets maléfiques dans un rayon de 18 mètres."},
        {"name": "Protection contre le mal", "level": 1, "description": "Protège contre les attaques des créatures maléfiques."},
        {"name": "Purification de l'eau et de la nourriture", "level": 1, "description": "Rend l'eau et la nourriture consommables."}
    ]
    
    # Sorts de niveau 1 pour les elfes
    elf_spells = [
        {"name": "Lecture de la magie", "level": 1, "description": "Permet de lire les parchemins et grimoires magiques."},
        {"name": "Détection de la magie", "level": 1, "description": "Détecte la présence de magie dans un rayon de 18 mètres."}
    ]
    
    # Sélection aléatoire de sorts pour chaque classe
    if character_class == CharacterClass.MAGICIEN:
        return random.sample(wizard_spells, 2)
    elif character_class == CharacterClass.CLERC:
        return random.sample(cleric_spells, 1)
    elif character_class == CharacterClass.ELFE:
        return random.sample(elf_spells, 1)
    else:
        return []

def get_starting_skills(character_class: CharacterClass) -> List[Dict[str, Any]]:
    """
    Génère les compétences de départ pour une classe de personnage.
    
    Args:
        character_class: Classe du personnage
    
    Returns:
        Liste de compétences de départ
    """
    # Compétences de voleur
    thief_skills = [
        {"name": "Crochetage", "value": 15, "description": "Capacité à crocheter les serrures."},
        {"name": "Désamorçage", "value": 10, "description": "Capacité à désamorcer les pièges."},
        {"name": "Pickpocket", "value": 20, "description": "Capacité à voler discrètement."},
        {"name": "Déplacement silencieux", "value": 25, "description": "Capacité à se déplacer sans faire de bruit."},
        {"name": "Escalade", "value": 30, "description": "Capacité à escalader des surfaces verticales."},
        {"name": "Cachette", "value": 10, "description": "Capacité à se cacher dans les ombres."},
        {"name": "Détection", "value": 35, "description": "Capacité à détecter les pièges et portes secrètes."},
        {"name": "Écoute", "value": 30, "description": "Capacité à entendre les bruits derrière les portes."}
    ]
    
    # Compétences de nain
    dwarf_skills = [
        {"name": "Détection des pièges", "value": 25, "description": "Capacité à détecter les pièges dans les constructions."},
        {"name": "Détection des passages secrets", "value": 15, "description": "Capacité à détecter les passages secrets dans la pierre."},
        {"name": "Détection des salles secrètes", "value": 15, "description": "Capacité à détecter les salles secrètes dans la pierre."},
        {"name": "Estimation des trésors", "value": 20, "description": "Capacité à estimer la valeur des trésors."}
    ]
    
    # Compétences d'elfe
    elf_skills = [
        {"name": "Détection des portes secrètes", "value": 20, "description": "Capacité à détecter les portes secrètes."},
        {"name": "Immunité au paralysie", "value": 100, "description": "Immunité à la paralysie des goules."}
    ]
    
    # Compétences de halfelin
    halfling_skills = [
        {"name": "Déplacement silencieux", "value": 20, "description": "Capacité à se déplacer sans faire de bruit."},
        {"name": "Cachette", "value": 30, "description": "Capacité à se cacher."}
    ]
    
    # Retourne les compétences en fonction de la classe
    if character_class == CharacterClass.VOLEUR:
        return thief_skills
    elif character_class == CharacterClass.NAIN:
        return dwarf_skills
    elif character_class == CharacterClass.ELFE:
        return elf_skills
    elif character_class == CharacterClass.HALFELIN:
        return halfling_skills
    else:
        return []

def generate_character_stats(character_class: CharacterClass) -> Dict[str, Any]:
    """
    Génère les statistiques complètes pour un nouveau personnage.
    
    Args:
        character_class: Classe du personnage
    
    Returns:
        Dictionnaire contenant toutes les statistiques du personnage
    """
    # Générer les caractéristiques
    strength = roll_ability_score()
    intelligence = roll_ability_score()
    wisdom = roll_ability_score()
    dexterity = roll_ability_score()
    constitution = roll_ability_score()
    charisma = roll_ability_score()
    
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

def calculate_hp_for_level_up(character_class: CharacterClass, constitution: int) -> int:
    """
    Calcule les points de vie gagnés lors d'une montée de niveau.
    
    Args:
        character_class: Classe du personnage
        constitution: Valeur de constitution du personnage
    
    Returns:
        Nombre de points de vie gagnés
    """
    # Obtenir le type de dé de vie
    hit_dice = get_hit_dice(character_class)
    
    # Calculer les points de vie (avec modificateur de constitution)
    con_mod = get_ability_modifier(constitution)
    hp_gain = max(1, roll_dice(hit_dice["num"], hit_dice["type"]) + con_mod)
    
    return hp_gain

def calculate_experience_for_level(level: int) -> int:
    """
    Calcule l'expérience nécessaire pour atteindre un niveau donné.
    
    Args:
        level: Niveau cible
    
    Returns:
        Points d'expérience nécessaires
    """
    # Table d'expérience simplifiée (basée sur les règles OSE)
    xp_table = {
        1: 0,
        2: 2000,
        3: 4000,
        4: 8000,
        5: 16000,
        6: 32000,
        7: 64000,
        8: 120000,
        9: 240000,
        10: 360000,
        11: 480000,
        12: 600000,
        13: 720000,
        14: 840000
    }
    
    return xp_table.get(level, 0)