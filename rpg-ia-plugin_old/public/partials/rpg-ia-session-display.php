<?php
/**
 * Template pour afficher les détails d'une session de jeu
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/public/partials
 */

// Enregistrer le script spécifique aux sessions
wp_enqueue_script('rpg-ia-session', plugin_dir_url(dirname(__FILE__)) . 'js/rpg-ia-session.js', array('jquery'), RPG_IA_VERSION, true);

// Récupérer l'ID de la session
$session_id = isset($session_id) ? $session_id : get_query_var('session_id', 0);
if (empty($session_id)) {
    $session_id = isset($atts['id']) ? $atts['id'] : 0;
}

// Vérifier si une session est spécifiée
if (empty($session_id)) {
    echo '<p>' . __('Aucune session spécifiée.', 'rpg-ia') . '</p>';
    return;
}
?>

<div class="rpg-ia-container rpg-ia-session-details" data-session-id="<?php echo esc_attr($session_id); ?>">
    <div id="rpg-ia-session-loading" class="rpg-ia-loading">
        <div class="rpg-ia-spinner"></div>
        <p><?php _e('Chargement de la session...', 'rpg-ia'); ?></p>
    </div>

    <div id="rpg-ia-session-error" class="rpg-ia-error" style="display: none;">
        <p><?php _e('Erreur lors du chargement de la session.', 'rpg-ia'); ?></p>
        <button id="rpg-ia-retry-session" class="rpg-ia-button"><?php _e('Réessayer', 'rpg-ia'); ?></button>
    </div>

    <div id="rpg-ia-session-content" style="display: none;">
        <div class="rpg-ia-header">
            <h2 id="rpg-ia-session-name"></h2>
            <div class="rpg-ia-session-actions">
                <button id="rpg-ia-edit-session-btn" class="rpg-ia-button">
                    <i class="fas fa-edit"></i> <?php _e('Modifier', 'rpg-ia'); ?>
                </button>
                <button id="rpg-ia-play-session-btn" class="rpg-ia-button rpg-ia-primary-button">
                    <i class="fas fa-play"></i> <?php _e('Jouer', 'rpg-ia'); ?>
                </button>
            </div>
        </div>

        <div class="rpg-ia-session-status-badge">
            <span id="rpg-ia-session-status-text"></span>
        </div>

        <div class="rpg-ia-section">
            <h3><?php _e('Informations', 'rpg-ia'); ?></h3>
            <div class="rpg-ia-grid rpg-ia-two-columns">
                <div class="rpg-ia-info-card">
                    <h4><?php _e('Détails', 'rpg-ia'); ?></h4>
                    <div class="rpg-ia-info-item">
                        <span class="rpg-ia-info-label"><?php _e('Maître de jeu:', 'rpg-ia'); ?></span>
                        <span id="rpg-ia-session-gm" class="rpg-ia-info-value"></span>
                    </div>
                    <div class="rpg-ia-info-item">
                        <span class="rpg-ia-info-label"><?php _e('Créée le:', 'rpg-ia'); ?></span>
                        <span id="rpg-ia-session-created" class="rpg-ia-info-value"></span>
                    </div>
                    <div class="rpg-ia-info-item">
                        <span class="rpg-ia-info-label"><?php _e('Dernière activité:', 'rpg-ia'); ?></span>
                        <span id="rpg-ia-session-last-activity" class="rpg-ia-info-value"></span>
                    </div>
                    <div class="rpg-ia-info-item">
                        <span class="rpg-ia-info-label"><?php _e('Règles:', 'rpg-ia'); ?></span>
                        <span id="rpg-ia-session-rules" class="rpg-ia-info-value"></span>
                    </div>
                    <div class="rpg-ia-info-item">
                        <span class="rpg-ia-info-label"><?php _e('Difficulté:', 'rpg-ia'); ?></span>
                        <span id="rpg-ia-session-difficulty" class="rpg-ia-info-value"></span>
                    </div>
                </div>

                <div class="rpg-ia-info-card">
                    <h4><?php _e('Statistiques', 'rpg-ia'); ?></h4>
                    <div class="rpg-ia-info-item">
                        <span class="rpg-ia-info-label"><?php _e('Actions:', 'rpg-ia'); ?></span>
                        <span id="rpg-ia-session-action-count" class="rpg-ia-info-value"></span>
                    </div>
                    <div class="rpg-ia-info-item">
                        <span class="rpg-ia-info-label"><?php _e('Temps de jeu:', 'rpg-ia'); ?></span>
                        <span id="rpg-ia-session-playtime" class="rpg-ia-info-value"></span>
                    </div>
                    <div class="rpg-ia-info-item">
                        <span class="rpg-ia-info-label"><?php _e('Tokens utilisés:', 'rpg-ia'); ?></span>
                        <span id="rpg-ia-session-tokens" class="rpg-ia-info-value"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rpg-ia-section">
            <h3><?php _e('Description', 'rpg-ia'); ?></h3>
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-body">
                    <p id="rpg-ia-session-description"></p>
                </div>
            </div>
        </div>

        <div class="rpg-ia-grid rpg-ia-two-columns">
            <div class="rpg-ia-section">
                <h3><?php _e('Scénario Actuel', 'rpg-ia'); ?></h3>
                <div class="rpg-ia-card">
                    <div class="rpg-ia-card-body">
                        <div id="rpg-ia-no-scenario" style="display: none;">
                            <p><?php _e('Aucun scénario sélectionné.', 'rpg-ia'); ?></p>
                            <button id="rpg-ia-add-scenario-btn" class="rpg-ia-button">
                                <i class="fas fa-plus"></i> <?php _e('Ajouter un scénario', 'rpg-ia'); ?>
                            </button>
                        </div>
                        <div id="rpg-ia-scenario-details">
                            <h4 id="rpg-ia-scenario-name"></h4>
                            <p id="rpg-ia-scenario-description"></p>
                            <button id="rpg-ia-change-scenario-btn" class="rpg-ia-button">
                                <i class="fas fa-exchange-alt"></i> <?php _e('Changer', 'rpg-ia'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rpg-ia-section">
                <h3><?php _e('Scène Actuelle', 'rpg-ia'); ?></h3>
                <div class="rpg-ia-card">
                    <div class="rpg-ia-card-body">
                        <div id="rpg-ia-no-scene" style="display: none;">
                            <p><?php _e('Aucune scène active.', 'rpg-ia'); ?></p>
                        </div>
                        <div id="rpg-ia-scene-details">
                            <h4 id="rpg-ia-scene-name"></h4>
                            <p id="rpg-ia-scene-type"></p>
                            <button id="rpg-ia-view-scene-btn" class="rpg-ia-button">
                                <i class="fas fa-eye"></i> <?php _e('Détails', 'rpg-ia'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rpg-ia-section">
            <h3><?php _e('Joueurs', 'rpg-ia'); ?></h3>
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-body">
                    <div id="rpg-ia-no-players" style="display: none;">
                        <p><?php _e('Aucun joueur dans cette session.', 'rpg-ia'); ?></p>
                    </div>
                    <div id="rpg-ia-players-list">
                        <!-- Les joueurs seront ajoutés ici dynamiquement -->
                    </div>
                    <div class="rpg-ia-invite-section">
                        <button id="rpg-ia-invite-player-btn" class="rpg-ia-button">
                            <i class="fas fa-user-plus"></i> <?php _e('Inviter des joueurs', 'rpg-ia'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="rpg-ia-section">
            <h3><?php _e('Dernières Actions', 'rpg-ia'); ?></h3>
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-body">
                    <div id="rpg-ia-no-actions" style="display: none;">
                        <p><?php _e('Aucune action enregistrée.', 'rpg-ia'); ?></p>
                    </div>
                    <div id="rpg-ia-actions-list">
                        <!-- Les actions seront ajoutées ici dynamiquement -->
                    </div>
                    <div class="rpg-ia-view-more">
                        <a href="#" id="rpg-ia-view-all-actions" class="rpg-ia-link">
                            <?php _e('Voir toutes les actions', 'rpg-ia'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="rpg-ia-section rpg-ia-danger-zone">
            <h3><?php _e('Zone de Danger', 'rpg-ia'); ?></h3>
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-body">
                    <p><?php _e('Les actions suivantes sont irréversibles.', 'rpg-ia'); ?></p>
                    <div class="rpg-ia-danger-actions">
                        <button id="rpg-ia-delete-session-btn" class="rpg-ia-button rpg-ia-danger-button">
                            <i class="fas fa-trash"></i> <?php _e('Supprimer la session', 'rpg-ia'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template pour un joueur -->
<template id="rpg-ia-player-item-template">
    <div class="rpg-ia-player-item">
        <div class="rpg-ia-player-info">
            <span class="rpg-ia-player-name"></span>
            <span class="rpg-ia-player-character"></span>
        </div>
        <div class="rpg-ia-player-actions">
            <button class="rpg-ia-button rpg-ia-remove-player-btn">
                <i class="fas fa-user-minus"></i>
            </button>
        </div>
    </div>
</template>

<!-- Template pour une action -->
<template id="rpg-ia-action-item-template">
    <div class="rpg-ia-action-item">
        <div class="rpg-ia-action-header">
            <span class="rpg-ia-action-timestamp"></span>
            <span class="rpg-ia-action-player"></span>
        </div>
        <div class="rpg-ia-action-content">
            <p class="rpg-ia-action-description"></p>
        </div>
        <div class="rpg-ia-action-result">
            <p class="rpg-ia-action-response"></p>
        </div>
    </div>
</template>

<!-- Modal pour inviter des joueurs -->
<div id="rpg-ia-invite-players-modal" class="rpg-ia-modal">
    <div class="rpg-ia-modal-content">
        <div class="rpg-ia-modal-header">
            <h2><?php _e('Inviter des Joueurs', 'rpg-ia'); ?></h2>
            <span class="rpg-ia-modal-close">&times;</span>
        </div>
        <div class="rpg-ia-modal-body">
            <form id="rpg-ia-invite-players-form">
                <input type="hidden" id="rpg-ia-invite-session-id" value="">
                
                <div class="rpg-ia-form-group">
                    <label for="rpg-ia-invite-username"><?php _e('Nom d\'utilisateur', 'rpg-ia'); ?></label>
                    <div class="rpg-ia-input-group">
                        <input type="text" id="rpg-ia-invite-username" class="rpg-ia-input" placeholder="<?php _e('Entrez un nom d\'utilisateur', 'rpg-ia'); ?>">
                        <button type="button" id="rpg-ia-add-invite-btn" class="rpg-ia-button"><?php _e('Ajouter', 'rpg-ia'); ?></button>
                    </div>
                </div>
                
                <div class="rpg-ia-form-group">
                    <label><?php _e('Joueurs à inviter', 'rpg-ia'); ?></label>
                    <div id="rpg-ia-invite-list" class="rpg-ia-invite-list">
                        <p id="rpg-ia-no-invites" class="rpg-ia-empty-message"><?php _e('Aucun joueur ajouté', 'rpg-ia'); ?></p>
                        <!-- Les joueurs à inviter seront ajoutés ici -->
                    </div>
                </div>
                
                <div class="rpg-ia-form-actions">
                    <button type="button" class="rpg-ia-button rpg-ia-modal-cancel"><?php _e('Annuler', 'rpg-ia'); ?></button>
                    <button type="submit" class="rpg-ia-button rpg-ia-primary-button"><?php _e('Envoyer les invitations', 'rpg-ia'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div id="rpg-ia-delete-confirm-modal" class="rpg-ia-modal">
    <div class="rpg-ia-modal-content">
        <div class="rpg-ia-modal-header">
            <h2><?php _e('Confirmer la suppression', 'rpg-ia'); ?></h2>
            <span class="rpg-ia-modal-close">&times;</span>
        </div>
        <div class="rpg-ia-modal-body">
            <p><?php _e('Êtes-vous sûr de vouloir supprimer cette session ? Cette action est irréversible et toutes les données associées seront perdues.', 'rpg-ia'); ?></p>
            
            <div class="rpg-ia-form-actions">
                <button type="button" class="rpg-ia-button rpg-ia-modal-cancel"><?php _e('Annuler', 'rpg-ia'); ?></button>
                <button type="button" id="rpg-ia-confirm-delete-btn" class="rpg-ia-button rpg-ia-danger-button"><?php _e('Supprimer définitivement', 'rpg-ia'); ?></button>
            </div>
        </div>
    </div>
</div>