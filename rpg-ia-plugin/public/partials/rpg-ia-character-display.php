<?php
/**
 * Template pour afficher les détails d'un personnage
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/public/partials
 */

// Récupérer l'ID du personnage
$character_id = isset($character) ? $character->ID : (isset($_GET['character_id']) ? intval($_GET['character_id']) : 0);

// Si aucun ID n'est fourni, afficher un message d'erreur
if (empty($character_id)) {
    echo '<p>' . __('Aucun personnage spécifié.', 'rpg-ia') . '</p>';
    return;
}

// Initialiser le gestionnaire de personnages
$character_manager = new RPG_IA_Character_Manager();
?>

<div class="rpg-ia-container">
    <div id="rpg-ia-character-loading" class="rpg-ia-loading">
        <span class="spinner is-active"></span>
        <p><?php _e('Chargement du personnage...', 'rpg-ia'); ?></p>
    </div>
    
    <div id="rpg-ia-character-error" class="rpg-ia-error" style="display: none;">
        <p><?php _e('Erreur lors du chargement du personnage.', 'rpg-ia'); ?></p>
        <button class="rpg-ia-button rpg-ia-retry-btn"><?php _e('Réessayer', 'rpg-ia'); ?></button>
    </div>
    
    <div id="rpg-ia-character-display" class="rpg-ia-character-details" style="display: none;" data-id="<?php echo esc_attr($character_id); ?>">
        <div class="rpg-ia-character-header">
            <h2 id="rpg-ia-character-name"></h2>
            <div class="rpg-ia-character-actions">
                <a href="#" class="rpg-ia-button rpg-ia-edit-character-btn">
                    <i class="dashicons dashicons-edit"></i> <?php _e('Modifier', 'rpg-ia'); ?>
                </a>
                <a href="<?php echo esc_url(get_permalink(get_option('rpg_ia_page_rpg-ia-characters'))); ?>" class="rpg-ia-button">
                    <i class="dashicons dashicons-arrow-left-alt"></i> <?php _e('Retour à la liste', 'rpg-ia'); ?>
                </a>
            </div>
        </div>
        
        <div class="rpg-ia-character-content">
            <div class="rpg-ia-character-row">
                <div class="rpg-ia-character-col">
                    <div class="rpg-ia-character-section">
                        <h3><?php _e('Informations Générales', 'rpg-ia'); ?></h3>
                        <div class="rpg-ia-character-info-grid">
                            <div class="rpg-ia-character-info-item">
                                <span class="rpg-ia-label"><?php _e('Classe:', 'rpg-ia'); ?></span>
                                <span id="rpg-ia-character-class"></span>
                            </div>
                            <div class="rpg-ia-character-info-item">
                                <span class="rpg-ia-label"><?php _e('Niveau:', 'rpg-ia'); ?></span>
                                <span id="rpg-ia-character-level"></span>
                            </div>
                            <div class="rpg-ia-character-info-item">
                                <span class="rpg-ia-label"><?php _e('Expérience:', 'rpg-ia'); ?></span>
                                <span id="rpg-ia-character-experience"></span>
                            </div>
                            <div class="rpg-ia-character-info-item">
                                <span class="rpg-ia-label"><?php _e('Session:', 'rpg-ia'); ?></span>
                                <span id="rpg-ia-character-session"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="rpg-ia-character-section">
                        <h3><?php _e('Points de Vie et Armure', 'rpg-ia'); ?></h3>
                        <div class="rpg-ia-character-vitals">
                            <div class="rpg-ia-character-hp">
                                <span class="rpg-ia-label"><?php _e('PV:', 'rpg-ia'); ?></span>
                                <span id="rpg-ia-character-hp"></span>
                            </div>
                            <div class="rpg-ia-character-ac">
                                <span class="rpg-ia-label"><?php _e('CA:', 'rpg-ia'); ?></span>
                                <span id="rpg-ia-character-ac"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="rpg-ia-character-col">
                    <div class="rpg-ia-character-section">
                        <h3><?php _e('Caractéristiques', 'rpg-ia'); ?></h3>
                        <div class="rpg-ia-character-abilities">
                            <div class="rpg-ia-ability">
                                <span class="rpg-ia-ability-name"><?php _e('FOR', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-ability-value" id="rpg-ia-character-strength"></span>
                                <span class="rpg-ia-ability-modifier" id="rpg-ia-strength-modifier"></span>
                            </div>
                            <div class="rpg-ia-ability">
                                <span class="rpg-ia-ability-name"><?php _e('INT', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-ability-value" id="rpg-ia-character-intelligence"></span>
                                <span class="rpg-ia-ability-modifier" id="rpg-ia-intelligence-modifier"></span>
                            </div>
                            <div class="rpg-ia-ability">
                                <span class="rpg-ia-ability-name"><?php _e('SAG', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-ability-value" id="rpg-ia-character-wisdom"></span>
                                <span class="rpg-ia-ability-modifier" id="rpg-ia-wisdom-modifier"></span>
                            </div>
                            <div class="rpg-ia-ability">
                                <span class="rpg-ia-ability-name"><?php _e('DEX', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-ability-value" id="rpg-ia-character-dexterity"></span>
                                <span class="rpg-ia-ability-modifier" id="rpg-ia-dexterity-modifier"></span>
                            </div>
                            <div class="rpg-ia-ability">
                                <span class="rpg-ia-ability-name"><?php _e('CON', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-ability-value" id="rpg-ia-character-constitution"></span>
                                <span class="rpg-ia-ability-modifier" id="rpg-ia-constitution-modifier"></span>
                            </div>
                            <div class="rpg-ia-ability">
                                <span class="rpg-ia-ability-name"><?php _e('CHA', 'rpg-ia'); ?></span>
                                <span class="rpg-ia-ability-value" id="rpg-ia-character-charisma"></span>
                                <span class="rpg-ia-ability-modifier" id="rpg-ia-charisma-modifier"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="rpg-ia-character-row">
                <div class="rpg-ia-character-col">
                    <div class="rpg-ia-character-section">
                        <h3><?php _e('Équipement', 'rpg-ia'); ?></h3>
                        <div class="rpg-ia-character-equipment">
                            <ul id="rpg-ia-character-equipment-list"></ul>
                            <div class="rpg-ia-character-gold">
                                <span class="rpg-ia-label"><?php _e('Or:', 'rpg-ia'); ?></span>
                                <span id="rpg-ia-character-gold"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="rpg-ia-character-col">
                    <div class="rpg-ia-character-section">
                        <h3><?php _e('Compétences', 'rpg-ia'); ?></h3>
                        <ul id="rpg-ia-character-skills-list"></ul>
                    </div>
                    
                    <div class="rpg-ia-character-section">
                        <h3><?php _e('Sorts', 'rpg-ia'); ?></h3>
                        <ul id="rpg-ia-character-spells-list"></ul>
                    </div>
                </div>
            </div>
            
            <div class="rpg-ia-character-row">
                <div class="rpg-ia-character-col">
                    <div class="rpg-ia-character-section">
                        <h3><?php _e('Biographie', 'rpg-ia'); ?></h3>
                        <div id="rpg-ia-character-background" class="rpg-ia-character-text"></div>
                    </div>
                </div>
                
                <div class="rpg-ia-character-col">
                    <div class="rpg-ia-character-section">
                        <h3><?php _e('Apparence', 'rpg-ia'); ?></h3>
                        <div id="rpg-ia-character-appearance" class="rpg-ia-character-text"></div>
                    </div>
                </div>
            </div>
            
            <div class="rpg-ia-character-row">
                <div class="rpg-ia-character-col rpg-ia-character-col-full">
                    <div class="rpg-ia-character-section">
                        <h3><?php _e('Historique des Actions', 'rpg-ia'); ?></h3>
                        <div id="rpg-ia-character-actions-loading" class="rpg-ia-loading">
                            <span class="spinner is-active"></span>
                            <p><?php _e('Chargement des actions...', 'rpg-ia'); ?></p>
                        </div>
                        <div id="rpg-ia-character-actions-empty" class="rpg-ia-empty" style="display: none;">
                            <p><?php _e('Aucune action enregistrée pour ce personnage.', 'rpg-ia'); ?></p>
                        </div>
                        <ul id="rpg-ia-character-actions-list" class="rpg-ia-actions-list" style="display: none;"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour l'édition de personnage -->
<div id="rpg-ia-character-modal" class="rpg-ia-modal" style="display: none;">
    <div class="rpg-ia-modal-content">
        <span class="rpg-ia-modal-close">&times;</span>
        <div id="rpg-ia-character-form-container">
            <!-- Le formulaire sera chargé ici via AJAX -->
            <?php include plugin_dir_path(__FILE__) . 'rpg-ia-character-form.php'; ?>
        </div>
    </div>
</div>