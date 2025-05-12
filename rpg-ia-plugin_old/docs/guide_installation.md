# Guide d'Installation du Plugin WordPress RPG-IA

Ce guide vous explique comment installer et configurer le plugin WordPress RPG-IA pour votre site WordPress.

## Prérequis

Avant d'installer le plugin RPG-IA, assurez-vous que votre environnement répond aux exigences suivantes :

- WordPress 5.6 ou supérieur
- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Un serveur backend RPG-IA fonctionnel (API FastAPI)
- Thème WordPress compatible (la plupart des thèmes modernes sont compatibles)

## Méthode 1 : Installation via le tableau de bord WordPress

1. **Téléchargez le plugin** : Téléchargez le fichier ZIP du plugin depuis le [site officiel de RPG-IA](https://dgroots81.mandragore.ai).

2. **Connectez-vous à votre tableau de bord WordPress** en tant qu'administrateur.

3. **Accédez à la page d'installation des plugins** : Dans le menu latéral, cliquez sur "Extensions" puis sur "Ajouter".

4. **Téléversez le plugin** : Cliquez sur le bouton "Téléverser une extension" en haut de la page, puis sur "Choisir un fichier". Sélectionnez le fichier ZIP du plugin RPG-IA que vous avez téléchargé précédemment.

5. **Installez le plugin** : Cliquez sur "Installer maintenant".

6. **Activez le plugin** : Une fois l'installation terminée, cliquez sur "Activer l'extension".

## Méthode 2 : Installation manuelle via FTP

1. **Téléchargez le plugin** : Téléchargez le fichier ZIP du plugin depuis le [site officiel de RPG-IA](https://dgroots81.mandragore.ai).

2. **Décompressez le fichier ZIP** sur votre ordinateur.

3. **Connectez-vous à votre serveur via FTP** en utilisant un client FTP comme FileZilla.

4. **Naviguez vers le répertoire des plugins WordPress** : `/wp-content/plugins/`.

5. **Téléversez le dossier `rpg-ia-plugin`** dans le répertoire des plugins.

6. **Connectez-vous à votre tableau de bord WordPress** en tant qu'administrateur.

7. **Activez le plugin** : Dans le menu latéral, cliquez sur "Extensions", puis trouvez "RPG-IA" dans la liste et cliquez sur "Activer".

## Configuration initiale

Après avoir activé le plugin, vous devez configurer la connexion avec le backend RPG-IA :

1. **Accédez aux réglages du plugin** : Dans le menu latéral, cliquez sur "RPG-IA" puis sur "Réglages".

2. **Configurez l'URL de l'API** : Entrez l'URL complète de votre API backend RPG-IA (par exemple : `https://api.votre-serveur.com`).

3. **Testez la connexion** : Cliquez sur le bouton "Tester la connexion" pour vérifier que votre site WordPress peut communiquer avec l'API backend.

4. **Enregistrez les réglages** : Cliquez sur "Enregistrer les modifications".

## Création des pages

Le plugin RPG-IA crée automatiquement plusieurs pages lors de l'activation :

- **Tableau de bord RPG-IA** : Page principale pour les joueurs
- **Personnages RPG-IA** : Page de gestion des personnages
- **Sessions RPG-IA** : Page de gestion des sessions de jeu
- **Interface de jeu RPG-IA** : Page pour jouer aux sessions

Vous pouvez vérifier que ces pages ont été créées en allant dans "Pages" dans le menu latéral de WordPress.

## Vérification de l'installation

Pour vérifier que le plugin est correctement installé et configuré :

1. **Visitez la page "Tableau de bord RPG-IA"** sur votre site.
2. **Créez un compte utilisateur** ou connectez-vous si vous avez déjà un compte.
3. **Créez un personnage** pour vérifier que la communication avec l'API fonctionne correctement.

## Résolution des problèmes courants

### Le plugin ne se connecte pas à l'API

- Vérifiez que l'URL de l'API est correcte et inclut le protocole (http:// ou https://).
- Assurez-vous que votre serveur backend est en cours d'exécution et accessible.
- Vérifiez les paramètres de pare-feu ou de proxy qui pourraient bloquer la connexion.

### Les pages du plugin affichent des erreurs

- Assurez-vous que votre thème WordPress est compatible avec les shortcodes.
- Vérifiez que les permaliens sont activés et fonctionnent correctement.
- Essayez de réinitialiser les permaliens en allant dans "Réglages" > "Permaliens" et en cliquant sur "Enregistrer les modifications".

### Problèmes d'affichage

- Assurez-vous que votre thème WordPress est à jour.
- Vérifiez s'il y a des conflits avec d'autres plugins en désactivant temporairement les autres plugins.
- Essayez de passer à un thème WordPress par défaut pour voir si le problème persiste.

## Mise à jour du plugin

Pour mettre à jour le plugin RPG-IA :

1. **Sauvegardez votre site** avant toute mise à jour.
2. **Téléchargez la nouvelle version** du plugin depuis le site officiel.
3. **Désactivez et supprimez l'ancienne version** du plugin.
4. **Installez et activez la nouvelle version** en suivant les étapes d'installation ci-dessus.

## Support technique

Si vous rencontrez des problèmes lors de l'installation ou de l'utilisation du plugin RPG-IA, vous pouvez obtenir de l'aide via :

- La [documentation en ligne](https://dgroots81.mandragore.ai/documentation)
- Le [forum de support](https://dgroots81.mandragore.ai/forum)
- L'adresse e-mail de support : support@rpg-ia.com