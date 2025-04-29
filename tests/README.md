# Tests pour RPG-IA-Backend

Ce dossier contient les tests automatisés pour le backend RPG-IA. Les tests sont écrits en utilisant pytest et couvrent les fonctionnalités principales de l'application.

## Structure des tests

Les tests sont organisés comme suit :

- `conftest.py` : Contient les fixtures de test partagées entre les différents tests
- `test_security.py` : Tests pour les fonctions de sécurité (authentification, hachage de mot de passe, etc.)
- `test_schemas.py` : Tests pour les schémas Pydantic (validation des données)
- `test_user_model.py` : Tests pour le modèle User (opérations CRUD)
- `test_auth_api.py` : Tests pour les endpoints d'authentification
- `test_users_api.py` : Tests pour les endpoints de gestion des utilisateurs

## Exécution des tests

Pour exécuter tous les tests :

```bash
pytest
```

Pour exécuter un fichier de test spécifique :

```bash
pytest tests/test_security.py
```

Pour exécuter un test spécifique :

```bash
pytest tests/test_security.py::test_verify_password
```

## Couverture de code

Les tests sont configurés pour générer des rapports de couverture de code. Pour voir la couverture de code :

```bash
pytest --cov=app
```

Pour générer un rapport HTML détaillé :

```bash
pytest --cov=app --cov-report=html
```

Le rapport HTML sera généré dans le dossier `coverage_html`.

## Ajout de nouveaux tests

Pour ajouter de nouveaux tests :

1. Créez un nouveau fichier de test dans le dossier `tests` avec le préfixe `test_`.
2. Utilisez les fixtures définies dans `conftest.py` pour configurer l'environnement de test.
3. Utilisez le décorateur `@pytest.mark.asyncio` pour les tests asynchrones.
4. Suivez le modèle des tests existants pour maintenir la cohérence.

## Dépendances de test

Les dépendances suivantes sont nécessaires pour exécuter les tests :

- pytest
- pytest-asyncio
- pytest-cov
- aiosqlite

Ces dépendances sont incluses dans le fichier `requirements.txt` à la racine du projet.