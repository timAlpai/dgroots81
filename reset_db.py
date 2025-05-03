"""
Script pour réinitialiser complètement la base de données FastAPI.
Ce script supprime toutes les tables existantes et recrée les tables avec des données initiales.
À utiliser avec précaution car toutes les données seront perdues.
"""

import asyncio
import logging
from sqlalchemy.ext.asyncio import create_async_engine
from sqlalchemy.sql import text

from app.core.config import settings
from app.models.base import Base
from app.core.security import get_password_hash
from app.models.user import User
from app.models.game_session import GameSession
from app.models.character import Character, CharacterClass
from app.models.action_log import ActionLog
from app.models.scenario import Scenario
from app.models.scene import Scene, SceneType

# Configuration du logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# URL de connexion à la base de données
DATABASE_URL = (
    f"postgresql+asyncpg://{settings.postgres_user}:"
    f"{settings.postgres_password}@{settings.postgres_host}:"
    f"{settings.postgres_port}/{settings.postgres_db}"
)

# Moteur de base de données asynchrone
engine = create_async_engine(DATABASE_URL, echo=True)

async def reset_database():
    """Réinitialise complètement la base de données en supprimant toutes les tables et en les recréant."""
    logger.info("Début de la réinitialisation de la base de données...")
    
    async with engine.begin() as conn:
        # Supprimer toutes les tables existantes avec CASCADE
        logger.info("Suppression de toutes les tables existantes avec CASCADE...")
        await conn.execute(text("DROP TABLE IF EXISTS action_logs CASCADE"))
        await conn.execute(text("DROP TABLE IF EXISTS characters CASCADE"))
        await conn.execute(text("DROP TABLE IF EXISTS game_sessions CASCADE"))
        await conn.execute(text("DROP TABLE IF EXISTS scenes CASCADE"))
        await conn.execute(text("DROP TABLE IF EXISTS scenarios CASCADE"))
        await conn.execute(text("DROP TABLE IF EXISTS users CASCADE"))
        await conn.execute(text("DROP TABLE IF EXISTS credit_transactions CASCADE"))
        
        # Créer toutes les tables
        logger.info("Création de nouvelles tables...")
        await conn.run_sync(Base.metadata.create_all)
    
    logger.info("Tables réinitialisées avec succès")

async def create_initial_data():
    """Crée des données initiales dans la base de données."""
    from sqlalchemy.ext.asyncio import AsyncSession
    from sqlalchemy.orm import sessionmaker
    
    logger.info("Création des données initiales...")
    
    # Créer une session asynchrone
    async_session = sessionmaker(
        engine, class_=AsyncSession, expire_on_commit=False
    )
    
    async with async_session() as session:
        # Créer un utilisateur administrateur
        admin_user = User(
            username="admin",
            email="admin@example.com",
            hashed_password=get_password_hash("admin123"),
            is_active=True,
            is_superuser=True
        )
        
        # Créer un utilisateur normal
        normal_user = User(
            username="user",
            email="user@example.com",
            hashed_password=get_password_hash("user123"),
            is_active=True,
            is_superuser=False
        )
        
        # Ajouter les utilisateurs à la session
        session.add(admin_user)
        session.add(normal_user)
        
        # Valider les changements
        await session.commit()
        
        # Rafraîchir les objets pour obtenir leurs IDs
        await session.refresh(admin_user)
        await session.refresh(normal_user)
        
        logger.info(f"Utilisateur administrateur créé: {admin_user.username}")
        logger.info(f"Utilisateur normal créé: {normal_user.username}")
        
        # Créer un scénario de démonstration
        demo_scenario = Scenario(
            title="Le Donjon des Ombres",
            description="Une aventure dans un donjon mystérieux rempli de dangers et de trésors.",
            introduction="Les aventuriers sont engagés pour explorer un ancien donjon découvert récemment dans les montagnes.",
            conclusion="Après avoir vaincu le boss final, les aventuriers découvrent un trésor légendaire.",
            recommended_level=1,
            difficulty="standard",
            tags=["fantasy", "dungeon", "beginner"],
            is_published=True,
            creator_id=admin_user.id
        )
        
        session.add(demo_scenario)
        await session.commit()
        await session.refresh(demo_scenario)
        
        logger.info(f"Scénario de démonstration créé: {demo_scenario.title}")
        
        # Créer des scènes pour le scénario
        scenes = [
            Scene(
                title="Entrée du Donjon",
                description="L'entrée sombre et humide du donjon.",
                scene_type=SceneType.INTRODUCTION,
                order=0,
                narrative_content="Les aventuriers se tiennent devant l'entrée imposante du donjon. Des torches vacillantes éclairent faiblement le passage qui s'enfonce dans les ténèbres. L'air est humide et froid, et un léger courant d'air émane de l'intérieur, portant avec lui une odeur de moisi et de terre.",
                scenario_id=demo_scenario.id,
                npcs=[],
                monsters=[],
                items=[]
            ),
            Scene(
                title="Salle des Gardes",
                description="Une salle où des gardes squelettes montent la garde.",
                scene_type=SceneType.COMBAT,
                order=1,
                narrative_content="Après avoir avancé dans le couloir, les aventuriers arrivent dans une grande salle qui semble avoir été une salle de garde. Des armes rouillées sont accrochées aux murs, et des tables renversées jonchent le sol. Dans un coin, des squelettes en armure se dressent soudainement, leurs orbites vides s'illuminant d'une lueur rouge menaçante.",
                scenario_id=demo_scenario.id,
                npcs=[],
                monsters=[
                    {"name": "Garde Squelette", "hp": 6, "ac": 12, "attack": "+2", "damage": "1d6", "xp": 50}
                ],
                items=[
                    {"name": "Épée Rouillée", "type": "weapon", "value": 2}
                ]
            ),
            Scene(
                title="Salle du Trésor",
                description="Une salle remplie de trésors gardée par un boss.",
                scene_type=SceneType.COMBAT,
                order=2,
                narrative_content="Au bout d'un long couloir, les aventuriers découvrent une porte massive ornée de symboles mystérieux. En l'ouvrant, ils pénètrent dans une vaste salle au plafond voûté, illuminée par des cristaux magiques. Au centre, sur un piédestal, repose un coffre orné de joyaux. Mais devant lui se dresse un imposant golem de pierre, ses yeux s'illuminant d'une lueur menaçante à l'approche des intrus.",
                scenario_id=demo_scenario.id,
                npcs=[],
                monsters=[
                    {"name": "Golem de Pierre", "hp": 20, "ac": 14, "attack": "+4", "damage": "2d6", "xp": 200}
                ],
                items=[
                    {"name": "Coffre au Trésor", "type": "container", "contents": [
                        {"name": "Épée Magique +1", "type": "weapon", "value": 100},
                        {"name": "Potion de Soins", "type": "potion", "value": 50},
                        {"name": "Pièces d'or", "type": "gold", "amount": 200}
                    ]}
                ]
            ),
            Scene(
                title="Sortie du Donjon",
                description="La sortie du donjon après l'aventure.",
                scene_type=SceneType.CONCLUSION,
                order=3,
                narrative_content="Après avoir vaincu le golem et récupéré le trésor, les aventuriers trouvent un passage secret derrière le piédestal. Ce passage les mène à une sortie cachée du donjon, débouchant sur un versant de la montagne offrant une vue magnifique sur la vallée en contrebas. Le soleil se couche à l'horizon, baignant le paysage d'une lueur dorée, comme pour célébrer leur victoire.",
                scenario_id=demo_scenario.id,
                npcs=[],
                monsters=[],
                items=[]
            )
        ]
        
        for scene in scenes:
            session.add(scene)
        
        await session.commit()
        logger.info(f"Scènes créées pour le scénario: {demo_scenario.title}")
        
        # Créer une session de jeu
        game_session = GameSession(
            name="Session de démonstration",
            description="Une session de jeu pour tester le système",
            game_master_id=admin_user.id,
            current_scenario_id=demo_scenario.id,
            current_scene_id=scenes[0].id,
            game_rules="OSE",
            difficulty_level="standard"
        )
        
        session.add(game_session)
        await session.commit()
        await session.refresh(game_session)
        
        logger.info(f"Session de jeu créée: {game_session.name}")
        
        # Créer un personnage pour l'utilisateur normal
        character = Character(
            name="Thorin",
            character_class=CharacterClass.GUERRIER,
            level=1,
            strength=16,
            intelligence=10,
            wisdom=12,
            dexterity=14,
            constitution=15,
            charisma=11,
            max_hp=10,
            current_hp=10,
            armor_class=14,
            user_id=normal_user.id,
            game_session_id=game_session.id,
            equipment=[
                {"name": "Épée longue", "type": "weapon", "damage": "1d8"},
                {"name": "Cotte de mailles", "type": "armor", "ac_bonus": 4}
            ],
            gold=50
        )
        
        session.add(character)
        await session.commit()
        
        logger.info(f"Personnage créé: {character.name}")
        logger.info("Données initiales créées avec succès")

async def main():
    """Fonction principale pour réinitialiser la base de données."""
    try:
        await reset_database()
        await create_initial_data()
        logger.info("Réinitialisation de la base de données terminée avec succès")
    except Exception as e:
        logger.error(f"Erreur lors de la réinitialisation de la base de données: {e}")
    finally:
        # Fermer le moteur de base de données
        await engine.dispose()

if __name__ == "__main__":
    asyncio.run(main())