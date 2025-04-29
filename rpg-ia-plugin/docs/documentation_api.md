# Documentation de l'API du Plugin WordPress RPG-IA

Cette documentation est destinée aux développeurs qui souhaitent étendre ou intégrer le plugin WordPress RPG-IA dans leurs propres projets.

## Table des matières

1. [Introduction](#introduction)
2. [Architecture du plugin](#architecture-du-plugin)
3. [API WordPress](#api-wordpress)
4. [API REST](#api-rest)
5. [Hooks et filtres](#hooks-et-filtres)
6. [Types de données personnalisés](#types-de-données-personnalisés)
7. [Classes principales](#classes-principales)
8. [Intégration avec des thèmes](#intégration-avec-des-thèmes)
9. [Exemples de code](#exemples-de-code)
10. [Bonnes pratiques](#bonnes-pratiques)

## Introduction

Le plugin RPG-IA est conçu avec une architecture modulaire qui permet aux développeurs de l'étendre ou de l'intégrer facilement. Cette documentation couvre les différentes API et points d'extension disponibles.

## Architecture du plugin

### Structure des fichiers

```
rpg-ia-plugin/
├── rpg-ia-plugin.php              # Fichier principal du plugin
├── includes/                      # Classes PHP principales
│   ├── class-rpg-ia-plugin.php    # Classe principale du plugin
│   ├── class-rpg-ia-api-client.php # Client API pour communiquer avec le backend
│   ├── class-rpg-ia-auth-handler.php # Gestion de l'authentification
│   ├── class-rpg-ia-character-manager.php # Gestion des personnages
│   ├── class-rpg-ia-session-manager.php # Gestion des sessions de jeu
│   ├── class-rpg-ia-game-interface.php # Interface de jeu
│   └── widgets/                   # Classes des widgets
├── admin/                         # Fichiers pour l'administration WordPress
│   ├── js/                        # JavaScript pour l'admin
│   ├── css/                       # Styles pour l'admin
│   └── partials/                  # Templates pour l'admin
└── public/                        # Fichiers accessibles publiquement
    ├── js/                        # JavaScript pour le frontend
    ├── css/                       # Styles pour le frontend
    └── partials/                  # Templates pour le frontend
```

### Flux de données

Le plugin suit un flux de données typique :

1. Les requêtes utilisateur sont reçues via l'interface WordPress
2. Le plugin traite ces requêtes et communique avec l'API backend si nécessaire
3. Les données sont récupérées, traitées et affichées à l'utilisateur

## API WordPress

### Constantes

Le plugin définit plusieurs constantes que vous pouvez utiliser :

```php
// Version du plugin
define('RPG_IA_VERSION', '1.0.0');

// Chemin du répertoire du plugin
define('RPG_IA_PATH', plugin_dir_path(__FILE__));

// URL du répertoire du plugin
define('RPG_IA_URL', plugin_dir_url(__FILE__));

// URL de l'API backend
define('RPG_IA_API_URL', get_option('rpg_ia_api_url', 'http://localhost:8000'));
```

### Fonctions globales

Le plugin expose plusieurs fonctions globales :

```php
// Récupère l'instance du gestionnaire d'authentification
function rpg_ia_get_auth_handler() {
    return new RPG_IA_Auth_Handler();
}

// Récupère l'instance du gestionnaire de personnages
function rpg_ia_get_character_manager() {
    return new RPG_IA_Character_Manager();
}

// Récupère l'instance du gestionnaire de sessions
function rpg_ia_get_session_manager() {
    return new RPG_IA_Session_Manager();
}

// Vérifie si l'utilisateur est authentifié
function rpg_ia_is_user_authenticated() {
    $auth_handler = rpg_ia_get_auth_handler();
    return $auth_handler->is_authenticated();
}

// Récupère l'utilisateur courant
function rpg_ia_get_current_user() {
    $auth_handler = rpg_ia_get_auth_handler();
    return $auth_handler->get_current_user();
}
```

## API REST

Le plugin enregistre plusieurs endpoints REST pour permettre l'interaction via AJAX :

### Authentification

```
POST /wp-json/rpg-ia/v1/auth/register
```
Enregistre un nouvel utilisateur.

**Paramètres :**
- `username` (string) : Nom d'utilisateur
- `email` (string) : Adresse e-mail
- `password` (string) : Mot de passe

**Réponse :**
```json
{
  "success": true,
  "data": {
    "user_id": 123,
    "username": "example_user"
  }
}
```

```
POST /wp-json/rpg-ia/v1/auth/login
```
Authentifie un utilisateur.

**Paramètres :**
- `username` (string) : Nom d'utilisateur
- `password` (string) : Mot de passe

**Réponse :**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "user_id": 123,
      "username": "example_user"
    }
  }
}
```

```
POST /wp-json/rpg-ia/v1/auth/logout
```
Déconnecte l'utilisateur courant.

**Réponse :**
```json
{
  "success": true
}
```

### Personnages

```
GET /wp-json/rpg-ia/v1/characters
```
Récupère la liste des personnages de l'utilisateur courant.

**Réponse :**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Aragorn",
      "character_class": "guerrier",
      "level": 3
    },
    {
      "id": 2,
      "name": "Gandalf",
      "character_class": "magicien",
      "level": 5
    }
  ]
}
```

```
GET /wp-json/rpg-ia/v1/characters/{id}
```
Récupère les détails d'un personnage spécifique.

**Réponse :**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Aragorn",
    "character_class": "guerrier",
    "level": 3,
    "strength": 16,
    "intelligence": 12,
    "wisdom": 14,
    "dexterity": 15,
    "constitution": 16,
    "charisma": 14,
    "max_hp": 28,
    "current_hp": 22,
    "armor_class": 16,
    "equipment": ["Épée longue", "Bouclier", "Cotte de mailles"],
    "inventory": ["Potion de soins", "Corde", "Torche"],
    "gold": 150,
    "skills": ["Combat à l'épée", "Tactique militaire"],
    "spells": [],
    "background": "Héritier du trône du Gondor...",
    "appearance": "Grand, cheveux noirs, yeux gris..."
  }
}
```

```
POST /wp-json/rpg-ia/v1/characters
```
Crée un nouveau personnage.

**Paramètres :**
- Voir la structure de données dans la réponse GET /characters/{id}

**Réponse :**
```json
{
  "success": true,
  "data": {
    "id": 3,
    "name": "Legolas",
    "character_class": "elfe",
    "level": 1
  }
}
```

```
PUT /wp-json/rpg-ia/v1/characters/{id}
```
Met à jour un personnage existant.

**Paramètres :**
- Voir la structure de données dans la réponse GET /characters/{id}

**Réponse :**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Aragorn",
    "character_class": "guerrier",
    "level": 4
  }
}
```

```
DELETE /wp-json/rpg-ia/v1/characters/{id}
```
Supprime un personnage.

**Réponse :**
```json
{
  "success": true
}
```

### Sessions

```
GET /wp-json/rpg-ia/v1/sessions
```
Récupère la liste des sessions disponibles.

```
GET /wp-json/rpg-ia/v1/sessions/{id}
```
Récupère les détails d'une session spécifique.

```
POST /wp-json/rpg-ia/v1/sessions
```
Crée une nouvelle session.

```
PUT /wp-json/rpg-ia/v1/sessions/{id}
```
Met à jour une session existante.

```
DELETE /wp-json/rpg-ia/v1/sessions/{id}
```
Supprime une session.

```
POST /wp-json/rpg-ia/v1/sessions/{id}/join
```
Rejoint une session avec un personnage.

```
POST /wp-json/rpg-ia/v1/sessions/{id}/leave
```
Quitte une session.

### Actions

```
POST /wp-json/rpg-ia/v1/actions
```
Soumet une action dans une session.

**Paramètres :**
- `session_id` (int) : ID de la session
- `character_id` (int) : ID du personnage
- `action_type` (string) : Type d'action (dialogue, combat, exploration, etc.)
- `description` (string) : Description de l'action

**Réponse :**
```json
{
  "success": true,
  "data": {
    "action_id": 42,
    "narration": "Aragorn dégaine son épée et charge le groupe d'orcs...",
    "character_updates": {
      "current_hp": 18
    }
  }
}
```

## Hooks et filtres

Le plugin fournit de nombreux hooks et filtres pour permettre l'extension de ses fonctionnalités :

### Actions

```php
// Avant la soumission d'une action
do_action('rpgia_before_action_submit', $character_id, $action_data);

// Après la soumission d'une action
do_action('rpgia_after_action_submit', $character_id, $action_data, $response);

// Avant la création d'un personnage
do_action('rpgia_before_character_create', $character_data);

// Après la création d'un personnage
do_action('rpgia_after_character_create', $character_id, $character_data);

// Avant la mise à jour d'un personnage
do_action('rpgia_before_character_update', $character_id, $character_data);

// Après la mise à jour d'un personnage
do_action('rpgia_after_character_update', $character_id, $character_data);

// Avant la suppression d'un personnage
do_action('rpgia_before_character_delete', $character_id);

// Après la suppression d'un personnage
do_action('rpgia_after_character_delete', $character_id);

// Avant la création d'une session
do_action('rpgia_before_session_create', $session_data);

// Après la création d'une session
do_action('rpgia_after_session_create', $session_id, $session_data);

// Quand un joueur rejoint une session
do_action('rpgia_player_join_session', $session_id, $character_id, $user_id);

// Quand un joueur quitte une session
do_action('rpgia_player_leave_session', $session_id, $character_id, $user_id);

// Quand une session démarre
do_action('rpgia_session_start', $session_id);

// Quand une session se termine
do_action('rpgia_session_end', $session_id);
```

### Filtres

```php
// Filtrer les données d'un personnage avant affichage
$character_data = apply_filters('rpgia_character_display', $character_data, $context);

// Filtrer les données d'une session avant affichage
$session_data = apply_filters('rpgia_session_display', $session_data, $context);

// Filtrer la narration générée par l'IA
$narration = apply_filters('rpgia_narration_display', $narration, $session_id, $action_id);

// Filtrer les types d'actions disponibles
$action_types = apply_filters('rpgia_action_types', $action_types);

// Filtrer les classes de personnages disponibles
$character_classes = apply_filters('rpgia_character_classes', $character_classes);

// Filtrer l'équipement de départ d'une classe
$starting_equipment = apply_filters('rpgia_starting_equipment', $starting_equipment, $character_class);

// Filtrer les compétences d'une classe
$class_skills = apply_filters('rpgia_class_skills', $class_skills, $character_class);

// Filtrer les sorts de départ d'une classe
$starting_spells = apply_filters('rpgia_starting_spells', $starting_spells, $character_class);
```

## Types de données personnalisés

Le plugin définit plusieurs types de données personnalisés dans WordPress :

### Types de post

```php
// Personnages
register_post_type('rpgia_character', array(
    'labels' => array(
        'name' => __('Characters', 'rpg-ia'),
        'singular_name' => __('Character', 'rpg-ia'),
        // ...
    ),
    'public' => true,
    'has_archive' => true,
    'menu_icon' => 'dashicons-superhero',
    'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
    'show_in_rest' => true,
    'rewrite' => array('slug' => 'rpg-ia-character')
));

// Sessions de jeu
register_post_type('rpgia_session', array(
    // ...
));

// Scénarios
register_post_type('rpgia_scenario', array(
    // ...
));

// Logs d'action
register_post_type('rpgia_action_log', array(
    // ...
));
```

### Tables personnalisées

Le plugin crée également des tables personnalisées dans la base de données WordPress :

```sql
-- Métadonnées utilisateur spécifiques au jeu
CREATE TABLE {prefix}_rpgia_user_meta (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    meta_key varchar(255) NOT NULL,
    meta_value longtext,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY meta_key (meta_key)
);

-- Association entre sessions et joueurs
CREATE TABLE {prefix}_rpgia_session_players (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    session_id bigint(20) NOT NULL,
    user_id bigint(20) NOT NULL,
    character_id bigint(20) NOT NULL,
    join_date datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY session_id (session_id),
    KEY user_id (user_id),
    KEY character_id (character_id)
);

-- État de jeu temporaire pour les sessions actives
CREATE TABLE {prefix}_rpgia_game_state (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    session_id bigint(20) NOT NULL,
    state_key varchar(255) NOT NULL,
    state_value longtext,
    last_updated datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY session_id (session_id),
    KEY state_key (state_key)
);
```

## Classes principales

### RPG_IA_Plugin

Classe principale du plugin qui initialise toutes les autres classes et définit les hooks principaux.

```php
class RPG_IA_Plugin {
    protected $loader;
    protected $plugin_name;
    protected $version;
    
    public function __construct() {
        $this->plugin_name = 'rpg-ia';
        $this->version = RPG_IA_VERSION;
        
        $this->load_dependencies();
        $this->set_locale();
        $this->register_post_types();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    // ...
}
```

### RPG_IA_API_Client

Client API pour communiquer avec le backend FastAPI.

```php
class RPG_IA_API_Client {
    private $api_base_url;
    private $token;
    
    public function __construct($api_base_url = null, $token = null) {
        $this->api_base_url = $api_base_url ?: RPG_IA_API_URL;
        $this->token = $token;
    }
    
    public function request($endpoint, $method = 'GET', $data = null) {
        // Implémentation des requêtes HTTP
    }
    
    // Méthodes pour chaque endpoint
    public function login($username, $password) { /* ... */ }
    public function register($username, $email, $password) { /* ... */ }
    public function get_characters() { /* ... */ }
    public function get_character($character_id) { /* ... */ }
    public function create_character($data) { /* ... */ }
    public function update_character($character_id, $data) { /* ... */ }
    public function delete_character($character_id) { /* ... */ }
    // ...
}
```

### RPG_IA_Auth_Handler

Gestion de l'authentification des utilisateurs.

```php
class RPG_IA_Auth_Handler {
    private $api_client;
    
    public function __construct() {
        $this->api_client = new RPG_IA_API_Client();
    }
    
    public function authenticate($username, $password, $refresh_token = false) { /* ... */ }
    public function register($username, $email, $password) { /* ... */ }
    public function logout() { /* ... */ }
    public function get_current_user() { /* ... */ }
    public function is_authenticated() { /* ... */ }
    public function validate_token($token) { /* ... */ }
    public function get_token() { /* ... */ }
    // ...
}
```

### RPG_IA_Character_Manager

Gestion des personnages.

```php
class RPG_IA_Character_Manager {
    private $api_client;
    
    public function __construct($api_client = null) { /* ... */ }
    
    public function get_characters() { /* ... */ }
    public function get_character($character_id) { /* ... */ }
    public function create_character($data) { /* ... */ }
    public function update_character($character_id, $data) { /* ... */ }
    public function delete_character($character_id) { /* ... */ }
    public function generate_random_character($name, $character_class) { /* ... */ }
    public function validate_character_data($data) { /* ... */ }
    // ...
}
```

## Intégration avec des thèmes

### Templates remplaçables

Le plugin utilise un système de templates qui peuvent être remplacés par le thème :

```php
function rpg_ia_get_template_part($slug, $name = null, $args = array()) {
    $template = '';
    
    // Chercher d'abord dans le thème
    if ($name) {
        $template = locate_template(array(
            "rpg-ia/{$slug}-{$name}.php",
            "rpg-ia/{$slug}.php"
        ));
    } else {
        $template = locate_template(array(
            "rpg-ia/{$slug}.php"
        ));
    }
    
    // Si non trouvé dans le thème, utiliser le template par défaut du plugin
    if (!$template) {
        if ($name) {
            $template = RPG_IA_PATH . "public/partials/{$slug}-{$name}.php";
        } else {
            $template = RPG_IA_PATH . "public/partials/{$slug}.php";
        }
    }
    
    // Inclure le template avec les arguments
    if (file_exists($template)) {
        extract($args);
        include $template;
    }
}
```

Pour remplacer un template, créez un fichier correspondant dans votre thème :

```
your-theme/
└── rpg-ia/
    ├── character-list.php
    ├── character-form.php
    ├── session-list.php
    └── game-interface.php
```

### Variables CSS

Le plugin utilise des variables CSS pour faciliter la personnalisation des styles :

```css
:root {
    --rpgia-primary-color: #007bff;
    --rpgia-secondary-color: #6c757d;
    --rpgia-success-color: #28a745;
    --rpgia-danger-color: #dc3545;
    --rpgia-warning-color: #ffc107;
    --rpgia-info-color: #17a2b8;
    --rpgia-light-color: #f8f9fa;
    --rpgia-dark-color: #343a40;
    --rpgia-font-family: 'Roboto', sans-serif;
    --rpgia-border-radius: 4px;
    --rpgia-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
```

Vous pouvez remplacer ces variables dans votre thème :

```css
:root {
    --rpgia-primary-color: #8e44ad;
    --rpgia-secondary-color: #2c3e50;
    --rpgia-font-family: 'Merriweather', serif;
}
```

## Exemples de code

### Créer un personnage programmatiquement

```php
function create_sample_character() {
    $character_manager = new RPG_IA_Character_Manager();
    
    $character_data = array(
        'name' => 'Thorin',
        'character_class' => 'nain',
        'level' => 1,
        'strength' => 16,
        'intelligence' => 10,
        'wisdom' => 12,
        'dexterity' => 12,
        'constitution' => 17,
        'charisma' => 11,
        'max_hp' => 12,
        'current_hp' => 12,
        'armor_class' => 16,
        'equipment' => array('Hache de bataille', 'Cotte de mailles', 'Bouclier'),
        'inventory' => array('Rations (5 jours)', 'Torche (3)', 'Silex et amorce'),
        'gold' => 150,
        'background' => 'Héritier de la montagne solitaire...',
        'appearance' => 'Nain robuste avec une longue barbe noire...'
    );
    
    $result = $character_manager->create_character($character_data);
    
    if (is_wp_error($result)) {
        echo 'Erreur : ' . $result->get_error_message();
    } else {
        echo 'Personnage créé avec l\'ID : ' . $result['id'];
    }
}
```

### Ajouter un nouveau type d'action

```php
// Ajouter un nouveau type d'action
function add_custom_action_type($action_types) {
    $action_types['ritual'] = __('Rituel', 'rpg-ia');
    return $action_types;
}
add_filter('rpgia_action_types', 'add_custom_action_type');

// Traiter le nouveau type d'action
function handle_ritual_action($narration, $session_id, $action_id) {
    $action = get_post_meta($action_id, 'action_data', true);
    
    if ($action['action_type'] === 'ritual') {
        // Ajouter des effets spéciaux pour les rituels
        $narration = '<div class="ritual-effect">' . $narration . '</div>';
        $narration .= '<script>playRitualSound();</script>';
    }
    
    return $narration;
}
add_filter('rpgia_narration_display', 'handle_ritual_action', 10, 3);
```

### Créer un shortcode personnalisé

```php
function rpgia_character_stats_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
        'show_equipment' => 'yes',
        'show_skills' => 'yes'
    ), $atts, 'rpgia_character_stats');
    
    if (empty($atts['id'])) {
        return '<p>' . __('Veuillez spécifier un ID de personnage.', 'rpg-ia') . '</p>';
    }
    
    $character_manager = new RPG_IA_Character_Manager();
    $character = $character_manager->get_character($atts['id']);
    
    if (is_wp_error($character)) {
        return '<p>' . __('Personnage non trouvé.', 'rpg-ia') . '</p>';
    }
    
    ob_start();
    ?>
    <div class="rpgia-character-stats">
        <h3><?php echo esc_html($character['name']); ?></h3>
        <p><?php echo esc_html($character['character_class']); ?> niveau <?php echo esc_html($character['level']); ?></p>
        
        <div class="rpgia-stats-grid">
            <div class="rpgia-stat">
                <span class="rpgia-stat-name">FOR</span>
                <span class="rpgia-stat-value"><?php echo esc_html($character['strength']); ?></span>
            </div>
            <div class="rpgia-stat">
                <span class="rpgia-stat-name">INT</span>
                <span class="rpgia-stat-value"><?php echo esc_html($character['intelligence']); ?></span>
            </div>
            <!-- Autres caractéristiques... -->
        </div>
        
        <?php if ($atts['show_equipment'] === 'yes' && !empty($character['equipment'])): ?>
        <div class="rpgia-equipment">
            <h4><?php _e('Équipement', 'rpg-ia'); ?></h4>
            <ul>
                <?php foreach ($character['equipment'] as $item): ?>
                <li><?php echo esc_html($item); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['show_skills'] === 'yes' && !empty($character['skills'])): ?>
        <div class="rpgia-skills">
            <h4><?php _e('Compétences', 'rpg-ia'); ?></h4>
            <ul>
                <?php foreach ($character['skills'] as $skill): ?>
                <li><?php echo esc_html($skill); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('rpgia_character_stats', 'rpgia_character_stats_shortcode');
```

## Bonnes pratiques

### Sécurité

- Toujours valider et assainir les données utilisateur
- Utiliser les nonces WordPress pour les formulaires
- Vérifier les capacités utilisateur avant chaque action
- Ne jamais faire confiance aux données côté client

```php
// Exemple de validation de nonce
if (!isset($_POST['rpgia_nonce']) || !wp_verify_nonce($_POST['rpgia_nonce'], 'rpgia_action')) {
    wp_die(__('Sécurité : nonce invalide.', 'rpg-ia'));
}

// Exemple de vérification de capacité
if (!current_user_can('edit_post', $character_id)) {
    wp_die(__('Vous n\'avez pas la permission de modifier ce personnage.', 'rpg-ia'));
}

// Exemple d'assainissement de données
$name = sanitize_text_field($_POST['name']);
$description = wp_kses_post($_POST['description']);
```

### Performance

- Utiliser la mise en cache pour les requêtes API fréquentes
- Charger les scripts et styles uniquement lorsque nécessaire
- Optimiser les requêtes de base de données

```php
// Exemple de mise en cache d'une requête API
function get_cached_characters() {
    $cache_key = 'rpgia_user_characters_' . get_current_user_id();
    $characters = wp_cache_get($cache_key);
    
    if (false === $characters) {
        $character_manager = new RPG_IA_Character_Manager();
        $characters = $character_manager->get_characters();
        
        if (!is_wp_error($characters)) {
            wp_cache_set($cache_key, $characters, 'rpg-ia', 300); // Cache pour 5 minutes
        }
    }
    
    return $characters;
}
```

### Internationalisation

- Utiliser les fonctions de traduction pour tous les textes
- Charger le domaine de texte correctement
- Fournir des fichiers POT/PO/MO pour les traductions

```php
// Exemple d'internationalisation
__('Personnage', 'rpg-ia');
_e('Créer un nouveau personnage', 'rpg-ia');
sprintf(__('Bienvenue, %s!', 'rpg-ia'), $username);
```

### Compatibilité avec les thèmes

- Utiliser des classes CSS préfixées pour éviter les conflits
- Fournir des hooks pour permettre la personnalisation
- Tester avec différents thèmes populaires

```php
// Exemple de classes CSS préfixées
<div class="rpgia-container">
    <div class="rpgia-character-card">
        <h3 class="rpgia-character-name"><?php echo esc_html($character['name']); ?></h3>
        <!-- ... -->
    </div>
</div>