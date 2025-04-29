<?php
/**
 * Affiche le tableau de bord du plugin.
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/public/partials
 */

// Si ce fichier est appelé directement, on sort
if (!defined('WPINC')) {
    die;
}

// Récupérer l'utilisateur courant
$current_user = wp_get_current_user();

// Récupérer les statistiques
$characters_count = count_user_posts($current_user->ID, 'rpgia_character');
$sessions_count = 0;

// Récupérer les sessions auxquelles l'utilisateur participe
global $wpdb;
$sessions_table = $wpdb->prefix . 'rpgia_session_players';
$sessions_count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $sessions_table WHERE user_id = %d",
    $current_user->ID
));

// Récupérer les sessions actives
$active_sessions = $wpdb->get_results($wpdb->prepare(
    "SELECT s.ID, s.post_title, m.meta_value as game_master_id
     FROM $wpdb->posts s
     JOIN $wpdb->postmeta m ON s.ID = m.post_id AND m.meta_key = '_rpgia_game_master'
     JOIN $sessions_table sp ON s.ID = sp.session_id
     WHERE s.post_type = 'rpgia_session'
     AND s.post_status = 'publish'
     AND sp.user_id = %d
     ORDER BY s.post_date DESC
     LIMIT 5",
    $current_user->ID
));

// Récupérer les dernières actions
$action_logs = $wpdb->get_results($wpdb->prepare(
    "SELECT a.ID, a.post_title, a.post_date, m1.meta_value as session_id, m2.meta_value as character_id
     FROM $wpdb->posts a
     JOIN $wpdb->postmeta m1 ON a.ID = m1.post_id AND m1.meta_key = '_rpgia_session_id'
     JOIN $wpdb->postmeta m2 ON a.ID = m2.post_id AND m2.meta_key = '_rpgia_character_id'
     JOIN $wpdb->posts c ON c.ID = m2.meta_value
     WHERE a.post_type = 'rpgia_action_log'
     AND a.post_status = 'publish'
     AND c.post_author = %d
     ORDER BY a.post_date DESC
     LIMIT 5",
    $current_user->ID
));
?>

<div class="rpg-ia-container">
    <h1><?php _e('RPG-IA Dashboard', 'rpg-ia'); ?></h1>
    
    <div class="rpg-ia-nav">
        <a href="<?php echo get_permalink(get_option('rpg_ia_page_rpg-ia-dashboard')); ?>" class="rpg-ia-nav-item active"><?php _e('Dashboard', 'rpg-ia'); ?></a>
        <a href="<?php echo get_permalink(get_option('rpg_ia_page_rpg-ia-characters')); ?>" class="rpg-ia-nav-item"><?php _e('Characters', 'rpg-ia'); ?></a>
        <a href="<?php echo get_permalink(get_option('rpg_ia_page_rpg-ia-sessions')); ?>" class="rpg-ia-nav-item"><?php _e('Sessions', 'rpg-ia'); ?></a>
    </div>
    
    <div class="rpg-ia-messages"></div>
    
    <div class="rpg-ia-row">
        <div class="rpg-ia-column">
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('Welcome', 'rpg-ia'); ?>, <?php echo esc_html($current_user->display_name); ?>!</h2>
                </div>
                <div class="rpg-ia-card-content">
                    <p><?php _e('Welcome to RPG-IA, a role-playing game with an AI as the game master.', 'rpg-ia'); ?></p>
                    <p><?php _e('Here you can create characters, join game sessions, and play with other players.', 'rpg-ia'); ?></p>
                    
                    <h3><?php _e('Your Statistics', 'rpg-ia'); ?></h3>
                    <ul>
                        <li><?php echo sprintf(__('Characters: %d', 'rpg-ia'), $characters_count); ?></li>
                        <li><?php echo sprintf(__('Sessions: %d', 'rpg-ia'), $sessions_count); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('Quick Actions', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <div class="rpg-ia-button-group">
                        <a href="<?php echo add_query_arg('action', 'new', get_permalink(get_option('rpg_ia_page_rpg-ia-characters'))); ?>" class="rpg-ia-button"><?php _e('Create Character', 'rpg-ia'); ?></a>
                        <a href="<?php echo add_query_arg('action', 'new', get_permalink(get_option('rpg_ia_page_rpg-ia-sessions'))); ?>" class="rpg-ia-button"><?php _e('Create Session', 'rpg-ia'); ?></a>
                        <a href="<?php echo get_permalink(get_option('rpg_ia_page_rpg-ia-sessions')); ?>" class="rpg-ia-button rpg-ia-button-secondary"><?php _e('Join Session', 'rpg-ia'); ?></a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="rpg-ia-column">
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('Active Sessions', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <?php if (empty($active_sessions)) : ?>
                        <p><?php _e('You are not participating in any active sessions.', 'rpg-ia'); ?></p>
                    <?php else : ?>
                        <ul class="rpg-ia-sessions-list">
                            <?php foreach ($active_sessions as $session) : ?>
                                <?php
                                $game_master = get_userdata($session->game_master_id);
                                $game_master_name = $game_master ? $game_master->display_name : __('Unknown', 'rpg-ia');
                                ?>
                                <li>
                                    <a href="<?php echo add_query_arg('session_id', $session->ID, get_permalink(get_option('rpg_ia_page_rpg-ia-play'))); ?>">
                                        <?php echo esc_html($session->post_title); ?>
                                    </a>
                                    <span class="rpg-ia-session-gm"><?php echo sprintf(__('GM: %s', 'rpg-ia'), esc_html($game_master_name)); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="rpg-ia-card-footer">
                    <a href="<?php echo get_permalink(get_option('rpg_ia_page_rpg-ia-sessions')); ?>" class="rpg-ia-button rpg-ia-button-secondary"><?php _e('View All Sessions', 'rpg-ia'); ?></a>
                </div>
            </div>
            
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('Recent Activity', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <?php if (empty($action_logs)) : ?>
                        <p><?php _e('No recent activity.', 'rpg-ia'); ?></p>
                    <?php else : ?>
                        <ul class="rpg-ia-activity-list">
                            <?php foreach ($action_logs as $log) : ?>
                                <?php
                                $session = get_post($log->session_id);
                                $character = get_post($log->character_id);
                                $session_name = $session ? $session->post_title : __('Unknown Session', 'rpg-ia');
                                $character_name = $character ? $character->post_title : __('Unknown Character', 'rpg-ia');
                                $date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->post_date));
                                ?>
                                <li>
                                    <span class="rpg-ia-activity-date"><?php echo esc_html($date); ?></span>
                                    <span class="rpg-ia-activity-content">
                                        <?php echo sprintf(__('%s in %s', 'rpg-ia'), esc_html($character_name), esc_html($session_name)); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>