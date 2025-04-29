#!/bin/bash

# Script pour exécuter les tests avec les bonnes variables d'environnement

# Charger les variables d'environnement de test
export $(grep -v '^#' .env.test | xargs)

# Ajouter le répertoire du projet au PYTHONPATH
export PYTHONPATH=$PYTHONPATH:$(pwd)

# Exécuter les tests
pytest "$@"

# Afficher un résumé de la couverture de code
echo -e "\n\033[1;34mRésumé de la couverture de code :\033[0m"
echo -e "\033[1;32mLe rapport HTML détaillé est disponible dans le dossier 'coverage_html'.\033[0m"