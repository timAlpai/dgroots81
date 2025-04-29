<?php
/**
 * Affiche l'interface du maître de jeu en jeu.
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

// Récupérer l'ID de la session s'il est spécifié
$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;

// Si aucun ID de session n'est spécifié, afficher la liste des sessions
if (!$session_id) {
    // Récupérer l'ID de l'utilisateur
    $user_id = get_current_user_id();
    
    // Récupérer les sessions du maître de jeu
    $gm_sessions = $game_master->get_gm_sessions($user_id);
    ?>
    
    <div class="wrap">
        <h1><?php _e('Game Master Interface', 'rpg-ia'); ?></h1>
        
        <div class="rpg-ia-admin-card">
            <h2><?php _e('Select a Session', 'rpg-ia'); ?></h2>
            
            <?php if (empty($gm_sessions)) : ?>
                <p><?php _e('You have no active sessions.', 'rpg-ia'); ?></p>
                <p>
                    <a href="<?php echo admin_url('post-new.php?post_type=rpgia_session'); ?>" class="button button-primary">
                        <?php _e('Create New Session', 'rpg-ia'); ?>
                    </a>
                </p>
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
                <a href="<?php echo admin_url('admin.php?page=rpg-ia-gm-dashboard'); ?>" class="button button-secondary">
                    <?php _e('Back to Dashboard', 'rpg-ia'); ?>
                </a>
            </p>
        </div>
    </div>
    
    <?php
    return;
}

// Récupérer les détails de la session
$session = $session_manager->get_session($session_id);

// Vérifier si la session existe
if (is_wp_error($session)) {
    ?>
    <div class="wrap">
        <h1><?php _e('Game Master Interface', 'rpg-ia'); ?></h1>
        
        <div class="rpg-ia-admin-card">
            <h2><?php _e('Error', 'rpg-ia'); ?></h2>
            <p><?php _e('The specified session does not exist or you do not have permission to access it.', 'rpg-ia'); ?></p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=rpg-ia-gm-interface'); ?>" class="button button-secondary">
                    <?php _e('Back to Session List', 'rpg-ia'); ?>
                </a>
            </p>
        </div>
    </div>
    <?php
    return;
}

// Récupérer les joueurs de la session
$players = $game_master->get_session_players($session_id);

// Récupérer les personnages des joueurs
$characters = $game_master->get_session_characters($session_id);

// Récupérer le scénario de la session
$scenario = $game_master->get_session_scenario($session_id);

// Récupérer les scènes du scénario si un scénario est associé
$scenes = array();
if ($scenario) {
    $scenes = $scenario_manager->get_scenario_scenes($scenario['id']);
    if (is_wp_error($scenes)) {
        $scenes = array();
    }
}

// Récupérer la scène actuelle
$current_scene = $game_master->get_session_current_scene($session_id);

// Récupérer les actions de la session
$actions = $session_manager->get_session_actions($session_id);
if (is_wp_error($actions)) {
    $actions = array();
}

// Trier les actions par date (la plus récente en premier)
usort($actions, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>
<div class="wrap">
    <h1><?php echo sprintf(__('Game Master Interface: %s', 'rpg-ia'), esc_html($session['name'])); ?></h1>
    
    <div class="rpg-ia-gm-interface">
        <div class="rpg-ia-admin-row">
            <div class="rpg-ia-admin-column rpg-ia-admin-column-left">
                <div class="rpg-ia-admin-card rpg-ia-gm-controls">
                    <h2><?php _e('Session Controls', 'rpg-ia'); ?></h2>
                    
                    <div class="rpg-ia-session-status-controls">
                        <span class="rpg-ia-session-status-label"><?php _e('Status:', 'rpg-ia'); ?></span>
                        <span class="rpg-ia-session-status rpg-ia-status-<?php echo esc_attr($session['status']); ?>">
                            <?php echo esc_html(ucfirst($session['status'])); ?>
                        </span>
                        
                        <div class="rpg-ia-session-status-actions">
                            <?php if ($session['status'] == 'active') : ?>
                                <button type="button" id="rpg-ia-pause-session" class="button button-secondary" data-session-id="<?php echo $session_id; ?>" data-status="paused">
                                    <?php _e('Pause Session', 'rpg-ia'); ?>
                                </button>
                                <button type="button" id="rpg-ia-complete-session" class="button button-secondary rpg-ia-confirm-action" data-session-id="<?php echo $session_id; ?>" data-status="completed" data-confirm-message="<?php _e('Are you sure you want to complete this session? This action cannot be undone.', 'rpg-ia'); ?>">
                                    <?php _e('Complete Session', 'rpg-ia'); ?>
                                </button>
                            <?php elseif ($session['status'] == 'paused') : ?>
                                <button type="button" id="rpg-ia-resume-session" class="button button-primary" data-session-id="<?php echo $session_id; ?>" data-status="active">
                                    <?php _e('Resume Session', 'rpg-ia'); ?>
                                </button>
                                <button type="button" id="rpg-ia-complete-session" class="button button-secondary rpg-ia-confirm-action" data-session-id="<?php echo $session_id; ?>" data-status="completed" data-confirm-message="<?php _e('Are you sure you want to complete this session? This action cannot be undone.', 'rpg-ia'); ?>">
                                    <?php _e('Complete Session', 'rpg-ia'); ?>
                                </button>
                            <?php else : ?>
                                <p><?php _e('This session is completed and cannot be modified.', 'rpg-ia'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($scenario && !empty($scenes)) : ?>
                        <div class="rpg-ia-scene-controls">
                            <h3><?php _e('Current Scene', 'rpg-ia'); ?></h3>
                            
                            <div class="rpg-ia-current-scene">
                                <?php if ($current_scene) : ?>
                                    <p>
                                        <strong><?php echo esc_html($current_scene['title']); ?></strong>
                                        <span class="rpg-ia-scene-type-badge"><?php echo esc_html(ucfirst($current_scene['type'])); ?></span>
                                    </p>
                                    <p><?php echo esc_html($current_scene['description']); ?></p>
                                <?php else : ?>
                                    <p><?php _e('No scene is currently active.', 'rpg-ia'); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="rpg-ia-scene-selector">
                                <label for="rpg-ia-scene-select"><?php _e('Change Scene:', 'rpg-ia'); ?></label>
                                <select id="rpg-ia-scene-select">
                                    <option value=""><?php _e('Select a scene', 'rpg-ia'); ?></option>
                                    <?php foreach ($scenes as $scene) : ?>
                                        <option value="<?php echo esc_attr($scene['id']); ?>" <?php echo ($current_scene && $current_scene['id'] == $scene['id']) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($scene['title']); ?> (<?php echo esc_html(ucfirst($scene['type'])); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" id="rpg-ia-change-scene" class="button button-secondary" data-session-id="<?php echo $session_id; ?>">
                                    <?php _e('Change', 'rpg-ia'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="rpg-ia-admin-card rpg-ia-gm-narration">
                    <h2><?php _e('Narration Control', 'rpg-ia'); ?></h2>
                    
                    <div class="rpg-ia-narration-override">
                        <h3><?php _e('Override Narration', 'rpg-ia'); ?></h3>
                        <p><?php _e('Use this to directly intervene in the narrative.', 'rpg-ia'); ?></p>
                        
                        <form id="rpg-ia-narration-form">
                            <div class="rpg-ia-form-row">
                                <label for="rpg-ia-narration-type"><?php _e('Type:', 'rpg-ia'); ?></label>
                                <select id="rpg-ia-narration-type" name="type">
                                    <option value="narration"><?php _e('Narration', 'rpg-ia'); ?></option>
                                    <option value="dialog"><?php _e('Dialog', 'rpg-ia'); ?></option>
                                    <option value="event"><?php _e('Event', 'rpg-ia'); ?></option>
                                </select>
                            </div>
                            
                            <div class="rpg-ia-form-row">
                                <label for="rpg-ia-narration-content"><?php _e('Content:', 'rpg-ia'); ?></label>
                                <textarea id="rpg-ia-narration-content" name="content" rows="5" placeholder="<?php _e('Enter your narration here...', 'rpg-ia'); ?>"></textarea>
                            </div>
                            
                            <div class="rpg-ia-form-actions">
                                <button type="button" id="rpg-ia-submit-narration" class="button button-primary" data-session-id="<?php echo $session_id; ?>">
                                    <?php _e('Submit', 'rpg-ia'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="rpg-ia-recent-narration">
                        <h3><?php _e('Recent Narration', 'rpg-ia'); ?></h3>
                        
                        <div id="rpg-ia-narration-history">
                            <?php if (empty($actions)) : ?>
                                <p><?php _e('No actions have been recorded yet.', 'rpg-ia'); ?></p>
                            <?php else : ?>
                                <?php foreach (array_slice($actions, 0, 5) as $action) : ?>
                                    <div class="rpg-ia-narration-entry">
                                        <div class="rpg-ia-narration-header">
                                            <span class="rpg-ia-narration-time">
                                                <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($action['created_at'])); ?>
                                            </span>
                                            <span class="rpg-ia-narration-type">
                                                <?php echo isset($action['type']) ? esc_html(ucfirst($action['type'])) : __('Action', 'rpg-ia'); ?>
                                            </span>
                                            <?php if (isset($action['character_name'])) : ?>
                                                <span class="rpg-ia-narration-character">
                                                    <?php echo esc_html($action['character_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="rpg-ia-narration-content">
                                            <?php echo wpautop(esc_html($action['content'])); ?>
                                        </div>
                                        <?php if (isset($action['result'])) : ?>
                                            <div class="rpg-ia-narration-result">
                                                <?php echo wpautop(esc_html($action['result'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="rpg-ia-form-actions">
                            <button type="button" id="rpg-ia-refresh-narration" class="button button-secondary" data-session-id="<?php echo $session_id; ?>">
                                <?php _e('Refresh', 'rpg-ia'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="rpg-ia-admin-column rpg-ia-admin-column-right">
                <div class="rpg-ia-admin-card rpg-ia-gm-players">
                    <h2><?php _e('Players', 'rpg-ia'); ?></h2>
                    
                    <?php if (empty($players)) : ?>
                        <p><?php _e('No players have joined this session yet.', 'rpg-ia'); ?></p>
                    <?php else : ?>
                        <div class="rpg-ia-players-list">
                            <?php foreach ($players as $player) : ?>
                                <?php
                                // Trouver le personnage correspondant
                                $character = null;
                                foreach ($characters as $char) {
                                    if (isset($char['id']) && isset($player['character_id']) && $char['id'] == $player['character_id']) {
                                        $character = $char;
                                        break;
                                    }
                                }
                                ?>
                                <div class="rpg-ia-player-card">
                                    <div class="rpg-ia-player-header">
                                        <h3><?php echo isset($player['username']) ? esc_html($player['username']) : __('Unknown Player', 'rpg-ia'); ?></h3>
                                        <?php if ($character) : ?>
                                            <span class="rpg-ia-player-character">
                                                <?php echo esc_html($character['name']); ?>
                                                (<?php echo isset($character['class']) ? esc_html($character['class']) : __('Unknown Class', 'rpg-ia'); ?>)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($character) : ?>
                                        <div class="rpg-ia-player-stats">
                                            <div class="rpg-ia-player-stat">
                                                <span class="rpg-ia-stat-label"><?php _e('HP:', 'rpg-ia'); ?></span>
                                                <span class="rpg-ia-stat-value">
                                                    <?php echo isset($character['current_hp']) ? esc_html($character['current_hp']) : '?'; ?> / 
                                                    <?php echo isset($character['max_hp']) ? esc_html($character['max_hp']) : '?'; ?>
                                                </span>
                                            </div>
                                            
                                            <div class="rpg-ia-player-stat">
                                                <span class="rpg-ia-stat-label"><?php _e('AC:', 'rpg-ia'); ?></span>
                                                <span class="rpg-ia-stat-value">
                                                    <?php echo isset($character['armor_class']) ? esc_html($character['armor_class']) : '?'; ?>
                                                </span>
                                            </div>
                                            
                                            <div class="rpg-ia-player-stat">
                                                <span class="rpg-ia-stat-label"><?php _e('Level:', 'rpg-ia'); ?></span>
                                                <span class="rpg-ia-stat-value">
                                                    <?php echo isset($character['level']) ? esc_html($character['level']) : '?'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="rpg-ia-player-actions">
                                            <button type="button" class="button button-secondary rpg-ia-view-character" data-character-id="<?php echo esc_attr($character['id']); ?>">
                                                <?php _e('View Character', 'rpg-ia'); ?>
                                            </button>
                                        </div>
                                    <?php else : ?>
                                        <p><?php _e('No character selected.', 'rpg-ia'); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="rpg-ia-admin-card rpg-ia-gm-resources">
                    <h2><?php _e('Resources', 'rpg-ia'); ?></h2>
                    
                    <div class="rpg-ia-resources-tabs">
                        <div class="rpg-ia-tabs-nav">
                            <button type="button" class="rpg-ia-tab-button active" data-tab="scenario">
                                <?php _e('Scenario', 'rpg-ia'); ?>
                            </button>
                            <button type="button" class="rpg-ia-tab-button" data-tab="npcs">
                                <?php _e('NPCs', 'rpg-ia'); ?>
                            </button>
                            <button type="button" class="rpg-ia-tab-button" data-tab="monsters">
                                <?php _e('Monsters', 'rpg-ia'); ?>
                            </button>
                            <button type="button" class="rpg-ia-tab-button" data-tab="items">
                                <?php _e('Items', 'rpg-ia'); ?>
                            </button>
                        </div>
                        
                        <div class="rpg-ia-tabs-content">
                            <div class="rpg-ia-tab-pane active" id="rpg-ia-tab-scenario">
                                <?php if ($scenario) : ?>
                                    <h3><?php echo esc_html($scenario['title']); ?></h3>
                                    <p><?php echo esc_html($scenario['description']); ?></p>
                                    
                                    <?php if (!empty($scenes)) : ?>
                                        <h4><?php _e('Scenes', 'rpg-ia'); ?></h4>
                                        <ul class="rpg-ia-scene-list">
                                            <?php foreach ($scenes as $scene) : ?>
                                                <li class="<?php echo ($current_scene && $current_scene['id'] == $scene['id']) ? 'rpg-ia-current-scene' : ''; ?>">
                                                    <strong><?php echo esc_html($scene['title']); ?></strong>
                                                    <span class="rpg-ia-scene-type-badge"><?php echo esc_html(ucfirst($scene['type'])); ?></span>
                                                    <p><?php echo esc_html($scene['description']); ?></p>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <p><?php _e('No scenario is associated with this session.', 'rpg-ia'); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="rpg-ia-tab-pane" id="rpg-ia-tab-npcs">
                                <p><?php _e('NPCs will be displayed here.', 'rpg-ia'); ?></p>
                                <!-- Contenu à implémenter dans une future version -->
                            </div>
                            
                            <div class="rpg-ia-tab-pane" id="rpg-ia-tab-monsters">
                                <p><?php _e('Monsters will be displayed here.', 'rpg-ia'); ?></p>
                                <!-- Contenu à implémenter dans une future version -->
                            </div>
                            
                            <div class="rpg-ia-tab-pane" id="rpg-ia-tab-items">
                                <p><?php _e('Items will be displayed here.', 'rpg-ia'); ?></p>
                                <!-- Contenu à implémenter dans une future version -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="rpg-ia-admin-row">
            <div class="rpg-ia-admin-column">
                <div class="rpg-ia-admin-card rpg-ia-gm-actions">
                    <h2><?php _e('Quick Actions', 'rpg-ia'); ?></h2>
                    
                    <div class="rpg-ia-quick-actions">
                        <button type="button" id="rpg-ia-add-combat" class="button button-secondary">
                            <?php _e('Add Combat', 'rpg-ia'); ?>
                        </button>
                        <button type="button" id="rpg-ia-add-encounter" class="button button-secondary">
                            <?php _e('Add Encounter', 'rpg-ia'); ?>
                        </button>
                        <button type="button" id="rpg-ia-add-trap" class="button button-secondary">
                            <?php _e('Add Trap', 'rpg-ia'); ?>
                        </button>
                        <button type="button" id="rpg-ia-add-reward" class="button button-secondary">
                            <?php _e('Add Reward', 'rpg-ia'); ?>
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=rpg-ia-gm-dashboard'); ?>" class="button button-secondary">
                            <?php _e('Back to Dashboard', 'rpg-ia'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour afficher les détails d'un personnage -->
<div id="rpg-ia-character-modal" class="rpg-ia-modal" style="display: none;">
    <div class="rpg-ia-modal-content">
        <span class="rpg-ia-modal-close">&times;</span>
        <h3><?php _e('Character Details', 'rpg-ia'); ?></h3>
        
        <div id="rpg-ia-character-details">
            <p><?php _e('Loading character details...', 'rpg-ia'); ?></p>
        </div>
    </div>
</div>

<!-- Modal pour les actions rapides -->
<div id="rpg-ia-quick-action-modal" class="rpg-ia-modal" style="display: none;">
    <div class="rpg-ia-modal-content">
        <span class="rpg-ia-modal-close">&times;</span>
        <h3 id="rpg-ia-quick-action-title"><?php _e('Add Event', 'rpg-ia'); ?></h3>
        
        <form id="rpg-ia-quick-action-form">
            <input type="hidden" id="rpg-ia-quick-action-type" value="">
            
            <div class="rpg-ia-form-row">
                <label for="rpg-ia-quick-action-content"><?php _e('Description:', 'rpg-ia'); ?></label>
                <textarea id="rpg-ia-quick-action-content" rows="5" placeholder="<?php _e('Enter the description of the event...', 'rpg-ia'); ?>"></textarea>
            </div>
            
            <div class="rpg-ia-form-actions">
                <button type="button" id="rpg-ia-submit-quick-action" class="button button-primary" data-session-id="<?php echo $session_id; ?>">
                    <?php _e('Submit', 'rpg-ia'); ?>
                </button>
                <button type="button" id="rpg-ia-cancel-quick-action" class="button button-secondary">
                    <?php _e('Cancel', 'rpg-ia'); ?>
                </button>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Variables globales
        var sessionId = <?php echo $session_id; ?>;
        var lastActionTimestamp = <?php echo !empty($actions) ? strtotime($actions[0]['created_at']) : 0; ?>;
        
        // Mettre à jour le statut de la session
        $('.rpg-ia-session-status-actions button').on('click', function() {
            var status = $(this).data('status');
            
            // Confirmer si nécessaire
            if ($(this).hasClass('rpg-ia-confirm-action') && !confirm($(this).data('confirm-message'))) {
                return;
            }
            
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_update_session_status',
                    nonce: rpg_ia_admin.nonce,
                    session_id: sessionId,
                    status: status
                },
                beforeSend: function() {
                    $('.rpg-ia-session-status-actions button').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        // Mettre à jour l'affichage du statut
                        $('.rpg-ia-session-status')
                            .removeClass('rpg-ia-status-active rpg-ia-status-paused rpg-ia-status-completed')
                            .addClass('rpg-ia-status-' + status)
                            .text(status.charAt(0).toUpperCase() + status.slice(1));
                        
                        // Recharger la page pour mettre à jour les contrôles
                        location.reload();
                    } else {
                        alert('<?php _e('Error updating session status: ', 'rpg-ia'); ?>' + response.data);
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred while updating the session status.', 'rpg-ia'); ?>');
                },
                complete: function() {
                    $('.rpg-ia-session-status-actions button').prop('disabled', false);
                }
            });
        });
        
        // Changer la scène
        $('#rpg-ia-change-scene').on('click', function() {
            var sceneId = $('#rpg-ia-scene-select').val();
            
            if (!sceneId) {
                alert('<?php _e('Please select a scene.', 'rpg-ia'); ?>');
                return;
            }
            
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_change_session_scene',
                    nonce: rpg_ia_admin.nonce,
                    session_id: sessionId,
                    scene_id: sceneId
                },
                beforeSend: function() {
                    $('#rpg-ia-change-scene').prop('disabled', true).text('<?php _e('Changing...', 'rpg-ia'); ?>');
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Scene changed successfully.', 'rpg-ia'); ?>');
                        // Recharger la page pour mettre à jour l'affichage
                        location.reload();
                    } else {
                        alert('<?php _e('Error changing scene: ', 'rpg-ia'); ?>' + response.data);
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred while changing the scene.', 'rpg-ia'); ?>');
                },
                complete: function() {
                    $('#rpg-ia-change-scene').prop('disabled', false).text('<?php _e('Change', 'rpg-ia'); ?>');
                }
            });
        });
        
        // Soumettre une narration
        $('#rpg-ia-submit-narration').on('click', function() {
            var type = $('#rpg-ia-narration-type').val();
            var content = $('#rpg-ia-narration-content').val();
            
            if (!content) {
                alert('<?php _e('Please enter narration content.', 'rpg-ia'); ?>');
                return;
            }
            
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_override_narration',
                    nonce: rpg_ia_admin.nonce,
                    session_id: sessionId,
                    content: content,
                    type: type
                },
                beforeSend: function() {
                    $('#rpg-ia-submit-narration').prop('disabled', true).text('<?php _e('Submitting...', 'rpg-ia'); ?>');
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Narration submitted successfully.', 'rpg-ia'); ?>');
                        // Vider le formulaire
                        $('#rpg-ia-narration-content').val('');
                        // Rafraîchir l'historique des narrations
                        refreshNarrationHistory();
                    } else {
                        alert('<?php _e('Error submitting narration: ', 'rpg-ia'); ?>' + response.data);
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred while submitting the narration.', 'rpg-ia'); ?>');
                },
                complete: function() {
                    $('#rpg-ia-submit-narration').prop('disabled', false).text('<?php _e('Submit', 'rpg-ia'); ?>');
                }
            });
        });
        
        // Rafraîchir l'historique des narrations
        $('#rpg-ia-refresh-narration').on('click', function() {
            refreshNarrationHistory();
        });
        
        function refreshNarrationHistory() {
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_get_session_actions',
                    nonce: rpg_ia_admin.nonce,
                    session_id: sessionId,
                    timestamp: lastActionTimestamp
                },
                beforeSend: function() {
                    $('#rpg-ia-refresh-narration').prop('disabled', true).text('<?php _e('Refreshing...', 'rpg-ia'); ?>');
                },
                success: function(response) {
                    if (response.success) {
                        // Mettre à jour l'historique des narrations
                        if (response.data.length > 0) {
                            // Mettre à jour le timestamp de la dernière action
                            lastActionTimestamp = new Date(response.data[0].created_at).getTime() / 1000;
                            
                            // Vider l'historique si c'est la première fois
                            if ($('#rpg-ia-narration-history p').length === 1 && $('#rpg-ia-narration-history p').text() === '<?php _e('No actions have been recorded yet.', 'rpg-ia'); ?>') {
                                $('#rpg-ia-narration-history').empty();
                            }
                            
                            // Ajouter les nouvelles actions
                            var actionsHtml = '';
                            $.each(response.data, function(index, action) {
                                actionsHtml += '<div class="rpg-ia-narration-entry">';
                                actionsHtml += '<div class="rpg-ia-narration-header">';
                                actionsHtml += '<span class="rpg-ia-narration-time">' + formatDateTime(action.created_at) + '</span>';
                                actionsHtml += '<span class="rpg-ia-narration-type">' + (action.type ? action.type.charAt(0).toUpperCase() + action.type.slice(1) : '<?php _e('Action', 'rpg-ia'); ?>') + '</span>';
                                if (action.character_name) {
                                    actionsHtml += '<span class="rpg-ia-narration-character">' + action.character_name + '</span>';
                                }
                                actionsHtml += '</div>';
                                actionsHtml += '<div class="rpg-ia-narration-content">' + formatText(action.content) + '</div>';
                                if (action.result) {
                                    actionsHtml += '<div class="rpg-ia-narration-result">' + formatText(action.result) + '</div>';
                                }
                                actionsHtml += '</div>';
                            });
                            
                            // Ajouter les nouvelles actions au début de l'historique
                            $('#rpg-ia-narration-history').prepend(actionsHtml);
                        }
                    } else {
                        alert('<?php _e('Error refreshing narration history: ', 'rpg-ia'); ?>' + response.data);
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred while refreshing the narration history.', 'rpg-ia'); ?>');
                },
                complete: function() {
                    $('#rpg-ia-refresh-narration').prop('disabled', false).text('<?php _e('Refresh', 'rpg-ia'); ?>');
                }
            });
        }
        
        // Formater une date
        function formatDateTime(dateTimeString) {
            var date = new Date(dateTimeString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        }
        
        // Formater du texte
        function formatText(text) {
            return text.replace(/\n/g, '<br>');
        }
        
        // Afficher les détails d'un personnage
        $('.rpg-ia-view-character').on('click', function() {
            var characterId = $(this).data('character-id');
            
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_get_character',
                    nonce: rpg_ia_admin.nonce,
                    character_id: characterId
                },
                beforeSend: function() {
                    $('#rpg-ia-character-details').html('<p><?php _e('Loading character details...', 'rpg-ia'); ?></p>');
                    $('#rpg-ia-character-modal').show();
                },
                success: function(response) {
                    if (response.success) {
                        var character = response.data;
                        var detailsHtml = '';
                        
                        // Informations générales
                        detailsHtml += '<div class="rpg-ia-character-section">';
                        detailsHtml += '<h4><?php _e('General Information', 'rpg-ia'); ?></h4>';
                        detailsHtml += '<p><strong><?php _e('Name:', 'rpg-ia'); ?></strong> ' + character.name + '</p>';
                        detailsHtml += '<p><strong><?php _e('Class:', 'rpg-ia'); ?></strong> ' + (character.class || '<?php _e('Unknown', 'rpg-ia'); ?>') + '</p>';
                        detailsHtml += '<p><strong><?php _e('Level:', 'rpg-ia'); ?></strong> ' + (character.level || '<?php _e('Unknown', 'rpg-ia'); ?>') + '</p>';
                        detailsHtml += '</div>';
                        
                        // Caractéristiques
                        if (character.abilities) {
                            detailsHtml += '<div class="rpg-ia-character-section">';
                            detailsHtml += '<h4><?php _e('Abilities', 'rpg-ia'); ?></h4>';
                            detailsHtml += '<div class="rpg-ia-character-abilities">';
                            $.each(character.abilities, function(ability, value) {
                                var modifier = Math.floor((value - 10) / 2);
                                var modifierText = (modifier >= 0) ? '+' + modifier : modifier;
                                detailsHtml += '<div class="rpg-ia-character-ability">';
                                detailsHtml += '<span class="rpg-ia-ability-name">' + ability.toUpperCase() + '</span>';
                                detailsHtml += '<span class="rpg-ia-ability-value">' + value + '</span>';
                                detailsHtml += '<span class="rpg-ia-ability-modifier">' + modifierText + '</span>';
                                detailsHtml += '</div>';
                            });
                            detailsHtml += '</div>';
                            detailsHtml += '</div>';
                        }
                        
                        // Statistiques de combat
                        detailsHtml += '<div class="rpg-ia-character-section">';
                        detailsHtml += '<h4><?php _e('Combat Stats', 'rpg-ia'); ?></h4>';
                        detailsHtml += '<p><strong><?php _e('HP:', 'rpg-ia'); ?></strong> ' + (character.current_hp || '?') + ' / ' + (character.max_hp || '?') + '</p>';
                        detailsHtml += '<p><strong><?php _e('AC:', 'rpg-ia'); ?></strong> ' + (character.armor_class || '?') + '</p>';
                        detailsHtml += '</div>';
                        
                        // Équipement
                        if (character.equipment && character.equipment.length > 0) {
                            detailsHtml += '<div class="rpg-ia-character-section">';
                            detailsHtml += '<h4><?php _e('Equipment', 'rpg-ia'); ?></h4>';
                            detailsHtml += '<ul>';
                            $.each(character.equipment, function(index, item) {
                                detailsHtml += '<li>' + item.name + (item.quantity > 1 ? ' (' + item.quantity + ')' : '') + '</li>';
                            });
                            detailsHtml += '</ul>';
                            detailsHtml += '</div>';
                        }
                        
                        // Compétences
                        if (character.skills && character.skills.length > 0) {
                            detailsHtml += '<div class="rpg-ia-character-section">';
                            detailsHtml += '<h4><?php _e('Skills', 'rpg-ia'); ?></h4>';
                            detailsHtml += '<ul>';
                            $.each(character.skills, function(index, skill) {
                                detailsHtml += '<li>' + skill.name + (skill.proficient ? ' (<?php _e('Proficient', 'rpg-ia'); ?>)' : '') + '</li>';
                            });
                            detailsHtml += '</ul>';
                            detailsHtml += '</div>';
                        }
                        
                        // Biographie
                        if (character.biography) {
                            detailsHtml += '<div class="rpg-ia-character-section">';
                            detailsHtml += '<h4><?php _e('Biography', 'rpg-ia'); ?></h4>';
                            detailsHtml += '<p>' + formatText(character.biography) + '</p>';
                            detailsHtml += '</div>';
                        }
                        
                        $('#rpg-ia-character-details').html(detailsHtml);
                    } else {
                        $('#rpg-ia-character-details').html('<p><?php _e('Error loading character details: ', 'rpg-ia'); ?>' + response.data + '</p>');
                    }
                },
                error: function() {
                    $('#rpg-ia-character-details').html('<p><?php _e('An error occurred while loading the character details.', 'rpg-ia'); ?></p>');
                }
            });
        });
        
        // Fermer le modal
        $('.rpg-ia-modal-close').on('click', function() {
            $(this).closest('.rpg-ia-modal').hide();
        });
        
        // Fermer le modal en cliquant en dehors
        $(window).on('click', function(event) {
            if ($(event.target).hasClass('rpg-ia-modal')) {
                $('.rpg-ia-modal').hide();
            }
        });
        
        // Gérer les onglets
        $('.rpg-ia-tab-button').on('click', function() {
            var tab = $(this).data('tab');
            
            // Activer l'onglet
            $('.rpg-ia-tab-button').removeClass('active');
            $(this).addClass('active');
            
            // Afficher le contenu de l'onglet
            $('.rpg-ia-tab-pane').removeClass('active');
            $('#rpg-ia-tab-' + tab).addClass('active');
        });
        
        // Gérer les actions rapides
        $('#rpg-ia-add-combat, #rpg-ia-add-encounter, #rpg-ia-add-trap, #rpg-ia-add-reward').on('click', function() {
            var actionType = $(this).attr('id').replace('rpg-ia-add-', '');
            var actionTitle = '';
            
            switch (actionType) {
                case 'combat':
                    actionTitle = '<?php _e('Add Combat', 'rpg-ia'); ?>';
                    break;
                case 'encounter':
                    actionTitle = '<?php _e('Add Encounter', 'rpg-ia'); ?>';
                    break;
                case 'trap':
                    actionTitle = '<?php _e('Add Trap', 'rpg-ia'); ?>';
                    break;
                case 'reward':
                    actionTitle = '<?php _e('Add Reward', 'rpg-ia'); ?>';
                    break;
            }
            
            // Mettre à jour le modal
            $('#rpg-ia-quick-action-title').text(actionTitle);
            $('#rpg-ia-quick-action-type').val(actionType);
            $('#rpg-ia-quick-action-content').val('');
            
            // Afficher le modal
            $('#rpg-ia-quick-action-modal').show();
        });
        
        // Soumettre une action rapide
        $('#rpg-ia-submit-quick-action').on('click', function() {
            var actionType = $('#rpg-ia-quick-action-type').val();
            var content = $('#rpg-ia-quick-action-content').val();
            
            if (!content) {
                alert('<?php _e('Please enter a description.', 'rpg-ia'); ?>');
                return;
            }
            
            // Préparer le contenu en fonction du type d'action
            var formattedContent = '';
            switch (actionType) {
                case 'combat':
                    formattedContent = '<?php _e('Combat Encounter: ', 'rpg-ia'); ?>' + content;
                    break;
                case 'encounter':
                    formattedContent = '<?php _e('Encounter: ', 'rpg-ia'); ?>' + content;
                    break;
                case 'trap':
                    formattedContent = '<?php _e('Trap: ', 'rpg-ia'); ?>' + content;
                    break;
                case 'reward':
                    formattedContent = '<?php _e('Reward: ', 'rpg-ia'); ?>' + content;
                    break;
                default:
                    formattedContent = content;
            }
            
            // Soumettre l'action
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_override_narration',
                    nonce: rpg_ia_admin.nonce,
                    session_id: sessionId,
                    content: formattedContent,
                    type: 'event'
                },
                beforeSend: function() {
                    $('#rpg-ia-submit-quick-action').prop('disabled', true).text('<?php _e('Submitting...', 'rpg-ia'); ?>');
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Action submitted successfully.', 'rpg-ia'); ?>');
                        // Fermer le modal
                        $('#rpg-ia-quick-action-modal').hide();
                        // Rafraîchir l'historique des narrations
                        refreshNarrationHistory();
                    } else {
                        alert('<?php _e('Error submitting action: ', 'rpg-ia'); ?>' + response.data);
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred while submitting the action.', 'rpg-ia'); ?>');
                },
                complete: function() {
                    $('#rpg-ia-submit-quick-action').prop('disabled', false).text('<?php _e('Submit', 'rpg-ia'); ?>');
                }
            });
        });
        
        // Annuler une action rapide
        $('#rpg-ia-cancel-quick-action').on('click', function() {
            $('#rpg-ia-quick-action-modal').hide();
        });
        
        // Rafraîchir automatiquement l'historique des narrations toutes les 30 secondes
        setInterval(refreshNarrationHistory, 30000);
    });
</script>