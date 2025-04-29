<?php
/**
 * Template pour l'interface de jeu
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/public/partials
 */

// Enregistrer les scripts spécifiques
wp_enqueue_script('rpg-ia-session', plugin_dir_url(dirname(__FILE__)) . 'js/rpg-ia-session.js', array('jquery'), RPG_IA_VERSION, true);
wp_enqueue_script('rpg-ia-game', plugin_dir_url(dirname(__FILE__)) . 'js/rpg-ia-game.js', array('jquery'), RPG_IA_VERSION, true);

// Récupérer l'ID de la session
$session_id = isset($session_id) ? $session_id : get_query_var('session_id', 0);
if (empty($session_id)) {
    $session_id = isset($atts['session']) ? $atts['session'] : 0;
}

// Vérifier si une session est spécifiée
if (empty($session_id)) {
    echo '<p>' . __('Aucune session spécifiée.', 'rpg-ia') . '</p>';
    return;
}
?>

<div class="rpg-ia-container rpg-ia-game-interface" data-session-id="<?php echo esc_attr($session_id); ?>">
    <div id="rpg-ia-game-loading" class="rpg-ia-loading">
        <div class="rpg-ia-spinner"></div>
        <p><?php _e('Chargement de la session...', 'rpg-ia'); ?></p>
    </div>

    <div id="rpg-ia-game-error" class="rpg-ia-error" style="display: none;">
        <p><?php _e('Erreur lors du chargement de la session.', 'rpg-ia'); ?></p>
        <button id="rpg-ia-retry-game" class="rpg-ia-button"><?php _e('Réessayer', 'rpg-ia'); ?></button>
    </div>

    <div id="rpg-ia-character-selection" style="display: none;">
        <h2><?php _e('Sélectionner un personnage', 'rpg-ia'); ?></h2>
        <p><?php _e('Veuillez sélectionner un personnage pour rejoindre cette session.', 'rpg-ia'); ?></p>
        
        <div class="rpg-ia-form-group">
            <select id="rpg-ia-select-character" class="rpg-ia-select">
                <option value=""><?php _e('Choisir un personnage', 'rpg-ia'); ?></option>
            </select>
        </div>
        
        <div id="rpg-ia-no-characters-message" class="rpg-ia-message rpg-ia-warning" style="display: none;">
            <p><?php _e('Vous n\'avez pas encore de personnage.', 'rpg-ia'); ?></p>
            <a href="<?php echo get_permalink(get_option('rpg_ia_page_rpg-ia-characters')); ?>" class="rpg-ia-button"><?php _e('Créer un personnage', 'rpg-ia'); ?></a>
        </div>
        
        <button id="rpg-ia-join-game-btn" class="rpg-ia-button rpg-ia-primary-button"><?php _e('Rejoindre la partie', 'rpg-ia'); ?></button>
    </div>

    <div id="rpg-ia-game-content" style="display: none;">
        <div class="rpg-ia-game-header">
            <div class="rpg-ia-game-info">
                <h2 id="rpg-ia-game-session-name"></h2>
                <span id="rpg-ia-game-session-status" class="rpg-ia-badge"></span>
            </div>
            <div class="rpg-ia-game-actions">
                <button id="rpg-ia-toggle-sidebar-btn" class="rpg-ia-button">
                    <i class="fas fa-bars"></i>
                </button>
                <button id="rpg-ia-leave-game-btn" class="rpg-ia-button">
                    <i class="fas fa-sign-out-alt"></i> <?php _e('Quitter', 'rpg-ia'); ?>
                </button>
            </div>
        </div>

        <div class="rpg-ia-game-layout">
            <div class="rpg-ia-game-main">
                <div class="rpg-ia-narration-container">
                    <div class="rpg-ia-narration-header">
                        <h3><?php _e('Narration', 'rpg-ia'); ?></h3>
                        <div class="rpg-ia-narration-controls">
                            <button id="rpg-ia-clear-narration-btn" class="rpg-ia-button rpg-ia-small-button">
                                <i class="fas fa-eraser"></i>
                            </button>
                            <button id="rpg-ia-scroll-down-btn" class="rpg-ia-button rpg-ia-small-button">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                        </div>
                    </div>
                    <div id="rpg-ia-narration-content" class="rpg-ia-narration-content">
                        <!-- Le contenu de la narration sera ajouté ici dynamiquement -->
                    </div>
                </div>

                <div class="rpg-ia-action-container">
                    <div class="rpg-ia-action-header">
                        <h3><?php _e('Action', 'rpg-ia'); ?></h3>
                    </div>
                    <div class="rpg-ia-action-form">
                        <form id="rpg-ia-action-form">
                            <div class="rpg-ia-form-group">
                                <label for="rpg-ia-action-type"><?php _e('Type d\'action', 'rpg-ia'); ?></label>
                                <select id="rpg-ia-action-type" class="rpg-ia-select">
                                    <option value="dialogue"><?php _e('Dialogue', 'rpg-ia'); ?></option>
                                    <option value="movement"><?php _e('Déplacement', 'rpg-ia'); ?></option>
                                    <option value="combat"><?php _e('Combat', 'rpg-ia'); ?></option>
                                    <option value="skill"><?php _e('Compétence', 'rpg-ia'); ?></option>
                                    <option value="item"><?php _e('Utiliser un objet', 'rpg-ia'); ?></option>
                                    <option value="spell"><?php _e('Lancer un sort', 'rpg-ia'); ?></option>
                                    <option value="other"><?php _e('Autre', 'rpg-ia'); ?></option>
                                </select>
                            </div>
                            <div class="rpg-ia-form-group">
                                <label for="rpg-ia-action-description"><?php _e('Description', 'rpg-ia'); ?></label>
                                <textarea id="rpg-ia-action-description" class="rpg-ia-textarea" rows="3" placeholder="<?php _e('Décrivez votre action...', 'rpg-ia'); ?>"></textarea>
                            </div>
                            <div id="rpg-ia-action-options" class="rpg-ia-form-group" style="display: none;">
                                <label><?php _e('Options avancées', 'rpg-ia'); ?></label>
                                <div class="rpg-ia-checkbox-group">
                                    <label>
                                        <input type="checkbox" id="rpg-ia-action-roll-dice">
                                        <?php _e('Lancer les dés', 'rpg-ia'); ?>
                                    </label>
                                </div>
                                <div id="rpg-ia-dice-options" style="display: none;">
                                    <select id="rpg-ia-dice-type" class="rpg-ia-select">
                                        <option value="d20"><?php _e('d20 (jet de compétence)', 'rpg-ia'); ?></option>
                                        <option value="d20adv"><?php _e('d20 avec avantage', 'rpg-ia'); ?></option>
                                        <option value="d20dis"><?php _e('d20 avec désavantage', 'rpg-ia'); ?></option>
                                        <option value="d4"><?php _e('d4', 'rpg-ia'); ?></option>
                                        <option value="d6"><?php _e('d6', 'rpg-ia'); ?></option>
                                        <option value="d8"><?php _e('d8', 'rpg-ia'); ?></option>
                                        <option value="d10"><?php _e('d10', 'rpg-ia'); ?></option>
                                        <option value="d12"><?php _e('d12', 'rpg-ia'); ?></option>
                                        <option value="d100"><?php _e('d100 (pourcentage)', 'rpg-ia'); ?></option>
                                        <option value="custom"><?php _e('Personnalisé', 'rpg-ia'); ?></option>
                                    </select>
                                    <input type="text" id="rpg-ia-custom-dice" class="rpg-ia-input" placeholder="<?php _e('ex: 2d6+3', 'rpg-ia'); ?>" style="display: none;">
                                </div>
                            </div>
                            <div class="rpg-ia-form-actions">
                                <button type="button" id="rpg-ia-toggle-options-btn" class="rpg-ia-button">
                                    <i class="fas fa-cog"></i> <?php _e('Options', 'rpg-ia'); ?>
                                </button>
                                <button type="submit" id="rpg-ia-submit-action-btn" class="rpg-ia-button rpg-ia-primary-button">
                                    <i class="fas fa-paper-plane"></i> <?php _e('Soumettre', 'rpg-ia'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="rpg-ia-game-sidebar" id="rpg-ia-game-sidebar">
                <div class="rpg-ia-sidebar-tabs">
                    <button class="rpg-ia-tab-button active" data-tab="character">
                        <i class="fas fa-user"></i> <?php _e('Personnage', 'rpg-ia'); ?>
                    </button>
                    <button class="rpg-ia-tab-button" data-tab="players">
                        <i class="fas fa-users"></i> <?php _e('Joueurs', 'rpg-ia'); ?>
                    </button>
                    <button class="rpg-ia-tab-button" data-tab="journal">
                        <i class="fas fa-book"></i> <?php _e('Journal', 'rpg-ia'); ?>
                    </button>
                </div>

                <div class="rpg-ia-sidebar-content">
                    <div class="rpg-ia-tab-content active" id="rpg-ia-tab-character">
                        <div class="rpg-ia-character-header">
                            <h3 id="rpg-ia-character-name"></h3>
                            <span id="rpg-ia-character-class-level"></span>
                        </div>
                        
                        <div class="rpg-ia-character-stats">
                            <div class="rpg-ia-stat-item">
                                <span class="rpg-ia-stat-label"><?php _e('PV', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-stat-value" id="rpg-ia-character-hp"></span>
                            </div>
                            <div class="rpg-ia-stat-item">
                                <span class="rpg-ia-stat-label"><?php _e('CA', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-stat-value" id="rpg-ia-character-ac"></span>
                            </div>
                            <div class="rpg-ia-stat-item">
                                <span class="rpg-ia-stat-label"><?php _e('XP', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-stat-value" id="rpg-ia-character-xp"></span>
                            </div>
                        </div>
                        
                        <div class="rpg-ia-character-abilities">
                            <div class="rpg-ia-ability-item">
                                <span class="rpg-ia-ability-label"><?php _e('FOR', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-ability-value" id="rpg-ia-character-str"></span>
                            </div>
                            <div class="rpg-ia-ability-item">
                                <span class="rpg-ia-ability-label"><?php _e('DEX', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-ability-value" id="rpg-ia-character-dex"></span>
                            </div>
                            <div class="rpg-ia-ability-item">
                                <span class="rpg-ia-ability-label"><?php _e('CON', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-ability-value" id="rpg-ia-character-con"></span>
                            </div>
                            <div class="rpg-ia-ability-item">
                                <span class="rpg-ia-ability-label"><?php _e('INT', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-ability-value" id="rpg-ia-character-int"></span>
                            </div>
                            <div class="rpg-ia-ability-item">
                                <span class="rpg-ia-ability-label"><?php _e('SAG', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-ability-value" id="rpg-ia-character-wis"></span>
                            </div>
                            <div class="rpg-ia-ability-item">
                                <span class="rpg-ia-ability-label"><?php _e('CHA', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-ability-value" id="rpg-ia-character-cha"></span>
                            </div>
                        </div>
                        
                        <div class="rpg-ia-character-sections">
                            <div class="rpg-ia-collapsible">
                                <div class="rpg-ia-collapsible-header">
                                    <h4><?php _e('Équipement', 'rpg-ia'); ?></h4>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="rpg-ia-collapsible-content">
                                    <ul id="rpg-ia-character-equipment" class="rpg-ia-list">
                                        <!-- L'équipement sera ajouté ici dynamiquement -->
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="rpg-ia-collapsible">
                                <div class="rpg-ia-collapsible-header">
                                    <h4><?php _e('Inventaire', 'rpg-ia'); ?></h4>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="rpg-ia-collapsible-content">
                                    <ul id="rpg-ia-character-inventory" class="rpg-ia-list">
                                        <!-- L'inventaire sera ajouté ici dynamiquement -->
                                    </ul>
                                    <div class="rpg-ia-inventory-gold">
                                        <span><?php _e('Or:', 'rpg-ia'); ?></span>
                                        <span id="rpg-ia-character-gold"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="rpg-ia-collapsible">
                                <div class="rpg-ia-collapsible-header">
                                    <h4><?php _e('Compétences', 'rpg-ia'); ?></h4>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="rpg-ia-collapsible-content">
                                    <ul id="rpg-ia-character-skills" class="rpg-ia-list">
                                        <!-- Les compétences seront ajoutées ici dynamiquement -->
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="rpg-ia-collapsible">
                                <div class="rpg-ia-collapsible-header">
                                    <h4><?php _e('Sorts', 'rpg-ia'); ?></h4>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="rpg-ia-collapsible-content">
                                    <ul id="rpg-ia-character-spells" class="rpg-ia-list">
                                        <!-- Les sorts seront ajoutés ici dynamiquement -->
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rpg-ia-tab-content" id="rpg-ia-tab-players">
                        <h3><?php _e('Joueurs', 'rpg-ia'); ?></h3>
                        <div id="rpg-ia-players-list" class="rpg-ia-players-list">
                            <!-- Les joueurs seront ajoutés ici dynamiquement -->
                        </div>
                        
                        <div class="rpg-ia-chat-container">
                            <h4><?php _e('Chat', 'rpg-ia'); ?></h4>
                            <div id="rpg-ia-chat-messages" class="rpg-ia-chat-messages">
                                <!-- Les messages de chat seront ajoutés ici dynamiquement -->
                            </div>
                            <div class="rpg-ia-chat-input">
                                <input type="text" id="rpg-ia-chat-message" class="rpg-ia-input" placeholder="<?php _e('Envoyer un message...', 'rpg-ia'); ?>">
                                <button id="rpg-ia-send-chat-btn" class="rpg-ia-button">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="rpg-ia-tab-content" id="rpg-ia-tab-journal">
                        <h3><?php _e('Journal de Session', 'rpg-ia'); ?></h3>
                        
                        <div class="rpg-ia-journal-filters">
                            <select id="rpg-ia-journal-filter" class="rpg-ia-select">
                                <option value="all"><?php _e('Tout', 'rpg-ia'); ?></option>
                                <option value="combat"><?php _e('Combats', 'rpg-ia'); ?></option>
                                <option value="dialogue"><?php _e('Dialogues', 'rpg-ia'); ?></option>
                                <option value="skill"><?php _e('Compétences', 'rpg-ia'); ?></option>
                                <option value="item"><?php _e('Objets', 'rpg-ia'); ?></option>
                                <option value="npc"><?php _e('PNJ', 'rpg-ia'); ?></option>
                            </select>
                        </div>
                        
                        <div id="rpg-ia-journal-entries" class="rpg-ia-journal-entries">
                            <!-- Les entrées du journal seront ajoutées ici dynamiquement -->
                        </div>
                        
                        <div class="rpg-ia-notes-section">
                            <h4><?php _e('Notes Personnelles', 'rpg-ia'); ?></h4>
                            <textarea id="rpg-ia-personal-notes" class="rpg-ia-textarea" rows="5" placeholder="<?php _e('Ajoutez vos notes ici...', 'rpg-ia'); ?>"></textarea>
                            <button id="rpg-ia-save-notes-btn" class="rpg-ia-button">
                                <i class="fas fa-save"></i> <?php _e('Enregistrer', 'rpg-ia'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template pour un message de narration -->
<template id="rpg-ia-narration-message-template">
    <div class="rpg-ia-narration-message">
        <div class="rpg-ia-narration-content"></div>
        <div class="rpg-ia-narration-timestamp"></div>
    </div>
</template>

<!-- Template pour une action de joueur dans la narration -->
<template id="rpg-ia-player-action-template">
    <div class="rpg-ia-player-action">
        <div class="rpg-ia-player-action-header">
            <span class="rpg-ia-player-name"></span>
            <span class="rpg-ia-action-type"></span>
        </div>
        <div class="rpg-ia-player-action-content"></div>
        <div class="rpg-ia-narration-timestamp"></div>
    </div>
</template>

<!-- Template pour un joueur dans la liste des joueurs -->
<template id="rpg-ia-player-list-item-template">
    <div class="rpg-ia-player-list-item">
        <div class="rpg-ia-player-info">
            <span class="rpg-ia-player-name"></span>
            <span class="rpg-ia-player-character"></span>
        </div>
        <div class="rpg-ia-player-status">
            <span class="rpg-ia-player-hp"></span>
        </div>
    </div>
</template>

<!-- Template pour un message de chat -->
<template id="rpg-ia-chat-message-template">
    <div class="rpg-ia-chat-message">
        <span class="rpg-ia-chat-sender"></span>
        <span class="rpg-ia-chat-content"></span>
        <span class="rpg-ia-chat-timestamp"></span>
    </div>
</template>

<!-- Template pour une entrée de journal -->
<template id="rpg-ia-journal-entry-template">
    <div class="rpg-ia-journal-entry">
        <div class="rpg-ia-journal-entry-header">
            <span class="rpg-ia-journal-timestamp"></span>
            <span class="rpg-ia-journal-type"></span>
        </div>
        <div class="rpg-ia-journal-entry-content"></div>
    </div>
</template>

<!-- Modal de confirmation pour quitter la partie -->
<div id="rpg-ia-leave-game-modal" class="rpg-ia-modal">
    <div class="rpg-ia-modal-content">
        <div class="rpg-ia-modal-header">
            <h2><?php _e('Quitter la partie', 'rpg-ia'); ?></h2>
            <span class="rpg-ia-modal-close">&times;</span>
        </div>
        <div class="rpg-ia-modal-body">
            <p><?php _e('Êtes-vous sûr de vouloir quitter cette partie ?', 'rpg-ia'); ?></p>
            
            <div class="rpg-ia-form-actions">
                <button type="button" class="rpg-ia-button rpg-ia-modal-cancel"><?php _e('Annuler', 'rpg-ia'); ?></button>
                <button type="button" id="rpg-ia-confirm-leave-btn" class="rpg-ia-button rpg-ia-primary-button"><?php _e('Quitter', 'rpg-ia'); ?></button>
            </div>
        </div>
    </div>
</div>