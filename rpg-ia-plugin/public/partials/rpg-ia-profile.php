<?php
/**
 * Affiche le profil utilisateur du plugin.
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

// Récupérer les informations du profil depuis l'API
$auth_handler = new RPG_IA_Auth_Handler();
$api_client = new RPG_IA_API_Client();
$token = $auth_handler->get_token();

if ($token) {
    $api_client->set_token($token);
    $user_profile = $api_client->get_current_user();
} else {
    $user_profile = null;
}

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

// Récupérer le temps de jeu total
$game_time = get_user_meta($current_user->ID, '_rpgia_game_time', true);
$game_time = $game_time ? $game_time : 0;

// Récupérer le nombre de tokens utilisés
$tokens_used = get_user_meta($current_user->ID, '_rpgia_tokens_used', true);
$tokens_used = $tokens_used ? $tokens_used : 0;
?>

<div class="rpg-ia-container">
    <h1><?php _e('User Profile', 'rpg-ia'); ?></h1>
    
    <div class="rpg-ia-nav">
        <a href="<?php echo get_permalink(get_option('rpg_ia_page_rpg-ia-dashboard')); ?>" class="rpg-ia-nav-item"><?php _e('Dashboard', 'rpg-ia'); ?></a>
        <a href="<?php echo get_permalink(get_option('rpg_ia_page_rpg-ia-characters')); ?>" class="rpg-ia-nav-item"><?php _e('Characters', 'rpg-ia'); ?></a>
        <a href="<?php echo get_permalink(get_option('rpg_ia_page_rpg-ia-sessions')); ?>" class="rpg-ia-nav-item"><?php _e('Sessions', 'rpg-ia'); ?></a>
        <a href="<?php echo get_permalink(get_option('rpg_ia_page_rpg-ia-profile')); ?>" class="rpg-ia-nav-item active"><?php _e('Profile', 'rpg-ia'); ?></a>
    </div>
    
    <div class="rpg-ia-messages"></div>
    
    <div class="rpg-ia-row">
        <div class="rpg-ia-column">
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('Profile Information', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <form id="rpg-ia-profile-form" class="rpg-ia-form">
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-username" class="rpg-ia-form-label"><?php _e('Username', 'rpg-ia'); ?></label>
                            <input type="text" id="rpg-ia-username" name="username" class="rpg-ia-form-input" value="<?php echo esc_attr($current_user->user_login); ?>" disabled>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-email" class="rpg-ia-form-label"><?php _e('Email', 'rpg-ia'); ?></label>
                            <input type="email" id="rpg-ia-email" name="email" class="rpg-ia-form-input" value="<?php echo esc_attr($current_user->user_email); ?>">
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-display-name" class="rpg-ia-form-label"><?php _e('Display Name', 'rpg-ia'); ?></label>
                            <input type="text" id="rpg-ia-display-name" name="display_name" class="rpg-ia-form-input" value="<?php echo esc_attr($current_user->display_name); ?>">
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <button type="submit" class="rpg-ia-button"><?php _e('Update Profile', 'rpg-ia'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('Change Password', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <form id="rpg-ia-password-form" class="rpg-ia-form">
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-current-password" class="rpg-ia-form-label"><?php _e('Current Password', 'rpg-ia'); ?></label>
                            <input type="password" id="rpg-ia-current-password" name="current_password" class="rpg-ia-form-input" required>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-new-password" class="rpg-ia-form-label"><?php _e('New Password', 'rpg-ia'); ?></label>
                            <input type="password" id="rpg-ia-new-password" name="new_password" class="rpg-ia-form-input" required>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-confirm-password" class="rpg-ia-form-label"><?php _e('Confirm New Password', 'rpg-ia'); ?></label>
                            <input type="password" id="rpg-ia-confirm-password" name="confirm_password" class="rpg-ia-form-input" required>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <button type="submit" class="rpg-ia-button"><?php _e('Change Password', 'rpg-ia'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="rpg-ia-column">
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('Game Statistics', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <div class="rpg-ia-stats">
                        <div class="rpg-ia-stat">
                            <div class="rpg-ia-stat-name"><?php _e('Characters', 'rpg-ia'); ?></div>
                            <div class="rpg-ia-stat-value"><?php echo esc_html($characters_count); ?></div>
                        </div>
                        
                        <div class="rpg-ia-stat">
                            <div class="rpg-ia-stat-name"><?php _e('Sessions', 'rpg-ia'); ?></div>
                            <div class="rpg-ia-stat-value"><?php echo esc_html($sessions_count); ?></div>
                        </div>
                        
                        <div class="rpg-ia-stat">
                            <div class="rpg-ia-stat-name"><?php _e('Game Time', 'rpg-ia'); ?></div>
                            <div class="rpg-ia-stat-value"><?php echo esc_html(round($game_time / 60, 1)); ?> <?php _e('hours', 'rpg-ia'); ?></div>
                        </div>
                        
                        <div class="rpg-ia-stat">
                            <div class="rpg-ia-stat-name"><?php _e('Tokens Used', 'rpg-ia'); ?></div>
                            <div class="rpg-ia-stat-value"><?php echo esc_html($tokens_used); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($user_profile && !is_wp_error($user_profile)) : ?>
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('API Account Information', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <div class="rpg-ia-api-info">
                        <p><strong><?php _e('Username:', 'rpg-ia'); ?></strong> <?php echo esc_html($user_profile['username']); ?></p>
                        <p><strong><?php _e('Email:', 'rpg-ia'); ?></strong> <?php echo esc_html($user_profile['email']); ?></p>
                        <p><strong><?php _e('Account Created:', 'rpg-ia'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($user_profile['created_at']))); ?></p>
                        <?php if (isset($user_profile['is_active'])) : ?>
                        <p><strong><?php _e('Account Status:', 'rpg-ia'); ?></strong> <?php echo $user_profile['is_active'] ? __('Active', 'rpg-ia') : __('Inactive', 'rpg-ia'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('Account Actions', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <div class="rpg-ia-button-group">
                        <a href="#" id="rpg-ia-refresh-token" class="rpg-ia-button"><?php _e('Refresh Token', 'rpg-ia'); ?></a>
                        <a href="#" id="rpg-ia-logout" class="rpg-ia-button rpg-ia-button-danger"><?php _e('Logout', 'rpg-ia'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Formulaire de mise à jour du profil
        $('#rpg-ia-profile-form').on('submit', function(e) {
            e.preventDefault();
            
            var email = $('#rpg-ia-email').val();
            var displayName = $('#rpg-ia-display-name').val();
            
            if (!email) {
                showMessage('error', '<?php _e('Email is required.', 'rpg-ia'); ?>');
                return;
            }
            
            // Afficher le message de chargement
            showMessage('info', '<?php _e('Updating profile...', 'rpg-ia'); ?>');
            
            // Appeler l'API pour mettre à jour le profil
            $.ajax({
                url: rpg_ia_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_update_profile',
                    email: email,
                    display_name: displayName,
                    nonce: rpg_ia_public.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message);
                    } else {
                        showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    showMessage('error', '<?php _e('An error occurred while updating your profile.', 'rpg-ia'); ?>');
                }
            });
        });
        
        // Formulaire de changement de mot de passe
        $('#rpg-ia-password-form').on('submit', function(e) {
            e.preventDefault();
            
            var currentPassword = $('#rpg-ia-current-password').val();
            var newPassword = $('#rpg-ia-new-password').val();
            var confirmPassword = $('#rpg-ia-confirm-password').val();
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                showMessage('error', '<?php _e('All fields are required.', 'rpg-ia'); ?>');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showMessage('error', '<?php _e('New passwords do not match.', 'rpg-ia'); ?>');
                return;
            }
            
            // Afficher le message de chargement
            showMessage('info', '<?php _e('Changing password...', 'rpg-ia'); ?>');
            
            // Appeler l'API pour changer le mot de passe
            $.ajax({
                url: rpg_ia_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_change_password',
                    current_password: currentPassword,
                    new_password: newPassword,
                    nonce: rpg_ia_public.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message);
                        $('#rpg-ia-password-form')[0].reset();
                    } else {
                        showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    showMessage('error', '<?php _e('An error occurred while changing your password.', 'rpg-ia'); ?>');
                }
            });
        });
        
        // Bouton de rafraîchissement du token
        $('#rpg-ia-refresh-token').on('click', function(e) {
            e.preventDefault();
            
            // Afficher le message de chargement
            showMessage('info', '<?php _e('Refreshing token...', 'rpg-ia'); ?>');
            
            // Appeler l'API pour rafraîchir le token
            $.ajax({
                url: rpg_ia_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_refresh_token',
                    nonce: rpg_ia_public.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showMessage('success', response.data.message);
                    } else {
                        showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    showMessage('error', '<?php _e('An error occurred while refreshing your token.', 'rpg-ia'); ?>');
                }
            });
        });
        
        // Bouton de déconnexion
        $('#rpg-ia-logout').on('click', function(e) {
            e.preventDefault();
            
            // Supprimer le token
            localStorage.removeItem('rpg_ia_token');
            
            // Rediriger vers la page de connexion
            window.location.href = rpg_ia_public.login_url;
        });
        
        // Afficher un message
        function showMessage(type, message) {
            var messageContainer = $('.rpg-ia-messages');
            
            if (!messageContainer.length) {
                messageContainer = $('<div class="rpg-ia-messages"></div>');
                $('.rpg-ia-container').prepend(messageContainer);
            }
            
            var messageHtml = '<div class="rpg-ia-message rpg-ia-message-' + type + '">' + message + '</div>';
            
            messageContainer.html(messageHtml);
            
            // Faire disparaître le message après un délai (sauf pour les erreurs)
            if (type !== 'error') {
                setTimeout(function() {
                    messageContainer.empty();
                }, 5000);
            }
        }
    });
</script>