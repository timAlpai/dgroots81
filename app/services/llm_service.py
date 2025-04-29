"""
Service pour l'interaction avec le modèle de langage (LLM).
Ce service gère la génération de réponses aux actions des joueurs.
"""

import json
from typing import Dict, Any, List
from app.core import llm_client
from app.models.action_log import ActionType

async def generate_action_response(
    action_type: ActionType,
    description: str,
    game_data: Dict[str, Any],
    context: Dict[str, Any]
) -> Dict[str, Any]:
    """
    Génère une réponse à une action de joueur en utilisant le LLM.
    
    Args:
        action_type: Type d'action
        description: Description de l'action
        game_data: Données de jeu associées à l'action
        context: Contexte du jeu (session, personnage, scène, etc.)
    
    Returns:
        Dictionnaire contenant la réponse générée et les métadonnées
    """
    # Construire le prompt pour le LLM
    prompt = build_prompt(action_type, description, game_data, context)
    
    # Appeler le LLM
    response = await llm_client.call_llm(prompt, max_tokens=1000, temperature=0.7)
    
    # Extraire la réponse
    result = response["choices"][0]["message"]["content"]
    tokens_used = response["usage"]["total_tokens"]
    
    # Essayer de parser la réponse structurée
    try:
        # Vérifier si la réponse contient une structure JSON
        if "```json" in result and "```" in result:
            json_str = result.split("```json")[1].split("```")[0].strip()
            structured_data = json.loads(json_str)
            
            # Extraire les différentes parties de la réponse
            return {
                "result": structured_data.get("narrative_response", result),
                "game_data": structured_data.get("game_data", {}),
                "character_updates": structured_data.get("character_updates"),
                "scene_updates": structured_data.get("scene_updates"),
                "next_possible_actions": structured_data.get("next_possible_actions", []),
                "narrative_context": structured_data.get("narrative_context"),
                "tokens_used": tokens_used
            }
    except Exception as e:
        # En cas d'erreur de parsing, retourner la réponse brute
        print(f"Erreur de parsing de la réponse structurée: {e}")
    
    # Retourner la réponse brute si le parsing a échoué
    return {
        "result": result,
        "game_data": {},
        "tokens_used": tokens_used
    }

def build_prompt(
    action_type: ActionType,
    description: str,
    game_data: Dict[str, Any],
    context: Dict[str, Any]
) -> List[Dict[str, str]]:
    """
    Construit le prompt pour le LLM.
    
    Args:
        action_type: Type d'action
        description: Description de l'action
        game_data: Données de jeu associées à l'action
        context: Contexte du jeu
    
    Returns:
        Liste de messages pour le LLM
    """
    # Extraire les informations du contexte
    session = context.get("session", {})
    character = context.get("character", {})
    other_characters = context.get("other_characters", [])
    scene = context.get("scene", {})
    context_window = context.get("context_window", [])
    
    # Construire le système prompt
    system_prompt = """
Tu es un Maître de Jeu (MJ) pour un jeu de rôle Old-School Essentials (OSE).
Ta mission est de narrer l'aventure, décrire les scènes, interpréter les PNJ, et résoudre les actions des joueurs.

RÈGLES DU JEU:
- Utilise les règles OSE pour résoudre les actions (jets de dés, combats, sauvegardes, etc.)
- Niveau de difficulté: {0}
- Sois cohérent avec l'univers et l'ambiance du jeu

INFORMATIONS SUR LA SESSION:
- Nom: {1}
- Description: {2}

INFORMATIONS SUR LE PERSONNAGE:
- Nom: {3}
- Classe: {4}
- Niveau: {5}
- PV: {6}/{7}
- CA: {8}
- FOR: {9} | INT: {10} | SAG: {11}
- DEX: {12} | CON: {13} | CHA: {14}

AUTRES PERSONNAGES PRÉSENTS:
{15}

SCÈNE ACTUELLE:
- Titre: {16}
- Description: {17}
- Contenu narratif: {18}

INSTRUCTIONS:
1. Réponds en tant que MJ à l'action du joueur de manière immersive et narrative
2. Décris les conséquences de l'action, les réactions des PNJ, et l'évolution de la scène
3. Utilise les règles OSE pour résoudre les actions (jets de dés, combats, etc.)
4. Fournis une réponse structurée au format JSON avec les champs suivants:
   - narrative_response: La réponse narrative au joueur
   - game_data: Données techniques du jeu (résultats des jets de dés, etc.)
   - character_updates: Modifications à appliquer au personnage (PV, inventaire, etc.)
   - scene_updates: Modifications à appliquer à la scène
   - next_possible_actions: Suggestions d'actions possibles pour le joueur
   - narrative_context: Contexte narratif pour les prochaines actions

FORMAT DE RÉPONSE:
```json
{{
  "narrative_response": "Description narrative des résultats de l'action",
  "game_data": {{
    "dice_rolls": [],
    "combat_results": {{}},
    "other_data": {{}}
  }},
  "character_updates": {{
    "current_hp": 0,
    "inventory": [],
    "other_updates": {{}}
  }},
  "scene_updates": {{
    "description_updates": "",
    "npc_updates": [],
    "monster_updates": [],
    "item_updates": []
  }},
  "next_possible_actions": [
    {{"type": "DIALOGUE", "description": "Parler à..."}},
    {{"type": "COMBAT", "description": "Attaquer..."}},
    {{"type": "MOUVEMENT", "description": "Aller vers..."}}
  ],
  "narrative_context": "Contexte narratif pour les prochaines actions"
}}
```
""".format(
        session.get("difficulty_level", "standard"),
        session.get("name", "Session sans nom"),
        session.get("description", ""),
        character.get("name", "Inconnu"),
        character.get("class", "Inconnu"),
        character.get("level", 1),
        character.get("current_hp", 0),
        character.get("max_hp", 0),
        character.get("armor_class", 10),
        character.get("strength", 10),
        character.get("intelligence", 10),
        character.get("wisdom", 10),
        character.get("dexterity", 10),
        character.get("constitution", 10),
        character.get("charisma", 10),
        ", ".join([f"{c.get('name', 'Inconnu')} ({c.get('class', 'Inconnu')} niv.{c.get('level', 1)})" for c in other_characters]) if other_characters else "Aucun",
        scene.get("title", "Aucune scène"),
        scene.get("description", ""),
        scene.get("narrative_content", "")
    )
    
    # Construire le contexte historique
    history = ""
    if context_window:
        history = "HISTORIQUE RÉCENT:\n"
        for entry in context_window:
            history += f"- {entry.get('character_name', 'Inconnu')}: {entry.get('description', '')}\n"
    
    # Construire le message utilisateur
    user_message = f"""
ACTION DU JOUEUR:
Type: {action_type}
Description: {description}

{history if history else ""}

Données de jeu: {json.dumps(game_data, ensure_ascii=False) if game_data else "Aucune"}

Réponds en tant que Maître de Jeu à cette action.
"""
    
    # Construire la liste de messages
    messages = [
        {"role": "system", "content": system_prompt},
        {"role": "user", "content": user_message}
    ]
    
    return messages

async def generate_scene_description(scene_data: Dict[str, Any], context: Dict[str, Any]) -> str:
    """
    Génère une description détaillée d'une scène en utilisant le LLM.
    
    Args:
        scene_data: Données de la scène
        context: Contexte du jeu
    
    Returns:
        Description générée de la scène
    """
    # Construire le prompt pour le LLM
    system_prompt = """
Tu es un Maître de Jeu (MJ) pour un jeu de rôle Old-School Essentials (OSE).
Ta mission est de créer des descriptions de scènes immersives et évocatrices.

INSTRUCTIONS:
1. Crée une description détaillée et atmosphérique de la scène
2. Inclus des détails sensoriels (vue, ouïe, odorat, toucher)
3. Mentionne les éléments importants de l'environnement
4. Décris l'ambiance générale et l'atmosphère
5. Suggère subtilement des points d'intérêt ou d'interaction possibles
"""
    
    user_message = f"""
INFORMATIONS SUR LA SCÈNE:
- Titre: {scene_data.get("title", "Sans titre")}
- Type: {scene_data.get("scene_type", "EXPLORATION")}
- Description de base: {scene_data.get("description", "")}

ÉLÉMENTS PRÉSENTS:
- PNJ: {", ".join([pnj.get("name", "Inconnu") for pnj in scene_data.get("npcs", [])])}
- Monstres: {", ".join([monster.get("name", "Inconnu") for monster in scene_data.get("monsters", [])])}
- Objets: {", ".join([item.get("name", "Inconnu") for item in scene_data.get("items", [])])}

Génère une description immersive et détaillée de cette scène pour les joueurs.
"""
    
    messages = [
        {"role": "system", "content": system_prompt},
        {"role": "user", "content": user_message}
    ]
    
    # Appeler le LLM
    response = await llm_client.call_llm(messages, max_tokens=500, temperature=0.7)
    
    # Extraire la réponse
    result = response["choices"][0]["message"]["content"]
    
    return result

async def generate_npc_dialogue(
    npc_data: Dict[str, Any],
    player_input: str,
    context: Dict[str, Any]
) -> str:
    """
    Génère un dialogue de PNJ en réponse à l'input du joueur.
    
    Args:
        npc_data: Données du PNJ
        player_input: Input du joueur
        context: Contexte du jeu
    
    Returns:
        Dialogue généré du PNJ
    """
    # Construire le prompt pour le LLM
    system_prompt = f"""
Tu es un Maître de Jeu (MJ) pour un jeu de rôle Old-School Essentials (OSE).
Ta mission est d'interpréter les PNJ et de générer leurs dialogues.

INFORMATIONS SUR LE PNJ:
- Nom: {npc_data.get("name", "Inconnu")}
- Description: {npc_data.get("description", "")}
- Personnalité: {npc_data.get("personality", "")}
- Objectifs: {npc_data.get("goals", "")}
- Connaissances: {npc_data.get("knowledge", "")}

INSTRUCTIONS:
1. Réponds en tant que ce PNJ au joueur
2. Respecte la personnalité et les objectifs du PNJ
3. Adapte le ton, le vocabulaire et le style de parole à ce personnage
4. Ne révèle que les informations que le PNJ connaît et accepterait de partager
5. Réagis de manière cohérente avec l'attitude du PNJ envers le personnage du joueur
"""
    
    user_message = f"""
Le joueur dit au PNJ: "{player_input}"

Contexte de la conversation:
- Scène: {context.get("scene", {}).get("title", "Inconnue")}
- Relation avec le joueur: {npc_data.get("relation_to_player", "Neutre")}

Génère la réponse du PNJ.
"""
    
    messages = [
        {"role": "system", "content": system_prompt},
        {"role": "user", "content": user_message}
    ]
    
    # Appeler le LLM
    response = await llm_client.call_llm(messages, max_tokens=300, temperature=0.7)
    
    # Extraire la réponse
    result = response["choices"][0]["message"]["content"]
    
    return result

async def generate_combat_results(
    combat_data: Dict[str, Any],
    context: Dict[str, Any]
) -> Dict[str, Any]:
    """
    Génère les résultats d'un combat en utilisant le LLM.
    
    Args:
        combat_data: Données du combat
        context: Contexte du jeu
    
    Returns:
        Résultats du combat
    """
    # Construire le prompt pour le LLM
    system_prompt = """
Tu es un Maître de Jeu (MJ) pour un jeu de rôle Old-School Essentials (OSE).
Ta mission est de résoudre les combats selon les règles OSE et de narrer leur déroulement.

RÈGLES DE COMBAT OSE:
1. Initiative: 1d6 par camp, le plus haut agit en premier
2. Attaque: Jet d'attaque (d20) + bonus vs CA de la cible
3. Dégâts: Selon l'arme utilisée + modificateurs
4. Morale: Les monstres peuvent fuir si la situation tourne mal

INSTRUCTIONS:
1. Résous le combat en utilisant les règles OSE
2. Narre le déroulement du combat de manière immersive
3. Décris les actions, attaques, défenses et leurs conséquences
4. Fournis les résultats techniques (jets de dés, dégâts, etc.)
5. Indique l'état final des participants (PV restants, conditions, etc.)
6. Fournis une réponse structurée au format JSON
"""
    
    # Extraire les informations du personnage et des ennemis
    character = context.get("character", {})
    
    user_message = f"""
INFORMATIONS DE COMBAT:
- Attaquant: {character.get("name", "Inconnu")} ({character.get("class", "Inconnu")} niv.{character.get("level", 1)})
- Arme: {combat_data.get("weapon", "Inconnue")}
- Cible: {combat_data.get("target", "Inconnue")}
- Action spécifique: {combat_data.get("action", "Attaque standard")}
- Tactique: {combat_data.get("tactics", "Standard")}

STATISTIQUES DU PERSONNAGE:
- PV: {character.get("current_hp", 0)}/{character.get("max_hp", 0)}
- CA: {character.get("armor_class", 10)}
- FOR: {character.get("strength", 10)} | DEX: {character.get("dexterity", 10)}
- Bonus d'attaque: {combat_data.get("attack_bonus", 0)}

STATISTIQUES DE LA CIBLE:
- Type: {combat_data.get("target_type", "Monstre")}
- PV: {combat_data.get("target_hp", "Inconnu")}
- CA: {combat_data.get("target_ac", "Inconnu")}
- Attaque: {combat_data.get("target_attack", "Inconnue")}
- Dégâts: {combat_data.get("target_damage", "Inconnus")}

Résous ce combat et fournis une narration immersive ainsi que les résultats techniques.
"""
    
    messages = [
        {"role": "system", "content": system_prompt},
        {"role": "user", "content": user_message}
    ]
    
    # Appeler le LLM
    response = await llm_client.call_llm(messages, max_tokens=800, temperature=0.7)
    
    # Extraire la réponse
    result = response["choices"][0]["message"]["content"]
    
    # Essayer de parser la réponse structurée
    try:
        if "```json" in result and "```" in result:
            json_str = result.split("```json")[1].split("```")[0].strip()
            return json.loads(json_str)
    except Exception as e:
        print(f"Erreur de parsing des résultats de combat: {e}")
    
    # Retourner un format par défaut si le parsing a échoué
    return {
        "narrative": result,
        "technical_results": {
            "initiative": {},
            "attacks": [],
            "damage_dealt": 0,
            "damage_received": 0
        },
        "final_state": {
            "character_hp": character.get("current_hp", 0),
            "enemies_state": "Inconnu"
        }
    }