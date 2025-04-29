# RPG-IA Backend

Un système de jeu de rôle en ligne multi-joueurs avec IA comme maître de jeu.

## Description

RPG-IA est une API complète permettant de gérer des sessions de jeu de rôle en ligne avec une IA comme maître de jeu. Le système utilise les règles Old-School Essentials (OSE) et s'appuie sur un modèle de langage avancé pour générer des narrations, résoudre les actions des joueurs et créer une expérience de jeu immersive.

## Caractéristiques

- Gestion des utilisateurs (CRUD, authentification JWT)
- Orchestration des sessions de jeu (création, sauvegarde, restauration)
- Création et gestion de personnages selon les règles Old-School Essentials
- Communication avec l'IA-MJ pour la narration et la résolution des actions
- Suivi des métriques (tokens, temps de jeu)
- Système de persistance hybride PostgreSQL/Redis
- Parser pour fichiers markdown avec codes couleur

## Architecture technique

- **Backend**: FastAPI (Python 3.12)
- **Base de données**: PostgreSQL pour le stockage persistant
- **Cache**: Redis pour les sessions actives et l'état de jeu en temps réel
- **IA**: Mistral-Nemo-12B-Instruct GPTQ INT8 via vLLM
- **Infrastructure**: Kubernetes, RTX4090

## Structure de données

### PostgreSQL

- **users**: comptes, métriques d'utilisation
- **game_sessions**: sessions persistantes
- **characters**: fiches personnages complètes
- **action_logs**: historique des actions
- **scenarios**: contenus narratifs structurés
- **scenes**: découpages des scénarios

### Redis

- **Session active**: `session:{id}` avec TTL 24h
- **État de jeu temps réel**
- **Cache de contexte pour l'IA**

## Installation

### Prérequis

- Python 3.12+
- PostgreSQL
- Redis
- vLLM avec Mistral-Nemo-12B-Instruct GPTQ INT8

### Configuration

1. Cloner le dépôt:
   ```bash
   git clone https://github.com/votre-username/rpg-ia-backend.git
   cd rpg-ia-backend
   ```

2. Créer un environnement virtuel:
   ```bash
   python -m venv venv
   source venv/bin/activate  # Sur Windows: venv\Scripts\activate
   ```

3. Installer les dépendances:
   ```bash
   pip install -r requirements.txt
   ```

4. Configurer les variables d'environnement:
   ```bash
   cp env-example .env
   # Modifier les valeurs dans .env selon votre configuration
   ```

5. Initialiser la base de données:
   ```bash
   python -m app.init_db
   ```

### Démarrage

```bash
uvicorn app.main:app --reload
```

L'API sera disponible à l'adresse: http://localhost:8000

La documentation Swagger UI: http://localhost:8000/docs

## Utilisation de l'API

### Authentification

```bash
# Obtenir un token JWT
curl -X POST "http://localhost:8000/api/auth/token" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=user&password=user123"
```

### Créer une session de jeu

```bash
# Créer une nouvelle session de jeu
curl -X POST "http://localhost:8000/api/game-sessions/" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ma première aventure",
    "description": "Une aventure épique",
    "game_master_id": 1,
    "game_rules": "OSE",
    "difficulty_level": "standard"
  }'
```

### Créer un personnage

```bash
# Créer un nouveau personnage
curl -X POST "http://localhost:8000/api/characters/" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Gandalf",
    "character_class": "magicien",
    "strength": 10,
    "intelligence": 18,
    "wisdom": 16,
    "dexterity": 12,
    "constitution": 14,
    "charisma": 15,
    "max_hp": 6,
    "current_hp": 6,
    "user_id": 2,
    "game_session_id": 1
  }'
```

### Soumettre une action

```bash
# Soumettre une action de joueur
curl -X POST "http://localhost:8000/api/actions/" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "action_type": "dialogue",
    "description": "Je demande à l'aubergiste s'il a vu des étrangers récemment",
    "character_id": 1,
    "scene_id": 1
  }'
```

## Développement

### Structure du projet

```
app/
  ├── api/                # Routes API
  ├── core/               # Configuration et dépendances
  ├── models/             # Modèles SQLAlchemy
  ├── schemas/            # Schémas Pydantic
  ├── services/           # Services métier
  ├── utils/              # Utilitaires
  ├── init_db.py          # Script d'initialisation de la BDD
  └── main.py             # Point d'entrée de l'application
```

### Exécuter les tests

```bash
pytest
```

## Déploiement

### Docker

```bash
docker build -t rpg-ia-backend .
docker run -p 8000:8000 rpg-ia-backend
```

### Kubernetes

```bash
kubectl apply -f kubernetes/
```

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

## Contributeurs

- Votre Nom (@votre-username)

## Remerciements

- L'équipe de FastAPI pour leur excellent framework
- La communauté OSE pour les règles de jeu
- Les développeurs de Mistral-Nemo pour leur modèle de langage