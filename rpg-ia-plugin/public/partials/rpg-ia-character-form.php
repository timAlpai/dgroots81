<?php
/**
 * Template pour le formulaire de création/édition de personnage
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/public/partials
 */
?>

<div class="rpg-ia-character-form">
    <h2 id="rpg-ia-character-form-title"><?php _e('Créer un Nouveau Personnage', 'rpg-ia'); ?></h2>
    
    <form id="rpg-ia-character-form">
        <input type="hidden" id="rpg-ia-character-id" name="character_id" value="">
        
        <div class="rpg-ia-form-row">
            <div class="rpg-ia-form-col">
                <div class="rpg-ia-form-section">
                    <h3><?php _e('Informations Générales', 'rpg-ia'); ?></h3>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-name"><?php _e('Nom:', 'rpg-ia'); ?></label>
                        <input type="text" id="rpg-ia-character-name" name="name" required>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-class"><?php _e('Classe:', 'rpg-ia'); ?></label>
                        <select id="rpg-ia-character-class" name="character_class" required>
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
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-level"><?php _e('Niveau:', 'rpg-ia'); ?></label>
                        <input type="number" id="rpg-ia-character-level" name="level" min="1" max="20" value="1" readonly>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-experience"><?php _e('Expérience:', 'rpg-ia'); ?></label>
                        <input type="number" id="rpg-ia-character-experience" name="experience" min="0" value="0" readonly>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-game-session"><?php _e('Session de jeu:', 'rpg-ia'); ?></label>
                        <select id="rpg-ia-character-game-session" name="game_session_id" required>
                            <option value=""><?php _e('Sélectionner une session', 'rpg-ia'); ?></option>
                            <!-- Les options seront ajoutées dynamiquement via JavaScript -->
                        </select>
                    </div>
                </div>
                
                <div class="rpg-ia-form-section">
                    <h3><?php _e('Points de Vie et Armure', 'rpg-ia'); ?></h3>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-max-hp"><?php _e('PV Maximum:', 'rpg-ia'); ?></label>
                        <input type="number" id="rpg-ia-character-max-hp" name="max_hp" min="1" required>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-current-hp"><?php _e('PV Actuels:', 'rpg-ia'); ?></label>
                        <input type="number" id="rpg-ia-character-current-hp" name="current_hp" min="0" required>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-armor-class"><?php _e('Classe d\'Armure:', 'rpg-ia'); ?></label>
                        <input type="number" id="rpg-ia-character-armor-class" name="armor_class" min="0" value="10" required>
                    </div>
                </div>
            </div>
            
            <div class="rpg-ia-form-col">
                <div class="rpg-ia-form-section">
                    <h3><?php _e('Caractéristiques', 'rpg-ia'); ?></h3>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-strength"><?php _e('Force:', 'rpg-ia'); ?></label>
                        <div class="rpg-ia-ability-input">
                            <input type="number" id="rpg-ia-character-strength" name="strength" min="3" max="18" required>
                            <span class="rpg-ia-ability-modifier" id="rpg-ia-strength-modifier"></span>
                        </div>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-intelligence"><?php _e('Intelligence:', 'rpg-ia'); ?></label>
                        <div class="rpg-ia-ability-input">
                            <input type="number" id="rpg-ia-character-intelligence" name="intelligence" min="3" max="18" required>
                            <span class="rpg-ia-ability-modifier" id="rpg-ia-intelligence-modifier"></span>
                        </div>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-wisdom"><?php _e('Sagesse:', 'rpg-ia'); ?></label>
                        <div class="rpg-ia-ability-input">
                            <input type="number" id="rpg-ia-character-wisdom" name="wisdom" min="3" max="18" required>
                            <span class="rpg-ia-ability-modifier" id="rpg-ia-wisdom-modifier"></span>
                        </div>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-dexterity"><?php _e('Dextérité:', 'rpg-ia'); ?></label>
                        <div class="rpg-ia-ability-input">
                            <input type="number" id="rpg-ia-character-dexterity" name="dexterity" min="3" max="18" required>
                            <span class="rpg-ia-ability-modifier" id="rpg-ia-dexterity-modifier"></span>
                        </div>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-constitution"><?php _e('Constitution:', 'rpg-ia'); ?></label>
                        <div class="rpg-ia-ability-input">
                            <input type="number" id="rpg-ia-character-constitution" name="constitution" min="3" max="18" required>
                            <span class="rpg-ia-ability-modifier" id="rpg-ia-constitution-modifier"></span>
                        </div>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-charisma"><?php _e('Charisme:', 'rpg-ia'); ?></label>
                        <div class="rpg-ia-ability-input">
                            <input type="number" id="rpg-ia-character-charisma" name="charisma" min="3" max="18" required>
                            <span class="rpg-ia-ability-modifier" id="rpg-ia-charisma-modifier"></span>
                        </div>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <button type="button" id="rpg-ia-roll-abilities" class="rpg-ia-button"><?php _e('Générer Aléatoirement', 'rpg-ia'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="rpg-ia-form-row">
            <div class="rpg-ia-form-col">
                <div class="rpg-ia-form-section">
                    <h3><?php _e('Équipement', 'rpg-ia'); ?></h3>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-equipment"><?php _e('Équipement:', 'rpg-ia'); ?></label>
                        <div class="rpg-ia-equipment-list" id="rpg-ia-equipment-list">
                            <div class="rpg-ia-equipment-item">
                                <input type="text" name="equipment[]" placeholder="<?php _e('Nom de l\'objet', 'rpg-ia'); ?>">
                                <button type="button" class="rpg-ia-remove-item">&times;</button>
                            </div>
                        </div>
                        <button type="button" id="rpg-ia-add-equipment" class="rpg-ia-button rpg-ia-small-button"><?php _e('Ajouter un objet', 'rpg-ia'); ?></button>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-gold"><?php _e('Or:', 'rpg-ia'); ?></label>
                        <input type="number" id="rpg-ia-character-gold" name="gold" min="0" value="0">
                    </div>
                </div>
            </div>
            
            <div class="rpg-ia-form-col">
                <div class="rpg-ia-form-section">
                    <h3><?php _e('Compétences et Sorts', 'rpg-ia'); ?></h3>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-skills"><?php _e('Compétences:', 'rpg-ia'); ?></label>
                        <div class="rpg-ia-skills-list" id="rpg-ia-skills-list">
                            <div class="rpg-ia-skill-item">
                                <input type="text" name="skills[]" placeholder="<?php _e('Nom de la compétence', 'rpg-ia'); ?>">
                                <button type="button" class="rpg-ia-remove-item">&times;</button>
                            </div>
                        </div>
                        <button type="button" id="rpg-ia-add-skill" class="rpg-ia-button rpg-ia-small-button"><?php _e('Ajouter une compétence', 'rpg-ia'); ?></button>
                    </div>
                    
                    <div class="rpg-ia-form-field">
                        <label for="rpg-ia-character-spells"><?php _e('Sorts:', 'rpg-ia'); ?></label>
                        <div class="rpg-ia-spells-list" id="rpg-ia-spells-list">
                            <div class="rpg-ia-spell-item">
                                <input type="text" name="spells[]" placeholder="<?php _e('Nom du sort', 'rpg-ia'); ?>">
                                <button type="button" class="rpg-ia-remove-item">&times;</button>
                            </div>
                        </div>
                        <button type="button" id="rpg-ia-add-spell" class="rpg-ia-button rpg-ia-small-button"><?php _e('Ajouter un sort', 'rpg-ia'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="rpg-ia-form-row">
            <div class="rpg-ia-form-col">
                <div class="rpg-ia-form-section">
                    <h3><?php _e('Biographie', 'rpg-ia'); ?></h3>
                    
                    <div class="rpg-ia-form-field">
                        <textarea id="rpg-ia-character-background" name="background" rows="5" placeholder="<?php _e('Histoire et antécédents du personnage...', 'rpg-ia'); ?>"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="rpg-ia-form-col">
                <div class="rpg-ia-form-section">
                    <h3><?php _e('Apparence', 'rpg-ia'); ?></h3>
                    
                    <div class="rpg-ia-form-field">
                        <textarea id="rpg-ia-character-appearance" name="appearance" rows="5" placeholder="<?php _e('Description physique du personnage...', 'rpg-ia'); ?>"></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="rpg-ia-form-actions">
            <button type="button" id="rpg-ia-cancel-character" class="rpg-ia-button rpg-ia-cancel-btn"><?php _e('Annuler', 'rpg-ia'); ?></button>
            <button type="submit" id="rpg-ia-save-character" class="rpg-ia-button rpg-ia-save-btn"><?php _e('Enregistrer', 'rpg-ia'); ?></button>
        </div>
    </form>
    
    <div id="rpg-ia-character-form-loading" class="rpg-ia-loading" style="display: none;">
        <span class="spinner is-active"></span>
        <p><?php _e('Enregistrement en cours...', 'rpg-ia'); ?></p>
    </div>
    
    <div id="rpg-ia-character-form-error" class="rpg-ia-error" style="display: none;">
        <p id="rpg-ia-character-form-error-message"></p>
    </div>
</div>