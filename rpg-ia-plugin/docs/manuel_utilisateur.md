# Manuel Utilisateur du Plugin WordPress RPG-IA

Ce manuel vous guide dans l'utilisation du plugin WordPress RPG-IA, qui vous permet de jouer à des jeux de rôle en ligne avec une IA comme maître de jeu.

## Table des matières

1. [Introduction](#introduction)
2. [Premiers pas](#premiers-pas)
3. [Interface utilisateur](#interface-utilisateur)
4. [Gestion des personnages](#gestion-des-personnages)
5. [Gestion des sessions de jeu](#gestion-des-sessions-de-jeu)
6. [Interface de jeu](#interface-de-jeu)
7. [Fonctionnalités pour le maître de jeu](#fonctionnalités-pour-le-maître-de-jeu)
8. [Widgets et shortcodes](#widgets-et-shortcodes)
9. [Astuces et bonnes pratiques](#astuces-et-bonnes-pratiques)
10. [FAQ](#faq)

## Introduction

RPG-IA est un plugin WordPress qui permet de jouer à des jeux de rôle en ligne en utilisant une intelligence artificielle comme maître de jeu. Il offre une interface intuitive pour créer des personnages, gérer des sessions de jeu et interagir avec l'IA pour vivre des aventures immersives.

### Fonctionnalités principales

- Création et gestion de personnages
- Organisation de sessions de jeu
- Interface de jeu interactive
- Système de chat entre joueurs
- Outils pour les maîtres de jeu humains
- Intégration complète avec WordPress

## Premiers pas

### Inscription et connexion

1. **Inscription** : Accédez à la page d'inscription du plugin et créez un compte en fournissant un nom d'utilisateur, une adresse e-mail et un mot de passe.

2. **Connexion** : Une fois inscrit, connectez-vous avec vos identifiants sur la page de connexion.

3. **Profil utilisateur** : Après la connexion, vous serez redirigé vers le tableau de bord où vous pourrez accéder à votre profil utilisateur.

### Tableau de bord

Le tableau de bord est votre point d'entrée principal dans RPG-IA. Il affiche :

- Vos statistiques personnelles (personnages, sessions, temps de jeu)
- Les sessions actives auxquelles vous participez
- Des actions rapides (créer un personnage, créer une session, rejoindre une partie)
- Vos dernières activités

## Interface utilisateur

### Navigation principale

La barre de navigation RPG-IA contient les éléments suivants :

- **Tableau de bord** : Vue d'ensemble de votre activité
- **Personnages** : Gestion de vos personnages
- **Sessions** : Gestion des sessions de jeu
- **Jouer** : Accès rapide à vos sessions actives

### Thèmes et personnalisation

Le plugin s'intègre à votre thème WordPress actuel. Vous pouvez personnaliser l'apparence via :

- Les options de personnalisation de WordPress
- Les CSS personnalisés (pour les utilisateurs avancés)
- Les options de thème du plugin (si disponibles)

## Gestion des personnages

### Création de personnage

1. Accédez à la page "Personnages" et cliquez sur "Nouveau personnage".
2. Remplissez le formulaire avec les informations de votre personnage :
   - **Informations générales** : Nom, classe, etc.
   - **Caractéristiques** : Force, intelligence, sagesse, etc.
   - **Équipement** : Armes, armures, objets, etc.
   - **Compétences** : Capacités spéciales selon la classe
   - **Biographie et apparence** : Détails sur l'histoire et l'apparence du personnage

3. Cliquez sur "Enregistrer" pour créer votre personnage.

### Liste des personnages

La page "Personnages" affiche tous vos personnages avec des informations de base :
- Nom
- Classe
- Niveau
- Session actuelle (si applicable)

Vous pouvez filtrer cette liste par session ou par classe.

### Détails du personnage

En cliquant sur un personnage dans la liste, vous accédez à sa page de détails qui affiche :

- **Informations générales** : Classe, niveau, expérience, etc.
- **Caractéristiques** : Valeurs et modificateurs
- **Équipement** : Liste des objets équipés et dans l'inventaire
- **Compétences** : Liste des capacités spéciales
- **Historique des actions** : Journal des actions récentes du personnage

### Modification de personnage

Pour modifier un personnage :
1. Accédez à la page de détails du personnage
2. Cliquez sur "Modifier"
3. Effectuez vos changements dans le formulaire
4. Cliquez sur "Enregistrer"

## Gestion des sessions de jeu

### Création de session

Pour créer une nouvelle session de jeu :

1. Accédez à la page "Sessions" et cliquez sur "Nouvelle session".
2. Remplissez le formulaire avec les informations de la session :
   - **Informations générales** : Nom, description
   - **Paramètres** : Règles, difficulté
   - **Scénario** : Sélection ou création d'un scénario
   - **Joueurs** : Capacité maximale et invitations

3. Cliquez sur "Enregistrer" pour créer la session.

### Liste des sessions

La page "Sessions" affiche toutes les sessions disponibles avec des informations de base :
- Nom
- Maître de jeu
- Nombre de joueurs
- Statut (active, en pause, inactive)

Vous pouvez filtrer cette liste pour voir toutes les sessions, vos sessions ou uniquement les sessions actives.

### Détails de la session

En cliquant sur une session dans la liste, vous accédez à sa page de détails qui affiche :

- **Informations générales** : Maître de jeu, date de création, statut, etc.
- **Statistiques** : Nombre d'actions, temps de jeu, tokens utilisés
- **Scénario et scène actuels** : Informations sur l'aventure en cours
- **Liste des joueurs** : Participants avec leurs personnages

### Rejoindre une session

Pour rejoindre une session existante :

1. Accédez à la page "Sessions"
2. Trouvez une session avec des places disponibles
3. Cliquez sur "Rejoindre"
4. Sélectionnez un de vos personnages pour participer
5. Confirmez votre participation

## Interface de jeu

### Écran principal

L'interface de jeu est divisée en plusieurs zones :

- **Zone de narration** : Affiche le texte généré par l'IA, les descriptions de scènes et les résultats des actions
- **Zone d'action** : Permet de soumettre des actions pour votre personnage
- **Zone d'information du personnage** : Affiche les statistiques et l'inventaire de votre personnage
- **Zone d'information de la session** : Montre les autres joueurs et l'état de la session

### Soumission d'actions

Pour interagir avec le jeu :

1. Sélectionnez un type d'action dans le menu déroulant (dialogue, combat, exploration, etc.)
2. Saisissez une description détaillée de votre action
3. Cliquez sur "Soumettre"
4. L'IA traitera votre action et générera une réponse dans la zone de narration

### Gestion de l'inventaire

Pour gérer l'inventaire de votre personnage :

1. Cliquez sur "Inventaire" dans la zone d'information du personnage
2. Un panneau s'ouvrira avec deux sections :
   - **Équipement** : Objets actuellement équipés
   - **Sac à dos** : Objets transportés

3. Vous pouvez utiliser, équiper ou abandonner des objets en cliquant sur les boutons correspondants

### Journal de session

Le journal de session enregistre automatiquement les événements importants :

1. Cliquez sur l'onglet "Journal" dans l'interface de jeu
2. Consultez l'historique des actions et leurs résultats
3. Filtrez le journal par type d'événement (combats, dialogues, PNJ)
4. Ajoutez des notes personnelles qui ne sont visibles que par vous

### Chat entre joueurs

Pour communiquer avec les autres joueurs :

1. Cliquez sur l'onglet "Chat" dans l'interface de jeu
2. Saisissez votre message dans la zone de texte
3. Cliquez sur "Envoyer"
4. Tous les participants de la session verront votre message

## Fonctionnalités pour le maître de jeu

### Tableau de bord MJ

Si vous êtes maître de jeu, vous avez accès à un tableau de bord spécial qui affiche :

- Vos sessions actives
- Des statistiques détaillées
- Vos scénarios
- Des ressources disponibles (cartes, PNJ, monstres, objets)

### Gestion des scénarios

En tant que MJ, vous pouvez créer et gérer des scénarios :

1. Accédez à "Scénarios" depuis le tableau de bord MJ
2. Créez un nouveau scénario ou modifiez un existant
3. Ajoutez des scènes, des PNJ, des monstres et des objets
4. Organisez les scènes dans l'ordre souhaité
5. Assignez le scénario à une session

### Interface MJ en jeu

Pendant une session, le MJ dispose d'outils supplémentaires :

- **Contrôles de session** : Pause, reprise, fin de session
- **Gestion des scènes** : Changement de scène, ajout de contenu
- **Supervision des joueurs** : Suivi des statistiques, aide aux joueurs en difficulté
- **Ressources** : Accès rapide aux PNJ, monstres, objets et cartes
- **Événements** : Déclenchement d'événements spéciaux (combats, rencontres, pièges, récompenses)

### Intervention narrative

Le MJ peut intervenir directement dans la narration :

1. Consultez la narration actuelle générée par l'IA
2. Ajoutez du texte personnalisé pour enrichir l'histoire
3. Remplacez complètement la réponse de l'IA si nécessaire

## Widgets et shortcodes

### Widgets disponibles

Le plugin fournit plusieurs widgets pour les sidebars WordPress :

- **RPG-IA Sessions** : Affiche les sessions actives
- **RPG-IA Characters** : Affiche vos personnages
- **RPG-IA Stats** : Affiche vos statistiques de jeu

Pour ajouter un widget :
1. Accédez à "Apparence" > "Widgets" dans le tableau de bord WordPress
2. Faites glisser le widget souhaité dans une zone de sidebar
3. Configurez les options du widget
4. Cliquez sur "Enregistrer"

### Shortcodes

Vous pouvez intégrer des éléments RPG-IA dans n'importe quelle page ou article WordPress en utilisant des shortcodes :

- `[rpgia_character id=X]` : Affiche les informations d'un personnage
- `[rpgia_session id=Y]` : Affiche les informations d'une session
- `[rpgia_dashboard]` : Affiche le tableau de bord du joueur
- `[rpgia_game_interface session=Z]` : Affiche l'interface de jeu pour une session

## Astuces et bonnes pratiques

### Pour les joueurs

- **Descriptions détaillées** : Plus vos actions sont détaillées, plus les réponses de l'IA seront riches et pertinentes.
- **Cohérence du personnage** : Essayez de maintenir une cohérence dans les actions et les dialogues de votre personnage.
- **Collaboration** : Communiquez avec les autres joueurs pour coordonner vos actions.
- **Prise de notes** : Utilisez la fonction de notes personnelles pour garder une trace des informations importantes.

### Pour les maîtres de jeu

- **Préparation** : Créez des scénarios détaillés avant de commencer une session.
- **Équilibre** : Laissez l'IA gérer la narration de base, mais intervenez pour ajouter de la profondeur ou corriger des incohérences.
- **Feedback** : Encouragez les joueurs à donner leur avis pour améliorer l'expérience.
- **Ressources** : Préparez des ressources visuelles (cartes, images de PNJ) pour enrichir l'expérience.

## FAQ

### Questions générales

**Q : Puis-je utiliser RPG-IA sans connexion internet ?**  
R : Non, le plugin nécessite une connexion internet pour communiquer avec l'API backend.

**Q : Combien de personnages puis-je créer ?**  
R : Il n'y a pas de limite au nombre de personnages que vous pouvez créer.

**Q : Le plugin est-il compatible avec tous les thèmes WordPress ?**  
R : Le plugin est conçu pour fonctionner avec la plupart des thèmes WordPress modernes, mais des ajustements CSS peuvent être nécessaires pour certains thèmes.

### Questions sur le jeu

**Q : Quels systèmes de règles sont supportés ?**  
R : Actuellement, le plugin supporte principalement les règles OSE (Old School Essentials), mais d'autres systèmes pourront être ajoutés dans le futur.

**Q : Puis-je importer un personnage existant ?**  
R : Pas directement, mais vous pouvez créer manuellement un personnage avec les mêmes caractéristiques.

**Q : Comment fonctionne la progression des personnages ?**  
R : Les personnages gagnent de l'expérience en participant à des sessions. Lorsqu'ils atteignent certains seuils, ils montent de niveau et obtiennent de nouvelles capacités.

### Questions techniques

**Q : Que faire si l'IA ne répond pas ?**  
R : Vérifiez votre connexion internet et l'état du serveur backend. Si le problème persiste, contactez le support technique.

**Q : Comment signaler un bug ?**  
R : Vous pouvez signaler les bugs via le forum de support ou par e-mail à support@rpg-ia.com.

**Q : Mes données sont-elles sauvegardées ?**  
R : Oui, toutes les données (personnages, sessions, actions) sont sauvegardées dans la base de données WordPress et synchronisées avec le backend.