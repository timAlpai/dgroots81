<?php
/**
 * Classe principale du plugin
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Classe principale du plugin.
 *
 * Cette classe est responsable de:
 * - Charger les dépendances
 * - Définir les hooks d'administration et publics
 * - Enregistrer les types de contenu personnalisés
 * - Initialiser le plugin
 *
 * @since      1.0.0
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_Plugin {

    /**
     * Le chargeur qui est responsable de maintenir et d'enregistrer tous les hooks du plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      RPG_IA_Loader    $loader    Maintient et enregistre tous les hooks du plugin.
     */
    protected $loader;

    /**
     * L'identifiant unique de ce plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    L'identifiant unique de ce plugin.
     */
    protected $plugin_name;

    /**
     * La version actuelle du plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    La version actuelle du plugin.
     */
    protected $version;

    /**
     * Définit les propriétés de base du plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->plugin_name = 'rpg-ia';
        $this->version = RPG_IA_VERSION;

        $this->load_dependencies();
        $this->set_locale();
        $this->register_post_types();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Charge les dépendances requises pour ce plugin.
     *
     * Inclut les fichiers suivants qui composent le plugin:
     *
     * - RPG_IA_Loader. Orchestre les hooks du plugin.
     * - RPG_IA_i18n. Définit la fonctionnalité d'internationalisation.
     * - RPG_IA_Admin. Définit tous les hooks de l'administration.
     * - RPG_IA_Public. Définit tous les hooks publics.
     * - RPG_IA_API_Client. Gère la communication avec l'API backend.
     * - RPG_IA_Auth_Handler. Gère l'authentification.
     * - RPG_IA_Character_Manager. Gère les personnages.
     * - RPG_IA_Session_Manager. Gère les sessions de jeu.
     * - RPG_IA_Game_Interface. Gère l'interface de jeu.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * La classe responsable d'orchestrer les actions et filtres du plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rpg-ia-loader.php';

        /**
         * La classe responsable de définir la fonctionnalité d'internationalisation.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rpg-ia-i18n.php';

        /**
         * La classe responsable de définir tous les hooks de l'administration.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-rpg-ia-admin.php';

        /**
         * La classe responsable de définir tous les hooks publics.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-rpg-ia-public.php';

        /**
         * La classe responsable de la communication avec l'API backend.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rpg-ia-api-client.php';

        /**
         * La classe responsable de gérer l'authentification.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rpg-ia-auth-handler.php';

        /**
         * La classe responsable de gérer les personnages.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rpg-ia-character-manager.php';

        /**
         * La classe responsable de gérer les sessions de jeu.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rpg-ia-session-manager.php';

        /**
         * La classe responsable de gérer l'interface de jeu.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rpg-ia-game-interface.php';

        /**
         * Les widgets du plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/widgets/index.php';

        $this->loader = new RPG_IA_Loader();
    }

    /**
     * Définit la locale pour l'internationalisation.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new RPG_IA_i18n();
        $plugin_i18n->set_domain($this->get_plugin_name());

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Enregistre les types de contenu personnalisés.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_post_types() {
        $this->loader->add_action('init', $this, 'init_post_types');
    }

    /**
     * Initialise les types de contenu personnalisés.
     * Cette méthode est appelée pendant l'action 'init' de WordPress.
     *
     * @since    1.0.0
     * @access   public
     */
    public function init_post_types() {
        // Enregistrer le type de post personnalisé pour les personnages
        register_post_type('rpgia_character', array(
            'labels' => array(
                'name' => __('Characters', 'rpg-ia'),
                'singular_name' => __('Character', 'rpg-ia'),
                'menu_name' => __('Characters', 'rpg-ia'),
                'all_items' => __('All Characters', 'rpg-ia'),
                'add_new' => __('Add New', 'rpg-ia'),
                'add_new_item' => __('Add New Character', 'rpg-ia'),
                'edit_item' => __('Edit Character', 'rpg-ia'),
                'new_item' => __('New Character', 'rpg-ia'),
                'view_item' => __('View Character', 'rpg-ia'),
                'search_items' => __('Search Characters', 'rpg-ia'),
                'not_found' => __('No characters found', 'rpg-ia'),
                'not_found_in_trash' => __('No characters found in Trash', 'rpg-ia')
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-superhero',
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'rpg-ia-character')
        ));

        // Enregistrer le type de post personnalisé pour les sessions de jeu
        register_post_type('rpgia_session', array(
            'labels' => array(
                'name' => __('Sessions', 'rpg-ia'),
                'singular_name' => __('Session', 'rpg-ia'),
                'menu_name' => __('Sessions', 'rpg-ia'),
                'all_items' => __('All Sessions', 'rpg-ia'),
                'add_new' => __('Add New', 'rpg-ia'),
                'add_new_item' => __('Add New Session', 'rpg-ia'),
                'edit_item' => __('Edit Session', 'rpg-ia'),
                'new_item' => __('New Session', 'rpg-ia'),
                'view_item' => __('View Session', 'rpg-ia'),
                'search_items' => __('Search Sessions', 'rpg-ia'),
                'not_found' => __('No sessions found', 'rpg-ia'),
                'not_found_in_trash' => __('No sessions found in Trash', 'rpg-ia')
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-groups',
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'rpg-ia-session')
        ));

        // Enregistrer le type de post personnalisé pour les scénarios
        register_post_type('rpgia_scenario', array(
            'labels' => array(
                'name' => __('Scenarios', 'rpg-ia'),
                'singular_name' => __('Scenario', 'rpg-ia'),
                'menu_name' => __('Scenarios', 'rpg-ia'),
                'all_items' => __('All Scenarios', 'rpg-ia'),
                'add_new' => __('Add New', 'rpg-ia'),
                'add_new_item' => __('Add New Scenario', 'rpg-ia'),
                'edit_item' => __('Edit Scenario', 'rpg-ia'),
                'new_item' => __('New Scenario', 'rpg-ia'),
                'view_item' => __('View Scenario', 'rpg-ia'),
                'search_items' => __('Search Scenarios', 'rpg-ia'),
                'not_found' => __('No scenarios found', 'rpg-ia'),
                'not_found_in_trash' => __('No scenarios found in Trash', 'rpg-ia')
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-book',
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'rpg-ia-scenario')
        ));

        // Enregistrer le type de post personnalisé pour les logs d'action
        register_post_type('rpgia_action_log', array(
            'labels' => array(
                'name' => __('Action Logs', 'rpg-ia'),
                'singular_name' => __('Action Log', 'rpg-ia'),
                'menu_name' => __('Action Logs', 'rpg-ia'),
                'all_items' => __('All Action Logs', 'rpg-ia'),
                'add_new' => __('Add New', 'rpg-ia'),
                'add_new_item' => __('Add New Action Log', 'rpg-ia'),
                'edit_item' => __('Edit Action Log', 'rpg-ia'),
                'new_item' => __('New Action Log', 'rpg-ia'),
                'view_item' => __('View Action Log', 'rpg-ia'),
                'search_items' => __('Search Action Logs', 'rpg-ia'),
                'not_found' => __('No action logs found', 'rpg-ia'),
                'not_found_in_trash' => __('No action logs found in Trash', 'rpg-ia')
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-list-view',
            'supports' => array('title', 'editor', 'custom-fields'),
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'rpg-ia-action-log')
        ));
    }

    /**
     * Enregistre tous les hooks liés à la fonctionnalité d'administration du plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new RPG_IA_Admin($this->get_plugin_name(), $this->get_version());

        // Enregistrer les styles et scripts d'administration
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Ajouter le menu d'administration
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');

        // Ajouter la page de réglages
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        
        // Enregistrer les hooks AJAX
        $plugin_admin->register_ajax_hooks();
    }

    /**
     * Enregistre tous les hooks liés à la fonctionnalité publique du plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new RPG_IA_Public($this->get_plugin_name(), $this->get_version());

        // Enregistrer les styles et scripts publics
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Enregistrer les shortcodes
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');

        // Enregistrer les widgets
        $this->loader->add_action('widgets_init', $plugin_public, 'register_widgets');

        // Initialiser l'API REST
        $this->loader->add_action('rest_api_init', $plugin_public, 'register_rest_routes');
    }

    /**
     * Exécute le chargeur pour exécuter tous les hooks avec WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * Le nom du plugin utilisé pour l'identifier de manière unique dans le contexte de
     * WordPress et pour définir la fonctionnalité d'internationalisation.
     *
     * @since     1.0.0
     * @return    string    Le nom du plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * La référence à la classe qui orchestre les hooks du plugin.
     *
     * @since     1.0.0
     * @return    RPG_IA_Loader    Orchestre les hooks du plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Récupère le numéro de version du plugin.
     *
     * @since     1.0.0
     * @return    string    Le numéro de version du plugin.
     */
    public function get_version() {
        return $this->version;
    }
}