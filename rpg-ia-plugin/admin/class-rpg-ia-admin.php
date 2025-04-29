<?php
/**
 * Fonctionnalités d'administration du plugin
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/admin
 */

/**
 * Fonctionnalités d'administration du plugin.
 *
 * Définit le nom du plugin, la version et les hooks d'administration.
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/admin
 * @author     RPG-IA Team
 */
class RPG_IA_Admin {

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
     * Enregistre les styles pour l'administration.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/rpg-ia-admin.css', array(), $this->version, 'all');
    }

    /**
     * Enregistre les scripts pour l'administration.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/rpg-ia-admin.js', array('jquery'), $this->version, false);
        
        // Ajouter les variables localisées pour le script
        wp_localize_script($this->plugin_name, 'rpg_ia_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rpg_ia_admin_nonce'),
            'api_url' => RPG_IA_API_URL
        ));
        
        // Ajouter les chaînes de traduction pour le script
        wp_localize_script($this->plugin_name, 'rpg_ia_admin_l10n', array(
            'checking_api' => __('Vérification de la connexion à l\'API...', 'rpg-ia'),
            'api_connected' => __('Connecté à l\'API RPG-IA', 'rpg-ia'),
            'api_version' => __('Version de l\'API', 'rpg-ia'),
            'api_error' => __('Erreur de connexion à l\'API', 'rpg-ia'),
            'ajax_error' => __('Erreur de communication avec le serveur', 'rpg-ia'),
            'no_users_found' => __('Aucun utilisateur trouvé', 'rpg-ia'),
            'player_already_added' => __('Ce joueur est déjà ajouté à la session', 'rpg-ia'),
            'select_character' => __('Sélectionner un personnage', 'rpg-ia')
        ));
    }

    /**
     * Ajoute les menus d'administration.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        // Menu principal
        add_menu_page(
            __('RPG-IA', 'rpg-ia'),
            __('RPG-IA', 'rpg-ia'),
            'manage_options',
            'rpg-ia',
            array($this, 'display_plugin_admin_dashboard'),
            'dashicons-games',
            30
        );

        // Sous-menu: Tableau de bord
        add_submenu_page(
            'rpg-ia',
            __('Dashboard', 'rpg-ia'),
            __('Dashboard', 'rpg-ia'),
            'manage_options',
            'rpg-ia',
            array($this, 'display_plugin_admin_dashboard')
        );

        // Sous-menu: Personnages
        add_submenu_page(
            'rpg-ia',
            __('Characters', 'rpg-ia'),
            __('Characters', 'rpg-ia'),
            'manage_options',
            'edit.php?post_type=rpgia_character',
            null
        );

        // Sous-menu: Sessions
        add_submenu_page(
            'rpg-ia',
            __('Sessions', 'rpg-ia'),
            __('Sessions', 'rpg-ia'),
            'manage_options',
            'edit.php?post_type=rpgia_session',
            null
        );

        // Sous-menu: Scénarios
        add_submenu_page(
            'rpg-ia',
            __('Scenarios', 'rpg-ia'),
            __('Scenarios', 'rpg-ia'),
            'manage_options',
            'edit.php?post_type=rpgia_scenario',
            null
        );

        // Sous-menu: Tableau de bord MJ
        add_submenu_page(
            'rpg-ia',
            __('Game Master Dashboard', 'rpg-ia'),
            __('GM Dashboard', 'rpg-ia'),
            'manage_options',
            'rpg-ia-gm-dashboard',
            array($this, 'display_plugin_gm_dashboard')
        );

        // Sous-menu: Gestion des scénarios
        add_submenu_page(
            'rpg-ia',
            __('Scenario Management', 'rpg-ia'),
            __('Scenario Management', 'rpg-ia'),
            'manage_options',
            'rpg-ia-scenario-management',
            array($this, 'display_plugin_scenario_management')
        );

        // Sous-menu: Interface MJ
        add_submenu_page(
            'rpg-ia',
            __('Game Master Interface', 'rpg-ia'),
            __('GM Interface', 'rpg-ia'),
            'manage_options',
            'rpg-ia-gm-interface',
            array($this, 'display_plugin_gm_interface')
        );

        // Sous-menu: Logs d'action
        add_submenu_page(
            'rpg-ia',
            __('Action Logs', 'rpg-ia'),
            __('Action Logs', 'rpg-ia'),
            'manage_options',
            'edit.php?post_type=rpgia_action_log',
            null
        );
        
        // Sous-menu: Tests
        add_submenu_page(
            'rpg-ia',
            __('Tests', 'rpg-ia'),
            __('Tests', 'rpg-ia'),
            'manage_options',
            'rpg-ia-tests',
            array($this, 'display_plugin_admin_tests')
        );

        // Sous-menu: Réglages
        add_submenu_page(
            'rpg-ia',
            __('Settings', 'rpg-ia'),
            __('Settings', 'rpg-ia'),
            'manage_options',
            'rpg-ia-settings',
            array($this, 'display_plugin_admin_settings')
        );
    }

    /**
     * Enregistre les réglages du plugin.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Enregistrer la section de réglages
        add_settings_section(
            'rpg_ia_general_settings',
            __('General Settings', 'rpg-ia'),
            array($this, 'rpg_ia_general_settings_callback'),
            'rpg-ia-settings'
        );

        // Enregistrer le champ URL de l'API
        add_settings_field(
            'rpg_ia_api_url',
            __('API URL', 'rpg-ia'),
            array($this, 'rpg_ia_api_url_callback'),
            'rpg-ia-settings',
            'rpg_ia_general_settings'
        );
        register_setting('rpg_ia_settings', 'rpg_ia_api_url');

        // Enregistrer le champ nombre maximum de joueurs
        add_settings_field(
            'rpg_ia_max_players',
            __('Maximum Players per Session', 'rpg-ia'),
            array($this, 'rpg_ia_max_players_callback'),
            'rpg-ia-settings',
            'rpg_ia_general_settings'
        );
        register_setting('rpg_ia_settings', 'rpg_ia_max_players', array(
            'sanitize_callback' => 'absint'
        ));

        // Enregistrer le champ intervalle de mise à jour
        add_settings_field(
            'rpg_ia_update_interval',
            __('Update Interval (seconds)', 'rpg-ia'),
            array($this, 'rpg_ia_update_interval_callback'),
            'rpg-ia-settings',
            'rpg_ia_general_settings'
        );
        register_setting('rpg_ia_settings', 'rpg_ia_update_interval', array(
            'sanitize_callback' => 'absint'
        ));

        // Enregistrer le champ activer le chat
        add_settings_field(
            'rpg_ia_enable_chat',
            __('Enable Chat', 'rpg-ia'),
            array($this, 'rpg_ia_enable_chat_callback'),
            'rpg-ia-settings',
            'rpg_ia_general_settings'
        );
        register_setting('rpg_ia_settings', 'rpg_ia_enable_chat');
        
        // Enregistrer la section de réglages des tests
        add_settings_section(
            'rpg_ia_tests_settings',
            __('Tests Settings', 'rpg-ia'),
            array($this, 'rpg_ia_tests_settings_callback'),
            'rpg-ia-settings'
        );
        
        // Enregistrer le champ mode test
        add_settings_field(
            'rpg_ia_test_mode',
            __('Test Mode', 'rpg-ia'),
            array($this, 'rpg_ia_test_mode_callback'),
            'rpg-ia-settings',
            'rpg_ia_tests_settings'
        );
        register_setting('rpg_ia_settings', 'rpg_ia_test_mode');
        
        // Enregistrer le champ exécuter les tests lors de l'activation
        add_settings_field(
            'rpg_ia_run_tests_on_activation',
            __('Run Tests on Activation', 'rpg-ia'),
            array($this, 'rpg_ia_run_tests_on_activation_callback'),
            'rpg-ia-settings',
            'rpg_ia_tests_settings'
        );
        register_setting('rpg_ia_settings', 'rpg_ia_run_tests_on_activation');
        
        // Enregistrer le champ exécuter les tests manuellement
        add_settings_field(
            'rpg_ia_run_tests_manually',
            __('Run Tests Manually', 'rpg-ia'),
            array($this, 'rpg_ia_run_tests_manually_callback'),
            'rpg-ia-settings',
            'rpg_ia_tests_settings'
        );
    }

    /**
     * Callback pour la section de réglages généraux.
     *
     * @since    1.0.0
     */
    public function rpg_ia_general_settings_callback() {
        echo '<p>' . __('Configure the general settings for the RPG-IA plugin.', 'rpg-ia') . '</p>';
    }

    /**
     * Callback pour le champ URL de l'API.
     *
     * @since    1.0.0
     */
    public function rpg_ia_api_url_callback() {
        $api_url = get_option('rpg_ia_api_url', 'http://localhost:8000');
        echo '<input type="text" id="rpg_ia_api_url" name="rpg_ia_api_url" value="' . esc_attr($api_url) . '" class="regular-text" />';
        echo '<p class="description">' . __('The URL of the RPG-IA backend API.', 'rpg-ia') . '</p>';
    }

    /**
     * Callback pour le champ nombre maximum de joueurs.
     *
     * @since    1.0.0
     */
    public function rpg_ia_max_players_callback() {
        $max_players = get_option('rpg_ia_max_players', 6);
        echo '<input type="number" id="rpg_ia_max_players" name="rpg_ia_max_players" value="' . esc_attr($max_players) . '" class="small-text" min="1" max="20" />';
        echo '<p class="description">' . __('The maximum number of players allowed in a game session.', 'rpg-ia') . '</p>';
    }

    /**
     * Callback pour le champ intervalle de mise à jour.
     *
     * @since    1.0.0
     */
    public function rpg_ia_update_interval_callback() {
        $update_interval = get_option('rpg_ia_update_interval', 5);
        echo '<input type="number" id="rpg_ia_update_interval" name="rpg_ia_update_interval" value="' . esc_attr($update_interval) . '" class="small-text" min="1" max="60" />';
        echo '<p class="description">' . __('The interval (in seconds) between updates in the game interface.', 'rpg-ia') . '</p>';
    }

    /**
     * Callback pour le champ activer le chat.
     *
     * @since    1.0.0
     */
    public function rpg_ia_enable_chat_callback() {
        $enable_chat = get_option('rpg_ia_enable_chat', 'yes');
        echo '<select id="rpg_ia_enable_chat" name="rpg_ia_enable_chat">';
        echo '<option value="yes" ' . selected($enable_chat, 'yes', false) . '>' . __('Yes', 'rpg-ia') . '</option>';
        echo '<option value="no" ' . selected($enable_chat, 'no', false) . '>' . __('No', 'rpg-ia') . '</option>';
        echo '</select>';
        echo '<p class="description">' . __('Enable or disable the chat feature between players.', 'rpg-ia') . '</p>';
    }
    
    /**
     * Callback pour le champ mode test.
     *
     * @since    1.0.0
     */
    public function rpg_ia_test_mode_callback() {
        $test_mode = get_option('rpg_ia_test_mode', 'no');
        echo '<select id="rpg_ia_test_mode" name="rpg_ia_test_mode">';
        echo '<option value="yes" ' . selected($test_mode, 'yes', false) . '>' . __('Yes', 'rpg-ia') . '</option>';
        echo '<option value="no" ' . selected($test_mode, 'no', false) . '>' . __('No', 'rpg-ia') . '</option>';
        echo '</select>';
        echo '<p class="description">' . __('Enable or disable the test mode. When enabled, the plugin will test the API functionality instead of just checking if classes exist.', 'rpg-ia') . '</p>';
    }
    
    /**
     * Callback pour le champ exécuter les tests lors de l'activation.
     *
     * @since    1.0.0
     */
    public function rpg_ia_run_tests_on_activation_callback() {
        $run_tests = get_option('rpg_ia_run_tests_on_activation', 'yes');
        echo '<select id="rpg_ia_run_tests_on_activation" name="rpg_ia_run_tests_on_activation">';
        echo '<option value="yes" ' . selected($run_tests, 'yes', false) . '>' . __('Yes', 'rpg-ia') . '</option>';
        echo '<option value="no" ' . selected($run_tests, 'no', false) . '>' . __('No', 'rpg-ia') . '</option>';
        echo '</select>';
        echo '<p class="description">' . __('Enable or disable automatic tests when the plugin is activated.', 'rpg-ia') . '</p>';
    }
    
    
    /**
     * Callback pour la section de réglages des tests.
     *
     * @since    1.0.0
     */
    public function rpg_ia_tests_settings_callback() {
        echo '<p>' . __('Configure the tests settings for the RPG-IA plugin.', 'rpg-ia') . '</p>';
    }
    
    /**
     * Callback pour le champ exécuter les tests manuellement.
     *
     * @since    1.0.0
     */
    public function rpg_ia_run_tests_manually_callback() {
        echo '<button id="rpg-ia-run-tests" class="button button-secondary">' . __('Run Tests Now', 'rpg-ia') . '</button>';
        echo '<div id="rpg-ia-tests-result"></div>';
        echo '<p class="description">' . __('Run the tests manually and see the results.', 'rpg-ia') . '</p>';
    }

    /**
     * Affiche le tableau de bord d'administration du plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_dashboard() {
        include_once plugin_dir_path(__FILE__) . 'partials/rpg-ia-admin-dashboard.php';
    }

    /**
     * Affiche la page de réglages du plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_settings() {
        include_once plugin_dir_path(__FILE__) . 'partials/rpg-ia-admin-settings.php';
    }

    /**
     * Affiche le tableau de bord du maître de jeu.
     *
     * @since    1.0.0
     */
    public function display_plugin_gm_dashboard() {
        include_once plugin_dir_path(__FILE__) . 'partials/rpg-ia-gm-dashboard.php';
    }

    /**
     * Affiche la page de gestion des scénarios.
     *
     * @since    1.0.0
     */
    public function display_plugin_scenario_management() {
        include_once plugin_dir_path(__FILE__) . 'partials/rpg-ia-scenario-management.php';
    }

    /**
     * Affiche l'interface du maître de jeu.
     *
     * @since    1.0.0
     */
    public function display_plugin_gm_interface() {
        include_once plugin_dir_path(__FILE__) . 'partials/rpg-ia-gm-interface.php';
    }
    
    /**
     * Affiche la page de tests du plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_tests() {
        include_once plugin_dir_path(__FILE__) . 'partials/rpg-ia-admin-tests.php';
    }

    /**
     * Enregistre les hooks AJAX pour les fonctionnalités du maître de jeu.
     *
     * @since    1.0.0
     */
    public function register_ajax_hooks() {
        // Hooks pour les scénarios
        add_action('wp_ajax_rpg_ia_get_scenarios', array($this, 'ajax_get_scenarios'));
        add_action('wp_ajax_rpg_ia_get_scenario', array($this, 'ajax_get_scenario'));
        add_action('wp_ajax_rpg_ia_create_scenario', array($this, 'ajax_create_scenario'));
        add_action('wp_ajax_rpg_ia_update_scenario', array($this, 'ajax_update_scenario'));
        add_action('wp_ajax_rpg_ia_delete_scenario', array($this, 'ajax_delete_scenario'));
        
        // Hooks pour les scènes
        add_action('wp_ajax_rpg_ia_get_scenario_scenes', array($this, 'ajax_get_scenario_scenes'));
        add_action('wp_ajax_rpg_ia_get_scenario_scene', array($this, 'ajax_get_scenario_scene'));
        add_action('wp_ajax_rpg_ia_create_scenario_scene', array($this, 'ajax_create_scenario_scene'));
        add_action('wp_ajax_rpg_ia_update_scenario_scene', array($this, 'ajax_update_scenario_scene'));
        add_action('wp_ajax_rpg_ia_delete_scenario_scene', array($this, 'ajax_delete_scenario_scene'));
        
        // Hooks pour les sessions
        add_action('wp_ajax_rpg_ia_update_session_status', array($this, 'ajax_update_session_status'));
        add_action('wp_ajax_rpg_ia_override_narration', array($this, 'ajax_override_narration'));
        add_action('wp_ajax_rpg_ia_change_session_scene', array($this, 'ajax_change_session_scene'));
        
        // Hooks pour les statistiques
        add_action('wp_ajax_rpg_ia_get_gm_stats', array($this, 'ajax_get_gm_stats'));
        
        // Hook pour la vérification de l'API
        add_action('wp_ajax_rpg_ia_check_api_status', array($this, 'ajax_check_api_status'));
        
        // Hook pour l'exécution des tests
        add_action('wp_ajax_rpg_ia_run_tests', array($this, 'ajax_run_tests'));
    }

    /**
     * Récupère la liste des scénarios via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_get_scenarios() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Récupérer les scénarios
        $api_client = new RPG_IA_API_Client();
        $scenario_manager = new RPG_IA_Scenario_Manager($api_client);
        $scenarios = $scenario_manager->get_scenarios();
        
        if (is_wp_error($scenarios)) {
            wp_send_json_error($scenarios->get_error_message());
            return;
        }
        
        wp_send_json_success($scenarios);
    }

    /**
     * Récupère un scénario spécifique via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_get_scenario() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($_POST['scenario_id'])) {
            wp_send_json_error(__('Missing scenario ID.', 'rpg-ia'));
            return;
        }
        
        $scenario_id = intval($_POST['scenario_id']);
        
        // Récupérer le scénario
        $api_client = new RPG_IA_API_Client();
        $scenario_manager = new RPG_IA_Scenario_Manager($api_client);
        $scenario = $scenario_manager->get_scenario($scenario_id);
        
        if (is_wp_error($scenario)) {
            wp_send_json_error($scenario->get_error_message());
            return;
        }
        
        wp_send_json_success($scenario);
    }

    /**
     * Crée un nouveau scénario via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_create_scenario() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($_POST['scenario_data'])) {
            wp_send_json_error(__('Missing scenario data.', 'rpg-ia'));
            return;
        }
        
        $scenario_data = json_decode(stripslashes($_POST['scenario_data']), true);
        
        if (!$scenario_data) {
            wp_send_json_error(__('Invalid scenario data.', 'rpg-ia'));
            return;
        }
        
        // Créer le scénario
        $api_client = new RPG_IA_API_Client();
        $scenario_manager = new RPG_IA_Scenario_Manager($api_client);
        $result = $scenario_manager->create_scenario($scenario_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
    }

    /**
     * Met à jour un scénario existant via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_update_scenario() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($_POST['scenario_id']) || !isset($_POST['scenario_data'])) {
            wp_send_json_error(__('Missing scenario ID or data.', 'rpg-ia'));
            return;
        }
        
        $scenario_id = intval($_POST['scenario_id']);
        $scenario_data = json_decode(stripslashes($_POST['scenario_data']), true);
        
        if (!$scenario_data) {
            wp_send_json_error(__('Invalid scenario data.', 'rpg-ia'));
            return;
        }
        
        // Mettre à jour le scénario
        $api_client = new RPG_IA_API_Client();
        $scenario_manager = new RPG_IA_Scenario_Manager($api_client);
        $result = $scenario_manager->update_scenario($scenario_id, $scenario_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
    }

    /**
     * Supprime un scénario via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_delete_scenario() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($_POST['scenario_id'])) {
            wp_send_json_error(__('Missing scenario ID.', 'rpg-ia'));
            return;
        }
        
        $scenario_id = intval($_POST['scenario_id']);
        
        // Supprimer le scénario
        $api_client = new RPG_IA_API_Client();
        $scenario_manager = new RPG_IA_Scenario_Manager($api_client);
        $result = $scenario_manager->delete_scenario($scenario_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
    }

    /**
     * Récupère les scènes d'un scénario via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_get_scenario_scenes() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($_POST['scenario_id'])) {
            wp_send_json_error(__('Missing scenario ID.', 'rpg-ia'));
            return;
        }
        
        $scenario_id = intval($_POST['scenario_id']);
        
        // Récupérer les scènes
        $api_client = new RPG_IA_API_Client();
        $scenario_manager = new RPG_IA_Scenario_Manager($api_client);
        $scenes = $scenario_manager->get_scenario_scenes($scenario_id);
        
        if (is_wp_error($scenes)) {
            wp_send_json_error($scenes->get_error_message());
            return;
        }
        
        wp_send_json_success($scenes);
    }

    /**
     * Récupère une scène spécifique d'un scénario via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_get_scenario_scene() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($_POST['scenario_id']) || !isset($_POST['scene_id'])) {
            wp_send_json_error(__('Missing scenario ID or scene ID.', 'rpg-ia'));
            return;
        }
        
        $scenario_id = intval($_POST['scenario_id']);
        $scene_id = intval($_POST['scene_id']);
        
        // Récupérer la scène
        $api_client = new RPG_IA_API_Client();
        $scenario_manager = new RPG_IA_Scenario_Manager($api_client);
        $scene = $scenario_manager->get_scenario_scene($scenario_id, $scene_id);
        
        if (is_wp_error($scene)) {
            wp_send_json_error($scene->get_error_message());
            return;
        }
        
        wp_send_json_success($scene);
    }

    /**
     * Crée une nouvelle scène dans un scénario via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_create_scenario_scene() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($_POST['scenario_id']) || !isset($_POST['scene_data'])) {
            wp_send_json_error(__('Missing scenario ID or scene data.', 'rpg-ia'));
            return;
        }
        
        $scenario_id = intval($_POST['scenario_id']);
        $scene_data = json_decode(stripslashes($_POST['scene_data']), true);
        
        if (!$scene_data) {
            wp_send_json_error(__('Invalid scene data.', 'rpg-ia'));
            return;
        }
        
        // Créer la scène
        $api_client = new RPG_IA_API_Client();
        $scenario_manager = new RPG_IA_Scenario_Manager($api_client);
        $result = $scenario_manager->create_scenario_scene($scenario_id, $scene_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
    }

    /**
     * Met à jour une scène existante dans un scénario via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_update_scenario_scene() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($_POST['scenario_id']) || !isset($_POST['scene_id']) || !isset($_POST['scene_data'])) {
            wp_send_json_error(__('Missing scenario ID, scene ID or scene data.', 'rpg-ia'));
            return;
        }
        
        $scenario_id = intval($_POST['scenario_id']);
        $scene_id = intval($_POST['scene_id']);
        $scene_data = json_decode(stripslashes($_POST['scene_data']), true);
        
        if (!$scene_data) {
            wp_send_json_error(__('Invalid scene data.', 'rpg-ia'));
            return;
        }
        
        // Mettre à jour la scène
        $api_client = new RPG_IA_API_Client();
        $scenario_manager = new RPG_IA_Scenario_Manager($api_client);
        $result = $scenario_manager->update_scenario_scene($scenario_id, $scene_id, $scene_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
    }

    /**
     * Supprime une scène d'un scénario via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_delete_scenario_scene() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($_POST['scenario_id']) || !isset($_POST['scene_id'])) {
            wp_send_json_error(__('Missing scenario ID or scene ID.', 'rpg-ia'));
            return;
        }
        
        $scenario_id = intval($_POST['scenario_id']);
        $scene_id = intval($_POST['scene_id']);
        
        // Supprimer la scène
        $api_client = new RPG_IA_API_Client();
        $scenario_manager = new RPG_IA_Scenario_Manager($api_client);
        $result = $scenario_manager->delete_scenario_scene($scenario_id, $scene_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
    }

    /**
     * Met à jour le statut d'une session via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_update_session_status() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($_POST['session_id']) || !isset($_POST['status'])) {
            wp_send_json_error(__('Missing session ID or status.', 'rpg-ia'));
            return;
        }
        
        $session_id = intval($_POST['session_id']);
        $status = sanitize_text_field($_POST['status']);
        
        // Mettre à jour le statut
        $api_client = new RPG_IA_API_Client();
        $session_manager = new RPG_IA_Session_Manager($api_client);
        $game_master = new RPG_IA_Game_Master($api_client, $session_manager);
        $result = $game_master->update_session_status($session_id, $status);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
    }

    /**
     * Intervient dans la narration d'une session via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_override_narration() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($_POST['session_id']) || !isset($_POST['content'])) {
            wp_send_json_error(__('Missing session ID or content.', 'rpg-ia'));
            return;
        }
        
        $session_id = intval($_POST['session_id']);
        $content = sanitize_textarea_field($_POST['content']);
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'narration';
        
        // Intervenir dans la narration
        $api_client = new RPG_IA_API_Client();
        $session_manager = new RPG_IA_Session_Manager($api_client);
        $game_master = new RPG_IA_Game_Master($api_client, $session_manager);
        $result = $game_master->override_narration($session_id, $content, $type);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
    }

    /**
     * Change la scène actuelle d'une session via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_change_session_scene() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Vérifier les paramètres
        if (!isset($_POST['session_id']) || !isset($_POST['scene_id'])) {
            wp_send_json_error(__('Missing session ID or scene ID.', 'rpg-ia'));
            return;
        }
        
        $session_id = intval($_POST['session_id']);
        $scene_id = intval($_POST['scene_id']);
        
        // Changer la scène
        $api_client = new RPG_IA_API_Client();
        $session_manager = new RPG_IA_Session_Manager($api_client);
        $game_master = new RPG_IA_Game_Master($api_client, $session_manager);
        $result = $game_master->change_session_scene($session_id, $scene_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
    }

    /**
     * Récupère les statistiques du maître de jeu via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_get_gm_stats() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Récupérer l'ID de l'utilisateur
        $user_id = get_current_user_id();
        
        // Récupérer les statistiques
        $api_client = new RPG_IA_API_Client();
        $session_manager = new RPG_IA_Session_Manager($api_client);
        $game_master = new RPG_IA_Game_Master($api_client, $session_manager);
        $scenario_manager = new RPG_IA_Scenario_Manager($api_client);
        
        $session_stats = $game_master->get_gm_session_stats($user_id);
        $scenario_stats = $scenario_manager->get_user_scenario_stats($user_id);
        
        $stats = array(
            'sessions' => $session_stats,
            'scenarios' => $scenario_stats
        );
        
        wp_send_json_success($stats);
    }
    
    /**
     * Vérifie l'état de l'API backend via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_check_api_status() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Récupérer l'URL de l'API
        $api_url = get_option('rpg_ia_api_url', 'http://localhost:8000');
        
        // Créer une instance du client API
        $api_client = new RPG_IA_API_Client();
        
        // Vérifier l'état de l'API
        $result = $api_client->check_api_status();
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        // Renvoyer les informations sur l'API
        wp_send_json_success($result);
    }
    
    /**
     * Exécute les tests du plugin via AJAX.
     *
     * @since    1.0.0
     */
    public function ajax_run_tests() {
        // Vérifier le nonce
        check_ajax_referer('rpg_ia_admin_nonce', 'nonce');
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'rpg-ia'));
            return;
        }
        
        // Charger la classe de tests
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rpg-ia-tests.php';
        
        // Créer une instance de la classe de tests
        $tests = new RPG_IA_Tests();
        
        // Exécuter tous les tests
        $results = $tests->run_all_tests();
        
        // Renvoyer les résultats
        wp_send_json_success($results);
    }
}