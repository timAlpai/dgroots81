# Conseils de Refactorisation pour le Répertoire `app/`

## Contexte
L'analyse du répertoire `app/` révèle une structure typique d'application FastAPI. Cependant, une opportunité majeure de refactorisation réside dans la séparation des préoccupations entre la logique de gestion des requêtes HTTP (dans `app/routes/`) et la logique d'accès et de manipulation des données (actuellement mélangée dans les routes et potentiellement dans `app/db/`).

## Objectif
L'objectif de cette refactorisation est d'améliorer la maintenabilité, la testabilité et la modularité du code en introduisant une couche de service dédiée à la logique métier et à l'interaction avec la base de données.

## Proposition de Refactorisation : Introduction d'une Couche de Service

Il est proposé d'introduire un nouveau répertoire `app/services/` qui contiendra des modules Python, chacun responsable de la logique métier pour une entité ou un domaine spécifique (par exemple, `joueur_service.py`, `personnage_service.py`, `auth_service.py`).

### Structure Proposée

```
app/
├── ...
├── routes/
│   ├── ...
│   └── joueur.py  # Les routes appellent les services
├── services/
│   ├── __init__.py
│   ├── joueur_service.py  # Logique métier et DB pour les joueurs
│   ├── personnage_service.py # Logique métier et DB pour les personnages
│   └── auth_service.py # Logique métier et DB pour l'authentification
├── db/
│   ├── ...
│   └── models/ # Modèles SQLAlchemy
│       └── ...
├── schemas/ # Modèles Pydantic
│   └── ...
└── ...
```

### Responsabilités de la Couche de Service

Chaque module de service sera responsable de :
- Interagir directement avec les modèles SQLAlchemy (`app/db/models/`) via la session de base de données.
- Contenir la logique métier complexe (validation, calculs, etc.).
- Retourner des objets modèles ou des structures de données simples, mais pas directement des réponses HTTP.
- Être injecté comme dépendance dans les gestionnaires de route (`app/routes/`).

### Responsabilités Mises à Jour de la Couche de Routes

Les gestionnaires de route (`app/routes/`) seront responsables de :
- Parser les requêtes entrantes en utilisant les schémas Pydantic (`app/schemas/`).
- Appeler les méthodes appropriées des services injectés.
- Gérer les exceptions levées par les services et les traduire en réponses HTTP appropriées (avec les codes de statut corrects).
- Sérialiser les données retournées par les services en utilisant les schémas Pydantic de sortie.

### Avantages Attendus

- **Meilleure Séparation des Préoccupations :** La logique HTTP est séparée de la logique métier et de l'accès aux données.
- **Amélioration de la Testabilité :** Les services peuvent être testés indépendamment des routes, en mockant la session de base de données.
- **Réduction de la Duplication de Code :** La logique métier réutilisable peut être centralisée dans les services.
- **Maintenance Simplifiée :** Les modifications de la logique métier ou de la base de données affectent principalement la couche de service.

## Tâches d'Implémentation

La mise en œuvre de cette refactorisation impliquera les étapes suivantes :

1.  Créer le répertoire `app/services/` et les fichiers de service initiaux (par exemple, `joueur_service.py`).
2.  Déplacer la logique d'accès à la base de données et la logique métier associée des routes vers les services correspondants.
3.  Mettre à jour les gestionnaires de route pour utiliser les services via l'injection de dépendances.
4.  Adapter les tests existants ou créer de nouveaux tests pour la couche de service.

## Spécifications Détaillées des Tâches

Voir les tâches correspondantes ajoutées dans `project_state.json` pour les détails d'implémentation spécifiques.