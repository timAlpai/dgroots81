<?php
/**
 * Affiche le tableau de bord d'administration du plugin.
 *
 * @link       https://dgroots81.mandragore.ai/
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/admin/partials
 */

// Si ce fichier est appelé directement, on sort
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="rpg-ia-admin-dashboard">
        <div class="rpg-ia-admin-card">
            <h2><?php _e('Welcome to RPG-IA', 'rpg-ia'); ?></h2>
            <p><?php _e('RPG-IA is a WordPress plugin that provides a user interface for the RPG-IA backend API.', 'rpg-ia'); ?></p>
            <p><?php _e('This plugin allows users to play a role-playing game with an AI as the game master.', 'rpg-ia'); ?></p>
        </div>
        
        <div class="rpg-ia-admin-row">
            <div class="rpg-ia-admin-column">
                <div class="rpg-ia-admin-card">
                    <h3><?php _e('Statistics', 'rpg-ia'); ?></h3>
                    <?php
                    // Récupérer les statistiques
                    $characters_count = wp_count_posts('rpgia_character')->publish;
                    $sessions_count = wp_count_posts('rpgia_session')->publish;
                    $scenarios_count = wp_count_posts('rpgia_scenario')->publish;
                    $action_logs_count = wp_count_posts('rpgia_action_log')->publish;
                    ?>
                    <ul>
                        <li><?php echo sprintf(__('Characters: %d', 'rpg-ia'), $characters_count); ?></li>
                        <li><?php echo sprintf(__('Sessions: %d', 'rpg-ia'), $sessions_count); ?></li>
                        <li><?php echo sprintf(__('Scenarios: %d', 'rpg-ia'), $scenarios_count); ?></li>
                        <li><?php echo sprintf(__('Action Logs: %d', 'rpg-ia'), $action_logs_count); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="rpg-ia-admin-column">
                <div class="rpg-ia-admin-card">
                    <h3><?php _e('API Status', 'rpg-ia'); ?></h3>
                    <div id="rpg-ia-api-status">
                        <p><?php _e('Checking API connection...', 'rpg-ia'); ?></p>
                    </div>
                    <button id="rpg-ia-check-api" class="button button-primary"><?php _e('Check API Connection', 'rpg-ia'); ?></button>
                </div>
            </div>
        </div>
        
        <div class="rpg-ia-admin-row">
            <div class="rpg-ia-admin-column">
                <div class="rpg-ia-admin-card">
                    <h3><?php _e('Quick Links', 'rpg-ia'); ?></h3>
                    <ul>
                        <li><a href="<?php echo admin_url('post-new.php?post_type=rpgia_character'); ?>"><?php _e('Create New Character', 'rpg-ia'); ?></a></li>
                        <li><a href="<?php echo admin_url('post-new.php?post_type=rpgia_session'); ?>"><?php _e('Create New Session', 'rpg-ia'); ?></a></li>
                        <li><a href="<?php echo admin_url('post-new.php?post_type=rpgia_scenario'); ?>"><?php _e('Create New Scenario', 'rpg-ia'); ?></a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=rpg-ia-settings'); ?>"><?php _e('Plugin Settings', 'rpg-ia'); ?></a></li>
                    </ul>
                </div>
            </div>
            
            <div class="rpg-ia-admin-column">
                <div class="rpg-ia-admin-card">
                    <h3><?php _e('Documentation', 'rpg-ia'); ?></h3>
                    <p><?php _e('For more information on how to use this plugin, please refer to the documentation.', 'rpg-ia'); ?></p>
                    <a href="https://dgroots81.mandragore.ai/documentation" class="button button-secondary" target="_blank"><?php _e('View Documentation', 'rpg-ia'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>