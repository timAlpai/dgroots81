<?php
/**
 * Affiche la page de tests du plugin.
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/admin/partials
 */

// Charger la classe de tests
require_once plugin_dir_path(dirname(dirname(__FILE__))) . 'includes/class-rpg-ia-tests.php';

// Récupérer les résultats des tests
$test_results = RPG_IA_Tests::get_test_results();
$last_test_run = RPG_IA_Tests::get_last_test_run();
$all_tests_passed = RPG_IA_Tests::all_tests_passed();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="rpg-ia-admin-section">
        <h2><?php _e('Plugin Tests', 'rpg-ia'); ?></h2>
        <p><?php _e('This page allows you to run tests on the plugin and view the results.', 'rpg-ia'); ?></p>
        
        <div class="rpg-ia-admin-card">
            <h3><?php _e('Run Tests', 'rpg-ia'); ?></h3>
            <p><?php _e('Click the button below to run all tests.', 'rpg-ia'); ?></p>
            <button id="rpg-ia-run-tests" class="button button-primary"><?php _e('Run Tests Now', 'rpg-ia'); ?></button>
            <div id="rpg-ia-tests-result"></div>
        </div>
        
        <div class="rpg-ia-admin-card">
            <h3><?php _e('Test Results', 'rpg-ia'); ?></h3>
            <?php if (!empty($last_test_run)) : ?>
                <p><?php _e('Last test run:', 'rpg-ia'); ?> <strong><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_test_run)); ?></strong></p>
                <p><?php _e('Overall status:', 'rpg-ia'); ?> 
                    <?php if ($all_tests_passed) : ?>
                        <span class="rpg-ia-success"><?php _e('All tests passed', 'rpg-ia'); ?></span>
                    <?php else : ?>
                        <span class="rpg-ia-error"><?php _e('Some tests failed', 'rpg-ia'); ?></span>
                    <?php endif; ?>
                </p>
                
                <?php if (!empty($test_results)) : ?>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php _e('Test', 'rpg-ia'); ?></th>
                                <th><?php _e('Description', 'rpg-ia'); ?></th>
                                <th><?php _e('Status', 'rpg-ia'); ?></th>
                                <th><?php _e('Message', 'rpg-ia'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($test_results as $result) : ?>
                                <tr>
                                    <td><?php echo esc_html($result['name']); ?></td>
                                    <td><?php echo esc_html($result['description']); ?></td>
                                    <td>
                                        <?php if ($result['success']) : ?>
                                            <span class="rpg-ia-success"><?php _e('Success', 'rpg-ia'); ?></span>
                                        <?php else : ?>
                                            <span class="rpg-ia-error"><?php _e('Failed', 'rpg-ia'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($result['message']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p><?php _e('No test results available.', 'rpg-ia'); ?></p>
                <?php endif; ?>
            <?php else : ?>
                <p><?php _e('No tests have been run yet.', 'rpg-ia'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#rpg-ia-run-tests').on('click', function() {
        var button = $(this);
        var result = $('#rpg-ia-tests-result');
        
        button.prop('disabled', true);
        result.html('<p><?php _e('Running tests...', 'rpg-ia'); ?></p>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'rpg_ia_run_tests',
                nonce: rpg_ia_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    result.html('<p class="rpg-ia-success"><?php _e('Tests completed successfully. Reloading page...', 'rpg-ia'); ?></p>');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    result.html('<p class="rpg-ia-error"><?php _e('Error running tests:', 'rpg-ia'); ?> ' + response.data + '</p>');
                    button.prop('disabled', false);
                }
            },
            error: function() {
                result.html('<p class="rpg-ia-error"><?php _e('An error occurred while running the tests.', 'rpg-ia'); ?></p>');
                button.prop('disabled', false);
            }
        });
    });
});
</script>

<style>
.rpg-ia-admin-section {
    margin-top: 20px;
}

.rpg-ia-admin-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,0.04);
}

.rpg-ia-success {
    color: #46b450;
    font-weight: bold;
}

.rpg-ia-error {
    color: #dc3232;
    font-weight: bold;
}

#rpg-ia-tests-result {
    margin-top: 10px;
    padding: 10px;
    background: #f8f8f8;
    border-radius: 4px;
}
</style>