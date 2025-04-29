# Maquettes d'Interface Utilisateur - Plugin WordPress RPG-IA

Ce document décrit les maquettes d'interface utilisateur pour le plugin WordPress RPG-IA. Il servira de guide pour la conception et le développement de l'interface utilisateur.

## 1. Structure Générale

Toutes les pages du plugin suivront une structure commune:

```
+-----------------------------------------------+
| [HEADER WORDPRESS]                            |
+-----------------------------------------------+
| [NAVIGATION RPG-IA]                           |
+-----------------------------------------------+
|                                               |
|                                               |
|                CONTENU                        |
|                                               |
|                                               |
+-----------------------------------------------+
| [FOOTER WORDPRESS]                            |
+-----------------------------------------------+
```

### 1.1 Navigation RPG-IA

La barre de navigation spécifique au plugin contiendra:

```
+-----------------------------------------------+
| RPG-IA | Tableau de bord | Personnages | Sessions | Jouer |
+-----------------------------------------------+
```

## 2. Page d'Accueil du Plugin

La page d'accueil présentera une vue d'ensemble du jeu et des options disponibles:

```
+-----------------------------------------------+
| [HEADER + NAVIGATION]                         |
+-----------------------------------------------+
|                                               |
|  +-------------------+  +------------------+  |
|  | Bienvenue, [User] |  | Sessions Actives |  |
|  |                   |  |                  |  |
|  | Statistiques:     |  | - Session 1      |  |
|  | - Personnages: X  |  | - Session 2      |  |
|  | - Sessions: Y     |  | - Session 3      |  |
|  | - Temps de jeu: Z |  |                  |  |
|  +-------------------+  +------------------+  |
|                                               |
|  +-------------------+  +------------------+  |
|  | Actions Rapides   |  | Dernières        |  |
|  |                   |  | Activités        |  |
|  | [Créer Perso]     |  |                  |  |
|  | [Créer Session]   |  | - Action 1       |  |
|  | [Rejoindre]       |  | - Action 2       |  |
|  |                   |  | - Action 3       |  |
|  +-------------------+  +------------------+  |
|                                               |
+-----------------------------------------------+
```

## 3. Gestion des Personnages

### 3.1 Liste des Personnages

```
+-----------------------------------------------+
| [HEADER + NAVIGATION]                         |
+-----------------------------------------------+
|                                               |
|  Mes Personnages                [+ Nouveau]   |
|                                               |
|  +-------------------------------------------+  |
|  | Filtre: [Tous] [Par Session] [Par Classe] |  |
|  +-------------------------------------------+  |
|                                               |
|  +-------------------------------------------+  |
|  | Nom    | Classe   | Niveau | Session      |  |
|  |--------|----------|--------|--------------|  |
|  | Perso1 | Guerrier | 3      | Session A    |  |
|  | Perso2 | Magicien | 2      | Session B    |  |
|  | Perso3 | Clerc    | 1      | -            |  |
|  +-------------------------------------------+  |
|                                               |
+-----------------------------------------------+
```

### 3.2 Création/Édition de Personnage

```
+-----------------------------------------------+
| [HEADER + NAVIGATION]                         |
+-----------------------------------------------+
|                                               |
|  Créer un Nouveau Personnage                  |
|                                               |
|  +-------------------+  +------------------+  |
|  | Informations      |  | Caractéristiques |  |
|  | Générales         |  |                  |  |
|  |                   |  | Force: [____]    |  |
|  | Nom: [_________]  |  | Intel: [____]    |  |
|  |                   |  | Sagesse: [____]  |  |
|  | Classe:           |  | Dextérité: [___] |  |
|  | [Dropdown_______] |  | Constit.: [____] |  |
|  |                   |  | Charisme: [____] |  |
|  +-------------------+  +------------------+  |
|                                               |
|  +-------------------+  +------------------+  |
|  | Équipement        |  | Compétences      |  |
|  |                   |  |                  |  |
|  | [Liste d'items    |  | [Liste de        |  |
|  |  avec checkboxes] |  |  compétences]    |  |
|  |                   |  |                  |  |
|  +-------------------+  +------------------+  |
|                                               |
|  +-------------------+  +------------------+  |
|  | Biographie        |  | Apparence        |  |
|  |                   |  |                  |  |
|  | [Textarea_______] |  | [Textarea______] |  |
|  |                   |  |                  |  |
|  +-------------------+  +------------------+  |
|                                               |
|  [Annuler]                        [Enregistrer] |
|                                               |
+-----------------------------------------------+
```

### 3.3 Détails du Personnage

```
+-----------------------------------------------+
| [HEADER + NAVIGATION]                         |
+-----------------------------------------------+
|                                               |
|  Personnage: [Nom]                 [Modifier] |
|                                               |
|  +-------------------+  +------------------+  |
|  | Informations      |  | Caractéristiques |  |
|  | Générales         |  |                  |  |
|  |                   |  | FOR: XX (+Y)     |  |
|  | Classe: XXXXX     |  | INT: XX (+Y)     |  |
|  | Niveau: X         |  | SAG: XX (+Y)     |  |
|  | Exp: XXXX/YYYY    |  | DEX: XX (+Y)     |  |
|  | PV: XX/YY         |  | CON: XX (+Y)     |  |
|  | CA: ZZ            |  | CHA: XX (+Y)     |  |
|  +-------------------+  +------------------+  |
|                                               |
|  +-------------------+  +------------------+  |
|  | Équipement        |  | Compétences      |  |
|  |                   |  |                  |  |
|  | - Item 1          |  | - Compétence 1   |  |
|  | - Item 2          |  | - Compétence 2   |  |
|  | - Item 3          |  | - Compétence 3   |  |
|  |                   |  |                  |  |
|  +-------------------+  +------------------+  |
|                                               |
|  +-------------------------------------------+  |
|  | Historique des Actions                    |  |
|  |                                           |  |
|  | [Liste des dernières actions du personnage] |  |
|  |                                           |  |
|  +-------------------------------------------+  |
|                                               |
+-----------------------------------------------+
```

## 4. Gestion des Sessions

### 4.1 Liste des Sessions

```
+-----------------------------------------------+
| [HEADER + NAVIGATION]                         |
+-----------------------------------------------+
|                                               |
|  Sessions de Jeu                 [+ Nouvelle] |
|                                               |
|  +-------------------------------------------+  |
|  | Filtre: [Toutes] [Mes Sessions] [Actives] |  |
|  +-------------------------------------------+  |
|                                               |
|  +-------------------------------------------+  |
|  | Nom      | MJ      | Joueurs | Statut     |  |
|  |----------|---------|---------|------------|  |
|  | Session1 | User1   | 3/6     | Active     |  |
|  | Session2 | User2   | 4/4     | En pause   |  |
|  | Session3 | [Vous]  | 2/5     | Inactive   |  |
|  +-------------------------------------------+  |
|                                               |
+-----------------------------------------------+
```

### 4.2 Création/Édition de Session

```
+-----------------------------------------------+
| [HEADER + NAVIGATION]                         |
+-----------------------------------------------+
|                                               |
|  Créer une Nouvelle Session                   |
|                                               |
|  +-------------------------------------------+  |
|  | Informations Générales                    |  |
|  |                                           |  |
|  | Nom: [_______________________________]    |  |
|  |                                           |  |
|  | Description:                              |  |
|  | [_______________________________________] |  |
|  | [_______________________________________] |  |
|  |                                           |  |
|  +-------------------------------------------+  |
|                                               |
|  +-------------------+  +------------------+  |
|  | Paramètres        |  | Scénario         |  |
|  |                   |  |                  |  |
|  | Règles:           |  | Sélectionner:    |  |
|  | [OSE_________]    |  | [Dropdown______] |  |
|  |                   |  |                  |  |
|  | Difficulté:       |  | ou Créer:        |  |
|  | [Standard_____]   |  | [+ Nouveau]      |  |
|  |                   |  |                  |  |
|  +-------------------+  +------------------+  |
|                                               |
|  +-------------------------------------------+  |
|  | Joueurs                                   |  |
|  |                                           |  |
|  | Capacité max: [_____]                     |  |
|  |                                           |  |
|  | Inviter des joueurs:                      |  |
|  | [_______________________________] [Inviter] |  |
|  |                                           |  |
|  +-------------------------------------------+  |
|                                               |
|  [Annuler]                        [Enregistrer] |
|                                               |
+-----------------------------------------------+
```

### 4.3 Détails de la Session

```
+-----------------------------------------------+
| [HEADER + NAVIGATION]                         |
+-----------------------------------------------+
|                                               |
|  Session: [Nom]          [Modifier] [Jouer]   |
|                                               |
|  +-------------------+  +------------------+  |
|  | Informations      |  | Statistiques     |  |
|  |                   |  |                  |  |
|  | MJ: XXXXX         |  | Actions: XXX     |  |
|  | Créée le: XX/XX   |  | Temps: XX h      |  |
|  | Statut: XXXXX     |  | Tokens: XXXXX    |  |
|  | Règles: OSE       |  |                  |  |
|  | Difficulté: XXX   |  |                  |  |
|  +-------------------+  +------------------+  |
|                                               |
|  +-------------------+  +------------------+  |
|  | Scénario Actuel   |  | Scène Actuelle   |  |
|  |                   |  |                  |  |
|  | Nom: XXXXX        |  | Nom: XXXXX       |  |
|  | Type: XXXXX       |  | Type: XXXXX      |  |
|  |                   |  |                  |  |
|  | [Changer]         |  | [Détails]        |  |
|  +-------------------+  +------------------+  |
|                                               |
|  +-------------------------------------------+  |
|  | Joueurs                                   |  |
|  |                                           |  |
|  | - Joueur1 (Personnage1)                  |  |
|  | - Joueur2 (Personnage2)                  |  |
|  | - Joueur3 (Personnage3)                  |  |
|  |                                           |  |
|  | [+ Inviter]                               |  |
|  +-------------------------------------------+  |
|                                               |
+-----------------------------------------------+
```

## 5. Interface de Jeu

### 5.1 Écran Principal de Jeu

```
+-----------------------------------------------+
| [HEADER + NAVIGATION]                         |
+-----------------------------------------------+
|                                               |
|  Session: [Nom]  |  Personnage: [Nom]         |
|                                               |
|  +-------------------------------------------+  |
|  | Zone de Narration                         |  |
|  |                                           |  |
|  | [Texte généré par l'IA, description de la  |  |
|  |  scène actuelle, résultats des actions,    |  |
|  |  dialogues, etc.]                         |  |
|  |                                           |  |
|  |                                           |  |
|  |                                           |  |
|  +-------------------------------------------+  |
|                                               |
|  +-------------------+  +------------------+  |
|  | Actions           |  | Personnage       |  |
|  |                   |  |                  |  |
|  | Type:             |  | PV: XX/YY        |  |
|  | [Dialogue_____]   |  | CA: ZZ           |  |
|  |                   |  |                  |  |
|  | Description:      |  | [Inventaire]     |  |
|  | [_____________]   |  | [Compétences]    |  |
|  | [_____________]   |  | [Sorts]          |  |
|  |                   |  |                  |  |
|  | [Soumettre]       |  |                  |  |
|  +-------------------+  +------------------+  |
|                                               |
|  +-------------------------------------------+  |
|  | Autres Joueurs                            |  |
|  |                                           |  |
|  | - Joueur1 (Personnage1) - PV: XX/YY      |  |
|  | - Joueur2 (Personnage2) - PV: XX/YY      |  |
|  | - Joueur3 (Personnage3) - PV: XX/YY      |  |
|  |                                           |  |
|  +-------------------------------------------+  |
|                                               |
+-----------------------------------------------+
```

### 5.2 Panneau d'Inventaire (Modal)

```
+-----------------------------------------------+
|                                               |
|  Inventaire de [Personnage]                   |
|                                               |
|  +-------------------+  +------------------+  |
|  | Équipement        |  | Sac à dos        |  |
|  |                   |  |                  |  |
|  | Tête: [Item]      |  | - Item 1 [Util.] |  |
|  | Corps: [Item]     |  | - Item 2 [Util.] |  |
|  | Mains: [Item]     |  | - Item 3 [Util.] |  |
|  | Pieds: [Item]     |  | - Item 4 [Util.] |  |
|  |                   |  |                  |  |
|  | Arme1: [Item]     |  | Or: XXX          |  |
|  | Arme2: [Item]     |  |                  |  |
|  |                   |  |                  |  |
|  +-------------------+  +------------------+  |
|                                               |
|  [Fermer]                                     |
|                                               |
+-----------------------------------------------+
```

### 5.3 Journal de Session (Onglet)

```
+-----------------------------------------------+
| [HEADER + NAVIGATION]                         |
+-----------------------------------------------+
|                                               |
|  Session: [Nom]  |  Journal                   |
|                                               |
|  +-------------------------------------------+  |
|  | Filtre: [Tout] [Combats] [Dialogues] [PNJ] |  |
|  +-------------------------------------------+  |
|                                               |
|  +-------------------------------------------+  |
|  | Journal de Session                        |  |
|  |                                           |  |
|  | [Date/Heure] Action de Personnage1        |  |
|  | [Résultat de l'action]                    |  |
|  |                                           |  |
|  | [Date/Heure] Action de Personnage2        |  |
|  | [Résultat de l'action]                    |  |
|  |                                           |  |
|  | [Date/Heure] Action de Personnage3        |  |
|  | [Résultat de l'action]                    |  |
|  |                                           |  |
|  +-------------------------------------------+  |
|                                               |
|  +-------------------------------------------+  |
|  | Notes Personnelles                        |  |
|  |                                           |  |
|  | [Textarea pour ajouter des notes]         |  |
|  |                                           |  |
|  | [Enregistrer]                             |  |
|  +-------------------------------------------+  |
|                                               |
+-----------------------------------------------+
```

## 6. Interface d'Administration (Maître de Jeu)

### 6.1 Tableau de Bord MJ

```
+-----------------------------------------------+
| [HEADER + NAVIGATION]                         |
+-----------------------------------------------+
|                                               |
|  Tableau de Bord Maître de Jeu                |
|                                               |
|  +-------------------+  +------------------+  |
|  | Mes Sessions      |  | Statistiques     |  |
|  |                   |  |                  |  |
|  | - Session 1       |  | Sessions: XX     |  |
|  | - Session 2       |  | Joueurs: XX      |  |
|  | - Session 3       |  | Actions: XX      |  |
|  |                   |  | Temps: XX h      |  |
|  | [+ Nouvelle]      |  | Tokens: XX       |  |
|  +-------------------+  +------------------+  |
|                                               |
|  +-------------------+  +------------------+  |
|  | Scénarios         |  | Ressources       |  |
|  |                   |  |                  |  |
|  | - Scénario 1      |  | - Cartes         |  |
|  | - Scénario 2      |  | - PNJ            |  |
|  | - Scénario 3      |  | - Monstres       |  |
|  |                   |  | - Objets         |  |
|  | [+ Nouveau]       |  |                  |  |
|  +-------------------+  +------------------+  |
|                                               |
+-----------------------------------------------+
```

### 6.2 Gestion de Scénario

```
+-----------------------------------------------+
| [HEADER + NAVIGATION]                         |
+-----------------------------------------------+
|                                               |
|  Scénario: [Nom]                  [Modifier]  |
|                                               |
|  +-------------------------------------------+  |
|  | Informations                              |  |
|  |                                           |  |
|  | Titre: XXXXX                              |  |
|  | Description: XXXXX                        |  |
|  | Niveau recommandé: X-Y                    |  |
|  |                                           |  |
|  +-------------------------------------------+  |
|                                               |
|  +-------------------------------------------+  |
|  | Scènes                          [+ Ajouter] |  |
|  |                                           |  |
|  | 1. [Titre Scène 1] - [Type] [Éditer]      |  |
|  | 2. [Titre Scène 2] - [Type] [Éditer]      |  |
|  | 3. [Titre Scène 3] - [Type] [Éditer]      |  |
|  |                                           |  |
|  | [Réorganiser]                             |  |
|  +-------------------------------------------+  |
|                                               |
|  +-------------------------------------------+  |
|  | Ressources                      [+ Ajouter] |  |
|  |                                           |  |
|  | - [PNJ 1]                                 |  |
|  | - [Monstre 1]                             |  |
|  | - [Objet 1]                               |  |
|  |                                           |  |
|  +-------------------------------------------+  |
|                                               |
+-----------------------------------------------+
```

### 6.3 Interface MJ en Jeu

```
+-----------------------------------------------+
| [HEADER + NAVIGATION]                         |
+-----------------------------------------------+
|                                               |
|  Session: [Nom]  |  Mode MJ                   |
|                                               |
|  +-------------------------------------------+  |
|  | Contrôles MJ                              |  |
|  |                                           |  |
|  | [Pause] [Reprendre] [Terminer]            |  |
|  | Scène: [Dropdown_____] [Changer]          |  |
|  |                                           |  |
|  +-------------------------------------------+  |
|                                               |
|  +-------------------+  +------------------+  |
|  | Narration         |  | Joueurs          |  |
|  |                   |  |                  |  |
|  | [Voir narration   |  | - Joueur1 (PJ1)  |  |
|  |  actuelle]        |  |   PV: XX/YY      |  |
|  |                   |  |                  |  |
|  | [Ajouter texte    |  | - Joueur2 (PJ2)  |  |
|  |  personnalisé]    |  |   PV: XX/YY      |  |
|  |                   |  |                  |  |
|  | [Override IA]     |  | [Voir détails]   |  |
|  +-------------------+  +------------------+  |
|                                               |
|  +-------------------+  +------------------+  |
|  | Ressources        |  | Événements       |  |
|  |                   |  |                  |  |
|  | [PNJ]             |  | [+ Combat]       |  |
|  | [Monstres]        |  | [+ Rencontre]    |  |
|  | [Objets]          |  | [+ Piège]        |  |
|  | [Cartes]          |  | [+ Récompense]   |  |
|  |                   |  |                  |  |
|  +-------------------+  +------------------+  |
|                                               |
+-----------------------------------------------+
```

## 7. Composants Réutilisables

### 7.1 Carte de Personnage

```
+-------------------------------------------+
| [Image/Avatar]  Nom du Personnage         |
|                                           |
| Classe: XXXX          Niveau: X           |
| PV: XX/YY             CA: ZZ              |
|                                           |
| [Voir Détails]        [Sélectionner]      |
+-------------------------------------------+
```

### 7.2 Carte de Session

```
+-------------------------------------------+
| Nom de la Session                         |
|                                           |
| MJ: XXXX                                  |
| Joueurs: X/Y                              |
| Statut: XXXX                              |
|                                           |
| [Voir Détails]        [Rejoindre]         |
+-------------------------------------------+
```

### 7.3 Formulaire d'Action

```
+-------------------------------------------+
| Type d'Action:                            |
| [Dialogue___________________________]     |
|                                           |
| Description:                              |
| [_______________________________________] |
| [_______________________________________] |
|                                           |
| [Options avancées]                        |
|                                           |
| [Annuler]                    [Soumettre]  |
+-------------------------------------------+
```

### 7.4 Affichage de Narration

```
+-------------------------------------------+
| [Titre de la Scène]                       |
|                                           |
| [Texte de narration généré par l'IA,      |
|  formaté avec des styles pour mettre en   |
|  évidence les éléments importants comme   |
|  les PNJ, objets, etc.]                   |
|                                           |
| [Actions suggérées]                       |
+-------------------------------------------+
```

## 8. Responsive Design

### 8.1 Mobile (Smartphone)

Les interfaces seront adaptées pour les écrans mobiles:

- Navigation simplifiée avec menu hamburger
- Disposition en colonnes uniques
- Éléments redimensionnés pour le toucher
- Fonctionnalités critiques priorisées

### 8.2 Tablette

Les interfaces seront adaptées pour les tablettes:

- Disposition hybride (entre desktop et mobile)
- Certains panneaux côte à côte, d'autres empilés
- Optimisation pour l'interaction tactile

### 8.3 Desktop

Les interfaces seront optimisées pour les grands écrans:

- Utilisation complète de l'espace disponible
- Panneaux multiples visibles simultanément
- Raccourcis clavier pour les actions fréquentes