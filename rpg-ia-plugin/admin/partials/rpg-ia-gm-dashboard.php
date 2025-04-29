<?php
/**
 * Affiche le tableau de bord du maître de jeu.
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/admin/partials
 */

// Si ce fichier est appelé directement, on sort
if (!defined('WPINC')) {
    die;
}

// Initialiser les classes nécessaires
$api_client = new RPG_IA_API_Client();
$session_manager = new RPG_IA_Session_Manager($api_client);
$game_master = new RPG_IA_Game_Master($api_client, $session_manager);
$scenario_manager = new RPG_IA_Scenario_Manager($api_client);

// Récupérer l'ID de l'utilisateur
$user_id = get_current_user_id();

// Récupérer les sessions du maître de jeu
$gm_sessions = $game_master->get_gm_sessions($user_id);

// Récupérer les scénarios de l'utilisateur
$user_scenarios = $scenario_manager->get_user_scenarios($user_id);

// Récupérer les statistiques
$session_stats = $game_master->get_gm_session_stats($user_id);
$scenario_stats = $scenario_manager->get_user_scenario_stats($user_id);
?>

<div class="wrap">
    <h1><?php _e('Game Master Dashboard', 'rpg-ia'); ?></h1>
    
    <div class="rpg-ia-admin-dashboard">
        <div class="rpg-ia-admin-card">
            <h2><?php _e('Welcome to the Game Master Dashboard', 'rpg-ia'); ?></h2>
            <p><?php _e('This dashboard provides tools and information for managing your RPG sessions as a Game Master.', 'rpg-ia'); ?></p>
        </div>
        
        <div class="rpg-ia-admin-row">
            <div class="rpg-ia-admin-column">
                <div class="rpg-ia-admin-card">
                    <h3><?php _e('My Sessions', 'rpg-ia'); ?></h3>
                    <?php if (empty($gm_sessions)) : ?>
                        <p><?php _e('You have no active sessions.', 'rpg-ia'); ?></p>
                    <?php else : ?>
                        <ul class="rpg-ia-session-list">
                            <?php foreach ($gm_sessions as $session) : ?>
                                <li>
                                    <strong><?php echo esc_html($session['name']); ?></strong>
                                    <span class="rpg-ia-session-status rpg-ia-status-<?php echo esc_attr($session['status']); ?>">
                                        <?php echo esc_html(ucfirst($session['status'])); ?>
                                    </span>
                                    <div class="rpg-ia-session-actions">
                                        <a href="<?php echo admin_url('admin.php?page=rpg-ia-gm-interface&session_id=' . $session['id']); ?>" class="button button-primary">
                                            <?php _e('Manage', 'rpg-ia'); ?>
                                        </a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <p>
                        <a href="<?php echo admin_url('post-new.php?post_type=rpgia_session'); ?>" class="button button-secondary">
                            <?php _e('Create New Session', 'rpg-ia'); ?>
                        </a>
                    </p>
                </div>
            </div>
            
            <div class="rpg-ia-admin-column">
                <div class="rpg-ia-admin-card">
                    <h3><?php _e('Statistics', 'rpg-ia'); ?></h3>
                    <div class="rpg-ia-stats-container">
                        <div class="rpg-ia-stats-section">
                            <h4><?php _e('Sessions', 'rpg-ia'); ?></h4>
                            <ul>
                                <li><?php echo sprintf(__('Total Sessions: %d', 'rpg-ia'), $session_stats['total']); ?></li>
                                <li><?php echo sprintf(__('Active Sessions: %d', 'rpg-ia'), $session_stats['active']); ?></li>
                                <li><?php echo sprintf(__('Paused Sessions: %d', 'rpg-ia'), $session_stats['paused']); ?></li>
                                <li><?php echo sprintf(__('Completed Sessions: %d', 'rpg-ia'), $session_stats['completed']); ?></li>
                                <li><?php echo sprintf(__('Total Players: %d', 'rpg-ia'), $session_stats['players']); ?></li>
                                <li><?php echo sprintf(__('Total Actions: %d', 'rpg-ia'), $session_stats['actions']); ?></li>
                                <li><?php echo sprintf(__('Tokens Used: %d', 'rpg-ia'), $session_stats['tokens_used']); ?></li>
                            </ul>
                        </div>
                        
                        <div class="rpg-ia-stats-section">
                            <h4><?php _e('Scenarios', 'rpg-ia'); ?></h4>
                            <ul>
                                <li><?php echo sprintf(__('Total Scenarios: %d', 'rpg-ia'), $scenario_stats['total']); ?></li>
                                <li><?php echo sprintf(__('Total Scenes: %d', 'rpg-ia'), $scenario_stats['scenes_count']); ?></li>
                            </ul>
                            
                            <?php if (!empty($scenario_stats['types'])) : ?>
                                <h5><?php _e('Scenario Types', 'rpg-ia'); ?></h5>
                                <ul>
                                    <?php foreach ($scenario_stats['types'] as $type => $count) : ?>
                                        <li><?php echo sprintf(__('%s: %d', 'rpg-ia'), ucfirst($type), $count); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="rpg-ia-admin-row">
            <div class="rpg-ia-admin-column">
                <div class="rpg-ia-admin-card">
                    <h3><?php _e('My Scenarios', 'rpg-ia'); ?></h3>
                    <?php if (empty($user_scenarios)) : ?>
                        <p><?php _e('You have no scenarios.', 'rpg-ia'); ?></p>
                    <?php else : ?>
                        <ul class="rpg-ia-scenario-list">
                            <?php foreach ($user_scenarios as $scenario) : ?>
                                <li>
                                    <strong><?php echo esc_html($scenario['title']); ?></strong>
                                    <span class="rpg-ia-scenario-type">
                                        <?php echo isset($scenario['type']) ? esc_html(ucfirst($scenario['type'])) : ''; ?>
                                    </span>
                                    <div class="rpg-ia-scenario-actions">
                                        <a href="<?php echo admin_url('admin.php?page=rpg-ia-scenario-management&scenario_id=' . $scenario['id']); ?>" class="button button-secondary">
                                            <?php _e('Edit', 'rpg-ia'); ?>
                                        </a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <p>
                        <a href="<?php echo admin_url('admin.php?page=rpg-ia-scenario-management'); ?>" class="button button-secondary">
                            <?php _e('Create New Scenario', 'rpg-ia'); ?>
                        </a>
                    </p>
                </div>
            </div>
            
            <div class="rpg-ia-admin-column">
                <div class="rpg-ia-admin-card">
                    <h3><?php _e('Quick Links', 'rpg-ia'); ?></h3>
                    <ul>
                        <li>
                            <a href="<?php echo admin_url('admin.php?page=rpg-ia-scenario-management'); ?>">
                                <?php _e('Scenario Management', 'rpg-ia'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('edit.php?post_type=rpgia_session'); ?>">
                                <?php _e('Session Management', 'rpg-ia'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('edit.php?post_type=rpgia_character'); ?>">
                                <?php _e('Character Management', 'rpg-ia'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('edit.php?post_type=rpgia_action_log'); ?>">
                                <?php _e('Action Logs', 'rpg-ia'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Rafraîchir les statistiques
        function refreshStats() {
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_get_gm_stats',
                    nonce: rpg_ia_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Mettre à jour les statistiques dans l'interface
                        var stats = response.data;
                        
                        // Mettre à jour les statistiques des sessions
                        $('.rpg-ia-stats-section:first-child li:nth-child(1)').text('<?php _e("Total Sessions: ", "rpg-ia"); ?>' + stats.sessions.total);
                        $('.rpg-ia-stats-section:first-child li:nth-child(2)').text('<?php _e("Active Sessions: ", "rpg-ia"); ?>' + stats.sessions.active);
                        $('.rpg-ia-stats-section:first-child li:nth-child(3)').text('<?php _e("Paused Sessions: ", "rpg-ia"); ?>' + stats.sessions.paused);
                        $('.rpg-ia-stats-section:first-child li:nth-child(4)').text('<?php _e("Completed Sessions: ", "rpg-ia"); ?>' + stats.sessions.completed);
                        $('.rpg-ia-stats-section:first-child li:nth-child(5)').text('<?php _e("Total Players: ", "rpg-ia"); ?>' + stats.sessions.players);
                        $('.rpg-ia-stats-section:first-child li:nth-child(6)').text('<?php _e("Total Actions: ", "rpg-ia"); ?>' + stats.sessions.actions);
                        $('.rpg-ia-stats-section:first-child li:nth-child(7)').text('<?php _e("Tokens Used: ", "rpg-ia"); ?>' + stats.sessions.tokens_used);
                        
                        // Mettre à jour les statistiques des scénarios
                        $('.rpg-ia-stats-section:nth-child(2) li:nth-child(1)').text('<?php _e("Total Scenarios: ", "rpg-ia"); ?>' + stats.scenarios.total);
                        $('.rpg-ia-stats-section:nth-child(2) li:nth-child(2)').text('<?php _e("Total Scenes: ", "rpg-ia"); ?>' + stats.scenarios.scenes_count);
                    }
                }
            });
        }
        
        // Rafraîchir les statistiques toutes les 60 secondes
        setInterval(refreshStats, 60000);
    });
</script>