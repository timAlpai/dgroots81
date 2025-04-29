/**
 * Script pour gérer l'interface de jeu
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
     * Gestionnaire de l'interface de jeu
     */
    const RPGIAGame = {
        /**
         * ID de la session de jeu
         */
        sessionId: null,
        
        /**
         * ID du personnage du joueur
         */
        characterId: null,
        
        /**
         * Timestamp de la dernière action
         */
        lastActionTimestamp: null,
        
        /**
         * Intervalle de mise à jour
         */
        updateInterval: null,
        
        /**
         * Initialise le gestionnaire de jeu
         */
        init: function() {
            // Récupérer l'ID de la session
            this.sessionId = $('.rpg-ia-game-interface').data('session-id');
            
            if (!this.sessionId) {
                console.error('No session ID provided.');
                return;
            }
            
            // Initialiser les événements
            this.initEvents();
            
            // Charger la session
            this.loadGameSession();
        },
        
        /**
         * Initialise les événements
         */
        initEvents: function() {
            const self = this;
            
            // Événement pour le formulaire d'action
            $('#rpg-ia-action-form').on('submit', function(e) {
                e.preventDefault();
                self.submitAction();
            });
            
            // Événement pour le bouton "Options"
            $('#rpg-ia-toggle-options-btn').on('click', function() {
                $('#rpg-ia-action-options').toggle();
            });
            
            // Événement pour la case à cocher "Lancer les dés"
            $('#rpg-ia-action-roll-dice').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#rpg-ia-dice-options').show();
                } else {
                    $('#rpg-ia-dice-options').hide();
                }
            });
            
            // Événement pour le type de dé
            $('#rpg-ia-dice-type').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#rpg-ia-custom-dice').show();
                } else {
                    $('#rpg-ia-custom-dice').hide();
                }
            });
            
            // Événement pour le bouton "Quitter"
            $('#rpg-ia-leave-game-btn').on('click', function() {
                $('#rpg-ia-leave-game-modal').show();
            });
            
            // Événement pour le bouton "Confirmer quitter"
            $('#rpg-ia-confirm-leave-btn').on('click', function() {
                self.leaveGame();
            });
            
            // Événement pour le bouton "Envoyer" du chat
            $('#rpg-ia-send-chat-btn').on('click', function() {
                self.sendChatMessage();
            });
            
            // Événement pour la touche Entrée dans le champ de chat
            $('#rpg-ia-chat-message').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.sendChatMessage();
                }
            });
            
            // Événement pour le bouton "Enregistrer" des notes
            $('#rpg-ia-save-notes-btn').on('click', function() {
                self.savePersonalNotes();
            });
            
            // Événement pour les onglets de la sidebar
            $('.rpg-ia-tab-button').on('click', function() {
                const tab = $(this).data('tab');
                $('.rpg-ia-tab-button').removeClass('active');
                $(this).addClass('active');
                $('.rpg-ia-tab-content').removeClass('active');
                $('#rpg-ia-tab-' + tab).addClass('active');
            });
            
            // Événement pour le bouton de basculement de la sidebar
            $('#rpg-ia-toggle-sidebar-btn').on('click', function() {
                $('#rpg-ia-game-sidebar').toggleClass('collapsed');
                $('.rpg-ia-game-main').toggleClass('expanded');
            });
            
            // Événement pour les en-têtes dépliables
            $('.rpg-ia-collapsible-header').on('click', function() {
                $(this).next('.rpg-ia-collapsible-content').slideToggle();
                $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            });
            
            // Événement pour le filtre du journal
            $('#rpg-ia-journal-filter').on('change', function() {
                self.filterJournalEntries($(this).val());
            });
            
            // Événement pour le bouton "Effacer la narration"
            $('#rpg-ia-clear-narration-btn').on('click', function() {
                $('#rpg-ia-narration-content').empty();
            });
            
            // Événement pour le bouton "Défiler vers le bas"
            $('#rpg-ia-scroll-down-btn').on('click', function() {
                const narrationContent = $('#rpg-ia-narration-content');
                narrationContent.scrollTop(narrationContent[0].scrollHeight);
            });
            
            // Événement pour le bouton "Rejoindre la partie"
            $('#rpg-ia-join-game-btn').on('click', function() {
                const characterId = $('#rpg-ia-select-character').val();
                if (characterId) {
                    self.joinGameWithCharacter(characterId);
                } else {
                    alert(rpg_ia_public.messages.no_character_selected);
                }
            });
            
            // Événement pour le bouton "Réessayer"
            $('#rpg-ia-retry-game').on('click', function() {
                self.loadGameSession();
            });
        },
        
        /**
         * Charge la session de jeu
         */
        loadGameSession: function() {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            // Afficher le chargement
            $('#rpg-ia-game-loading').show();
            $('#rpg-ia-game-error').hide();
            $('#rpg-ia-character-selection').hide();
            $('#rpg-ia-game-content').hide();
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions/' + this.sessionId,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                success: function(response) {
                    $('#rpg-ia-game-loading').hide();
                    
                    if (response.success) {
                        const session = response.session;
                        
                        // Vérifier si l'utilisateur a déjà un personnage dans la session
                        const currentUser = self.getCurrentUser();
                        let userCharacter = null;
                        
                        if (session.players) {
                            for (let i = 0; i < session.players.length; i++) {
                                if (session.players[i].id === currentUser.id && session.players[i].character) {
                                    userCharacter = session.players[i].character;
                                    break;
                                }
                            }
                        }
                        
                        if (userCharacter) {
                            // L'utilisateur a déjà un personnage dans la session
                            self.characterId = userCharacter.id;
                            self.initGameInterface(session, userCharacter);
                        } else {
                            // L'utilisateur doit sélectionner un personnage
                            self.loadCharactersForSelection();
                        }
                    } else {
                        $('#rpg-ia-game-error').show();
                    }
                },
                error: function() {
                    $('#rpg-ia-game-loading').hide();
                    $('#rpg-ia-game-error').show();
                }
            });
        },
        
        /**
         * Récupère l'utilisateur courant
         * 
         * @return {Object} L'utilisateur courant
         */
        getCurrentUser: function() {
            // Cette fonction devrait être implémentée pour récupérer l'utilisateur courant
            // Pour l'instant, on retourne un objet factice
            return {
                id: 1,
                username: 'Utilisateur'
            };
        },
        
        /**
         * Récupère le token JWT du cookie
         * 
         * @return {string|null} Le token JWT ou null s'il n'existe pas
         */
        getToken: function() {
            return Cookies.get('rpg_ia_token');
        },
        
        /**
         * Récupère le nom du type d'action à partir de son code
         * 
         * @param {string} actionType Le code du type d'action
         * @return {string} Le nom du type d'action
         */
        getActionTypeName: function(actionType) {
            const actionTypeNames = {
                'dialogue': 'Dialogue',
                'movement': 'Déplacement',
                'combat': 'Combat',
                'skill': 'Compétence',
                'item': 'Objet',
                'spell': 'Sort',
                'other': 'Autre'
            };
            
            return actionTypeNames[actionType] || actionType;
        },
        
        /**
         * Récupère le nom du statut à partir de son code
         * 
         * @param {string} statusCode Le code du statut
         * @return {string} Le nom du statut
         */
        getStatusName: function(statusCode) {
            const statusNames = {
                'active': 'Active',
                'paused': 'En pause',
                'completed': 'Terminée',
                'planning': 'En préparation'
            };
            
            return statusNames[statusCode] || statusCode;
        },
        
        /**
         * Calcule le modificateur de caractéristique
         * 
         * @param {number} abilityScore La valeur de la caractéristique
         * @return {string} Le modificateur formaté
         */
        getAbilityModifier: function(abilityScore) {
            const modifier = Math.floor((abilityScore - 10) / 2);
            
            if (modifier >= 0) {
                return '+' + modifier;
            } else {
                return modifier.toString();
            }
        },
        
        /**
         * Calcule l'expérience nécessaire pour le prochain niveau
         * 
         * @param {number} currentLevel Le niveau actuel
         * @return {number} L'expérience nécessaire
         */
        getNextLevelXP: function(currentLevel) {
            // Tableau d'expérience pour OSE (simplifié)
            const xpTable = {
                1: 2000,
                2: 4000,
                3: 8000,
                4: 16000,
                5: 32000,
                6: 64000,
                7: 120000,
                8: 240000,
                9: 360000
            };
            
            return xpTable[currentLevel] || 0;
        },
        
        /**
         * Formate une date
         * 
         * @param {string} dateString La date à formater
         * @return {string} La date formatée
         */
        formatDate: function(dateString) {
            if (!dateString) {
                return 'N/A';
            }
            
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        },
        
        /**
         * Formate l'heure
         * 
         * @param {Date} date La date à formater
         * @return {string} L'heure formatée
         */
        formatTime: function(date) {
            return date.toLocaleTimeString();
        },

        /**
         * Charge les personnages disponibles pour la sélection
         */
        loadCharactersForSelection: function() {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            // Afficher la sélection de personnage
            $('#rpg-ia-game-loading').hide();
            $('#rpg-ia-game-error').hide();
            $('#rpg-ia-character-selection').show();
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/characters',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                success: function(response) {
                    if (response.success) {
                        const characters = response.characters;
                        const selectCharacter = $('#rpg-ia-select-character');
                        
                        // Vider la liste
                        selectCharacter.find('option:not(:first)').remove();
                        
                        if (characters && characters.length > 0) {
                            // Ajouter les personnages à la liste
                            for (let i = 0; i < characters.length; i++) {
                                const character = characters[i];
                                selectCharacter.append(
                                    $('<option></option>')
                                        .attr('value', character.id)
                                        .text(character.name + ' (' + character.class + ' niveau ' + character.level + ')')
                                );
                            }
                            
                            // Cacher le message "pas de personnages"
                            $('#rpg-ia-no-characters-message').hide();
                        } else {
                            // Afficher le message "pas de personnages"
                            $('#rpg-ia-no-characters-message').show();
                        }
                    } else {
                        $('#rpg-ia-game-error').show();
                    }
                },
                error: function() {
                    $('#rpg-ia-game-error').show();
                }
            });
        },
        
        /**
         * Rejoint la partie avec un personnage sélectionné
         *
         * @param {number} characterId L'ID du personnage
         */
        joinGameWithCharacter: function(characterId) {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            // Afficher le chargement
            $('#rpg-ia-character-selection').hide();
            $('#rpg-ia-game-loading').show();
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions/' + this.sessionId + '/join',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                data: {
                    character_id: characterId
                },
                success: function(response) {
                    $('#rpg-ia-game-loading').hide();
                    
                    if (response.success) {
                        self.characterId = characterId;
                        self.loadGameSession();
                    } else {
                        $('#rpg-ia-game-error').show();
                    }
                },
                error: function() {
                    $('#rpg-ia-game-loading').hide();
                    $('#rpg-ia-game-error').show();
                }
            });
        },
        
        /**
         * Initialise l'interface de jeu
         *
         * @param {Object} session Les données de la session
         * @param {Object} character Les données du personnage
         */
        initGameInterface: function(session, character) {
            const self = this;
            
            // Afficher l'interface de jeu
            $('#rpg-ia-game-content').show();
            
            // Mettre à jour les informations de la session
            $('#rpg-ia-game-session-name').text(session.name);
            $('#rpg-ia-game-session-status').text(this.getStatusName(session.status));
            
            // Mettre à jour les informations du personnage
            $('#rpg-ia-character-name').text(character.name);
            $('#rpg-ia-character-class-level').text(character.class + ' niveau ' + character.level);
            $('#rpg-ia-character-hp').text(character.current_hp + '/' + character.max_hp);
            $('#rpg-ia-character-ac').text(character.armor_class);
            $('#rpg-ia-character-xp').text(character.experience + '/' + this.getNextLevelXP(character.level));
            
            // Mettre à jour les caractéristiques
            $('#rpg-ia-character-str').text(character.strength + ' (' + this.getAbilityModifier(character.strength) + ')');
            $('#rpg-ia-character-dex').text(character.dexterity + ' (' + this.getAbilityModifier(character.dexterity) + ')');
            $('#rpg-ia-character-con').text(character.constitution + ' (' + this.getAbilityModifier(character.constitution) + ')');
            $('#rpg-ia-character-int').text(character.intelligence + ' (' + this.getAbilityModifier(character.intelligence) + ')');
            $('#rpg-ia-character-wis').text(character.wisdom + ' (' + this.getAbilityModifier(character.wisdom) + ')');
            $('#rpg-ia-character-cha').text(character.charisma + ' (' + this.getAbilityModifier(character.charisma) + ')');
            
            // Mettre à jour l'équipement
            this.updateEquipment(character.equipment);
            
            // Mettre à jour l'inventaire
            this.updateInventory(character.inventory);
            
            // Mettre à jour les compétences
            this.updateSkills(character.skills);
            
            // Mettre à jour les sorts
            this.updateSpells(character.spells);
            
            // Mettre à jour la liste des joueurs
            this.updatePlayersList(session.players);
            
            // Charger les actions précédentes
            this.loadActions();
            
            // Charger les entrées du journal
            this.loadJournalEntries();
            
            // Charger les notes personnelles
            this.loadPersonalNotes();
            
            // Démarrer l'intervalle de mise à jour
            if (this.updateInterval) {
                clearInterval(this.updateInterval);
            }
            
            this.updateInterval = setInterval(function() {
                self.updateGameState();
            }, 10000); // Mise à jour toutes les 10 secondes
        },
        
        /**
         * Met à jour l'équipement du personnage
         *
         * @param {Array} equipment L'équipement du personnage
         */
        updateEquipment: function(equipment) {
            const equipmentList = $('#rpg-ia-character-equipment');
            equipmentList.empty();
            
            if (equipment && equipment.length > 0) {
                for (let i = 0; i < equipment.length; i++) {
                    const item = equipment[i];
                    equipmentList.append(
                        $('<li></li>').text(item.name + (item.description ? ' - ' + item.description : ''))
                    );
                }
            } else {
                equipmentList.append(
                    $('<li></li>').text('Aucun équipement')
                );
            }
        },
        
        /**
         * Met à jour l'inventaire du personnage
         *
         * @param {Object} inventory L'inventaire du personnage
         */
        updateInventory: function(inventory) {
            const inventoryList = $('#rpg-ia-character-inventory');
            inventoryList.empty();
            
            if (inventory && inventory.items && inventory.items.length > 0) {
                for (let i = 0; i < inventory.items.length; i++) {
                    const item = inventory.items[i];
                    const listItem = $('<li></li>').text(item.name + (item.quantity > 1 ? ' (' + item.quantity + ')' : ''));
                    
                    if (item.usable) {
                        const useButton = $('<button></button>')
                            .addClass('rpg-ia-button rpg-ia-button-small')
                            .text('Utiliser')
                            .attr('data-item-id', item.id)
                            .on('click', function() {
                                RPGIAGame.useItem($(this).data('item-id'));
                            });
                        
                        listItem.append(' ').append(useButton);
                    }
                    
                    inventoryList.append(listItem);
                }
            } else {
                inventoryList.append(
                    $('<li></li>').text('Inventaire vide')
                );
            }
            
            // Mettre à jour l'or
            $('#rpg-ia-character-gold').text(inventory && inventory.gold ? inventory.gold : 0);
        },
        
        /**
         * Met à jour les compétences du personnage
         *
         * @param {Array} skills Les compétences du personnage
         */
        updateSkills: function(skills) {
            const skillsList = $('#rpg-ia-character-skills');
            skillsList.empty();
            
            if (skills && skills.length > 0) {
                for (let i = 0; i < skills.length; i++) {
                    const skill = skills[i];
                    skillsList.append(
                        $('<li></li>').text(skill.name + (skill.description ? ' - ' + skill.description : ''))
                    );
                }
            } else {
                skillsList.append(
                    $('<li></li>').text('Aucune compétence')
                );
            }
        },
        
        /**
         * Met à jour les sorts du personnage
         *
         * @param {Array} spells Les sorts du personnage
         */
        updateSpells: function(spells) {
            const spellsList = $('#rpg-ia-character-spells');
            spellsList.empty();
            
            if (spells && spells.length > 0) {
                for (let i = 0; i < spells.length; i++) {
                    const spell = spells[i];
                    spellsList.append(
                        $('<li></li>').text(spell.name + (spell.description ? ' - ' + spell.description : ''))
                    );
                }
            } else {
                spellsList.append(
                    $('<li></li>').text('Aucun sort')
                );
            }
        },
        
        /**
         * Met à jour la liste des joueurs
         *
         * @param {Array} players Les joueurs de la session
         */
        updatePlayersList: function(players) {
            const playersList = $('#rpg-ia-players-list');
            playersList.empty();
            
            if (players && players.length > 0) {
                for (let i = 0; i < players.length; i++) {
                    const player = players[i];
                    const character = player.character;
                    
                    if (character) {
                        const template = $('#rpg-ia-player-list-item-template').html();
                        const playerItem = $(template);
                        
                        playerItem.find('.rpg-ia-player-name').text(player.username);
                        playerItem.find('.rpg-ia-player-character').text(character.name);
                        playerItem.find('.rpg-ia-player-hp').text('PV: ' + character.current_hp + '/' + character.max_hp);
                        
                        playersList.append(playerItem);
                    }
                }
            } else {
                playersList.append(
                    $('<div></div>').text('Aucun joueur')
                );
            }
        },
        
        /**
         * Charge les actions précédentes
         */
        loadActions: function() {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions/' + this.sessionId + '/actions',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                success: function(response) {
                    if (response.success) {
                        const actions = response.actions;
                        const narrationContent = $('#rpg-ia-narration-content');
                        
                        // Vider la narration
                        narrationContent.empty();
                        
                        if (actions && actions.length > 0) {
                            // Ajouter les actions à la narration
                            for (let i = 0; i < actions.length; i++) {
                                const action = actions[i];
                                self.addActionToNarration(action);
                            }
                            
                            // Défiler vers le bas
                            narrationContent.scrollTop(narrationContent[0].scrollHeight);
                            
                            // Mettre à jour le timestamp de la dernière action
                            self.lastActionTimestamp = actions[actions.length - 1].timestamp;
                        }
                    }
                }
            });
        },
        
        /**
         * Charge les entrées du journal
         */
        loadJournalEntries: function() {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions/' + this.sessionId + '/journal',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                success: function(response) {
                    if (response.success) {
                        const entries = response.entries;
                        const journalEntries = $('#rpg-ia-journal-entries');
                        
                        // Vider le journal
                        journalEntries.empty();
                        
                        if (entries && entries.length > 0) {
                            // Ajouter les entrées au journal
                            for (let i = 0; i < entries.length; i++) {
                                const entry = entries[i];
                                self.addEntryToJournal(entry);
                            }
                        } else {
                            journalEntries.append(
                                $('<div></div>').addClass('rpg-ia-message').text('Aucune entrée dans le journal')
                            );
                        }
                    }
                }
            });
        },
        
        /**
         * Charge les notes personnelles
         */
        loadPersonalNotes: function() {
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions/' + this.sessionId + '/notes',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                success: function(response) {
                    if (response.success && response.notes) {
                        $('#rpg-ia-personal-notes').val(response.notes);
                    }
                }
            });
        },
        
        /**
         * Met à jour l'état du jeu
         */
        updateGameState: function() {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions/' + this.sessionId + '/state',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                data: {
                    last_action_timestamp: self.lastActionTimestamp
                },
                success: function(response) {
                    if (response.success) {
                        // Mettre à jour les informations de la session
                        $('#rpg-ia-game-session-status').text(self.getStatusName(response.session.status));
                        
                        // Mettre à jour les informations du personnage
                        if (response.character) {
                            const character = response.character;
                            
                            $('#rpg-ia-character-hp').text(character.current_hp + '/' + character.max_hp);
                            $('#rpg-ia-character-ac').text(character.armor_class);
                            $('#rpg-ia-character-xp').text(character.experience + '/' + self.getNextLevelXP(character.level));
                            
                            // Mettre à jour l'équipement si nécessaire
                            if (character.equipment) {
                                self.updateEquipment(character.equipment);
                            }
                            
                            // Mettre à jour l'inventaire si nécessaire
                            if (character.inventory) {
                                self.updateInventory(character.inventory);
                            }
                        }
                        
                        // Mettre à jour la liste des joueurs
                        if (response.players) {
                            self.updatePlayersList(response.players);
                        }
                        
                        // Ajouter les nouvelles actions à la narration
                        if (response.new_actions && response.new_actions.length > 0) {
                            const narrationContent = $('#rpg-ia-narration-content');
                            
                            for (let i = 0; i < response.new_actions.length; i++) {
                                const action = response.new_actions[i];
                                self.addActionToNarration(action);
                            }
                            
                            // Défiler vers le bas
                            narrationContent.scrollTop(narrationContent[0].scrollHeight);
                            
                            // Mettre à jour le timestamp de la dernière action
                            self.lastActionTimestamp = response.new_actions[response.new_actions.length - 1].timestamp;
                        }
                        
                        // Ajouter les nouveaux messages de chat
                        if (response.new_chat_messages && response.new_chat_messages.length > 0) {
                            const chatMessages = $('#rpg-ia-chat-messages');
                            
                            for (let i = 0; i < response.new_chat_messages.length; i++) {
                                const message = response.new_chat_messages[i];
                                self.addChatMessage(message);
                            }
                            
                            // Défiler vers le bas
                            chatMessages.scrollTop(chatMessages[0].scrollHeight);
                        }
                    }
                }
            });
        },
        
        /**
         * Ajoute une action à la narration
         *
         * @param {Object} action L'action à ajouter
         */
        addActionToNarration: function(action) {
            const narrationContent = $('#rpg-ia-narration-content');
            
            if (action.type === 'narration') {
                // Utiliser le template de narration
                const template = $('#rpg-ia-narration-message-template').html();
                const narrationMessage = $(template);
                
                narrationMessage.find('.rpg-ia-narration-content').html(action.content);
                narrationMessage.find('.rpg-ia-narration-timestamp').text(this.formatDate(action.timestamp));
                
                narrationContent.append(narrationMessage);
            } else {
                // Utiliser le template d'action de joueur
                const template = $('#rpg-ia-player-action-template').html();
                const playerAction = $(template);
                
                playerAction.find('.rpg-ia-player-name').text(action.player_name);
                playerAction.find('.rpg-ia-action-type').text(this.getActionTypeName(action.type));
                playerAction.find('.rpg-ia-player-action-content').html(action.content);
                playerAction.find('.rpg-ia-narration-timestamp').text(this.formatDate(action.timestamp));
                
                narrationContent.append(playerAction);
            }
        },
        
        /**
         * Ajoute une entrée au journal
         *
         * @param {Object} entry L'entrée à ajouter
         */
        addEntryToJournal: function(entry) {
            const journalEntries = $('#rpg-ia-journal-entries');
            const template = $('#rpg-ia-journal-entry-template').html();
            const journalEntry = $(template);
            
            journalEntry.addClass('rpg-ia-journal-entry-' + entry.type);
            journalEntry.find('.rpg-ia-journal-timestamp').text(this.formatDate(entry.timestamp));
            journalEntry.find('.rpg-ia-journal-type').text(this.getActionTypeName(entry.type));
            journalEntry.find('.rpg-ia-journal-entry-content').html(entry.content);
            
            journalEntries.append(journalEntry);
        },
        
        /**
         * Ajoute un message au chat
         *
         * @param {Object} message Le message à ajouter
         */
        addChatMessage: function(message) {
            const chatMessages = $('#rpg-ia-chat-messages');
            const template = $('#rpg-ia-chat-message-template').html();
            const chatMessage = $(template);
            
            chatMessage.find('.rpg-ia-chat-sender').text(message.sender_name);
            chatMessage.find('.rpg-ia-chat-content').text(message.content);
            chatMessage.find('.rpg-ia-chat-timestamp').text(this.formatTime(new Date(message.timestamp)));
            
            chatMessages.append(chatMessage);
        },
        
        /**
         * Soumet une action
         */
        submitAction: function() {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            const actionType = $('#rpg-ia-action-type').val();
            const actionDescription = $('#rpg-ia-action-description').val();
            
            if (!actionDescription) {
                alert(rpg_ia_public.messages.empty_action);
                return;
            }
            
            // Préparer les données de l'action
            const actionData = {
                type: actionType,
                description: actionDescription,
                session_id: this.sessionId,
                character_id: this.characterId
            };
            
            // Ajouter les options de dés si nécessaire
            if ($('#rpg-ia-action-roll-dice').is(':checked')) {
                const diceType = $('#rpg-ia-dice-type').val();
                
                if (diceType === 'custom') {
                    actionData.dice = $('#rpg-ia-custom-dice').val();
                } else {
                    actionData.dice = diceType;
                }
            }
            
            // Désactiver le bouton de soumission
            const submitButton = $('#rpg-ia-submit-action-btn');
            submitButton.prop('disabled', true);
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/actions',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                data: actionData,
                success: function(response) {
                    // Réactiver le bouton de soumission
                    submitButton.prop('disabled', false);
                    
                    if (response.success) {
                        // Vider le champ de description
                        $('#rpg-ia-action-description').val('');
                        
                        // Ajouter l'action à la narration
                        self.addActionToNarration(response.action);
                        
                        // Mettre à jour le timestamp de la dernière action
                        self.lastActionTimestamp = response.action.timestamp;
                        
                        // Défiler vers le bas
                        const narrationContent = $('#rpg-ia-narration-content');
                        narrationContent.scrollTop(narrationContent[0].scrollHeight);
                        
                        // Mettre à jour l'état du jeu
                        self.updateGameState();
                    } else {
                        alert(response.message || rpg_ia_public.messages.action_error);
                    }
                },
                error: function() {
                    // Réactiver le bouton de soumission
                    submitButton.prop('disabled', false);
                    alert(rpg_ia_public.messages.action_error);
                }
            });
        },
        
        /**
         * Quitte la partie
         */
        leaveGame: function() {
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions/' + this.sessionId + '/leave',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                data: {
                    character_id: this.characterId
                },
                success: function(response) {
                    // Fermer la modal
                    $('#rpg-ia-leave-game-modal').hide();
                    
                    if (response.success) {
                        // Rediriger vers la liste des sessions
                        window.location.href = rpg_ia_public.sessions_url;
                    } else {
                        alert(response.message || rpg_ia_public.messages.leave_error);
                    }
                },
                error: function() {
                    // Fermer la modal
                    $('#rpg-ia-leave-game-modal').hide();
                    alert(rpg_ia_public.messages.leave_error);
                }
            });
        },
        
        /**
         * Envoie un message de chat
         */
        sendChatMessage: function() {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            const messageInput = $('#rpg-ia-chat-message');
            const message = messageInput.val();
            
            if (!message) {
                return;
            }
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions/' + this.sessionId + '/chat',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                data: {
                    message: message
                },
                success: function(response) {
                    if (response.success) {
                        // Vider le champ de message
                        messageInput.val('');
                        
                        // Ajouter le message au chat
                        self.addChatMessage(response.message);
                        
                        // Défiler vers le bas
                        const chatMessages = $('#rpg-ia-chat-messages');
                        chatMessages.scrollTop(chatMessages[0].scrollHeight);
                    }
                }
            });
        },
        
        /**
         * Enregistre les notes personnelles
         */
        savePersonalNotes: function() {
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            const notes = $('#rpg-ia-personal-notes').val();
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions/' + this.sessionId + '/notes',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                data: {
                    notes: notes
                },
                success: function(response) {
                    if (response.success) {
                        alert(rpg_ia_public.messages.notes_saved);
                    } else {
                        alert(response.message || rpg_ia_public.messages.notes_error);
                    }
                },
                error: function() {
                    alert(rpg_ia_public.messages.notes_error);
                }
            });
        },
        
        /**
         * Filtre les entrées du journal
         *
         * @param {string} filter Le filtre à appliquer
         */
        filterJournalEntries: function(filter) {
            if (filter === 'all') {
                $('.rpg-ia-journal-entry').show();
            } else {
                $('.rpg-ia-journal-entry').hide();
                $('.rpg-ia-journal-entry-' + filter).show();
            }
        },
        
        /**
         * Utilise un objet de l'inventaire
         *
         * @param {number} itemId L'ID de l'objet
         */
        useItem: function(itemId) {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/characters/' + this.characterId + '/use-item',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                data: {
                    item_id: itemId,
                    session_id: this.sessionId
                },
                success: function(response) {
                    if (response.success) {
                        // Mettre à jour l'inventaire
                        if (response.inventory) {
                            self.updateInventory(response.inventory);
                        }
                        
                        // Ajouter l'action à la narration
                        if (response.action) {
                            self.addActionToNarration(response.action);
                            
                            // Mettre à jour le timestamp de la dernière action
                            self.lastActionTimestamp = response.action.timestamp;
                            
                            // Défiler vers le bas
                            const narrationContent = $('#rpg-ia-narration-content');
                            narrationContent.scrollTop(narrationContent[0].scrollHeight);
                        }
                    } else {
                        alert(response.message || rpg_ia_public.messages.item_error);
                    }
                },
                error: function() {
                    alert(rpg_ia_public.messages.item_error);
                }
            });
        }
    };
    
    // Initialiser le gestionnaire de jeu lorsque le document est prêt
    $(document).ready(function() {
        RPGIAGame.init();
    });
    
})(jQuery);