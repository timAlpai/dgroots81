<?php
/**
 * Classe responsable de l'activation du plugin
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Classe responsable de l'activation du plugin.
 *
 * Cette classe définit tout ce qui est nécessaire pendant l'activation du plugin.
 *
 * @since      1.0.0
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_Activator {

    /**
     * Méthode exécutée lors de l'activation du plugin.
     *
     * Crée les tables personnalisées, enregistre les options par défaut
     * et crée les pages personnalisées.
     *
     * @since    1.0.0
     */
    public static function activate() {
        self::create_custom_tables();
        self::set_default_options();
        self::create_custom_pages();
        
        // Exécuter les tests automatiques si l'option est activée
        if (get_option('rpg_ia_run_tests_on_activation', 'yes') === 'yes') {
            self::run_tests();
        }
        
        // Vider le cache des permaliens
        flush_rewrite_rules();
    }
    
    /**
     * Exécute les tests automatiques du plugin.
     *
     * @since    1.0.0
     * @return   array    Les résultats des tests.
     */
    private static function run_tests() {
        // Charger la classe de tests
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-rpg-ia-tests.php';
        
        // Créer une instance de la classe de tests
        $tests = new RPG_IA_Tests();
        
        // Exécuter tous les tests
        return $tests->run_all_tests();
    }

    /**
     * Crée les tables personnalisées dans la base de données.
     *
     * @since    1.0.0
     */
    private static function create_custom_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table des métadonnées utilisateur spécifiques au jeu
        $table_user_meta = $wpdb->prefix . 'rpgia_user_meta';
        $sql_user_meta = "CREATE TABLE $table_user_meta (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY meta_key (meta_key)
        ) $charset_collate;";
        
        // Table d'association entre sessions et joueurs
        $table_session_players = $wpdb->prefix . 'rpgia_session_players';
        $sql_session_players = "CREATE TABLE $table_session_players (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            character_id bigint(20) NOT NULL,
            joined_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY session_user (session_id,user_id),
            KEY session_id (session_id),
            KEY user_id (user_id),
            KEY character_id (character_id)
        ) $charset_collate;";
        
        // Table d'état de jeu temporaire pour les sessions actives
        $table_game_state = $wpdb->prefix . 'rpgia_game_state';
        $sql_game_state = "CREATE TABLE $table_game_state (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id bigint(20) NOT NULL,
            state_key varchar(255) NOT NULL,
            state_value longtext NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY session_key (session_id,state_key),
            KEY session_id (session_id),
            KEY state_key (state_key)
        ) $charset_collate;";
        
        // Inclure le fichier de mise à jour de la base de données
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Créer les tables
        dbDelta($sql_user_meta);
        dbDelta($sql_session_players);
        dbDelta($sql_game_state);
    }

    /**
     * Définit les options par défaut du plugin.
     *
     * @since    1.0.0
     */
    private static function set_default_options() {
        // URL de l'API backend
        if (!get_option('rpg_ia_api_url')) {
            update_option('rpg_ia_api_url', 'http://localhost:8000');
        }
        
        // Nombre maximum de joueurs par session
        if (!get_option('rpg_ia_max_players')) {
            update_option('rpg_ia_max_players', 6);
        }
        
        // Intervalle de mise à jour (en secondes)
        if (!get_option('rpg_ia_update_interval')) {
            update_option('rpg_ia_update_interval', 5);
        }
        
        // Activer le chat entre joueurs
        if (!get_option('rpg_ia_enable_chat')) {
            update_option('rpg_ia_enable_chat', 'yes');
        }
        
        // Exécuter les tests lors de l'activation
        if (!get_option('rpg_ia_run_tests_on_activation')) {
            update_option('rpg_ia_run_tests_on_activation', 'yes');
        }
        
        // Mode test (tester les fonctionnalités de l'API ou simplement le chargement des classes)
        if (!get_option('rpg_ia_test_mode')) {
            update_option('rpg_ia_test_mode', 'no');
        }
    }

    /**
     * Crée les pages personnalisées du plugin.
     *
     * @since    1.0.0
     */
    private static function create_custom_pages() {
        // Tableau des pages à créer
        $pages = array(
            'rpg-ia-dashboard' => array(
                'title' => __('RPG-IA Dashboard', 'rpg-ia'),
                'content' => '[rpgia_dashboard]',
                'slug' => 'rpg-ia/dashboard'
            ),
            'rpg-ia-characters' => array(
                'title' => __('RPG-IA Characters', 'rpg-ia'),
                'content' => '[rpgia_characters]',
                'slug' => 'rpg-ia/characters'
            ),
            'rpg-ia-sessions' => array(
                'title' => __('RPG-IA Sessions', 'rpg-ia'),
                'content' => '[rpgia_sessions]',
                'slug' => 'rpg-ia/sessions'
            ),
            'rpg-ia-play' => array(
                'title' => __('RPG-IA Play', 'rpg-ia'),
                'content' => '[rpgia_game_interface]',
                'slug' => 'rpg-ia/play'
            )
        );
        
        // Créer chaque page si elle n'existe pas déjà
        foreach ($pages as $page_key => $page_data) {
            // Vérifier si la page existe déjà
            $page_exists = get_page_by_path($page_data['slug']);
            
            if (!$page_exists) {
                // Créer la page
                $page_id = wp_insert_post(array(
                    'post_title' => $page_data['title'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => $page_data['slug']
                ));
                
                // Enregistrer l'ID de la page dans les options
                update_option('rpg_ia_page_' . $page_key, $page_id);
            } else {
                // Enregistrer l'ID de la page existante
                update_option('rpg_ia_page_' . $page_key, $page_exists->ID);
            }
        }
    }
}