/**
 * Script pour gérer les sessions de jeu
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
     * Gestionnaire des sessions de jeu
     */
    const RPGIASession = {
        /**
         * Initialise le gestionnaire de sessions
         */
        init: function() {
            // Initialiser les différentes vues selon la page actuelle
            if ($('.rpg-ia-sessions-list').length) {
                this.initSessionsList();
            }
            
            if ($('.rpg-ia-session-details').length) {
                this.initSessionDetails();
            }
            
            if ($('.rpg-ia-game-interface').length) {
                this.initGameInterface();
            }
            
            // Initialiser les modals
            this.initModals();
            
            // Initialiser les événements globaux
            this.initGlobalEvents();
        },
        
        /**
         * Initialise la liste des sessions
         */
        initSessionsList: function() {
            const self = this;
            
            // Charger les sessions
            this.loadSessions();
            
            // Événement pour le bouton "Nouvelle Session"
            $('#rpg-ia-new-session-btn').on('click', function() {
                self.showSessionModal();
            });
            
            // Événement pour le filtre de sessions
            $('#rpg-ia-session-filter').on('change', function() {
                self.filterSessions($(this).val());
            });
            
            // Événement pour la recherche de sessions
            $('#rpg-ia-session-search').on('input', function() {
                self.searchSessions($(this).val());
            });
            
            // Événement pour le bouton "Réessayer"
            $('#rpg-ia-retry-sessions').on('click', function() {
                self.loadSessions();
            });
            
            // Événement pour le formulaire de session
            $('#rpg-ia-session-form').on('submit', function(e) {
                e.preventDefault();
                self.saveSession();
            });
            
            // Événement pour le changement de scénario
            $('#rpg-ia-session-scenario').on('change', function() {
                if ($(this).val() === 'new') {
                    $('#rpg-ia-new-scenario-section').show();
                } else {
                    $('#rpg-ia-new-scenario-section').hide();
                }
            });
            
            // Événement pour ajouter un joueur invité
            $('#rpg-ia-add-player-btn').on('click', function() {
                self.addInvitedPlayer();
            });
            
            // Événement pour le formulaire de rejoindre une session
            $('#rpg-ia-join-session-form').on('submit', function(e) {
                e.preventDefault();
                self.joinSession();
            });
            
            // Délégation d'événements pour les boutons de la liste des sessions
            $('#rpg-ia-sessions-list').on('click', '.rpg-ia-view-session-btn', function() {
                const sessionId = $(this).closest('.rpg-ia-session-card').data('session-id');
                self.viewSession(sessionId);
            });
            
            $('#rpg-ia-sessions-list').on('click', '.rpg-ia-join-session-btn', function() {
                const sessionId = $(this).closest('.rpg-ia-session-card').data('session-id');
                self.showJoinSessionModal(sessionId);
            });
        },
        
        /**
         * Initialise la page de détails d'une session
         */
        initSessionDetails: function() {
            const self = this;
            const sessionId = $('.rpg-ia-session-details').data('session-id');
            
            // Charger les détails de la session
            this.loadSessionDetails(sessionId);
            
            // Événement pour le bouton "Modifier"
            $('#rpg-ia-edit-session-btn').on('click', function() {
                self.showSessionModal(sessionId);
            });
            
            // Événement pour le bouton "Jouer"
            $('#rpg-ia-play-session-btn').on('click', function() {
                self.playSession(sessionId);
            });
            
            // Événement pour le bouton "Inviter des joueurs"
            $('#rpg-ia-invite-player-btn').on('click', function() {
                self.showInvitePlayersModal(sessionId);
            });
            
            // Événement pour le bouton "Ajouter un scénario"
            $('#rpg-ia-add-scenario-btn').on('click', function() {
                // TODO: Implémenter l'ajout de scénario
            });
            
            // Événement pour le bouton "Changer de scénario"
            $('#rpg-ia-change-scenario-btn').on('click', function() {
                // TODO: Implémenter le changement de scénario
            });
            
            // Événement pour le bouton "Voir toutes les actions"
            $('#rpg-ia-view-all-actions').on('click', function(e) {
                e.preventDefault();
                // TODO: Implémenter l'affichage de toutes les actions
            });
            
            // Événement pour le bouton "Supprimer la session"
            $('#rpg-ia-delete-session-btn').on('click', function() {
                self.showDeleteConfirmModal(sessionId);
            });
            
            // Événement pour le bouton "Confirmer la suppression"
            $('#rpg-ia-confirm-delete-btn').on('click', function() {
                self.deleteSession(sessionId);
            });
            
            // Événement pour le bouton "Réessayer"
            $('#rpg-ia-retry-session').on('click', function() {
                self.loadSessionDetails(sessionId);
            });
            
            // Événement pour le formulaire d'invitation
            $('#rpg-ia-invite-players-form').on('submit', function(e) {
                e.preventDefault();
                self.sendInvitations();
            });
            
            // Événement pour ajouter un joueur à inviter
            $('#rpg-ia-add-invite-btn').on('click', function() {
                self.addPlayerToInvite();
            });
            
            // Délégation d'événements pour les boutons de suppression de joueur
            $('#rpg-ia-players-list').on('click', '.rpg-ia-remove-player-btn', function() {
                const playerId = $(this).closest('.rpg-ia-player-item').data('player-id');
                self.removePlayerFromSession(sessionId, playerId);
            });
        },
        
        /**
         * Initialise l'interface de jeu
         */
        initGameInterface: function() {
            const self = this;
            const sessionId = $('.rpg-ia-game-interface').data('session-id');
            
            // Charger les détails de la session
            this.loadGameSession(sessionId);
            
            // Événement pour le bouton "Rejoindre la partie"
            $('#rpg-ia-join-game-btn').on('click', function() {
                const characterId = $('#rpg-ia-select-character').val();
                if (characterId) {
                    self.joinGameWithCharacter(sessionId, characterId);
                } else {
                    alert(rpg_ia_public.messages.no_character_selected);
                }
            });
            
            // Événement pour le formulaire d'action
            $('#rpg-ia-action-form').on('submit', function(e) {
                e.preventDefault();
                self.submitAction(sessionId);
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
                self.leaveGame(sessionId);
            });
            
            // Événement pour le bouton "Envoyer" du chat
            $('#rpg-ia-send-chat-btn').on('click', function() {
                self.sendChatMessage(sessionId);
            });
            
            // Événement pour la touche Entrée dans le champ de chat
            $('#rpg-ia-chat-message').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.sendChatMessage(sessionId);
                }
            });
            
            // Événement pour le bouton "Enregistrer" des notes
            $('#rpg-ia-save-notes-btn').on('click', function() {
                self.savePersonalNotes(sessionId);
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
            
            // Mettre en place la mise à jour périodique
            this.setupPeriodicUpdates(sessionId);
        },
        
/**
         * Initialise les modals
         */
        initModals: function() {
            // Fermer les modals lorsqu'on clique sur la croix
            $('.rpg-ia-modal-close').on('click', function() {
                $(this).closest('.rpg-ia-modal').hide();
            });
            
            // Fermer les modals lorsqu'on clique sur "Annuler"
            $('.rpg-ia-modal-cancel').on('click', function() {
                $(this).closest('.rpg-ia-modal').hide();
            });
            
            // Fermer les modals lorsqu'on clique en dehors
            $('.rpg-ia-modal').on('click', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });
        },
        
        /**
         * Initialise les événements globaux
         */
        initGlobalEvents: function() {
            // Vérifier si l'utilisateur est connecté
            this.checkAuthentication();
            
            // Mettre en place le rafraîchissement automatique du token
            this.setupTokenRefresh();
        },
        
        /**
         * Vérifie si l'utilisateur est authentifié
         */
        checkAuthentication: function() {
            const token = this.getToken();
            
            if (!token) {
                // Rediriger vers la page de connexion si nécessaire
                if (!$('.rpg-ia-login-form').length) {
                    window.location.href = rpg_ia_public.login_url;
                }
            }
        },
        
        /**
         * Configure le rafraîchissement automatique du token
         */
        setupTokenRefresh: function() {
            const self = this;
            const token = this.getToken();
            
            if (token) {
                // Rafraîchir le token toutes les 15 minutes
                setInterval(function() {
                    self.refreshToken();
                }, 15 * 60 * 1000);
            }
        },
        
        /**
         * Rafraîchit le token JWT
         */
        refreshToken: function() {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/auth/refresh',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                success: function(response) {
                    if (response.success && response.access_token) {
                        self.setToken(response.access_token);
                        console.log(rpg_ia_public.messages.token_refreshed);
                    }
                },
                error: function(xhr) {
                    console.error(rpg_ia_public.messages.token_refresh_error, xhr.responseJSON);
                    // Si le token est invalide, rediriger vers la page de connexion
                    if (xhr.status === 401) {
                        self.clearToken();
                        window.location.href = rpg_ia_public.login_url;
                    }
                }
            });
        },
        
        /**
         * Charge la liste des sessions
         */
        loadSessions: function() {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            // Afficher le chargement
            $('#rpg-ia-sessions-loading').show();
            $('#rpg-ia-sessions-error').hide();
            $('#rpg-ia-no-sessions').hide();
            $('#rpg-ia-sessions-list').hide();
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                success: function(response) {
                    $('#rpg-ia-sessions-loading').hide();
                    
                    if (response.success) {
                        const sessions = response.sessions;
                        
                        if (sessions.length === 0) {
                            $('#rpg-ia-no-sessions').show();
                        } else {
                            self.renderSessions(sessions);
                            $('#rpg-ia-sessions-list').show();
                        }
                    } else {
                        $('#rpg-ia-sessions-error').show();
                    }
                },
                error: function() {
                    $('#rpg-ia-sessions-loading').hide();
                    $('#rpg-ia-sessions-error').show();
                }
            });
        },
        
        /**
         * Affiche les sessions dans la liste
         * 
         * @param {Array} sessions Les sessions à afficher
         */
        renderSessions: function(sessions) {
            const sessionsList = $('#rpg-ia-sessions-list');
            sessionsList.empty();
            
            const template = $('#rpg-ia-session-card-template').html();
            
            sessions.forEach(function(session) {
                const sessionCard = $(template);
                
                sessionCard.attr('data-session-id', session.id);
                sessionCard.find('.rpg-ia-session-name').text(session.name);
                sessionCard.find('.rpg-ia-session-description').text(session.description);
                sessionCard.find('.rpg-ia-session-gm-name').text(session.game_master.username);
                sessionCard.find('.rpg-ia-session-player-count').text(session.players.length + '/' + session.max_players);
                sessionCard.find('.rpg-ia-session-rules-name').text(this.getRulesName(session.rules));
                
                // Ajouter la classe de statut
                const statusBadge = sessionCard.find('.rpg-ia-session-status');
                statusBadge.text(this.getStatusName(session.status));
                statusBadge.addClass('rpg-ia-status-' + session.status);
                
                sessionsList.append(sessionCard);
            }, this);
        },
        
        /**
         * Filtre les sessions selon un critère
         * 
         * @param {string} filter Le filtre à appliquer
         */
        filterSessions: function(filter) {
            const sessionCards = $('.rpg-ia-session-card');
            
            if (filter === 'all') {
                sessionCards.show();
                return;
            }
            
            sessionCards.each(function() {
                const card = $(this);
                const sessionId = card.data('session-id');
                const status = card.find('.rpg-ia-session-status').hasClass('rpg-ia-status-' + filter);
                const isMySession = card.find('.rpg-ia-session-gm-name').text() === 'Vous';
                
                if (filter === 'my_sessions') {
                    card.toggle(isMySession);
                } else {
                    card.toggle(status);
                }
            });
            
            // Afficher un message si aucune session ne correspond
            if ($('.rpg-ia-session-card:visible').length === 0) {
                $('#rpg-ia-no-sessions').show();
            } else {
                $('#rpg-ia-no-sessions').hide();
            }
        },
        
        /**
         * Recherche des sessions par nom
         * 
         * @param {string} query La requête de recherche
         */
        searchSessions: function(query) {
            const sessionCards = $('.rpg-ia-session-card');
            
            if (!query) {
                sessionCards.show();
                return;
            }
            
            query = query.toLowerCase();
            
            sessionCards.each(function() {
                const card = $(this);
                const name = card.find('.rpg-ia-session-name').text().toLowerCase();
                const description = card.find('.rpg-ia-session-description').text().toLowerCase();
                const gm = card.find('.rpg-ia-session-gm-name').text().toLowerCase();
                
                const match = name.includes(query) || description.includes(query) || gm.includes(query);
                card.toggle(match);
            });
            
            // Afficher un message si aucune session ne correspond
            if ($('.rpg-ia-session-card:visible').length === 0) {
                $('#rpg-ia-no-sessions').show();
            } else {
                $('#rpg-ia-no-sessions').hide();
            }
        },
/**
         * Affiche le modal de création/édition de session
         * 
         * @param {number} sessionId L'ID de la session à éditer (optionnel)
         */
        showSessionModal: function(sessionId) {
            const modal = $('#rpg-ia-session-modal');
            const form = $('#rpg-ia-session-form');
            
            // Réinitialiser le formulaire
            form[0].reset();
            $('#rpg-ia-session-id').val('');
            $('#rpg-ia-new-scenario-section').hide();
            $('#rpg-ia-invited-players').empty();
            
            if (sessionId) {
                // Mode édition
                $('#rpg-ia-session-modal-title').text('Modifier la Session');
                this.loadSessionForEdit(sessionId);
            } else {
                // Mode création
                $('#rpg-ia-session-modal-title').text('Nouvelle Session');
            }
            
            modal.show();
        },
        
        /**
         * Charge les données d'une session pour l'édition
         * 
         * @param {number} sessionId L'ID de la session à éditer
         */
        loadSessionForEdit: function(sessionId) {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions/' + sessionId,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                success: function(response) {
                    if (response.success) {
                        const session = response.session;
                        
                        // Remplir le formulaire
                        $('#rpg-ia-session-id').val(session.id);
                        $('#rpg-ia-session-name').val(session.name);
                        $('#rpg-ia-session-description').val(session.description);
                        $('#rpg-ia-session-rules').val(session.rules);
                        $('#rpg-ia-session-difficulty').val(session.difficulty);
                        $('#rpg-ia-session-max-players').val(session.max_players);
                        $('#rpg-ia-session-status').val(session.status);
                        
                        // Gérer le scénario
                        if (session.scenario) {
                            $('#rpg-ia-session-scenario').val(session.scenario.id);
                        }
                        
                        // Afficher les joueurs invités
                        session.players.forEach(function(player) {
                            if (player.id !== session.game_master.id) {
                                self.addInvitedPlayerToList(player.username);
                            }
                        });
                    }
                },
                error: function() {
                    alert('Erreur lors du chargement de la session.');
                }
            });
        },
        
        /**
         * Charge les détails d'une session
         * 
         * @param {number} sessionId L'ID de la session
         */
        loadSessionDetails: function(sessionId) {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            // Afficher le chargement
            $('#rpg-ia-session-loading').show();
            $('#rpg-ia-session-error').hide();
            $('#rpg-ia-session-content').hide();
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions/' + sessionId,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                success: function(response) {
                    $('#rpg-ia-session-loading').hide();
                    
                    if (response.success) {
                        const session = response.session;
                        
                        // Remplir les informations de la session
                        $('#rpg-ia-session-name').text(session.name);
                        $('#rpg-ia-session-description').text(session.description);
                        $('#rpg-ia-session-gm').text(session.game_master.username);
                        $('#rpg-ia-session-created').text(self.formatDate(session.created_at));
                        $('#rpg-ia-session-last-activity').text(self.formatDate(session.updated_at));
                        $('#rpg-ia-session-rules').text(self.getRulesName(session.rules));
                        $('#rpg-ia-session-difficulty').text(self.getDifficultyName(session.difficulty));
                        
                        // Afficher le statut
                        const statusText = $('#rpg-ia-session-status-text');
                        statusText.text(self.getStatusName(session.status));
                        statusText.removeClass().addClass('rpg-ia-status-' + session.status);
                        
                        // Afficher les statistiques
                        $('#rpg-ia-session-action-count').text(session.action_count || 0);
                        $('#rpg-ia-session-playtime').text(self.formatPlaytime(session.playtime || 0));
                        $('#rpg-ia-session-tokens').text(session.tokens_used || 0);
                        
                        // Gérer le scénario
                        if (session.scenario) {
                            $('#rpg-ia-no-scenario').hide();
                            $('#rpg-ia-scenario-details').show();
                            $('#rpg-ia-scenario-name').text(session.scenario.name);
                            $('#rpg-ia-scenario-description').text(session.scenario.description);
                        } else {
                            $('#rpg-ia-no-scenario').show();
                            $('#rpg-ia-scenario-details').hide();
                        }
                        
                        // Gérer la scène
                        if (session.current_scene) {
                            $('#rpg-ia-no-scene').hide();
                            $('#rpg-ia-scene-details').show();
                            $('#rpg-ia-scene-name').text(session.current_scene.name);
                            $('#rpg-ia-scene-type').text(session.current_scene.type);
                        } else {
                            $('#rpg-ia-no-scene').show();
                            $('#rpg-ia-scene-details').hide();
                        }
                        
                        // Afficher les joueurs
                        self.renderPlayers(session.players, session.game_master.id);
                        
                        // Afficher les actions
                        if (session.recent_actions && session.recent_actions.length > 0) {
                            $('#rpg-ia-no-actions').hide();
                            self.renderActions(session.recent_actions);
                        } else {
                            $('#rpg-ia-no-actions').show();
                            $('#rpg-ia-actions-list').empty();
                        }
                        
                        // Afficher le contenu
                        $('#rpg-ia-session-content').show();
                    } else {
                        $('#rpg-ia-session-error').show();
                    }
                },
                error: function() {
                    $('#rpg-ia-session-loading').hide();
                    $('#rpg-ia-session-error').show();
                }
            });
        },
        
        /**
         * Affiche les joueurs d'une session
         * 
         * @param {Array} players Les joueurs à afficher
         * @param {number} gmId L'ID du maître de jeu
         */
        renderPlayers: function(players, gmId) {
            const playersList = $('#rpg-ia-players-list');
            playersList.empty();
            
            if (players.length === 0) {
                $('#rpg-ia-no-players').show();
                return;
            }
            
            $('#rpg-ia-no-players').hide();
            
            const template = $('#rpg-ia-player-item-template').html();
            
            players.forEach(function(player) {
                const playerItem = $(template);
                
                playerItem.attr('data-player-id', player.id);
                playerItem.find('.rpg-ia-player-name').text(player.username);
                
                if (player.character) {
                    playerItem.find('.rpg-ia-player-character').text('(' + player.character.name + ')');
                } else {
                    playerItem.find('.rpg-ia-player-character').text('(Aucun personnage)');
                }
                
                // Masquer le bouton de suppression pour le MJ
                if (player.id === gmId) {
                    playerItem.find('.rpg-ia-remove-player-btn').hide();
                    playerItem.find('.rpg-ia-player-name').append(' (MJ)');
                }
                
                playersList.append(playerItem);
            });
        },
        
        /**
         * Affiche les actions récentes d'une session
         * 
         * @param {Array} actions Les actions à afficher
         */
        renderActions: function(actions) {
            const actionsList = $('#rpg-ia-actions-list');
            actionsList.empty();
            
            const template = $('#rpg-ia-action-item-template').html();
            
            actions.forEach(function(action) {
                const actionItem = $(template);
                
                actionItem.attr('data-action-id', action.id);
                actionItem.find('.rpg-ia-action-timestamp').text(this.formatDate(action.timestamp));
                actionItem.find('.rpg-ia-action-player').text(action.player.username);
                actionItem.find('.rpg-ia-action-description').text(action.description);
                actionItem.find('.rpg-ia-action-response').text(action.response);
                
                actionsList.append(actionItem);
            }, this);
        },
        
        /**
         * Ajoute un joueur à la liste des invités
         */
        addInvitedPlayer: function() {
            const username = $('#rpg-ia-player-invite').val().trim();
            
            if (!username) {
                return;
            }
            
            this.addInvitedPlayerToList(username);
            $('#rpg-ia-player-invite').val('');
        },
        
        /**
         * Ajoute un joueur à la liste des invités dans l'interface
         * 
         * @param {string} username Le nom d'utilisateur du joueur
         */
        addInvitedPlayerToList: function(username) {
            const invitedPlayers = $('#rpg-ia-invited-players');
            
            // Vérifier si le joueur est déjà dans la liste
            if (invitedPlayers.find('[data-username="' + username + '"]').length > 0) {
                return;
            }
            
            const playerItem = $('<div class="rpg-ia-invited-player" data-username="' + username + '"></div>');
            playerItem.append('<span class="rpg-ia-invited-player-name">' + username + '</span>');
            playerItem.append('<button type="button" class="rpg-ia-button rpg-ia-remove-invited-player"><i class="fas fa-times"></i></button>');
            
            invitedPlayers.append(playerItem);
            
            // Événement pour supprimer le joueur
            playerItem.find('.rpg-ia-remove-invited-player').on('click', function() {
                playerItem.remove();
            });
        },
/**
         * Enregistre une session (création ou mise à jour)
         */
        saveSession: function() {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            // Récupérer les données du formulaire
            const sessionId = $('#rpg-ia-session-id').val();
            const name = $('#rpg-ia-session-name').val();
            const description = $('#rpg-ia-session-description').val();
            const rules = $('#rpg-ia-session-rules').val();
            const difficulty = $('#rpg-ia-session-difficulty').val();
            const maxPlayers = $('#rpg-ia-session-max-players').val();
            const status = $('#rpg-ia-session-status').val();
            const scenarioId = $('#rpg-ia-session-scenario').val();
            
            // Récupérer les joueurs invités
            const invitedPlayers = [];
            $('#rpg-ia-invited-players .rpg-ia-invited-player').each(function() {
                invitedPlayers.push($(this).data('username'));
            });
            
            // Vérifier les champs obligatoires
            if (!name || !description) {
                alert('Veuillez remplir tous les champs obligatoires.');
                return;
            }
            
            // Préparer les données
            const data = {
                name: name,
                description: description,
                rules: rules,
                difficulty: difficulty,
                max_players: parseInt(maxPlayers),
                status: status,
                invited_players: invitedPlayers
            };
            
            // Ajouter le scénario si nécessaire
            if (scenarioId === 'new') {
                const scenarioName = $('#rpg-ia-scenario-name').val();
                const scenarioDescription = $('#rpg-ia-scenario-description').val();
                
                if (scenarioName && scenarioDescription) {
                    data.new_scenario = {
                        name: scenarioName,
                        description: scenarioDescription
                    };
                }
            } else if (scenarioId) {
                data.scenario_id = parseInt(scenarioId);
            }
            
            // Déterminer l'URL et la méthode
            let url = rpg_ia_public.rest_url + 'rpg-ia/v1/sessions';
            let method = 'POST';
            
            if (sessionId) {
                url += '/' + sessionId;
                method = 'PUT';
            }
            
            // Envoyer la requête
            $.ajax({
                url: url,
                method: method,
                data: JSON.stringify(data),
                contentType: 'application/json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                success: function(response) {
                    if (response.success) {
                        // Fermer le modal
                        $('#rpg-ia-session-modal').hide();
                        
                        // Recharger les sessions ou rediriger
                        if (sessionId) {
                            // En mode édition, recharger les détails
                            if ($('.rpg-ia-session-details').length) {
                                self.loadSessionDetails(sessionId);
                            } else {
                                // Rediriger vers la page de détails
                                window.location.href = rpg_ia_public.sessions_url + '?session_id=' + sessionId;
                            }
                        } else {
                            // En mode création, recharger la liste ou rediriger
                            if ($('.rpg-ia-sessions-list').length) {
                                self.loadSessions();
                            } else {
                                // Rediriger vers la page de la session créée
                                window.location.href = rpg_ia_public.sessions_url + '?session_id=' + response.session.id;
                            }
                        }
                    } else {
                        alert('Erreur lors de l\'enregistrement de la session: ' + response.message);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Erreur lors de l\'enregistrement de la session.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += ' ' + xhr.responseJSON.message;
                    }
                    
                    alert(errorMessage);
                }
            });
        },
        
        /**
         * Supprime une session
         * 
         * @param {number} sessionId L'ID de la session à supprimer
         */
        deleteSession: function(sessionId) {
            const self = this;
            const token = this.getToken();
            
            if (!token) {
                return;
            }
            
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/sessions/' + sessionId,
                method: 'DELETE',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                },
                success: function(response) {
                    if (response.success) {
                        // Fermer le modal
                        $('#rpg-ia-delete-confirm-modal').hide();
                        
                        // Rediriger vers la liste des sessions
                        window.location.href = rpg_ia_public.sessions_url;
                    } else {
                        alert('Erreur lors de la suppression de la session: ' + response.message);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Erreur lors de la suppression de la session.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += ' ' + xhr.responseJSON.message;
                    }
                    
                    alert(errorMessage);
                }
            });
        },
        
        /**
         * Affiche le modal de confirmation de suppression
         * 
         * @param {number} sessionId L'ID de la session à supprimer
         */
        showDeleteConfirmModal: function(sessionId) {
            $('#rpg-ia-delete-confirm-modal').show();
        },
        
        /**
         * Redirige vers la page de détails d'une session
         * 
         * @param {number} sessionId L'ID de la session
         */
        viewSession: function(sessionId) {
            window.location.href = rpg_ia_public.sessions_url + '?session_id=' + sessionId;
        },
        
        /**
         * Redirige vers l'interface de jeu d'une session
         * 
         * @param {number} sessionId L'ID de la session
         */
        playSession: function(sessionId) {
            window.location.href = rpg_ia_public.play_url + '?session_id=' + sessionId;
        },
        
        /**
         * Récupère le nom des règles à partir de leur code
         * 
         * @param {string} rulesCode Le code des règles
         * @return {string} Le nom des règles
         */
        getRulesName: function(rulesCode) {
            const rulesNames = {
                'ose': 'Old School Essentials',
                'dnd5e': 'Dungeons & Dragons 5e',
                'pathfinder': 'Pathfinder',
                'custom': 'Règles personnalisées'
            };
            
            return rulesNames[rulesCode] || rulesCode;
        },
        
        /**
         * Récupère le nom du niveau de difficulté à partir de son code
         * 
         * @param {string} difficultyCode Le code du niveau de difficulté
         * @return {string} Le nom du niveau de difficulté
         */
        getDifficultyName: function(difficultyCode) {
            const difficultyNames = {
                'easy': 'Facile',
                'standard': 'Standard',
                'hard': 'Difficile',
                'deadly': 'Mortel'
            };
            
            return difficultyNames[difficultyCode] || difficultyCode;
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
         * Formate un temps de jeu en heures et minutes
         * 
         * @param {number} minutes Le temps de jeu en minutes
         * @return {string} Le temps de jeu formaté
         */
        formatPlaytime: function(minutes) {
            if (!minutes) {
                return '0h';
            }
            
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            
            if (hours === 0) {
                return mins + 'm';
            } else if (mins === 0) {
                return hours + 'h';
            } else {
                return hours + 'h ' + mins + 'm';
            }
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
         * Enregistre le token JWT dans un cookie
         * 
         * @param {string} token Le token JWT
         */
        setToken: function(token) {
            Cookies.set('rpg_ia_token', token, { expires: 1 }); // Expire après 1 jour
        },
        
        /**
         * Supprime le token JWT du cookie
         */
        clearToken: function() {
            Cookies.remove('rpg_ia_token');
        }
    };
    
    // Initialiser le gestionnaire de sessions lorsque le document est prêt
    $(document).ready(function() {
        RPGIASession.init();
    });
    
})(jQuery);