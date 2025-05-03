<?php
/**
 * Affiche le formulaire de connexion API du plugin.
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

// Récupérer l'utilisateur WordPress courant
$current_user = wp_get_current_user();

// Vérifier si l'utilisateur a déjà un compte API associé
$auth_handler = new RPG_IA_Auth_Handler();
$has_api_account = $auth_handler->has_api_account();
$api_username = get_user_meta($current_user->ID, 'rpg_ia_api_username', true);

// Nombre maximal de tentatives de connexion
$max_login_attempts = 5;
$login_attempts = get_user_meta($current_user->ID, 'rpg_ia_login_attempts', true) ?: 0;
$is_locked = $login_attempts >= $max_login_attempts;
$lockout_time = get_user_meta($current_user->ID, 'rpg_ia_lockout_time', true) ?: 0;

// Vérifier si le verrouillage doit être levé (après 30 minutes)
if ($is_locked && time() > $lockout_time + 1800) {
    $is_locked = false;
    update_user_meta($current_user->ID, 'rpg_ia_login_attempts', 0);
    update_user_meta($current_user->ID, 'rpg_ia_lockout_time', 0);
}
?>

<div class="rpg-ia-container">
    <h1><?php _e('RPG-IA API Authentication', 'rpg-ia'); ?></h1>
    
    <div class="rpg-ia-messages"></div>
    
    <div class="rpg-ia-row">
        <div class="rpg-ia-column">
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('API Authentication Required', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <p><?php _e('To use the RPG-IA features, you need to authenticate with the API.', 'rpg-ia'); ?></p>
                    
                    <?php if ($is_locked): ?>
                        <div class="rpg-ia-error-message">
                            <p><?php _e('Your account is temporarily locked due to too many failed login attempts. Please try again later.', 'rpg-ia'); ?></p>
                        </div>
                    <?php else: ?>
                        <?php if ($has_api_account): ?>
                            <p><?php echo sprintf(__('You already have an API account with username: %s', 'rpg-ia'), '<strong>' . esc_html($api_username) . '</strong>'); ?></p>
                            <form id="rpg-ia-api-login-form" class="rpg-ia-form">
                                <input type="hidden" id="rpg-ia-api-username" name="username" value="<?php echo esc_attr($api_username); ?>">
                                
                                <div class="rpg-ia-form-row">
                                    <label for="rpg-ia-api-password" class="rpg-ia-form-label"><?php _e('API Password', 'rpg-ia'); ?></label>
                                    <input type="password" id="rpg-ia-api-password" name="password" class="rpg-ia-form-input" required>
                                </div>
                                
                                <div class="rpg-ia-form-row">
                                    <button type="submit" class="rpg-ia-button"><?php _e('Login to API', 'rpg-ia'); ?></button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p><?php _e('You don\'t have an API account yet. Please create one below:', 'rpg-ia'); ?></p>
                            <form id="rpg-ia-api-register-form" class="rpg-ia-form">
                                <div class="rpg-ia-form-row">
                                    <label for="rpg-ia-api-reg-username" class="rpg-ia-form-label"><?php _e('API Username', 'rpg-ia'); ?></label>
                                    <input type="text" id="rpg-ia-api-reg-username" name="username" class="rpg-ia-form-input" value="<?php echo esc_attr($current_user->user_login); ?>" required>
                                    <p class="rpg-ia-form-help"><?php _e('We recommend using your WordPress username.', 'rpg-ia'); ?></p>
                                </div>
                                
                                <div class="rpg-ia-form-row">
                                    <label for="rpg-ia-api-reg-email" class="rpg-ia-form-label"><?php _e('Email', 'rpg-ia'); ?></label>
                                    <input type="email" id="rpg-ia-api-reg-email" name="email" class="rpg-ia-form-input" value="<?php echo esc_attr($current_user->user_email); ?>" required>
                                </div>
                                
                                <div class="rpg-ia-form-row">
                                    <label for="rpg-ia-api-reg-password" class="rpg-ia-form-label"><?php _e('API Password', 'rpg-ia'); ?></label>
                                    <input type="password" id="rpg-ia-api-reg-password" name="password" class="rpg-ia-form-input" required>
                                    <p class="rpg-ia-form-help"><?php _e('Password must be at least 8 characters long.', 'rpg-ia'); ?></p>
                                </div>
                                
                                <div class="rpg-ia-form-row">
                                    <label for="rpg-ia-api-reg-password-confirm" class="rpg-ia-form-label"><?php _e('Confirm API Password', 'rpg-ia'); ?></label>
                                    <input type="password" id="rpg-ia-api-reg-password-confirm" name="password_confirm" class="rpg-ia-form-input" required>
                                </div>
                                
                                <div class="rpg-ia-form-row">
                                    <button type="submit" class="rpg-ia-button"><?php _e('Create API Account', 'rpg-ia'); ?></button>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="rpg-ia-column">
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('Important Information', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <p><strong><?php _e('One API Account Per User:', 'rpg-ia'); ?></strong> <?php _e('Each WordPress user (except administrators) can only have one API account.', 'rpg-ia'); ?></p>
                    <p><strong><?php _e('Security:', 'rpg-ia'); ?></strong> <?php _e('Your API credentials are used to access the RPG-IA backend services. Keep them secure.', 'rpg-ia'); ?></p>
                    <p><strong><?php _e('Password Recovery:', 'rpg-ia'); ?></strong> <?php _e('If you forget your API password, please contact an administrator.', 'rpg-ia'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Gestion du formulaire de connexion API
        $('#rpg-ia-api-login-form').on('submit', function(e) {
            e.preventDefault();
            
            var username = $('#rpg-ia-api-username').val();
            var password = $('#rpg-ia-api-password').val();
            
            if (!username || !password) {
                showMessage('error', 'Please enter your API credentials.');
                return;
            }
            
            // Afficher le message de chargement
            showMessage('info', 'Authenticating with API...');
            
            // Appeler l'API pour obtenir un token
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/auth/token',
                type: 'POST',
                data: {
                    username: username,
                    password: password
                },
                success: function(response) {
                    // Stocker le token
                    token = response.access_token;
                    localStorage.setItem('rpg_ia_token', token);
                    
                    // Stocker la date d'expiration du token
                    var tokenData = parseJwt(token);
                    if (tokenData && tokenData.exp) {
                        localStorage.setItem('rpg_ia_token_exp', tokenData.exp * 1000);
                    }
                    
                    // Réinitialiser le compteur de tentatives de connexion
                    $.ajax({
                        url: rpg_ia_public.rest_url + 'rpg-ia/v1/auth/reset-attempts',
                        type: 'POST',
                        headers: {
                            'X-WP-Nonce': rpg_ia_public.nonce
                        }
                    });
                    
                    // Rediriger vers la page actuelle pour afficher le contenu
                    window.location.reload();
                },
                error: function(xhr) {
                    var errorMessage = 'API authentication failed';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += ': ' + xhr.responseJSON.message;
                    }
                    
                    // Incrémenter le compteur de tentatives de connexion
                    $.ajax({
                        url: rpg_ia_public.rest_url + 'rpg-ia/v1/auth/increment-attempts',
                        type: 'POST',
                        headers: {
                            'X-WP-Nonce': rpg_ia_public.nonce
                        }
                    });
                    
                    showMessage('error', errorMessage);
                }
            });
        });

        // Gestion du formulaire d'inscription API
        $('#rpg-ia-api-register-form').on('submit', function(e) {
            e.preventDefault();
            
            var username = $('#rpg-ia-api-reg-username').val();
            var email = $('#rpg-ia-api-reg-email').val();
            var password = $('#rpg-ia-api-reg-password').val();
            var passwordConfirm = $('#rpg-ia-api-reg-password-confirm').val();
            
            if (!username || !email || !password) {
                showMessage('error', 'Please fill in all fields.');
                return;
            }
            
            if (password !== passwordConfirm) {
                showMessage('error', 'Passwords do not match.');
                return;
            }
            
            if (password.length < 8) {
                showMessage('error', 'Password must be at least 8 characters long.');
                return;
            }
            
            // Afficher le message de chargement
            showMessage('info', 'Creating API account...');
            
            // Appeler l'API pour créer un compte
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/auth/register',
                type: 'POST',
                data: {
                    username: username,
                    email: email,
                    password: password
                },
                success: function(response) {
                    showMessage('success', 'API account created successfully. You can now log in.');
                    
                    // Recharger la page après un délai pour afficher le formulaire de connexion
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                },
                error: function(xhr) {
                    var errorMessage = 'Failed to create API account';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += ': ' + xhr.responseJSON.message;
                    }
                    
                    showMessage('error', errorMessage);
                }
            });
        });

        // Fonction pour afficher un message
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

        // Fonction pour décoder un token JWT
        function parseJwt(token) {
            try {
                var base64Url = token.split('.')[1];
                var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
                var jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                }).join(''));
                
                return JSON.parse(jsonPayload);
            } catch (e) {
                return null;
            }
        }
    });
</script>