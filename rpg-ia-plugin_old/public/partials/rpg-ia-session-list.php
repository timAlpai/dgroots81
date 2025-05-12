<?php
/**
 * Template pour afficher la liste des sessions de jeu
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/public/partials
 */

// Enregistrer le script spécifique aux sessions
wp_enqueue_script('rpg-ia-session', plugin_dir_url(dirname(__FILE__)) . 'js/rpg-ia-session.js', array('jquery'), RPG_IA_VERSION, true);
?>

<div class="rpg-ia-container">
    <div class="rpg-ia-header">
        <h2><?php _e('Sessions de Jeu', 'rpg-ia'); ?></h2>
        <button id="rpg-ia-new-session-btn" class="rpg-ia-button rpg-ia-primary-button">
            <i class="fas fa-plus"></i> <?php _e('Nouvelle Session', 'rpg-ia'); ?>
        </button>
    </div>

    <div class="rpg-ia-filters">
        <select id="rpg-ia-session-filter" class="rpg-ia-select">
            <option value="all"><?php _e('Toutes les sessions', 'rpg-ia'); ?></option>
            <option value="my_sessions"><?php _e('Mes sessions', 'rpg-ia'); ?></option>
            <option value="active"><?php _e('Sessions actives', 'rpg-ia'); ?></option>
            <option value="paused"><?php _e('Sessions en pause', 'rpg-ia'); ?></option>
            <option value="planning"><?php _e('Sessions en préparation', 'rpg-ia'); ?></option>
            <option value="completed"><?php _e('Sessions terminées', 'rpg-ia'); ?></option>
        </select>
        <input type="text" id="rpg-ia-session-search" class="rpg-ia-input" placeholder="<?php _e('Rechercher...', 'rpg-ia'); ?>">
    </div>

    <div id="rpg-ia-sessions-loading" class="rpg-ia-loading">
        <div class="rpg-ia-spinner"></div>
        <p><?php _e('Chargement des sessions...', 'rpg-ia'); ?></p>
    </div>

    <div id="rpg-ia-sessions-error" class="rpg-ia-error" style="display: none;">
        <p><?php _e('Erreur lors du chargement des sessions.', 'rpg-ia'); ?></p>
        <button id="rpg-ia-retry-sessions" class="rpg-ia-button"><?php _e('Réessayer', 'rpg-ia'); ?></button>
    </div>

    <div id="rpg-ia-no-sessions" class="rpg-ia-empty" style="display: none;">
        <p><?php _e('Aucune session trouvée.', 'rpg-ia'); ?></p>
        <p><?php _e('Créez une nouvelle session pour commencer à jouer.', 'rpg-ia'); ?></p>
    </div>

    <div id="rpg-ia-sessions-list" class="rpg-ia-grid" style="display: none;">
        <!-- Les sessions seront ajoutées ici dynamiquement -->
    </div>

    <!-- Template pour une carte de session -->
    <template id="rpg-ia-session-card-template">
        <div class="rpg-ia-card rpg-ia-session-card" data-session-id="">
            <div class="rpg-ia-card-header">
                <h3 class="rpg-ia-session-name"></h3>
                <span class="rpg-ia-session-status"></span>
            </div>
            <div class="rpg-ia-card-body">
                <p class="rpg-ia-session-description"></p>
                <div class="rpg-ia-session-info">
                    <div class="rpg-ia-session-gm">
                        <strong><?php _e('MJ:', 'rpg-ia'); ?></strong> <span class="rpg-ia-session-gm-name"></span>
                    </div>
                    <div class="rpg-ia-session-players">
                        <strong><?php _e('Joueurs:', 'rpg-ia'); ?></strong> <span class="rpg-ia-session-player-count"></span>
                    </div>
                    <div class="rpg-ia-session-rules">
                        <strong><?php _e('Règles:', 'rpg-ia'); ?></strong> <span class="rpg-ia-session-rules-name"></span>
                    </div>
                </div>
            </div>
            <div class="rpg-ia-card-footer">
                <button class="rpg-ia-button rpg-ia-view-session-btn"><?php _e('Détails', 'rpg-ia'); ?></button>
                <button class="rpg-ia-button rpg-ia-primary-button rpg-ia-join-session-btn"><?php _e('Rejoindre', 'rpg-ia'); ?></button>
            </div>
        </div>
    </template>
</div>

<!-- Modal pour créer/éditer une session -->
<div id="rpg-ia-session-modal" class="rpg-ia-modal">
    <div class="rpg-ia-modal-content">
        <div class="rpg-ia-modal-header">
            <h2 id="rpg-ia-session-modal-title"><?php _e('Nouvelle Session', 'rpg-ia'); ?></h2>
            <span class="rpg-ia-modal-close">&times;</span>
        </div>
        <div class="rpg-ia-modal-body">
            <form id="rpg-ia-session-form">
                <input type="hidden" id="rpg-ia-session-id" value="">
                
                <div class="rpg-ia-form-group">
                    <label for="rpg-ia-session-name"><?php _e('Nom de la session', 'rpg-ia'); ?> *</label>
                    <input type="text" id="rpg-ia-session-name" class="rpg-ia-input" required>
                </div>
                
                <div class="rpg-ia-form-group">
                    <label for="rpg-ia-session-description"><?php _e('Description', 'rpg-ia'); ?> *</label>
                    <textarea id="rpg-ia-session-description" class="rpg-ia-textarea" rows="4" required></textarea>
                </div>
                
                <div class="rpg-ia-form-row">
                    <div class="rpg-ia-form-group">
                        <label for="rpg-ia-session-rules"><?php _e('Règles', 'rpg-ia'); ?></label>
                        <select id="rpg-ia-session-rules" class="rpg-ia-select">
                            <option value="ose"><?php _e('Old School Essentials', 'rpg-ia'); ?></option>
                            <option value="dnd5e"><?php _e('Dungeons & Dragons 5e', 'rpg-ia'); ?></option>
                            <option value="pathfinder"><?php _e('Pathfinder', 'rpg-ia'); ?></option>
                            <option value="custom"><?php _e('Règles personnalisées', 'rpg-ia'); ?></option>
                        </select>
                    </div>
                    
                    <div class="rpg-ia-form-group">
                        <label for="rpg-ia-session-difficulty"><?php _e('Difficulté', 'rpg-ia'); ?></label>
                        <select id="rpg-ia-session-difficulty" class="rpg-ia-select">
                            <option value="easy"><?php _e('Facile', 'rpg-ia'); ?></option>
                            <option value="standard" selected><?php _e('Standard', 'rpg-ia'); ?></option>
                            <option value="hard"><?php _e('Difficile', 'rpg-ia'); ?></option>
                            <option value="deadly"><?php _e('Mortel', 'rpg-ia'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="rpg-ia-form-group">
                    <label for="rpg-ia-session-max-players"><?php _e('Nombre maximum de joueurs', 'rpg-ia'); ?></label>
                    <input type="number" id="rpg-ia-session-max-players" class="rpg-ia-input" min="1" max="10" value="5">
                </div>
                
                <div class="rpg-ia-form-group">
                    <label for="rpg-ia-session-scenario"><?php _e('Scénario', 'rpg-ia'); ?></label>
                    <select id="rpg-ia-session-scenario" class="rpg-ia-select">
                        <option value=""><?php _e('Sélectionner un scénario', 'rpg-ia'); ?></option>
                        <option value="new"><?php _e('Créer un nouveau scénario', 'rpg-ia'); ?></option>
                    </select>
                </div>
                
                <div id="rpg-ia-new-scenario-section" class="rpg-ia-form-group" style="display: none;">
                    <label for="rpg-ia-scenario-name"><?php _e('Nom du scénario', 'rpg-ia'); ?></label>
                    <input type="text" id="rpg-ia-scenario-name" class="rpg-ia-input">
                    
                    <label for="rpg-ia-scenario-description"><?php _e('Description du scénario', 'rpg-ia'); ?></label>
                    <textarea id="rpg-ia-scenario-description" class="rpg-ia-textarea" rows="3"></textarea>
                </div>
                
                <div class="rpg-ia-form-group">
                    <label for="rpg-ia-session-status"><?php _e('Statut', 'rpg-ia'); ?></label>
                    <select id="rpg-ia-session-status" class="rpg-ia-select">
                        <option value="planning"><?php _e('En préparation', 'rpg-ia'); ?></option>
                        <option value="active"><?php _e('Active', 'rpg-ia'); ?></option>
                        <option value="paused"><?php _e('En pause', 'rpg-ia'); ?></option>
                        <option value="completed"><?php _e('Terminée', 'rpg-ia'); ?></option>
                    </select>
                </div>
                
                <div class="rpg-ia-form-group">
                    <label><?php _e('Inviter des joueurs', 'rpg-ia'); ?></label>
                    <div class="rpg-ia-invite-players">
                        <input type="text" id="rpg-ia-player-invite" class="rpg-ia-input" placeholder="<?php _e('Nom d\'utilisateur', 'rpg-ia'); ?>">
                        <button type="button" id="rpg-ia-add-player-btn" class="rpg-ia-button"><?php _e('Ajouter', 'rpg-ia'); ?></button>
                    </div>
                    <div id="rpg-ia-invited-players" class="rpg-ia-invited-players">
                        <!-- Les joueurs invités seront ajoutés ici -->
                    </div>
                </div>
                
                <div class="rpg-ia-form-actions">
                    <button type="button" class="rpg-ia-button rpg-ia-modal-cancel"><?php _e('Annuler', 'rpg-ia'); ?></button>
                    <button type="submit" class="rpg-ia-button rpg-ia-primary-button"><?php _e('Enregistrer', 'rpg-ia'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour rejoindre une session -->
<div id="rpg-ia-join-session-modal" class="rpg-ia-modal">
    <div class="rpg-ia-modal-content">
        <div class="rpg-ia-modal-header">
            <h2><?php _e('Rejoindre la Session', 'rpg-ia'); ?></h2>
            <span class="rpg-ia-modal-close">&times;</span>
        </div>
        <div class="rpg-ia-modal-body">
            <form id="rpg-ia-join-session-form">
                <input type="hidden" id="rpg-ia-join-session-id" value="">
                
                <div class="rpg-ia-form-group">
                    <label for="rpg-ia-join-character"><?php _e('Sélectionner un personnage', 'rpg-ia'); ?></label>
                    <select id="rpg-ia-join-character" class="rpg-ia-select" required>
                        <option value=""><?php _e('Choisir un personnage', 'rpg-ia'); ?></option>
                    </select>
                </div>
                
                <div id="rpg-ia-no-characters-message" class="rpg-ia-message rpg-ia-warning" style="display: none;">
                    <p><?php _e('Vous n\'avez pas encore de personnage.', 'rpg-ia'); ?></p>
                    <a href="<?php echo get_permalink(get_option('rpg_ia_page_rpg-ia-characters')); ?>" class="rpg-ia-button"><?php _e('Créer un personnage', 'rpg-ia'); ?></a>
                </div>
                
                <div class="rpg-ia-form-actions">
                    <button type="button" class="rpg-ia-button rpg-ia-modal-cancel"><?php _e('Annuler', 'rpg-ia'); ?></button>
                    <button type="submit" class="rpg-ia-button rpg-ia-primary-button"><?php _e('Rejoindre', 'rpg-ia'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>