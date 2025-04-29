#!/bin/bash

# Script pour remplacer toutes les occurrences de rpg-ia.com par dgroots81.mandragore.ai
# dans tous les fichiers du plugin RPG-IA

# Définir les répertoires à parcourir
PLUGIN_DIR="rpg-ia-plugin"

# Remplacer les liens dans tous les fichiers
find "$PLUGIN_DIR" -type f -name "*.php" -o -name "*.js" -o -name "*.css" -o -name "*.md" | while read file; do
    echo "Traitement du fichier: $file"
    sed -i 's|https://rpg-ia.com|https://dgroots81.mandragore.ai|g' "$file"
done

echo "Remplacement terminé."
