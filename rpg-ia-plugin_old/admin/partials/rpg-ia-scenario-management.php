<?php
/**
 * Affiche la page de gestion des scénarios.
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
$scenario_manager = new RPG_IA_Scenario_Manager($api_client);

// Récupérer l'ID du scénario s'il est spécifié
$scenario_id = isset($_GET['scenario_id']) ? intval($_GET['scenario_id']) : 0;
$scenario = null;
$scenes = array();

// Si un ID de scénario est spécifié, récupérer les détails du scénario
if ($scenario_id) {
    $scenario = $scenario_manager->get_scenario($scenario_id);
    if (!is_wp_error($scenario)) {
        $scenes = $scenario_manager->get_scenario_scenes($scenario_id);
        if (is_wp_error($scenes)) {
            $scenes = array();
        }
    } else {
        $scenario = null;
    }
}

// Définir le titre de la page
$page_title = $scenario ? sprintf(__('Edit Scenario: %s', 'rpg-ia'), $scenario['title']) : __('Create New Scenario', 'rpg-ia');
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <div class="rpg-ia-scenario-management">
        <div class="rpg-ia-admin-card">
            <div id="rpg-ia-scenario-form-container">
                <form id="rpg-ia-scenario-form" class="rpg-ia-form">
                    <input type="hidden" id="rpg-ia-scenario-id" value="<?php echo $scenario_id; ?>">
                    
                    <div class="rpg-ia-form-row">
                        <label for="rpg-ia-scenario-title"><?php _e('Title', 'rpg-ia'); ?> <span class="required">*</span></label>
                        <input type="text" id="rpg-ia-scenario-title" name="title" value="<?php echo $scenario ? esc_attr($scenario['title']) : ''; ?>" required>
                    </div>
                    
                    <div class="rpg-ia-form-row">
                        <label for="rpg-ia-scenario-description"><?php _e('Description', 'rpg-ia'); ?></label>
                        <textarea id="rpg-ia-scenario-description" name="description" rows="4"><?php echo $scenario ? esc_textarea($scenario['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="rpg-ia-form-row">
                        <label for="rpg-ia-scenario-type"><?php _e('Type', 'rpg-ia'); ?></label>
                        <select id="rpg-ia-scenario-type" name="type">
                            <option value="adventure" <?php echo ($scenario && $scenario['type'] == 'adventure') ? 'selected' : ''; ?>><?php _e('Adventure', 'rpg-ia'); ?></option>
                            <option value="dungeon" <?php echo ($scenario && $scenario['type'] == 'dungeon') ? 'selected' : ''; ?>><?php _e('Dungeon', 'rpg-ia'); ?></option>
                            <option value="mystery" <?php echo ($scenario && $scenario['type'] == 'mystery') ? 'selected' : ''; ?>><?php _e('Mystery', 'rpg-ia'); ?></option>
                            <option value="horror" <?php echo ($scenario && $scenario['type'] == 'horror') ? 'selected' : ''; ?>><?php _e('Horror', 'rpg-ia'); ?></option>
                            <option value="custom" <?php echo ($scenario && $scenario['type'] == 'custom') ? 'selected' : ''; ?>><?php _e('Custom', 'rpg-ia'); ?></option>
                        </select>
                    </div>
                    
                    <div class="rpg-ia-form-row">
                        <label for="rpg-ia-scenario-level-min"><?php _e('Recommended Level (Min)', 'rpg-ia'); ?></label>
                        <input type="number" id="rpg-ia-scenario-level-min" name="level_min" min="1" max="20" value="<?php echo $scenario ? esc_attr($scenario['level_min']) : '1'; ?>">
                    </div>
                    
                    <div class="rpg-ia-form-row">
                        <label for="rpg-ia-scenario-level-max"><?php _e('Recommended Level (Max)', 'rpg-ia'); ?></label>
                        <input type="number" id="rpg-ia-scenario-level-max" name="level_max" min="1" max="20" value="<?php echo $scenario ? esc_attr($scenario['level_max']) : '5'; ?>">
                    </div>
                    
                    <div class="rpg-ia-form-row">
                        <label for="rpg-ia-scenario-tags"><?php _e('Tags', 'rpg-ia'); ?></label>
                        <input type="text" id="rpg-ia-scenario-tags" name="tags" value="<?php echo $scenario && isset($scenario['tags']) ? esc_attr(implode(', ', $scenario['tags'])) : ''; ?>" placeholder="<?php _e('Comma-separated tags', 'rpg-ia'); ?>">
                    </div>
                    
                    <div class="rpg-ia-form-actions">
                        <button type="button" id="rpg-ia-save-scenario" class="button button-primary"><?php _e('Save Scenario', 'rpg-ia'); ?></button>
                        <?php if ($scenario_id) : ?>
                            <button type="button" id="rpg-ia-delete-scenario" class="button button-danger rpg-ia-confirm-action" data-confirm-message="<?php _e('Are you sure you want to delete this scenario? This action cannot be undone.', 'rpg-ia'); ?>"><?php _e('Delete Scenario', 'rpg-ia'); ?></button>
                        <?php endif; ?>
                        <a href="<?php echo admin_url('admin.php?page=rpg-ia-gm-dashboard'); ?>" class="button button-secondary"><?php _e('Back to Dashboard', 'rpg-ia'); ?></a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($scenario_id) : ?>
            <div class="rpg-ia-admin-card">
                <h2><?php _e('Scenes', 'rpg-ia'); ?></h2>
                
                <div id="rpg-ia-scenes-container">
                    <?php if (empty($scenes)) : ?>
                        <p id="rpg-ia-no-scenes-message"><?php _e('No scenes have been added to this scenario yet.', 'rpg-ia'); ?></p>
                    <?php else : ?>
                        <ul id="rpg-ia-scene-list" class="rpg-ia-sortable">
                            <?php foreach ($scenes as $index => $scene) : ?>
                                <li class="rpg-ia-scene-item" data-scene-id="<?php echo esc_attr($scene['id']); ?>">
                                    <div class="rpg-ia-scene-header">
                                        <span class="rpg-ia-scene-handle dashicons dashicons-menu"></span>
                                        <span class="rpg-ia-scene-number"><?php echo $index + 1; ?></span>
                                        <span class="rpg-ia-scene-title"><?php echo esc_html($scene['title']); ?></span>
                                        <span class="rpg-ia-scene-type-badge"><?php echo esc_html(ucfirst($scene['type'])); ?></span>
                                        <div class="rpg-ia-scene-actions">
                                            <button type="button" class="rpg-ia-edit-scene button button-small"><?php _e('Edit', 'rpg-ia'); ?></button>
                                            <button type="button" class="rpg-ia-delete-scene button button-small rpg-ia-confirm-action" data-confirm-message="<?php _e('Are you sure you want to delete this scene? This action cannot be undone.', 'rpg-ia'); ?>"><?php _e('Delete', 'rpg-ia'); ?></button>
                                        </div>
                                    </div>
                                    <div class="rpg-ia-scene-details" style="display: none;">
                                        <p><strong><?php _e('Description:', 'rpg-ia'); ?></strong> <?php echo esc_html($scene['description']); ?></p>
                                        <?php if (isset($scene['content']) && !empty($scene['content'])) : ?>
                                            <p><strong><?php _e('Content:', 'rpg-ia'); ?></strong> <?php echo esc_html(substr($scene['content'], 0, 100)) . (strlen($scene['content']) > 100 ? '...' : ''); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
                <div class="rpg-ia-form-actions">
                    <button type="button" id="rpg-ia-add-scene" class="button button-secondary"><?php _e('Add New Scene', 'rpg-ia'); ?></button>
                </div>
            </div>
            
            <!-- Modal pour l'édition des scènes -->
            <div id="rpg-ia-scene-modal" class="rpg-ia-modal" style="display: none;">
                <div class="rpg-ia-modal-content">
                    <span class="rpg-ia-modal-close">&times;</span>
                    <h3 id="rpg-ia-scene-modal-title"><?php _e('Add New Scene', 'rpg-ia'); ?></h3>
                    
                    <form id="rpg-ia-scene-form" class="rpg-ia-form">
                        <input type="hidden" id="rpg-ia-scene-id" value="0">
                        <input type="hidden" id="rpg-ia-scene-order" value="0">
                        
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-scene-title"><?php _e('Title', 'rpg-ia'); ?> <span class="required">*</span></label>
                            <input type="text" id="rpg-ia-scene-title" name="title" required>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-scene-type"><?php _e('Type', 'rpg-ia'); ?></label>
                            <select id="rpg-ia-scene-type" name="type">
                                <option value="introduction"><?php _e('Introduction', 'rpg-ia'); ?></option>
                                <option value="exploration"><?php _e('Exploration', 'rpg-ia'); ?></option>
                                <option value="combat"><?php _e('Combat', 'rpg-ia'); ?></option>
                                <option value="puzzle"><?php _e('Puzzle', 'rpg-ia'); ?></option>
                                <option value="social"><?php _e('Social', 'rpg-ia'); ?></option>
                                <option value="conclusion"><?php _e('Conclusion', 'rpg-ia'); ?></option>
                            </select>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-scene-description"><?php _e('Description', 'rpg-ia'); ?></label>
                            <textarea id="rpg-ia-scene-description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-scene-content"><?php _e('Content', 'rpg-ia'); ?></label>
                            <textarea id="rpg-ia-scene-content" name="content" rows="6"></textarea>
                            <p class="description"><?php _e('Detailed content of the scene, including descriptions, NPCs, monsters, etc.', 'rpg-ia'); ?></p>
                        </div>
                        
                        <div class="rpg-ia-form-actions">
                            <button type="button" id="rpg-ia-save-scene" class="button button-primary"><?php _e('Save Scene', 'rpg-ia'); ?></button>
                            <button type="button" id="rpg-ia-cancel-scene" class="button button-secondary"><?php _e('Cancel', 'rpg-ia'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Variables globales
        var scenarioId = $('#rpg-ia-scenario-id').val();
        var scenes = <?php echo json_encode($scenes); ?>;
        
        // Rendre la liste des scènes triable
        if ($('#rpg-ia-scene-list').length) {
            $('#rpg-ia-scene-list').sortable({
                handle: '.rpg-ia-scene-handle',
                update: function() {
                    updateSceneOrder();
                }
            });
        }
        
        // Fonction pour mettre à jour l'ordre des scènes
        function updateSceneOrder() {
            $('#rpg-ia-scene-list li').each(function(index) {
                $(this).find('.rpg-ia-scene-number').text(index + 1);
            });
        }
        
        // Sauvegarder un scénario
        $('#rpg-ia-save-scenario').on('click', function() {
            var scenarioData = {
                title: $('#rpg-ia-scenario-title').val(),
                description: $('#rpg-ia-scenario-description').val(),
                type: $('#rpg-ia-scenario-type').val(),
                level_min: parseInt($('#rpg-ia-scenario-level-min').val()),
                level_max: parseInt($('#rpg-ia-scenario-level-max').val()),
                tags: $('#rpg-ia-scenario-tags').val().split(',').map(function(tag) {
                    return tag.trim();
                }).filter(function(tag) {
                    return tag !== '';
                })
            };
            
            if (scenarioId) {
                // Mettre à jour un scénario existant
                $.ajax({
                    url: rpg_ia_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'rpg_ia_update_scenario',
                        nonce: rpg_ia_admin.nonce,
                        scenario_id: scenarioId,
                        scenario_data: JSON.stringify(scenarioData)
                    },
                    beforeSend: function() {
                        $('#rpg-ia-save-scenario').prop('disabled', true).text('<?php _e('Saving...', 'rpg-ia'); ?>');
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('Scenario updated successfully.', 'rpg-ia'); ?>');
                        } else {
                            alert('<?php _e('Error updating scenario: ', 'rpg-ia'); ?>' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php _e('An error occurred while updating the scenario.', 'rpg-ia'); ?>');
                    },
                    complete: function() {
                        $('#rpg-ia-save-scenario').prop('disabled', false).text('<?php _e('Save Scenario', 'rpg-ia'); ?>');
                    }
                });
            } else {
                // Créer un nouveau scénario
                $.ajax({
                    url: rpg_ia_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'rpg_ia_create_scenario',
                        nonce: rpg_ia_admin.nonce,
                        scenario_data: JSON.stringify(scenarioData)
                    },
                    beforeSend: function() {
                        $('#rpg-ia-save-scenario').prop('disabled', true).text('<?php _e('Creating...', 'rpg-ia'); ?>');
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('Scenario created successfully.', 'rpg-ia'); ?>');
                            // Rediriger vers la page d'édition du scénario
                            window.location.href = '<?php echo admin_url('admin.php?page=rpg-ia-scenario-management&scenario_id='); ?>' + response.data.id;
                        } else {
                            alert('<?php _e('Error creating scenario: ', 'rpg-ia'); ?>' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php _e('An error occurred while creating the scenario.', 'rpg-ia'); ?>');
                    },
                    complete: function() {
                        $('#rpg-ia-save-scenario').prop('disabled', false).text('<?php _e('Save Scenario', 'rpg-ia'); ?>');
                    }
                });
            }
        });
        
        // Supprimer un scénario
        $('#rpg-ia-delete-scenario').on('click', function() {
            if (!confirm($(this).data('confirm-message'))) {
                return;
            }
            
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_delete_scenario',
                    nonce: rpg_ia_admin.nonce,
                    scenario_id: scenarioId
                },
                beforeSend: function() {
                    $('#rpg-ia-delete-scenario').prop('disabled', true).text('<?php _e('Deleting...', 'rpg-ia'); ?>');
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Scenario deleted successfully.', 'rpg-ia'); ?>');
                        // Rediriger vers le tableau de bord
                        window.location.href = '<?php echo admin_url('admin.php?page=rpg-ia-gm-dashboard'); ?>';
                    } else {
                        alert('<?php _e('Error deleting scenario: ', 'rpg-ia'); ?>' + response.data);
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred while deleting the scenario.', 'rpg-ia'); ?>');
                },
                complete: function() {
                    $('#rpg-ia-delete-scenario').prop('disabled', false).text('<?php _e('Delete Scenario', 'rpg-ia'); ?>');
                }
            });
        });
        
        // Ajouter une nouvelle scène
        $('#rpg-ia-add-scene').on('click', function() {
            // Réinitialiser le formulaire
            $('#rpg-ia-scene-form')[0].reset();
            $('#rpg-ia-scene-id').val(0);
            $('#rpg-ia-scene-order').val($('#rpg-ia-scene-list li').length + 1);
            
            // Mettre à jour le titre du modal
            $('#rpg-ia-scene-modal-title').text('<?php _e('Add New Scene', 'rpg-ia'); ?>');
            
            // Afficher le modal
            $('#rpg-ia-scene-modal').show();
        });
        
        // Éditer une scène existante
        $(document).on('click', '.rpg-ia-edit-scene', function() {
            var sceneItem = $(this).closest('.rpg-ia-scene-item');
            var sceneId = sceneItem.data('scene-id');
            var scene = scenes.find(function(s) {
                return s.id == sceneId;
            });
            
            if (scene) {
                // Remplir le formulaire avec les données de la scène
                $('#rpg-ia-scene-id').val(scene.id);
                $('#rpg-ia-scene-title').val(scene.title);
                $('#rpg-ia-scene-type').val(scene.type);
                $('#rpg-ia-scene-description').val(scene.description);
                $('#rpg-ia-scene-content').val(scene.content);
                $('#rpg-ia-scene-order').val(sceneItem.index() + 1);
                
                // Mettre à jour le titre du modal
                $('#rpg-ia-scene-modal-title').text('<?php _e('Edit Scene', 'rpg-ia'); ?>');
                
                // Afficher le modal
                $('#rpg-ia-scene-modal').show();
            }
        });
        
        // Fermer le modal
        $('.rpg-ia-modal-close, #rpg-ia-cancel-scene').on('click', function() {
            $('#rpg-ia-scene-modal').hide();
        });
        
        // Sauvegarder une scène
        $('#rpg-ia-save-scene').on('click', function() {
            var sceneId = $('#rpg-ia-scene-id').val();
            var sceneData = {
                title: $('#rpg-ia-scene-title').val(),
                type: $('#rpg-ia-scene-type').val(),
                description: $('#rpg-ia-scene-description').val(),
                content: $('#rpg-ia-scene-content').val(),
                order: parseInt($('#rpg-ia-scene-order').val())
            };
            
            if (sceneId && sceneId != '0') {
                // Mettre à jour une scène existante
                $.ajax({
                    url: rpg_ia_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'rpg_ia_update_scenario_scene',
                        nonce: rpg_ia_admin.nonce,
                        scenario_id: scenarioId,
                        scene_id: sceneId,
                        scene_data: JSON.stringify(sceneData)
                    },
                    beforeSend: function() {
                        $('#rpg-ia-save-scene').prop('disabled', true).text('<?php _e('Saving...', 'rpg-ia'); ?>');
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('Scene updated successfully.', 'rpg-ia'); ?>');
                            // Recharger la page pour afficher les modifications
                            location.reload();
                        } else {
                            alert('<?php _e('Error updating scene: ', 'rpg-ia'); ?>' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php _e('An error occurred while updating the scene.', 'rpg-ia'); ?>');
                    },
                    complete: function() {
                        $('#rpg-ia-save-scene').prop('disabled', false).text('<?php _e('Save Scene', 'rpg-ia'); ?>');
                        $('#rpg-ia-scene-modal').hide();
                    }
                });
            } else {
                // Créer une nouvelle scène
                $.ajax({
                    url: rpg_ia_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'rpg_ia_create_scenario_scene',
                        nonce: rpg_ia_admin.nonce,
                        scenario_id: scenarioId,
                        scene_data: JSON.stringify(sceneData)
                    },
                    beforeSend: function() {
                        $('#rpg-ia-save-scene').prop('disabled', true).text('<?php _e('Creating...', 'rpg-ia'); ?>');
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('Scene created successfully.', 'rpg-ia'); ?>');
                            // Recharger la page pour afficher la nouvelle scène
                            location.reload();
                        } else {
                            alert('<?php _e('Error creating scene: ', 'rpg-ia'); ?>' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php _e('An error occurred while creating the scene.', 'rpg-ia'); ?>');
                    },
                    complete: function() {
                        $('#rpg-ia-save-scene').prop('disabled', false).text('<?php _e('Save Scene', 'rpg-ia'); ?>');
                        $('#rpg-ia-scene-modal').hide();
                    }
                });
            }
        });
        
        // Supprimer une scène
        $(document).on('click', '.rpg-ia-delete-scene', function() {
            if (!confirm($(this).data('confirm-message'))) {
                return;
            }
            
            var sceneItem = $(this).closest('.rpg-ia-scene-item');
            var sceneId = sceneItem.data('scene-id');
            
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_delete_scenario_scene',
                    nonce: rpg_ia_admin.nonce,
                    scenario_id: scenarioId,
                    scene_id: sceneId
                },
                beforeSend: function() {
                    $(this).prop('disabled', true).text('<?php _e('Deleting...', 'rpg-ia'); ?>');
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Scene deleted successfully.', 'rpg-ia'); ?>');
                        // Supprimer l'élément de la liste
                        sceneItem.remove();
                        // Mettre à jour l'ordre des scènes
                        updateSceneOrder();
                        // Afficher un message si la liste est vide
                        if ($('#rpg-ia-scene-list li').length === 0) {
                            $('#rpg-ia-scene-list').replaceWith('<p id="rpg-ia-no-scenes-message"><?php _e('No scenes have been added to this scenario yet.', 'rpg-ia'); ?></p>');
                        }
                    } else {
                        alert('<?php _e('Error deleting scene: ', 'rpg-ia'); ?>' + response.data);
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred while deleting the scene.', 'rpg-ia'); ?>');
                },
                complete: function() {
                    $(this).prop('disabled', false).text('<?php _e('Delete', 'rpg-ia'); ?>');
                }
            });
        });
        
        // Afficher/masquer les détails d'une scène
        $(document).on('click', '.rpg-ia-scene-header', function(e) {
            if (!$(e.target).hasClass('button') && !$(e.target).closest('.button').length) {
                $(this).siblings('.rpg-ia-scene-details').slideToggle();
            }
        });
    });
</script>