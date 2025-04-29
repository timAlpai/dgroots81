<?php
/**
 * Template pour afficher la liste des personnages
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/public/partials
 */
?>

<div class="rpg-ia-container">
    <h2><?php _e('Mes Personnages', 'rpg-ia'); ?></h2>
    
    <div class="rpg-ia-actions">
        <a href="#" class="rpg-ia-button rpg-ia-create-character-btn">
            <i class="dashicons dashicons-plus"></i> <?php _e('Nouveau Personnage', 'rpg-ia'); ?>
        </a>
    </div>
    
    <div class="rpg-ia-filters">
        <label for="rpg-ia-character-filter"><?php _e('Filtre:', 'rpg-ia'); ?></label>
        <select id="rpg-ia-character-filter">
            <option value="all"><?php _e('Tous', 'rpg-ia'); ?></option>
            <option value="session"><?php _e('Par Session', 'rpg-ia'); ?></option>
            <option value="class"><?php _e('Par Classe', 'rpg-ia'); ?></option>
        </select>
        
        <div id="rpg-ia-session-filter" class="rpg-ia-filter-option" style="display: none;">
            <select id="rpg-ia-session-select">
                <option value=""><?php _e('Sélectionner une session', 'rpg-ia'); ?></option>
                <!-- Les options seront ajoutées dynamiquement via JavaScript -->
            </select>
        </div>
        
        <div id="rpg-ia-class-filter" class="rpg-ia-filter-option" style="display: none;">
            <select id="rpg-ia-class-select">
                <option value=""><?php _e('Sélectionner une classe', 'rpg-ia'); ?></option>
                <option value="clerc"><?php _e('Clerc', 'rpg-ia'); ?></option>
                <option value="guerrier"><?php _e('Guerrier', 'rpg-ia'); ?></option>
                <option value="magicien"><?php _e('Magicien', 'rpg-ia'); ?></option>
                <option value="voleur"><?php _e('Voleur', 'rpg-ia'); ?></option>
                <option value="nain"><?php _e('Nain', 'rpg-ia'); ?></option>
                <option value="elfe"><?php _e('Elfe', 'rpg-ia'); ?></option>
                <option value="halfelin"><?php _e('Halfelin', 'rpg-ia'); ?></option>
            </select>
        </div>
    </div>
    
    <div id="rpg-ia-characters-loading" class="rpg-ia-loading">
        <span class="spinner is-active"></span>
        <p><?php _e('Chargement des personnages...', 'rpg-ia'); ?></p>
    </div>
    
    <div id="rpg-ia-characters-error" class="rpg-ia-error" style="display: none;">
        <p><?php _e('Erreur lors du chargement des personnages.', 'rpg-ia'); ?></p>
        <button class="rpg-ia-button rpg-ia-retry-btn"><?php _e('Réessayer', 'rpg-ia'); ?></button>
    </div>
    
    <div id="rpg-ia-no-characters" class="rpg-ia-empty" style="display: none;">
        <p><?php _e('Vous n\'avez pas encore de personnage.', 'rpg-ia'); ?></p>
        <p><?php _e('Créez votre premier personnage pour commencer à jouer!', 'rpg-ia'); ?></p>
        <button class="rpg-ia-button rpg-ia-create-character-btn"><?php _e('Créer un Personnage', 'rpg-ia'); ?></button>
    </div>
    
    <div id="rpg-ia-characters-list" class="rpg-ia-characters-grid" style="display: none;">
        <!-- Les cartes de personnage seront ajoutées dynamiquement via JavaScript -->
    </div>
    
    <!-- Template pour les cartes de personnage -->
    <template id="rpg-ia-character-card-template">
        <div class="rpg-ia-character-card" data-id="">
            <div class="rpg-ia-character-avatar">
                <img src="" alt="">
            </div>
            <div class="rpg-ia-character-info">
                <h3 class="rpg-ia-character-name"></h3>
                <div class="rpg-ia-character-details">
                    <p><span class="rpg-ia-label"><?php _e('Classe:', 'rpg-ia'); ?></span> <span class="rpg-ia-character-class"></span></p>
                    <p><span class="rpg-ia-label"><?php _e('Niveau:', 'rpg-ia'); ?></span> <span class="rpg-ia-character-level"></span></p>
                    <p><span class="rpg-ia-label"><?php _e('Session:', 'rpg-ia'); ?></span> <span class="rpg-ia-character-session"></span></p>
                </div>
                <div class="rpg-ia-character-stats">
                    <p><span class="rpg-ia-label"><?php _e('PV:', 'rpg-ia'); ?></span> <span class="rpg-ia-character-hp"></span></p>
                    <p><span class="rpg-ia-label"><?php _e('CA:', 'rpg-ia'); ?></span> <span class="rpg-ia-character-ac"></span></p>
                </div>
                <div class="rpg-ia-character-actions">
                    <a href="#" class="rpg-ia-button rpg-ia-view-character-btn"><?php _e('Voir Détails', 'rpg-ia'); ?></a>
                    <a href="#" class="rpg-ia-button rpg-ia-edit-character-btn"><?php _e('Modifier', 'rpg-ia'); ?></a>
                    <a href="#" class="rpg-ia-button rpg-ia-delete-character-btn"><?php _e('Supprimer', 'rpg-ia'); ?></a>
                </div>
            </div>
        </div>
    </template>
</div>

<!-- Modal pour la création/édition de personnage -->
<div id="rpg-ia-character-modal" class="rpg-ia-modal" style="display: none;">
    <div class="rpg-ia-modal-content">
        <span class="rpg-ia-modal-close">&times;</span>
        <div id="rpg-ia-character-form-container">
            <!-- Le formulaire sera chargé ici via AJAX -->
            <?php include plugin_dir_path(__FILE__) . 'rpg-ia-character-form.php'; ?>
        </div>
    </div>
</div>

<!-- Modal de confirmation pour la suppression -->
<div id="rpg-ia-confirm-delete-modal" class="rpg-ia-modal" style="display: none;">
    <div class="rpg-ia-modal-content rpg-ia-modal-small">
        <span class="rpg-ia-modal-close">&times;</span>
        <h3><?php _e('Confirmer la suppression', 'rpg-ia'); ?></h3>
        <p><?php _e('Êtes-vous sûr de vouloir supprimer ce personnage? Cette action est irréversible.', 'rpg-ia'); ?></p>
        <div class="rpg-ia-modal-actions">
            <button class="rpg-ia-button rpg-ia-cancel-btn"><?php _e('Annuler', 'rpg-ia'); ?></button>
            <button class="rpg-ia-button rpg-ia-confirm-delete-btn"><?php _e('Supprimer', 'rpg-ia'); ?></button>
        </div>
    </div>
</div>