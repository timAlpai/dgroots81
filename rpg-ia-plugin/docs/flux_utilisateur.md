# Flux Utilisateur pour le Plugin WordPress RPG-IA

Ce document décrit le flux utilisateur pour jouer à RPG-IA via le plugin WordPress.

## 1. Authentification et Gestion des Utilisateurs

### 1.1 Inscription
- L'utilisateur accède à la page d'inscription du plugin
- Il remplit un formulaire avec son nom d'utilisateur, email et mot de passe
- Le plugin envoie une requête à l'API `/api/auth/register`
- Après validation, l'utilisateur est redirigé vers la page de connexion

### 1.2 Connexion
- L'utilisateur accède à la page de connexion du plugin
- Il saisit son nom d'utilisateur et mot de passe
- Le plugin envoie une requête à l'API `/api/auth/token`
- Le token JWT est stocké localement (cookie ou localStorage)
- L'utilisateur est redirigé vers le tableau de bord

### 1.3 Profil Utilisateur
- L'utilisateur peut consulter et modifier son profil
- Le plugin récupère les informations via l'API `/api/auth/me`
- Les statistiques de jeu sont affichées (tokens utilisés, temps de jeu, etc.)

## 2. Gestion des Personnages

### 2.1 Création de Personnage
- L'utilisateur accède à la page de création de personnage
- Il remplit un formulaire avec les caractéristiques du personnage (nom, classe, attributs, etc.)
- Le plugin envoie une requête à l'API `/api/characters/`
- Le nouveau personnage est affiché dans la liste des personnages

### 2.2 Liste des Personnages
- L'utilisateur peut voir tous ses personnages
- Le plugin récupère la liste via l'API `/api/characters/`
- Pour chaque personnage, des options sont disponibles (voir détails, modifier, supprimer)

### 2.3 Détails du Personnage
- L'utilisateur peut consulter les détails d'un personnage
- Le plugin récupère les informations via l'API `/api/characters/{character_id}`
- Les caractéristiques, équipement, compétences et historique sont affichés

## 3. Gestion des Sessions de Jeu

### 3.1 Création de Session
- L'utilisateur (maître de jeu) accède à la page de création de session
- Il remplit un formulaire avec les détails de la session (nom, description, règles, etc.)
- Le plugin envoie une requête à l'API `/api/game-sessions/`
- La nouvelle session est affichée dans la liste des sessions

### 3.2 Liste des Sessions
- L'utilisateur peut voir toutes les sessions auxquelles il participe
- Le plugin récupère la liste via l'API `/api/game-sessions/`
- Pour chaque session, des options sont disponibles (rejoindre, voir détails, etc.)

### 3.3 Détails de la Session
- L'utilisateur peut consulter les détails d'une session
- Le plugin récupère les informations via l'API `/api/game-sessions/{session_id}`
- Les participants, scénario actuel et autres informations sont affichés

## 4. Interface de Jeu

### 4.1 Rejoindre une Session
- L'utilisateur sélectionne une session dans la liste
- Il choisit un personnage pour participer
- Le plugin vérifie si le personnage est déjà associé à la session
- Si non, le personnage est ajouté à la session

### 4.2 Écran de Jeu Principal
- L'écran est divisé en plusieurs zones:
  - Zone de narration (affichage du texte généré par l'IA)
  - Zone d'action (formulaire pour soumettre des actions)
  - Zone d'information du personnage (stats, inventaire, etc.)
  - Zone d'information de la session (participants, scène actuelle, etc.)

### 4.3 Soumission d'Actions
- L'utilisateur sélectionne un type d'action (dialogue, combat, exploration, etc.)
- Il saisit une description de l'action
- Le plugin envoie une requête à l'API `/api/actions/`
- La réponse de l'IA est affichée dans la zone de narration
- Les mises à jour du personnage sont appliquées si nécessaire

### 4.4 Visualisation des Scènes
- Les scènes sont affichées avec leur description et contenu narratif
- Des éléments visuels peuvent être ajoutés (images, cartes, etc.)
- Les PNJ, monstres et objets sont listés si pertinents

### 4.5 Gestion de l'Inventaire
- L'utilisateur peut consulter et gérer l'inventaire de son personnage
- Les objets peuvent être utilisés, équipés ou abandonnés
- Les modifications sont envoyées à l'API

## 5. Fonctionnalités Avancées

### 5.1 Chat entre Joueurs
- Les joueurs d'une même session peuvent communiquer via un chat intégré
- Les messages sont visibles par tous les participants
- Le maître de jeu peut envoyer des messages privés

### 5.2 Journal de Session
- Un journal automatique enregistre les événements importants
- Les joueurs peuvent ajouter des notes personnelles
- L'historique des actions est consultable

### 5.3 Gestion des Ressources
- Le maître de jeu peut ajouter des ressources (images, cartes, musiques)
- Ces ressources sont accessibles pendant la session

## 6. Administration (Maître de Jeu)

### 6.1 Gestion des Scénarios
- Le maître de jeu peut créer et gérer des scénarios
- Il peut définir des scènes, PNJ, monstres et objets
- Les scénarios peuvent être assignés à des sessions

### 6.2 Contrôle de Session
- Le maître de jeu peut démarrer, mettre en pause et arrêter une session
- Il peut modifier les paramètres de la session en cours
- Il peut intervenir directement dans la narration

### 6.3 Suivi des Joueurs
- Le maître de jeu peut voir les statistiques des joueurs
- Il peut aider les joueurs en difficulté
- Il peut ajuster la difficulté en temps réel

## 7. Intégration WordPress

### 7.1 Shortcodes
- Des shortcodes permettent d'intégrer des éléments du jeu dans les pages WordPress
- Exemples: [rpgia_character id=X], [rpgia_session id=Y], etc.

### 7.2 Widgets
- Des widgets affichent des informations sur le jeu dans les sidebars
- Exemples: sessions actives, personnages populaires, etc.

### 7.3 Pages Personnalisées
- Le plugin crée des pages personnalisées pour les principales fonctionnalités
- Ces pages sont intégrées au thème WordPress actif

## 8. Sécurité et Performance

### 8.1 Gestion des Tokens
- Les tokens JWT sont gérés de manière sécurisée
- Le rafraîchissement automatique est implémenté
- La déconnexion invalide le token

### 8.2 Mise en Cache
- Les données fréquemment utilisées sont mises en cache
- Les requêtes API sont optimisées
- Les ressources sont chargées de manière asynchrone

### 8.3 Responsive Design
- L'interface s'adapte à tous les appareils (desktop, tablette, mobile)
- L'expérience utilisateur est optimisée pour chaque format