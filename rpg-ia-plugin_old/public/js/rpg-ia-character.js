/**
 * Script pour gérer les personnages
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/public/js
 */

(function($) {
    'use strict';

    /**
     * Gestionnaire de personnages
     */
    const RPGIACharacter = {
        /**
         * Initialise le gestionnaire de personnages
         */
        init: function() {
            this.setupVariables();
            this.bindEvents();
            this.initPage();
        },

        /**
         * Configure les variables
         */
        setupVariables: function() {
            this.restUrl = rpg_ia_public.rest_url;
            this.nonce = rpg_ia_public.nonce;
            this.messages = rpg_ia_public.messages;
            this.token = this.getCookie('rpg_ia_token');
            this.characterId = null;
            this.isEditing = false;
            this.sessions = [];
        },

        /**
         * Lie les événements
         */
        bindEvents: function() {
            // Liste des personnages
            // $('.rpg-ia-create-character-btn').on('click', this.showCreateCharacterForm.bind(this));
            $(document).on('click', '.rpg-ia-create-character-btn', this.showCreateCharacterForm.bind(this));
            $(document).on('click', '.rpg-ia-view-character-btn', this.viewCharacter.bind(this));
            $(document).on('click', '.rpg-ia-edit-character-btn', this.editCharacter.bind(this));
            $(document).on('click', '.rpg-ia-delete-character-btn', this.confirmDeleteCharacter.bind(this));
            $('.rpg-ia-retry-btn').on('click', this.loadCharacters.bind(this));
            
            // Filtres
            $('#rpg-ia-character-filter').on('change', this.handleFilterChange.bind(this));
            $('#rpg-ia-session-select').on('change', this.filterCharacters.bind(this));
            $('#rpg-ia-class-select').on('change', this.filterCharacters.bind(this));
            
            // Formulaire de personnage
            $('#rpg-ia-character-form').on('submit', this.saveCharacter.bind(this));
            $('#rpg-ia-cancel-character').on('click', this.closeCharacterModal.bind(this));
            $('#rpg-ia-roll-abilities').on('click', this.rollAbilities.bind(this));
            $('#rpg-ia-character-class').on('change', this.updateClassSkills.bind(this));
            
            // Caractéristiques
            $('.rpg-ia-ability-input input').on('change', this.updateAbilityModifiers.bind(this));
            
            // Équipement, compétences et sorts
            $('#rpg-ia-add-equipment').on('click', this.addEquipmentItem.bind(this));
            $('#rpg-ia-add-skill').on('click', this.addSkillItem.bind(this));
            $('#rpg-ia-add-spell').on('click', this.addSpellItem.bind(this));
            $(document).on('click', '.rpg-ia-remove-item', this.removeItem.bind(this));
            
            // Modal de confirmation de suppression
            $('.rpg-ia-modal-close, .rpg-ia-cancel-btn').on('click', this.closeModal.bind(this));
            $('.rpg-ia-confirm-delete-btn').on('click', this.deleteCharacter.bind(this));
        },

        /**
         * Initialise la page en fonction du contexte
         */
        initPage: function() {
            // Vérifier si nous sommes sur la page de liste des personnages
            if ($('#rpg-ia-characters-list').length > 0) {
                this.loadCharacters();
                this.loadSessions();
            }
            
            // Vérifier si nous sommes sur la page de détails d'un personnage
            if ($('#rpg-ia-character-display').length > 0) {
                const characterId = $('#rpg-ia-character-display').data('id');
                this.loadCharacterDetails(characterId);
            }
        },

        /**
         * Charge la liste des personnages
         */
        loadCharacters: function() {
            $('#rpg-ia-characters-loading').show();
            $('#rpg-ia-characters-error').hide();
            $('#rpg-ia-characters-list').hide();
            $('#rpg-ia-no-characters').hide();
            
            $.ajax({
                url: this.restUrl + 'rpg-ia/v1/characters',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + this.token);
                }.bind(this),
                success: function(response) {
                    $('#rpg-ia-characters-loading').hide();
                    
                    if (response.success && response.characters && response.characters.length > 0) {
                        this.renderCharactersList(response.characters);
                        $('#rpg-ia-characters-list').show();
                    } else {
                        $('#rpg-ia-no-characters').show();
                    }
                }.bind(this),
                error: function(xhr) {
                    $('#rpg-ia-characters-loading').hide();
                    $('#rpg-ia-characters-error').show();
                    console.error('Error loading characters:', xhr.responseText);
                }
            });
        },

        /**
         * Charge la liste des sessions
         */
        loadSessions: function() {
            $.ajax({
                url: this.restUrl + 'rpg-ia/v1/sessions',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + this.token);
                }.bind(this),
                success: function(response) {
                    if (response.success && response.sessions) {
                        this.sessions = response.sessions;
                        this.populateSessionDropdowns();
                    }
                }.bind(this),
                error: function(xhr) {
                    console.error('Error loading sessions:', xhr.responseText);
                }
            });
        },

        /**
         * Remplit les listes déroulantes de sessions
         */
        populateSessionDropdowns: function() {
            const sessionSelect = $('#rpg-ia-session-select');
            const characterSessionSelect = $('#rpg-ia-character-game-session');
            
            sessionSelect.empty();
            characterSessionSelect.empty();
            
            sessionSelect.append($('<option>', {
                value: '',
                text: rpg_ia_public.messages.select_session || 'Sélectionner une session'
            }));
            
            characterSessionSelect.append($('<option>', {
                value: '',
                text: rpg_ia_public.messages.select_session || 'Sélectionner une session'
            }));
            
            this.sessions.forEach(function(session) {
                sessionSelect.append($('<option>', {
                    value: session.id,
                    text: session.name
                }));
                
                characterSessionSelect.append($('<option>', {
                    value: session.id,
                    text: session.name
                }));
            });
        },

        /**
         * Affiche la liste des personnages
         * 
         * @param {Array} characters La liste des personnages
         */
        renderCharactersList: function(characters) {
            const container = $('#rpg-ia-characters-list');
            container.empty();
            
            characters.forEach(function(character) {
                const template = document.getElementById('rpg-ia-character-card-template');
                const clone = document.importNode(template.content, true);
                
                // Remplir les données du personnage
                const card = $(clone).find('.rpg-ia-character-card');
                card.attr('data-id', character.id);
                card.attr('data-class', character.character_class);
                card.attr('data-session', character.game_session_id);
                
                $(clone).find('.rpg-ia-character-name').text(character.name);
                $(clone).find('.rpg-ia-character-class').text(this.formatClassName(character.character_class));
                $(clone).find('.rpg-ia-character-level').text(character.level);
                
                // Trouver le nom de la session
                const session = this.sessions.find(s => s.id === character.game_session_id);
                $(clone).find('.rpg-ia-character-session').text(session ? session.name : '-');
                
                $(clone).find('.rpg-ia-character-hp').text(character.current_hp + '/' + character.max_hp);
                $(clone).find('.rpg-ia-character-ac').text(character.armor_class);
                
                // Configurer les boutons d'action
                $(clone).find('.rpg-ia-view-character-btn').attr('data-id', character.id);
                $(clone).find('.rpg-ia-edit-character-btn').attr('data-id', character.id);
                $(clone).find('.rpg-ia-delete-character-btn').attr('data-id', character.id);
                
                container.append(clone);
            }.bind(this));
        },

        /**
         * Filtre les personnages en fonction des critères sélectionnés
         */
        filterCharacters: function() {
            const filterType = $('#rpg-ia-character-filter').val();
            const sessionId = $('#rpg-ia-session-select').val();
            const characterClass = $('#rpg-ia-class-select').val();
            
            $('.rpg-ia-character-card').show();
            
            if (filterType === 'session' && sessionId) {
                $('.rpg-ia-character-card').not(`[data-session="${sessionId}"]`).hide();
            } else if (filterType === 'class' && characterClass) {
                $('.rpg-ia-character-card').not(`[data-class="${characterClass}"]`).hide();
            }
        },

        /**
         * Gère le changement de filtre
         */
        handleFilterChange: function() {
            const filterType = $('#rpg-ia-character-filter').val();
            
            $('.rpg-ia-filter-option').hide();
            
            if (filterType === 'session') {
                $('#rpg-ia-session-filter').show();
            } else if (filterType === 'class') {
                $('#rpg-ia-class-filter').show();
            }
            
            this.filterCharacters();
        },

        /**
         * Affiche le formulaire de création de personnage
         * 
         * @param {Event} e L'événement
         */
        showCreateCharacterForm: function(e) {
            e.preventDefault();
            
            this.isEditing = false;
            this.characterId = null;
            
            // Réinitialiser le formulaire
            $('#rpg-ia-character-form')[0].reset();
            $('#rpg-ia-character-id').val('');
            $('#rpg-ia-character-form-title').text(rpg_ia_public.messages.create_character || 'Créer un Nouveau Personnage');
            
            // Réinitialiser les listes
            this.resetItemLists();
            
            // Afficher le modal
            $('#rpg-ia-character-modal').show();
        },

        /**
         * Affiche les détails d'un personnage
         * 
         * @param {Event} e L'événement
         */
        viewCharacter: function(e) {
            e.preventDefault();
            
            const characterId = $(e.currentTarget).data('id');
            window.location.href = rpg_ia_public.characters_url + '?character_id=' + characterId;
        },

        /**
         * Charge les détails d'un personnage
         * 
         * @param {number} characterId L'ID du personnage
         */
        loadCharacterDetails: function(characterId) {
            $('#rpg-ia-character-loading').show();
            $('#rpg-ia-character-error').hide();
            $('#rpg-ia-character-display').hide();
            
            $.ajax({
                url: this.restUrl + 'rpg-ia/v1/characters/' + characterId,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + this.token);
                }.bind(this),
                success: function(response) {
                    $('#rpg-ia-character-loading').hide();
                    
                    if (response.success && response.character) {
                        this.renderCharacterDetails(response.character);
                        $('#rpg-ia-character-display').show();
                        this.loadCharacterActions(characterId);
                    } else {
                        $('#rpg-ia-character-error').show();
                    }
                }.bind(this),
                error: function(xhr) {
                    $('#rpg-ia-character-loading').hide();
                    $('#rpg-ia-character-error').show();
                    console.error('Error loading character details:', xhr.responseText);
                }
            });
        },
/**
         * Affiche les détails d'un personnage
         * 
         * @param {Object} character Les données du personnage
         */
        renderCharacterDetails: function(character) {
            // Informations générales
            $('#rpg-ia-character-name').text(character.name);
            $('#rpg-ia-character-class').text(this.formatClassName(character.character_class));
            $('#rpg-ia-character-level').text(character.level);
            $('#rpg-ia-character-experience').text(character.experience);
            
            // Points de vie et armure
            $('#rpg-ia-character-hp').text(character.current_hp + '/' + character.max_hp);
            $('#rpg-ia-character-ac').text(character.armor_class);
            
            // Caractéristiques
            $('#rpg-ia-character-strength').text(character.strength);
            $('#rpg-ia-character-intelligence').text(character.intelligence);
            $('#rpg-ia-character-wisdom').text(character.wisdom);
            $('#rpg-ia-character-dexterity').text(character.dexterity);
            $('#rpg-ia-character-constitution').text(character.constitution);
            $('#rpg-ia-character-charisma').text(character.charisma);
            
            // Modificateurs
            $('#rpg-ia-strength-modifier').text(this.getAbilityModifier(character.strength));
            $('#rpg-ia-intelligence-modifier').text(this.getAbilityModifier(character.intelligence));
            $('#rpg-ia-wisdom-modifier').text(this.getAbilityModifier(character.wisdom));
            $('#rpg-ia-dexterity-modifier').text(this.getAbilityModifier(character.dexterity));
            $('#rpg-ia-constitution-modifier').text(this.getAbilityModifier(character.constitution));
            $('#rpg-ia-charisma-modifier').text(this.getAbilityModifier(character.charisma));
            
            // Équipement
            const equipmentList = $('#rpg-ia-character-equipment-list');
            equipmentList.empty();
            
            if (character.equipment && character.equipment.length > 0) {
                character.equipment.forEach(function(item) {
                    equipmentList.append($('<li>').text(item));
                });
            } else {
                equipmentList.append($('<li>').text('Aucun équipement'));
            }
            
            $('#rpg-ia-character-gold').text(character.gold);
            
            // Compétences
            const skillsList = $('#rpg-ia-character-skills-list');
            skillsList.empty();
            
            if (character.skills && character.skills.length > 0) {
                character.skills.forEach(function(skill) {
                    skillsList.append($('<li>').text(skill));
                });
            } else {
                skillsList.append($('<li>').text('Aucune compétence'));
            }
            
            // Sorts
            const spellsList = $('#rpg-ia-character-spells-list');
            spellsList.empty();
            
            if (character.spells && character.spells.length > 0) {
                character.spells.forEach(function(spell) {
                    spellsList.append($('<li>').text(spell));
                });
            } else {
                spellsList.append($('<li>').text('Aucun sort'));
            }
            
            // Biographie et apparence
            $('#rpg-ia-character-background').text(character.background || 'Aucune biographie');
            $('#rpg-ia-character-appearance').text(character.appearance || 'Aucune description');
            
            // Stocker l'ID du personnage pour l'édition
            this.characterId = character.id;
            
            // Charger la session
            this.loadSessions();
            
            // Mettre à jour les boutons d'action
            $('.rpg-ia-edit-character-btn').data('id', character.id);
        },

        /**
         * Charge les actions d'un personnage
         * 
         * @param {number} characterId L'ID du personnage
         */
        loadCharacterActions: function(characterId) {
            $('#rpg-ia-character-actions-loading').show();
            $('#rpg-ia-character-actions-empty').hide();
            $('#rpg-ia-character-actions-list').hide();
            
            $.ajax({
                url: this.restUrl + 'rpg-ia/v1/characters/' + characterId + '/actions',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + this.token);
                }.bind(this),
                success: function(response) {
                    $('#rpg-ia-character-actions-loading').hide();
                    
                    if (response.success && response.actions && response.actions.length > 0) {
                        this.renderCharacterActions(response.actions);
                        $('#rpg-ia-character-actions-list').show();
                    } else {
                        $('#rpg-ia-character-actions-empty').show();
                    }
                }.bind(this),
                error: function(xhr) {
                    $('#rpg-ia-character-actions-loading').hide();
                    $('#rpg-ia-character-actions-empty').show();
                    console.error('Error loading character actions:', xhr.responseText);
                }
            });
        },

        /**
         * Affiche les actions d'un personnage
         * 
         * @param {Array} actions La liste des actions
         */
        renderCharacterActions: function(actions) {
            const actionsList = $('#rpg-ia-character-actions-list');
            actionsList.empty();
            
            actions.forEach(function(action) {
                const actionItem = $('<li>').addClass('rpg-ia-action-item');
                
                const actionHeader = $('<div>').addClass('rpg-ia-action-header');
                actionHeader.append($('<span>').addClass('rpg-ia-action-date').text(this.formatDate(action.created_at)));
                actionHeader.append($('<span>').addClass('rpg-ia-action-type').text(action.action_type));
                
                const actionContent = $('<div>').addClass('rpg-ia-action-content');
                actionContent.append($('<p>').addClass('rpg-ia-action-description').text(action.description));
                actionContent.append($('<p>').addClass('rpg-ia-action-result').text(action.result));
                
                actionItem.append(actionHeader);
                actionItem.append(actionContent);
                
                actionsList.append(actionItem);
            }.bind(this));
        },

        /**
         * Affiche le formulaire d'édition de personnage
         * 
         * @param {Event} e L'événement
         */
        editCharacter: function(e) {
            e.preventDefault();
            
            const characterId = $(e.currentTarget).data('id');
            this.isEditing = true;
            this.characterId = characterId;
            
            // Charger les données du personnage
            $.ajax({
                url: this.restUrl + 'rpg-ia/v1/characters/' + characterId,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + this.token);
                }.bind(this),
                success: function(response) {
                    if (response.success && response.character) {
                        this.populateCharacterForm(response.character);
                        $('#rpg-ia-character-modal').show();
                    } else {
                        alert(rpg_ia_public.messages.error_loading_character || 'Erreur lors du chargement du personnage.');
                    }
                }.bind(this),
                error: function(xhr) {
                    alert(rpg_ia_public.messages.error_loading_character || 'Erreur lors du chargement du personnage.');
                    console.error('Error loading character for edit:', xhr.responseText);
                }
            });
        },

        /**
         * Remplit le formulaire avec les données du personnage
         * 
         * @param {Object} character Les données du personnage
         */
        populateCharacterForm: function(character) {
            // Titre du formulaire
            $('#rpg-ia-character-form-title').text(rpg_ia_public.messages.edit_character || 'Modifier le Personnage');
            
            // Champs cachés
            $('#rpg-ia-character-id').val(character.id);
            
            // Informations générales
            $('#rpg-ia-character-name').val(character.name);
            $('#rpg-ia-character-class').val(character.character_class);
            $('#rpg-ia-character-level').val(character.level);
            $('#rpg-ia-character-experience').val(character.experience);
            $('#rpg-ia-character-game-session').val(character.game_session_id);
            
            // Points de vie et armure
            $('#rpg-ia-character-max-hp').val(character.max_hp);
            $('#rpg-ia-character-current-hp').val(character.current_hp);
            $('#rpg-ia-character-armor-class').val(character.armor_class);
            
            // Caractéristiques
            $('#rpg-ia-character-strength').val(character.strength);
            $('#rpg-ia-character-intelligence').val(character.intelligence);
            $('#rpg-ia-character-wisdom').val(character.wisdom);
            $('#rpg-ia-character-dexterity').val(character.dexterity);
            $('#rpg-ia-character-constitution').val(character.constitution);
            $('#rpg-ia-character-charisma').val(character.charisma);
            
            // Mettre à jour les modificateurs
            this.updateAbilityModifiers();
            
            // Équipement
            this.resetItemLists();
            
            if (character.equipment && character.equipment.length > 0) {
                character.equipment.forEach(function(item, index) {
                    if (index === 0) {
                        // Utiliser le premier champ existant
                        $('#rpg-ia-equipment-list input').first().val(item);
                    } else {
                        // Ajouter de nouveaux champs pour les autres items
                        this.addEquipmentItem(null, item);
                    }
                }.bind(this));
            }
            
            $('#rpg-ia-character-gold').val(character.gold);
            
            // Compétences
            if (character.skills && character.skills.length > 0) {
                character.skills.forEach(function(skill, index) {
                    if (index === 0) {
                        // Utiliser le premier champ existant
                        $('#rpg-ia-skills-list input').first().val(skill);
                    } else {
                        // Ajouter de nouveaux champs pour les autres compétences
                        this.addSkillItem(null, skill);
                    }
                }.bind(this));
            }
            
            // Sorts
            if (character.spells && character.spells.length > 0) {
                character.spells.forEach(function(spell, index) {
                    if (index === 0) {
                        // Utiliser le premier champ existant
                        $('#rpg-ia-spells-list input').first().val(spell);
                    } else {
                        // Ajouter de nouveaux champs pour les autres sorts
                        this.addSpellItem(null, spell);
                    }
                }.bind(this));
            }
            
            // Biographie et apparence
            $('#rpg-ia-character-background').val(character.background);
            $('#rpg-ia-character-appearance').val(character.appearance);
        },

        /**
         * Enregistre un personnage
         * 
         * @param {Event} e L'événement
         */
        saveCharacter: function(e) {
            e.preventDefault();
            
            // Afficher le chargement
            $('#rpg-ia-character-form-loading').show();
            $('#rpg-ia-character-form-error').hide();
            
            // Récupérer les données du formulaire
            const formData = this.getCharacterFormData();
            
            // Déterminer l'URL et la méthode en fonction de l'édition ou de la création
            let url = this.restUrl + 'rpg-ia/v1/characters';
            let method = 'POST';
            
            if (this.isEditing && this.characterId) {
                url += '/' + this.characterId;
                method = 'PUT';
            }
            
            // Envoyer la requête
            $.ajax({
                url: url,
                method: method,
                data: JSON.stringify(formData),
                contentType: 'application/json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + this.token);
                }.bind(this),
                success: function(response) {
                    $('#rpg-ia-character-form-loading').hide();
                    
                    if (response.success) {
                        // Fermer le modal
                        this.closeCharacterModal();
                        
                        // Recharger la liste des personnages ou les détails
                        if ($('#rpg-ia-characters-list').length > 0) {
                            this.loadCharacters();
                        } else if ($('#rpg-ia-character-display').length > 0) {
                            this.loadCharacterDetails(this.characterId);
                        }
                    } else {
                        $('#rpg-ia-character-form-error-message').text(response.message || rpg_ia_public.messages.error_saving_character || 'Erreur lors de l\'enregistrement du personnage.');
                        $('#rpg-ia-character-form-error').show();
                    }
                }.bind(this),
                error: function(xhr) {
                    $('#rpg-ia-character-form-loading').hide();
                    
                    let errorMessage = rpg_ia_public.messages.error_saving_character || 'Erreur lors de l\'enregistrement du personnage.';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing error response:', e);
                    }
                    
                    $('#rpg-ia-character-form-error-message').text(errorMessage);
                    $('#rpg-ia-character-form-error').show();
                    console.error('Error saving character:', xhr.responseText);
                }
            });
        },

        /**
         * Récupère les données du formulaire de personnage
         * 
         * @return {Object} Les données du personnage
         */
        getCharacterFormData: function() {
            // Récupérer les valeurs de base
            const formData = {
                name: $('#rpg-ia-character-name').val(),
                character_class: $('#rpg-ia-character-class').val(),
                level: parseInt($('#rpg-ia-character-level').val(), 10),
                experience: parseInt($('#rpg-ia-character-experience').val(), 10),
                game_session_id: parseInt($('#rpg-ia-character-game-session').val(), 10),
                max_hp: parseInt($('#rpg-ia-character-max-hp').val(), 10),
                current_hp: parseInt($('#rpg-ia-character-current-hp').val(), 10),
                armor_class: parseInt($('#rpg-ia-character-armor-class').val(), 10),
                strength: parseInt($('#rpg-ia-character-strength').val(), 10),
                intelligence: parseInt($('#rpg-ia-character-intelligence').val(), 10),
                wisdom: parseInt($('#rpg-ia-character-wisdom').val(), 10),
                dexterity: parseInt($('#rpg-ia-character-dexterity').val(), 10),
                constitution: parseInt($('#rpg-ia-character-constitution').val(), 10),
                charisma: parseInt($('#rpg-ia-character-charisma').val(), 10),
                gold: parseInt($('#rpg-ia-character-gold').val(), 10),
                background: $('#rpg-ia-character-background').val(),
                appearance: $('#rpg-ia-character-appearance').val(),
                is_alive: true
            };
            
            // Récupérer les listes
            formData.equipment = [];
            $('#rpg-ia-equipment-list input').each(function() {
                const value = $(this).val().trim();
                if (value) {
                    formData.equipment.push(value);
                }
            });
            
            formData.skills = [];
            $('#rpg-ia-skills-list input').each(function() {
                const value = $(this).val().trim();
                if (value) {
                    formData.skills.push(value);
                }
            });
            
            formData.spells = [];
            $('#rpg-ia-spells-list input').each(function() {
                const value = $(this).val().trim();
                if (value) {
                    formData.spells.push(value);
                }
            });
            
            return formData;
        },

        /**
         * Affiche la confirmation de suppression d'un personnage
         * 
         * @param {Event} e L'événement
         */
        confirmDeleteCharacter: function(e) {
            e.preventDefault();
            
            this.characterId = $(e.currentTarget).data('id');
            $('#rpg-ia-confirm-delete-modal').show();
        },

        /**
         * Supprime un personnage
         * 
         * @param {Event} e L'événement
         */
        deleteCharacter: function(e) {
            e.preventDefault();
            
            if (!this.characterId) {
                return;
            }
            
            $.ajax({
                url: this.restUrl + 'rpg-ia/v1/characters/' + this.characterId,
                method: 'DELETE',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + this.token);
                }.bind(this),
                success: function(response) {
                    this.closeModal();
                    
                    if (response.success) {
                        // Recharger la liste des personnages ou rediriger
                        if ($('#rpg-ia-characters-list').length > 0) {
                            this.loadCharacters();
                        } else {
                            window.location.href = rpg_ia_public.characters_url;
                        }
                    } else {
                        alert(response.message || rpg_ia_public.messages.error_deleting_character || 'Erreur lors de la suppression du personnage.');
                    }
                }.bind(this),
                error: function(xhr) {
                    this.closeModal();
                    alert(rpg_ia_public.messages.error_deleting_character || 'Erreur lors de la suppression du personnage.');
                    console.error('Error deleting character:', xhr.responseText);
                }.bind(this)
            });
        },

        /**
         * Ferme le modal de personnage
         */
        closeCharacterModal: function() {
            $('#rpg-ia-character-modal').hide();
        },

        /**
         * Ferme un modal
         */
        closeModal: function() {
            $('.rpg-ia-modal').hide();
        },

        /**
         * Réinitialise les listes d'items
         */
        resetItemLists: function() {
            // Équipement
            $('#rpg-ia-equipment-list').html('<div class="rpg-ia-equipment-item"><input type="text" name="equipment[]" placeholder="Nom de l\'objet"><button type="button" class="rpg-ia-remove-item">&times;</button></div>');
            
            // Compétences
            $('#rpg-ia-skills-list').html('<div class="rpg-ia-skill-item"><input type="text" name="skills[]" placeholder="Nom de la compétence"><button type="button" class="rpg-ia-remove-item">&times;</button></div>');
            
            // Sorts
            $('#rpg-ia-spells-list').html('<div class="rpg-ia-spell-item"><input type="text" name="spells[]" placeholder="Nom du sort"><button type="button" class="rpg-ia-remove-item">&times;</button></div>');
        },

        /**
         * Ajoute un item d'équipement
         * 
         * @param {Event} e L'événement
         * @param {string} value La valeur de l'item (optionnel)
         */
        addEquipmentItem: function(e, value) {
            if (e) {
                e.preventDefault();
            }
            
            const item = $('<div>').addClass('rpg-ia-equipment-item');
            const input = $('<input>').attr({
                type: 'text',
                name: 'equipment[]',
                placeholder: 'Nom de l\'objet'
            });
            
            if (value) {
                input.val(value);
            }
            
            const removeButton = $('<button>').attr({
                type: 'button',
                class: 'rpg-ia-remove-item'
            }).html('&times;');
            
            item.append(input).append(removeButton);
            $('#rpg-ia-equipment-list').append(item);
        },

        /**
         * Ajoute une compétence
         * 
         * @param {Event} e L'événement
         * @param {string} value La valeur de la compétence (optionnel)
         */
        addSkillItem: function(e, value) {
            if (e) {
                e.preventDefault();
            }
            
            const item = $('<div>').addClass('rpg-ia-skill-item');
            const input = $('<input>').attr({
                type: 'text',
                name: 'skills[]',
                placeholder: 'Nom de la compétence'
            });
            
            if (value) {
                input.val(value);
            }
            
            const removeButton = $('<button>').attr({
                type: 'button',
                class: 'rpg-ia-remove-item'
            }).html('&times;');
            
            item.append(input).append(removeButton);
            $('#rpg-ia-skills-list').append(item);
        },

        /**
         * Ajoute un sort
         * 
         * @param {Event} e L'événement
         * @param {string} value La valeur du sort (optionnel)
         */
        addSpellItem: function(e, value) {
            if (e) {
                e.preventDefault();
            }
            
            const item = $('<div>').addClass('rpg-ia-spell-item');
            const input = $('<input>').attr({
                type: 'text',
                name: 'spells[]',
                placeholder: 'Nom du sort'
            });
            
            if (value) {
                input.val(value);
            }
            
            const removeButton = $('<button>').attr({
                type: 'button',
                class: 'rpg-ia-remove-item'
            }).html('&times;');
            
            item.append(input).append(removeButton);
            $('#rpg-ia-spells-list').append(item);
        },

        /**
         * Supprime un item
         * 
         * @param {Event} e L'événement
         */
        removeItem: function(e) {
            e.preventDefault();
            
            $(e.currentTarget).parent().remove();
        },

        /**
         * Lance les dés pour les caractéristiques
         * 
         * @param {Event} e L'événement
         */
        rollAbilities: function(e) {
            e.preventDefault();
            
            // Générer des valeurs aléatoires pour chaque caractéristique (3d6)
            $('#rpg-ia-character-strength').val(this.roll3d6());
            $('#rpg-ia-character-intelligence').val(this.roll3d6());
            $('#rpg-ia-character-wisdom').val(this.roll3d6());
            $('#rpg-ia-character-dexterity').val(this.roll3d6());
            $('#rpg-ia-character-constitution').val(this.roll3d6());
            $('#rpg-ia-character-charisma').val(this.roll3d6());
            
            // Mettre à jour les modificateurs
            this.updateAbilityModifiers();
            
            // Mettre à jour les points de vie
            const constitution = parseInt($('#rpg-ia-character-constitution').val(), 10);
            const characterClass = $('#rpg-ia-character-class').val();
            const maxHp = this.calculateStartingHp(characterClass, constitution);
            
            $('#rpg-ia-character-max-hp').val(maxHp);
            $('#rpg-ia-character-current-hp').val(maxHp);
            
            // Mettre à jour la classe d'armure
            const dexterity = parseInt($('#rpg-ia-character-dexterity').val(), 10);
            const dexMod = this.getAbilityModifier(dexterity);
            $('#rpg-ia-character-armor-class').val(10 + dexMod);
            
            // Générer l'or de départ
            $('#rpg-ia-character-gold').val(this.roll3d6() * 10);
        },

        /**
         * Lance 3d6
         * 
         * @return {number} Le résultat du lancer
         */
        roll3d6: function() {
            return Math.floor(Math.random() * 6) + 1 + Math.floor(Math.random() * 6) + 1 + Math.floor(Math.random() * 6) + 1;
        },

        /**
         * Calcule les points de vie de départ
         * 
         * @param {string} characterClass La classe du personnage
         * @param {number} constitution La constitution du personnage
         * @return {number} Les points de vie de départ
         */
        calculateStartingHp: function(characterClass, constitution) {
            const conMod = this.getAbilityModifier(constitution);
            let baseHp = 0;
            
            switch (characterClass) {
                case 'guerrier':
                case 'nain':
                    baseHp = Math.floor(Math.random() * 8) + 1; // d8
                    break;
                case 'clerc':
                case 'elfe':
                case 'halfelin':
                    baseHp = Math.floor(Math.random() * 6) + 1; // d6
                    break;
                case 'magicien':
                case 'voleur':
                    baseHp = Math.floor(Math.random() * 4) + 1; // d4
                    break;
                default:
                    baseHp = Math.floor(Math.random() * 6) + 1; // d6 par défaut
            }
            
            return Math.max(1, baseHp + conMod);
        },

        /**
         * Met à jour les compétences en fonction de la classe
         */
        updateClassSkills: function() {
            const characterClass = $('#rpg-ia-character-class').val();
            
            // Réinitialiser la liste des compétences
            $('#rpg-ia-skills-list').html('');
            
            // Ajouter les compétences de classe
            const skills = this.getClassSkills(characterClass);
            
            skills.forEach(function(skill) {
                this.addSkillItem(null, skill);
            }.bind(this));
            
            // Mettre à jour les sorts si nécessaire
            $('#rpg-ia-spells-list').html('');
            
            if (characterClass === 'magicien' || characterClass === 'elfe') {
                const spells = this.getStartingSpells(characterClass);
                
                spells.forEach(function(spell) {
                    this.addSpellItem(null, spell);
                }.bind(this));
            } else {
                this.addSpellItem();
            }
        },

        /**
         * Récupère les compétences de classe
         * 
         * @param {string} characterClass La classe du personnage
         * @return {Array} Les compétences de classe
         */
        getClassSkills: function(characterClass) {
            switch (characterClass) {
                case 'guerrier':
                    return ['Combat à l\'épée', 'Tactique militaire'];
                case 'clerc':
                    return ['Connaissance religieuse', 'Premiers soins'];
                case 'magicien':
                    return ['Connaissance des arcanes', 'Identification des objets magiques'];
                case 'voleur':
                    return ['Crochetage', 'Désamorçage de pièges', 'Escalade', 'Déplacement silencieux', 'Pickpocket'];
                case 'nain':
                    return ['Détection des passages secrets', 'Évaluation des trésors', 'Résistance à la magie'];
                case 'elfe':
                    return ['Détection des portes secrètes', 'Immunité au paralysie des goules', 'Vision dans le noir'];
                case 'halfelin':
                    return ['Discrétion', 'Tir précis', 'Initiative améliorée'];
                default:
                    return [];
            }
        },

        /**
         * Récupère les sorts de départ
         * 
         * @param {string} characterClass La classe du personnage
         * @return {Array} Les sorts de départ
         */
        getStartingSpells: function(characterClass) {
            if (characterClass === 'magicien') {
                return ['Lecture de la magie', 'Détection de la magie'];
            } else if (characterClass === 'elfe') {
                return ['Détection de la magie'];
            }
            
            return [];
        },

        /**
         * Met à jour les modificateurs de caractéristiques
         */
        updateAbilityModifiers: function() {
            const abilities = ['strength', 'intelligence', 'wisdom', 'dexterity', 'constitution', 'charisma'];
            
            abilities.forEach(function(ability) {
                const value = parseInt($('#rpg-ia-character-' + ability).val(), 10);
                const modifier = this.getAbilityModifier(value);
                
                $('#rpg-ia-' + ability + '-modifier').text(modifier >= 0 ? '+' + modifier : modifier);
            }.bind(this));
        },

        /**
         * Calcule le modificateur de caractéristique
         * 
         * @param {number} value La valeur de la caractéristique
         * @return {number} Le modificateur
         */
        getAbilityModifier: function(value) {
            if (value <= 3) {
                return -3;
            } else if (value <= 5) {
                return -2;
            } else if (value <= 8) {
                return -1;
            } else if (value <= 12) {
                return 0;
            } else if (value <= 15) {
                return 1;
            } else if (value <= 17) {
                return 2;
            } else {
                return 3;
            }
        },

        /**
         * Formate le nom de la classe
         * 
         * @param {string} className Le nom de la classe
         * @return {string} Le nom formaté
         */
        formatClassName: function(className) {
            switch (className) {
                case 'clerc':
                    return 'Clerc';
                case 'guerrier':
                    return 'Guerrier';
                case 'magicien':
                    return 'Magicien';
                case 'voleur':
                    return 'Voleur';
                case 'nain':
                    return 'Nain';
                case 'elfe':
                    return 'Elfe';
                case 'halfelin':
                    return 'Halfelin';
                default:
                    return className;
            }
        },

        /**
         * Formate une date
         * 
         * @param {string} dateString La date au format ISO
         * @return {string} La date formatée
         */
        formatDate: function(dateString) {
            if (!dateString) {
                return '';
            }
            
            const date = new Date(dateString);
            
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        },

        /**
         * Récupère un cookie
         * 
         * @param {string} name Le nom du cookie
         * @return {string} La valeur du cookie
         */
        getCookie: function(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            
            if (parts.length === 2) {
                return parts.pop().split(';').shift();
            }
            
            return '';
        }
    };

    // Initialiser le gestionnaire de personnages
    $(document).ready(function() {
        RPGIACharacter.init();
        
    });

})(jQuery);