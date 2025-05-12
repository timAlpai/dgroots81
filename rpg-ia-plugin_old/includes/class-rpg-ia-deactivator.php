<?php
/**
 * Classe responsable de la désactivation du plugin
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Classe responsable de la désactivation du plugin.
 *
 * Cette classe définit tout ce qui est nécessaire pendant la désactivation du plugin.
 *
 * @since      1.0.0
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_Deactivator {

    /**
     * Méthode exécutée lors de la désactivation du plugin.
     *
     * Nettoie les ressources temporaires et vide le cache des permaliens.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Nettoyer les ressources temporaires
        self::clean_temporary_resources();
        
        // Vider le cache des permaliens
        flush_rewrite_rules();
    }

    /**
     * Nettoie les ressources temporaires.
     *
     * @since    1.0.0
     */
    private static function clean_temporary_resources() {
        global $wpdb;
        
        // Supprimer les données d'état de jeu temporaires
        $table_game_state = $wpdb->prefix . 'rpgia_game_state';
        $wpdb->query("TRUNCATE TABLE $table_game_state");
        
        // Supprimer les transients liés au plugin
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_rpg_ia_%'");
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_timeout_rpg_ia_%'");
    }
}