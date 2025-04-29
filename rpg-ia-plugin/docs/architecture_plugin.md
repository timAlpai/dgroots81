# Architecture Technique du Plugin WordPress RPG-IA

Ce document décrit l'architecture technique du plugin WordPress pour RPG-IA, qui servira d'interface utilisateur pour le backend FastAPI.

## 1. Vue d'Ensemble

Le plugin WordPress RPG-IA est conçu pour fournir une interface utilisateur conviviale pour le jeu de rôle en ligne avec IA comme maître de jeu. Il s'intègre au backend FastAPI existant via des appels API REST.

### 1.1 Objectifs Techniques

- Créer une interface utilisateur intuitive et immersive
- Assurer une communication fluide avec le backend FastAPI
- S'intégrer harmonieusement avec WordPress
- Offrir une expérience responsive sur tous les appareils
- Maintenir de bonnes performances même avec des sessions de jeu complexes

### 1.2 Technologies Principales

- PHP 8.0+ (pour la compatibilité avec WordPress)
- JavaScript (ES6+)
- AJAX pour les appels API asynchrones
- WordPress REST API pour l'intégration
- JWT pour l'authentification
- CSS3/SASS pour les styles
- Bibliothèques JS: React (optionnel pour les composants complexes)

## 2. Structure du Plugin

### 2.1 Organisation des Fichiers

```
rpg-ia-plugin/
├── rpg-ia-plugin.php              # Fichier principal du plugin
├── includes/                      # Classes PHP principales
│   ├── class-rpg-ia-plugin.php    # Classe principale du plugin
│   ├── class-api-client.php       # Client API pour communiquer avec le backend
│   ├── class-auth-handler.php     # Gestion de l'authentification
│   ├── class-character-manager.php # Gestion des personnages
│   ├── class-session-manager.php  # Gestion des sessions de jeu
│   ├── class-game-interface.php   # Interface de jeu
│   └── class-admin.php            # Interface d'administration
├── admin/                         # Fichiers pour l'administration WordPress
│   ├── js/                        # JavaScript pour l'admin
│   ├── css/                       # Styles pour l'admin
│   └── partials/                  # Templates pour l'admin
├── public/                        # Fichiers accessibles publiquement
│   ├── js/                        # JavaScript pour le frontend
│   │   ├── rpg-ia-auth.js         # Gestion de l'authentification
│   │   ├── rpg-ia-character.js    # Gestion des personnages
│   │   ├── rpg-ia-session.js      # Gestion des sessions
│   │   └── rpg-ia-game.js         # Interface de jeu
│   ├── css/                       # Styles pour le frontend
│   └── partials/                  # Templates pour le frontend
├── templates/                     # Templates pour les pages personnalisées
│   ├── dashboard.php              # Tableau de bord
│   ├── character-create.php       # Création de personnage
│   ├── character-list.php         # Liste des personnages
│   ├── session-create.php         # Création de session
│   ├── session-list.php           # Liste des sessions
│   └── game-interface.php         # Interface de jeu
└── languages/                     # Fichiers de traduction
```

### 2.2 Types de Données Personnalisés

Le plugin définira plusieurs types de données personnalisés dans WordPress:

- **rpgia_character**: Type de post personnalisé pour les personnages
- **rpgia_session**: Type de post personnalisé pour les sessions de jeu
- **rpgia_scenario**: Type de post personnalisé pour les scénarios
- **rpgia_action_log**: Type de post personnalisé pour les logs d'action

### 2.3 Tables Personnalisées

En plus des types de post, le plugin créera des tables personnalisées dans la base de données WordPress:

- **{prefix}_rpgia_user_meta**: Métadonnées utilisateur spécifiques au jeu
- **{prefix}_rpgia_session_players**: Association entre sessions et joueurs
- **{prefix}_rpgia_game_state**: État de jeu temporaire pour les sessions actives

## 3. Intégration avec WordPress

### 3.1 Hooks et Filtres

Le plugin utilisera les hooks WordPress pour s'intégrer au système:

- **Hooks d'activation/désactivation**: Pour créer/supprimer les tables et réglages
- **Hooks d'initialisation**: Pour enregistrer les types de post, taxonomies, etc.
- **Hooks d'admin**: Pour ajouter les menus d'administration
- **Hooks de contenu**: Pour modifier l'affichage des contenus liés au jeu

### 3.2 Shortcodes

Le plugin fournira plusieurs shortcodes pour intégrer des éléments du jeu dans les pages WordPress:

- `[rpgia_character id=X]`: Affiche les informations d'un personnage
- `[rpgia_session id=Y]`: Affiche les informations d'une session
- `[rpgia_dashboard]`: Affiche le tableau de bord du joueur
- `[rpgia_game_interface session=Z]`: Affiche l'interface de jeu pour une session

### 3.3 Widgets

Des widgets seront disponibles pour les sidebars:

- **RPG-IA Sessions**: Affiche les sessions actives
- **RPG-IA Characters**: Affiche les personnages de l'utilisateur
- **RPG-IA Stats**: Affiche les statistiques de jeu

### 3.4 Pages Personnalisées

Le plugin créera automatiquement plusieurs pages personnalisées:

- **/rpg-ia/dashboard**: Tableau de bord principal
- **/rpg-ia/characters**: Gestion des personnages
- **/rpg-ia/sessions**: Gestion des sessions
- **/rpg-ia/play/{session_id}**: Interface de jeu

## 4. Communication avec le Backend

### 4.1 Client API

Une classe `API_Client` gèrera toutes les communications avec le backend FastAPI:

```php
class RPG_IA_API_Client {
    private $api_base_url;
    private $token;
    
    public function __construct($api_base_url, $token = null) {
        $this->api_base_url = $api_base_url;
        $this->token = $token;
    }
    
    public function request($endpoint, $method = 'GET', $data = null) {
        // Implémentation des requêtes HTTP avec gestion des tokens
    }
    
    // Méthodes spécifiques pour chaque endpoint
    public function login($username, $password) { ... }
    public function getCharacters() { ... }
    public function createCharacter($data) { ... }
    // etc.
}
```

### 4.2 Authentification

L'authentification sera gérée via JWT:

1. L'utilisateur se connecte via le formulaire WordPress
2. Le plugin appelle l'API backend pour obtenir un token JWT
3. Le token est stocké dans un cookie sécurisé ou localStorage
4. Le token est inclus dans tous les appels API ultérieurs
5. Un mécanisme de rafraîchissement automatique est implémenté

### 4.3 Mise en Cache

Pour optimiser les performances:

- Les réponses API fréquemment utilisées seront mises en cache
- Le cache sera invalidé lors des modifications
- Les options de mise en cache seront configurables

## 5. Interface de Jeu

### 5.1 Architecture Frontend

L'interface de jeu sera construite avec une architecture modulaire:

- **Module de Narration**: Affiche le texte généré par l'IA
- **Module d'Action**: Permet de soumettre des actions
- **Module de Personnage**: Affiche et gère les informations du personnage
- **Module de Session**: Affiche les informations de la session

### 5.2 Communication en Temps Réel

Pour une expérience plus interactive:

- Polling AJAX pour les mises à jour régulières
- Option d'intégration WebSocket pour les mises à jour en temps réel (si le backend le supporte)

### 5.3 Gestion d'État

L'état du jeu sera géré côté client:

- État local pour les interactions immédiates
- Synchronisation régulière avec le backend
- Gestion des conflits en cas de désynchronisation

## 6. Sécurité

### 6.1 Authentification et Autorisation

- Utilisation de nonces WordPress pour les formulaires
- Vérification des capacités utilisateur pour chaque action
- Validation des données côté serveur

### 6.2 Protection des Données

- Sanitisation de toutes les entrées utilisateur
- Échappement des sorties
- Préparation des requêtes SQL

### 6.3 Sécurité API

- Validation du token JWT avant chaque requête
- Gestion sécurisée des secrets
- Limitation de débit pour éviter les abus

## 7. Performance

### 7.1 Optimisation des Requêtes

- Regroupement des requêtes API lorsque possible
- Mise en cache des réponses
- Chargement asynchrone des données non critiques

### 7.2 Optimisation Frontend

- Minification des ressources JS/CSS
- Chargement différé des composants
- Utilisation de sprites CSS pour les icônes

### 7.3 Mise à l'Échelle

- Support de la mise en cache objet (Object Cache)
- Compatibilité avec les plugins de cache WordPress
- Optimisation pour les environnements à forte charge

## 8. Extensibilité

### 8.1 API du Plugin

Le plugin fournira sa propre API pour permettre l'extension:

```php
// Exemple d'API du plugin
do_action('rpgia_before_action_submit', $character_id, $action_data);
$custom_data = apply_filters('rpgia_character_display', $character_data, $context);
```

### 8.2 Thèmes Personnalisés

Le plugin supportera la personnalisation via des thèmes:

- Templates remplaçables
- Hooks pour modifier l'affichage
- Variables CSS pour les styles

### 8.3 Modules Additionnels

Architecture modulaire permettant d'ajouter des fonctionnalités:

- Système de modules/extensions
- API documentée pour les développeurs tiers
- Exemples de modules (dés virtuels, générateur de personnage, etc.)

## 9. Déploiement et Maintenance

### 9.1 Installation

- Script d'activation pour créer les tables et réglages
- Vérification des dépendances (version PHP, WordPress)
- Guide d'installation étape par étape

### 9.2 Mises à Jour

- Migrations de base de données automatiques
- Préservation des réglages utilisateur
- Notifications de mise à jour

### 9.3 Diagnostics

- Journal d'erreurs dédié
- Outils de diagnostic intégrés
- Mode debug pour le développement