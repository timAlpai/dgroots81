<?php
/**
 * Fonctionnalités publiques du plugin
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/public
 */

/**
 * Fonctionnalités publiques du plugin.
 *
 * Définit le nom du plugin, la version et les hooks publics.
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/public
 * @author     RPG-IA Team
 */
class RPG_IA_Public {

    /**
     * L'identifiant unique de ce plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    L'identifiant unique de ce plugin.
     */
    private $plugin_name;

    /**
     * La version actuelle du plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    La version actuelle du plugin.
     */
    private $version;

    /**
     * Initialise la classe et définit ses propriétés.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       L'identifiant unique de ce plugin.
     * @param    string    $version           La version du plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Enregistre les styles pour la partie publique.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/rpg-ia-public.css', array(), $this->version, 'all');
    }

    /**
     * Enregistre les scripts pour la partie publique.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'rpg-ia-public',
            plugin_dir_url(__FILE__) . 'js/rpg-ia-public.js',
            array('jquery'),
            $this->version,
            false
        );
    
        wp_enqueue_script(
            'rpg-ia-character-script',
            plugin_dir_url(__FILE__) . 'js/rpg-ia-character.js',
            array('rpg-ia-public'),
            $this->version,
            false
        );
    
        $localized_data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url(),
            'nonce' => wp_create_nonce('rpg_ia_public_nonce'),
            'api_url' => RPG_IA_API_URL,
            'update_interval' => get_option('rpg_ia_update_interval', 5) * 1000,
            'enable_chat' => get_option('rpg_ia_enable_chat', 'yes'),
            'dashboard_url' => get_permalink(get_option('rpg_ia_page_rpg-ia-dashboard')),
            'login_url' => home_url('/'),
            'profile_url' => get_permalink(get_option('rpg_ia_page_rpg-ia-profile')),
            'characters_url' => get_permalink(get_option('rpg_ia_page_rpg-ia-characters')),
            'sessions_url' => get_permalink(get_option('rpg_ia_page_rpg-ia-sessions')),
            'play_url' => get_permalink(get_option('rpg_ia_page_rpg-ia-play')),
            'messages' => array(
                'enter_credentials' => __('Please enter your username and password.', 'rpg-ia'),
                'enter_all_fields' => __('Please fill in all fields.', 'rpg-ia'),
                'passwords_not_match' => __('Passwords do not match.', 'rpg-ia'),
                'logging_in' => __('Logging in...', 'rpg-ia'),
                'login_error' => __('Login failed', 'rpg-ia'),
                'registering' => __('Registering...', 'rpg-ia'),
                'register_success' => __('Registration successful. You can now login.', 'rpg-ia'),
                'register_error' => __('Registration failed', 'rpg-ia'),
                'refreshing_token' => __('Refreshing token...', 'rpg-ia'),
                'token_refreshed' => __('Token refreshed successfully.', 'rpg-ia'),
                'token_refresh_error' => __('Failed to refresh token', 'rpg-ia'),
                'loading_characters' => __('Loading characters...', 'rpg-ia'),
                'no_characters' => __('You have no characters yet.', 'rpg-ia'),
                'error_loading_characters' => __('Error loading characters.', 'rpg-ia'),
                'loading_sessions' => __('Loading sessions...', 'rpg-ia'),
                'no_sessions' => __('No sessions found.', 'rpg-ia'),
                'error_loading_sessions' => __('Error loading sessions.', 'rpg-ia'),
                'loading_session' => __('Loading session...', 'rpg-ia'),
                'error_loading_session' => __('Error loading session.', 'rpg-ia'),
                'no_session_specified' => __('No session specified.', 'rpg-ia'),
                'no_session_loaded' => __('No session loaded.', 'rpg-ia'),
                'no_character_selected' => __('No character selected.', 'rpg-ia'),
                'enter_action_description' => __('Please enter an action description.', 'rpg-ia'),
                'processing_action' => __('Processing action...', 'rpg-ia'),
                'action_error' => __('Error processing action', 'rpg-ia'),
                'select_character' => __('Select a character', 'rpg-ia')
            )
        );
    
        wp_localize_script('rpg-ia-public', 'rpg_ia_public', $localized_data);
        wp_localize_script('rpg-ia-character-script', 'rpg_ia_public', $localized_data);
    }
    
    /**
     * Enregistre les shortcodes.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        // Shortcode pour le tableau de bord
        add_shortcode('rpgia_dashboard', array($this, 'shortcode_dashboard'));
        
        // Shortcode pour la liste des personnages
        add_shortcode('rpgia_characters', array($this, 'shortcode_characters'));
        
        // Shortcode pour la liste des sessions
        add_shortcode('rpgia_sessions', array($this, 'shortcode_sessions'));
        
        // Shortcode pour l'interface de jeu
        add_shortcode('rpgia_game_interface', array($this, 'shortcode_game_interface'));
        
        // Shortcode pour afficher un personnage
        add_shortcode('rpgia_character', array($this, 'shortcode_character'));
        
        // Shortcode pour afficher une session
        add_shortcode('rpgia_session', array($this, 'shortcode_session'));
        
        // Shortcode pour le profil utilisateur
        add_shortcode('rpgia_profile', array($this, 'shortcode_profile'));
    }

    /**
     * Enregistre les widgets.
     *
     * @since    1.0.0
     */
    public function register_widgets() {
        // Widget pour les sessions actives
        register_widget('RPG_IA_Sessions_Widget');
        
        // Widget pour les personnages de l'utilisateur
        register_widget('RPG_IA_Characters_Widget');
        
        // Widget pour les statistiques de jeu
        register_widget('RPG_IA_Stats_Widget');
    }

    /**
     * Enregistre les routes de l'API REST.
     *
     * @since    1.0.0
     */
    public function register_rest_routes() {
        // Enregistrer les routes de l'API REST
        register_rest_route('rpg-ia/v1', '/auth/token', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_auth_token'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('rpg-ia/v1', '/auth/register', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_auth_register'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('rpg-ia/v1', '/auth/me', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_auth_me'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
        
        register_rest_route('rpg-ia/v1', '/auth/refresh', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_auth_refresh'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
        
        // Nouvelles routes pour la gestion des tentatives de connexion
        register_rest_route('rpg-ia/v1', '/auth/increment-attempts', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_increment_login_attempts'),
            'permission_callback' => function() {
                return is_user_logged_in();
            }
        ));
        
        register_rest_route('rpg-ia/v1', '/auth/reset-attempts', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_reset_login_attempts'),
            'permission_callback' => function() {
                return is_user_logged_in();
            }
        ));
        
        register_rest_route('rpg-ia/v1', '/characters', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_characters'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
        
        register_rest_route('rpg-ia/v1', '/characters', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_create_character'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
        
        register_rest_route('rpg-ia/v1', '/characters/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_character'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
        
        register_rest_route('rpg-ia/v1', '/characters/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_character'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
        
        register_rest_route('rpg-ia/v1', '/characters/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_character'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
        
        register_rest_route('rpg-ia/v1', '/sessions', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_sessions'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
        
        register_rest_route('rpg-ia/v1', '/sessions', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_create_session'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
        
        register_rest_route('rpg-ia/v1', '/sessions/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_session'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
        
        register_rest_route('rpg-ia/v1', '/sessions/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'rest_update_session'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
        
        register_rest_route('rpg-ia/v1', '/sessions/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'rest_delete_session'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
        
        register_rest_route('rpg-ia/v1', '/actions', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_create_action'),
            'permission_callback' => array($this, 'rest_auth_permission')
        ));
    }

    /**
     * Vérifie l'authentification pour les routes de l'API REST.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   bool                           True si l'utilisateur est authentifié, false sinon.
     */
    public function rest_auth_permission($request) {
        $auth_handler = new RPG_IA_Auth_Handler();
        return $auth_handler->validate_token($request->get_header('Authorization'));
    }

    /**
     * Shortcode pour le tableau de bord.
     *
     * @since    1.0.0
     * @param    array    $atts    Les attributs du shortcode.
     * @return   string            Le contenu HTML du shortcode.
     */
    public function shortcode_dashboard($atts) {
        // Vérifier si l'utilisateur est connecté à WordPress
        if (!is_user_logged_in()) {
            return $this->get_login_form();
        }
        
        // Vérifier si l'utilisateur est authentifié auprès de l'API
        $auth_handler = new RPG_IA_Auth_Handler();
        if (!$auth_handler->is_api_authenticated()) {
            // Afficher le formulaire de connexion API
            ob_start();
            include plugin_dir_path(__FILE__) . 'partials/rpg-ia-api-login-form.php';
            return ob_get_clean();
        }
        
        // Inclure le template du tableau de bord
        ob_start();
        include plugin_dir_path(__FILE__) . 'partials/rpg-ia-dashboard.php';
        return ob_get_clean();
    }

    /**
     * Shortcode pour la liste des personnages.
     *
     * @since    1.0.0
     * @param    array    $atts    Les attributs du shortcode.
     * @return   string            Le contenu HTML du shortcode.
     */
    public function shortcode_characters($atts) {
        // Vérifier si l'utilisateur est connecté à WordPress
        if (!is_user_logged_in()) {
            return $this->get_login_form();
        }
        
        // Vérifier si l'utilisateur est authentifié auprès de l'API
        $auth_handler = new RPG_IA_Auth_Handler();
        if (!$auth_handler->is_api_authenticated()) {
            // Afficher le formulaire de connexion API
            ob_start();
            include plugin_dir_path(__FILE__) . 'partials/rpg-ia-api-login-form.php';
            return ob_get_clean();
        }
        
        // Inclure le template de la liste des personnages
        ob_start();
        include plugin_dir_path(__FILE__) . 'partials/rpg-ia-character-list.php';
        return ob_get_clean();
    }

    /**
     * Shortcode pour la liste des sessions.
     *
     * @since    1.0.0
     * @param    array    $atts    Les attributs du shortcode.
     * @return   string            Le contenu HTML du shortcode.
     */
    public function shortcode_sessions($atts) {
        // Vérifier si l'utilisateur est connecté à WordPress
        if (!is_user_logged_in()) {
            return $this->get_login_form();
        }
        
        // Vérifier si l'utilisateur est authentifié auprès de l'API
        $auth_handler = new RPG_IA_Auth_Handler();
        if (!$auth_handler->is_api_authenticated()) {
            // Afficher le formulaire de connexion API
            ob_start();
            include plugin_dir_path(__FILE__) . 'partials/rpg-ia-api-login-form.php';
            return ob_get_clean();
        }
        
        // Inclure le template de la liste des sessions
        ob_start();
        include plugin_dir_path(__FILE__) . 'partials/rpg-ia-session-list.php';
        return ob_get_clean();
    }

    /**
     * Shortcode pour l'interface de jeu.
     *
     * @since    1.0.0
     * @param    array    $atts    Les attributs du shortcode.
     * @return   string            Le contenu HTML du shortcode.
     */
    public function shortcode_game_interface($atts) {
        // Vérifier si l'utilisateur est connecté à WordPress
        if (!is_user_logged_in()) {
            return $this->get_login_form();
        }
        
        // Vérifier si l'utilisateur est authentifié auprès de l'API
        $auth_handler = new RPG_IA_Auth_Handler();
        if (!$auth_handler->is_api_authenticated()) {
            // Afficher le formulaire de connexion API
            ob_start();
            include plugin_dir_path(__FILE__) . 'partials/rpg-ia-api-login-form.php';
            return ob_get_clean();
        }
        
        // Extraire les attributs
        $atts = shortcode_atts(array(
            'session' => 0
        ), $atts, 'rpgia_game_interface');
        
        // Récupérer l'ID de session depuis l'URL si non spécifié
        if (empty($atts['session'])) {
            $session_id = get_query_var('session_id', 0);
        } else {
            $session_id = $atts['session'];
        }
        
        // Vérifier si une session est spécifiée
        if (empty($session_id)) {
            return '<p>' . __('No session specified.', 'rpg-ia') . '</p>';
        }
        
        // Inclure le template de l'interface de jeu
        ob_start();
        include plugin_dir_path(__FILE__) . 'partials/rpg-ia-game-interface.php';
        return ob_get_clean();
    }

    /**
     * Shortcode pour afficher un personnage.
     *
     * @since    1.0.0
     * @param    array    $atts    Les attributs du shortcode.
     * @return   string            Le contenu HTML du shortcode.
     */
    public function shortcode_character($atts) {
        // Extraire les attributs
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts, 'rpgia_character');
        
        // Vérifier si un ID est spécifié
        if (empty($atts['id'])) {
            return '<p>' . __('No character ID specified.', 'rpg-ia') . '</p>';
        }
        
        // Récupérer le personnage
        $character_id = $atts['id'];
        $character = get_post($character_id);
        
        // Vérifier si le personnage existe
        if (!$character || $character->post_type !== 'rpgia_character') {
            return '<p>' . __('Character not found.', 'rpg-ia') . '</p>';
        }
        
        // Inclure le template du personnage
        ob_start();
        include plugin_dir_path(__FILE__) . 'partials/rpg-ia-character-display.php';
        return ob_get_clean();
    }

    /**
     * Shortcode pour afficher une session.
     *
     * @since    1.0.0
     * @param    array    $atts    Les attributs du shortcode.
     * @return   string            Le contenu HTML du shortcode.
     */
    public function shortcode_session($atts) {
        // Extraire les attributs
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts, 'rpgia_session');
        
        // Vérifier si un ID est spécifié
        if (empty($atts['id'])) {
            return '<p>' . __('No session ID specified.', 'rpg-ia') . '</p>';
        }
        
        // Récupérer la session
        $session_id = $atts['id'];
        $session = get_post($session_id);
        
        // Vérifier si la session existe
        if (!$session || $session->post_type !== 'rpgia_session') {
            return '<p>' . __('Session not found.', 'rpg-ia') . '</p>';
        }
        
        // Inclure le template de la session
        ob_start();
        include plugin_dir_path(__FILE__) . 'partials/rpg-ia-session-display.php';
        return ob_get_clean();
    }

    /**
     * Récupère le formulaire de connexion WordPress.
     *
     * @since    1.0.0
     * @return   string    Le formulaire de connexion WordPress HTML.
     */
    private function get_login_form() {
        ob_start();
        include plugin_dir_path(__FILE__) . 'partials/rpg-ia-login-form.php';
        return ob_get_clean();
    }
    /**
     * Gère la requête REST pour obtenir un token JWT.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_auth_token($request) {
        // Récupérer les paramètres de la requête
        $username = $request->get_param('username');
        $password = $request->get_param('password');
        
        // Vérifier les paramètres
        if (empty($username) || empty($password)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Username and password are required.', 'rpg-ia')
            ), 400);
        }
        
        // Authentifier l'utilisateur
        $auth_handler = new RPG_IA_Auth_Handler();
        $response = $auth_handler->authenticate($username, $password);
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 401);
        }
        
        return new WP_REST_Response($response, 200);
    }
    
    /**
     * Gère la requête REST pour enregistrer un nouvel utilisateur.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_auth_register($request) {
        // Récupérer les paramètres de la requête
        $username = $request->get_param('username');
        $email = $request->get_param('email');
        $password = $request->get_param('password');
        
        // Vérifier les paramètres
        if (empty($username) || empty($email) || empty($password)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Username, email and password are required.', 'rpg-ia')
            ), 400);
        }

        // Vérifier que l'utilisateur WordPress est connecté
        $wp_user_id = get_current_user_id();
        if ($wp_user_id <= 0) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Vous devez être connecté à WordPress pour créer un compte API.', 'rpg-ia')
            ), 401);
        }

        // Enregistrer l'utilisateur via l'API
        $auth_handler = new RPG_IA_Auth_Handler();
        $response = $auth_handler->register($username, $email, $password);

        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 400);
        }

        // Associer le compte API à l'utilisateur WordPress
        $meta_result = update_user_meta($wp_user_id, 'rpg_ia_api_username', sanitize_text_field($username));
        if (!$meta_result) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Impossible d\'associer le compte API à l\'utilisateur WordPress.', 'rpg-ia')
            ), 500);
        }

        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Registration successful. You can now login.', 'rpg-ia'),
            'user' => $response
        ), 201);
    
    }
    /**
     * Gère la requête REST pour récupérer les informations de l'utilisateur courant.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_auth_me($request) {
        // Récupérer l'utilisateur courant
        $auth_handler = new RPG_IA_Auth_Handler();
        $response = $auth_handler->get_current_user();
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 401);
        }
        
        return new WP_REST_Response($response, 200);
    }
    
    /**
     * Shortcode pour le profil utilisateur.
     *
     * @since    1.0.0
     * @param    array    $atts    Les attributs du shortcode.
     * @return   string            Le contenu HTML du shortcode.
     */
    public function shortcode_profile($atts) {
        // Vérifier si l'utilisateur est connecté à WordPress
        if (!is_user_logged_in()) {
            return $this->get_login_form();
        }
        
        // Vérifier si l'utilisateur est authentifié auprès de l'API
        $auth_handler = new RPG_IA_Auth_Handler();
        if (!$auth_handler->is_api_authenticated()) {
            // Afficher le formulaire de connexion API
            ob_start();
            include plugin_dir_path(__FILE__) . 'partials/rpg-ia-api-login-form.php';
            return ob_get_clean();
        }
        
        // Inclure le template du profil utilisateur
        ob_start();
        include plugin_dir_path(__FILE__) . 'partials/rpg-ia-profile.php';
        return ob_get_clean();
    }
    
    /**
     * Gère la requête REST pour rafraîchir le token JWT.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_auth_refresh($request) {
        // Récupérer le token depuis l'en-tête Authorization
        $token = $request->get_header('Authorization');
        
        // Vérifier si le token est présent
        if (empty($token)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('No token provided.', 'rpg-ia')
            ), 401);
        }
        
        // Extraire le token du header Authorization
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        // Vérifier si le token est valide
        $auth_handler = new RPG_IA_Auth_Handler();
        if (!$auth_handler->validate_token($token)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Invalid or expired token.', 'rpg-ia')
            ), 401);
        }
        
        // Récupérer l'utilisateur associé au token
        $api_client = new RPG_IA_API_Client(null, $token);
        $user_data = $api_client->get_current_user();
        
        if (is_wp_error($user_data)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $user_data->get_error_message()
            ), 401);
        }
        
        // Générer un nouveau token
        $username = $user_data['username'];
        $user = get_user_by('login', $username);
        
        if (!$user) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('User not found.', 'rpg-ia')
            ), 404);
        }
        
        // Authentifier l'utilisateur pour obtenir un nouveau token
        $auth_handler = new RPG_IA_Auth_Handler();
        $response = $auth_handler->authenticate($username, null, true);
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 401);
        }
        
        return new WP_REST_Response($response, 200);
    }
    /**
     * Gère la requête REST pour récupérer la liste des personnages.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_get_characters($request) {
        // Récupérer les personnages
        $character_manager = new RPG_IA_Character_Manager();
        $response = $character_manager->get_characters();
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'characters' => $response
        ), 200);
    }
    
    /**
     * Gère la requête REST pour récupérer un personnage spécifique.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_get_character($request) {
        // Récupérer l'ID du personnage
        $character_id = $request->get_param('id');
        
        // Vérifier si l'ID est valide
        if (empty($character_id)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Character ID is required.', 'rpg-ia')
            ), 400);
        }
        
        // Récupérer le personnage
        $character_manager = new RPG_IA_Character_Manager();
        $response = $character_manager->get_character($character_id);
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'character' => $response
        ), 200);
    }
    
    /**
     * Gère la requête REST pour créer un nouveau personnage.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_create_character($request) {
        // Récupérer les données du personnage
        $data = $request->get_json_params();
        
        // Vérifier si les données sont valides
        if (empty($data)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('No character data provided.', 'rpg-ia')
            ), 400);
        }
        
        // Valider les données du personnage
        $character_manager = new RPG_IA_Character_Manager();
        $validation = $character_manager->validate_character_data($data);
        
        if (is_wp_error($validation)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $validation->get_error_message()
            ), 400);
        }
        
        // Créer le personnage
        $response = $character_manager->create_character($data);
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Character created successfully.', 'rpg-ia'),
            'character' => $response
        ), 201);
    }
    
    /**
     * Gère la requête REST pour mettre à jour un personnage existant.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_update_character($request) {
        // Récupérer l'ID du personnage
        $character_id = $request->get_param('id');
        
        // Vérifier si l'ID est valide
        if (empty($character_id)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Character ID is required.', 'rpg-ia')
            ), 400);
        }
        
        // Récupérer les données du personnage
        $data = $request->get_json_params();
        
        // Vérifier si les données sont valides
        if (empty($data)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('No character data provided.', 'rpg-ia')
            ), 400);
        }
        
        // Valider les données du personnage
        $character_manager = new RPG_IA_Character_Manager();
        $validation = $character_manager->validate_character_data($data);
        
        if (is_wp_error($validation)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $validation->get_error_message()
            ), 400);
        }
        
        // Mettre à jour le personnage
        $response = $character_manager->update_character($character_id, $data);
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Character updated successfully.', 'rpg-ia'),
            'character' => $response
        ), 200);
    }
    
    /**
     * Gère la requête REST pour supprimer un personnage.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_delete_character($request) {
        // Récupérer l'ID du personnage
        $character_id = $request->get_param('id');
        
        // Vérifier si l'ID est valide
        if (empty($character_id)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Character ID is required.', 'rpg-ia')
            ), 400);
        }
        
        // Supprimer le personnage
        $character_manager = new RPG_IA_Character_Manager();
        $response = $character_manager->delete_character($character_id);
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Character deleted successfully.', 'rpg-ia')
        ), 200);
    }
    
    /**
     * Gère la requête REST pour récupérer la liste des sessions.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_get_sessions($request) {
        // Récupérer les sessions
        $session_manager = new RPG_IA_Session_Manager();
        $response = $session_manager->get_sessions();
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'sessions' => $response
        ), 200);
    }
    
    /**
     * Gère la requête REST pour récupérer une session spécifique.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_get_session($request) {
        // Récupérer l'ID de la session
        $session_id = $request->get_param('id');
        
        // Vérifier si l'ID est valide
        if (empty($session_id)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Session ID is required.', 'rpg-ia')
            ), 400);
        }
        
        // Récupérer la session
        $session_manager = new RPG_IA_Session_Manager();
        $response = $session_manager->get_session($session_id);
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'session' => $response
        ), 200);
    }
    
    /**
     * Gère la requête REST pour créer une nouvelle session.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_create_session($request) {
        // Récupérer les données de la session
        $data = $request->get_json_params();
        
        // Vérifier si les données sont valides
        if (empty($data)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('No session data provided.', 'rpg-ia')
            ), 400);
        }
        
        // Valider les données de la session
        $session_manager = new RPG_IA_Session_Manager();
        $validation = $session_manager->validate_session_data($data);
        
        if (is_wp_error($validation)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $validation->get_error_message()
            ), 400);
        }
        
        // Créer la session
        $response = $session_manager->create_session($data);
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Session created successfully.', 'rpg-ia'),
            'session' => $response
        ), 201);
    }
    
    /**
     * Gère la requête REST pour mettre à jour une session existante.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_update_session($request) {
        // Récupérer l'ID de la session
        $session_id = $request->get_param('id');
        
        // Vérifier si l'ID est valide
        if (empty($session_id)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Session ID is required.', 'rpg-ia')
            ), 400);
        }
        
        // Récupérer les données de la session
        $data = $request->get_json_params();
        
        // Vérifier si les données sont valides
        if (empty($data)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('No session data provided.', 'rpg-ia')
            ), 400);
        }
        
        // Valider les données de la session
        $session_manager = new RPG_IA_Session_Manager();
        $validation = $session_manager->validate_session_data($data);
        
        if (is_wp_error($validation)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $validation->get_error_message()
            ), 400);
        }
        
        // Mettre à jour la session
        $response = $session_manager->update_session($session_id, $data);
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Session updated successfully.', 'rpg-ia'),
            'session' => $response
        ), 200);
    }
    
    /**
     * Gère la requête REST pour supprimer une session.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_delete_session($request) {
        // Récupérer l'ID de la session
        $session_id = $request->get_param('id');
        
        // Vérifier si l'ID est valide
        if (empty($session_id)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Session ID is required.', 'rpg-ia')
            ), 400);
        }
        
        // Supprimer la session
        $session_manager = new RPG_IA_Session_Manager();
        $response = $session_manager->delete_session($session_id);
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Session deleted successfully.', 'rpg-ia')
        ), 200);
    }
    
    /**
     * Incrémente le compteur de tentatives de connexion.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_increment_login_attempts($request) {
        $user_id = get_current_user_id();
        $attempts = get_user_meta($user_id, 'rpg_ia_login_attempts', true) ?: 0;
        $attempts++;
        
        update_user_meta($user_id, 'rpg_ia_login_attempts', $attempts);
        
        // Si le nombre maximal de tentatives est atteint, verrouiller le compte
        if ($attempts >= 5) {
            update_user_meta($user_id, 'rpg_ia_lockout_time', time());
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'attempts' => $attempts
        ), 200);
    }

    /**
     * Réinitialise le compteur de tentatives de connexion.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_reset_login_attempts($request) {
        $user_id = get_current_user_id();
        
        update_user_meta($user_id, 'rpg_ia_login_attempts', 0);
        update_user_meta($user_id, 'rpg_ia_lockout_time', 0);
        
        return new WP_REST_Response(array(
            'success' => true
        ), 200);
    }
    
    /**
     * Gère la requête REST pour créer une action.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    La requête REST.
     * @return   WP_REST_Response               La réponse REST.
     */
    public function rest_create_action($request) {
        // Récupérer les données de l'action
        $data = $request->get_json_params();
        
        // Vérifier si les données sont valides
        if (empty($data)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('No action data provided.', 'rpg-ia')
            ), 400);
        }
        
        // Vérifier les champs obligatoires
        if (empty($data['session_id']) || empty($data['character_id']) || empty($data['description'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Session ID, character ID and description are required.', 'rpg-ia')
            ), 400);
        }
        
        // Créer l'action
        $session_manager = new RPG_IA_Session_Manager();
        $response = $session_manager->create_action($data);
        
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message()
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Action created successfully.', 'rpg-ia'),
            'action' => $response
        ), 201);
    }
}