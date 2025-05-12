<?php
/**
 * Affiche la page de réglages d'administration du plugin.
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
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        // Afficher les champs cachés et les nonces
        settings_fields('rpg_ia_settings');
        
        // Afficher les sections de réglages
        do_settings_sections('rpg-ia-settings');
        
        // Afficher le bouton de soumission
        submit_button();
        ?>
    </form>
    
    <div class="rpg-ia-admin-card">
        <h3><?php _e('API Connection Test', 'rpg-ia'); ?></h3>
        <p><?php _e('Test the connection to the RPG-IA backend API.', 'rpg-ia'); ?></p>
        <div id="rpg-ia-api-test-result"></div>
        <button id="rpg-ia-test-api" class="button button-secondary"><?php _e('Test API Connection', 'rpg-ia'); ?></button>
    </div>
    
    <div class="rpg-ia-admin-card">
        <h3><?php _e('Reset Plugin', 'rpg-ia'); ?></h3>
        <p><?php _e('Reset the plugin to its default settings. This will delete all custom data.', 'rpg-ia'); ?></p>
        <p class="description"><?php _e('Warning: This action cannot be undone.', 'rpg-ia'); ?></p>
        <button id="rpg-ia-reset-plugin" class="button button-danger"><?php _e('Reset Plugin', 'rpg-ia'); ?></button>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Test API Connection
        $('#rpg-ia-test-api').on('click', function() {
            var button = $(this);
            var result = $('#rpg-ia-api-test-result');
            
            button.prop('disabled', true);
            result.html('<p><?php _e('Testing API connection...', 'rpg-ia'); ?></p>');
            
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_test_api',
                    nonce: rpg_ia_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        result.html('<p class="rpg-ia-success"><?php _e('API connection successful!', 'rpg-ia'); ?></p>');
                    } else {
                        result.html('<p class="rpg-ia-error"><?php _e('API connection failed:', 'rpg-ia'); ?> ' + response.data + '</p>');
                    }
                },
                error: function() {
                    result.html('<p class="rpg-ia-error"><?php _e('An error occurred while testing the API connection.', 'rpg-ia'); ?></p>');
                },
                complete: function() {
                    button.prop('disabled', false);
                }
            });
        });
        
        // Reset Plugin
        $('#rpg-ia-reset-plugin').on('click', function() {
            if (confirm('<?php _e('Are you sure you want to reset the plugin? This action cannot be undone.', 'rpg-ia'); ?>')) {
                var button = $(this);
                
                button.prop('disabled', true);
                
                $.ajax({
                    url: rpg_ia_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'rpg_ia_reset_plugin',
                        nonce: rpg_ia_admin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('Plugin reset successfully. The page will now reload.', 'rpg-ia'); ?>');
                            window.location.reload();
                        } else {
                            alert('<?php _e('Failed to reset plugin:', 'rpg-ia'); ?> ' + response.data);
                            button.prop('disabled', false);
                        }
                    },
                    error: function() {
                        alert('<?php _e('An error occurred while resetting the plugin.', 'rpg-ia'); ?>');
                        button.prop('disabled', false);
                    }
                });
            }
        });
    });
</script>