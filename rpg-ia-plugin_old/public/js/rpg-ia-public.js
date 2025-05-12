/**
 * Scripts pour la partie publique du plugin RPG-IA
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/public/js
 */

(function($) {
    'use strict';

    // Variables globales
    var token = localStorage.getItem('rpg_ia_token');
    var refreshInterval = null;
    var gameSession = null;
    var currentCharacter = null;
    var lastActionTimestamp = 0;

    $(document).ready(function() {
        // Initialiser l'authentification
        initAuth();
        
        // Initialiser les formulaires
        initForms();
        
        // Initialiser les listes
        initLists();
        
        // Initialiser l'interface de jeu si présente
        if ($('.rpg-ia-game-interface').length) {
            initGameInterface();
        }
        
        // Initialiser les tooltips (seulement si jQuery UI est disponible)
        if ($.fn.tooltip) {
            // Assurez-vous que les éléments ont un attribut title
            $('.rpg-ia-tooltip').each(function() {
                if (!$(this).attr('title') && $(this).data('tooltip')) {
                    $(this).attr('title', $(this).data('tooltip'));
                }
            });
            
            // Initialiser les tooltips
            $('.rpg-ia-tooltip[title]').tooltip();
        }
    });
    
    /**
     * Initialise l'authentification
     */
    function initAuth() {
        // Vérifier si un token existe et s'il est valide
        checkToken();
        
        // Formulaire de connexion WordPress
        $('#rpg-ia-login-form').on('submit', function(e) {
            e.preventDefault();
            
            var username = $('#rpg-ia-username').val();
            var password = $('#rpg-ia-password').val();
            
            if (!username || !password) {
                showMessage('error', rpg_ia_public.messages.enter_credentials);
                return;
            }
            
            // Afficher le message de chargement
            showMessage('info', rpg_ia_public.messages.logging_in);
            
            // Appeler l'API WordPress pour obtenir un token
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/auth/token',
                type: 'POST',
                data: {
                    username: username,
                    password: password
                },
                success: function(response) {
                    // Stocker le token
                    token = response.access_token;
                    localStorage.setItem('rpg_ia_token', token);
                    
                    // Stocker la date d'expiration du token
                    var tokenData = parseJwt(token);
                    if (tokenData && tokenData.exp) {
                        localStorage.setItem('rpg_ia_token_exp', tokenData.exp * 1000); // Convertir en millisecondes
                    }
                    
                    // Rediriger vers le tableau de bord
                    window.location.href = rpg_ia_public.dashboard_url;
                },
                error: function(xhr) {
                    var errorMessage = rpg_ia_public.messages.login_error;
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += ': ' + xhr.responseJSON.message;
                    }
                    
                    showMessage('error', errorMessage);
                }
            });
        });
        
        // Formulaire d'inscription
        $('#rpg-ia-register-form').on('submit', function(e) {
            e.preventDefault();
            
            var username = $('#rpg-ia-reg-username').val();
            var email = $('#rpg-ia-reg-email').val();
            var password = $('#rpg-ia-reg-password').val();
            var passwordConfirm = $('#rpg-ia-reg-password-confirm').val();
            
            if (!username || !email || !password) {
                showMessage('error', rpg_ia_public.messages.enter_all_fields);
                return;
            }
            
            if (password !== passwordConfirm) {
                showMessage('error', rpg_ia_public.messages.passwords_not_match);
                return;
            }
            
            // Afficher le message de chargement
            showMessage('info', rpg_ia_public.messages.registering);
            
            // Appeler l'API WordPress pour créer un compte
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/auth/register',
                type: 'POST',
                data: {
                    username: username,
                    email: email,
                    password: password
                },
                success: function(response) {
                    showMessage('success', rpg_ia_public.messages.register_success);
                    
                    // Rediriger vers la page de connexion après un délai
                    setTimeout(function() {
                        window.location.href = rpg_ia_public.login_url;
                    }, 2000);
                },
                error: function(xhr) {
                    var errorMessage = rpg_ia_public.messages.register_error;
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += ': ' + xhr.responseJSON.message;
                    }
                    
                    showMessage('error', errorMessage);
                }
            });
        });
        
        // Bouton de déconnexion
        $('.rpg-ia-logout, #rpg-ia-logout').on('click', function(e) {
            e.preventDefault();
            
            // Supprimer le token et la date d'expiration
            localStorage.removeItem('rpg_ia_token');
            localStorage.removeItem('rpg_ia_token_exp');
            
            // Rediriger vers la page de connexion
            window.location.href = rpg_ia_public.login_url;
        });
        
        // Bouton de rafraîchissement du token
        $('#rpg-ia-refresh-token').on('click', function(e) {
            e.preventDefault();
            refreshToken();
        });
        
        // Formulaire de connexion API
        $('#rpg-ia-api-login-form').on('submit', function(e) {
            e.preventDefault();
            
            var username = $('#rpg-ia-api-username').val();
            var password = $('#rpg-ia-api-password').val();
            
            if (!username || !password) {
                showMessage('error', rpg_ia_public.messages.enter_credentials);
                return;
            }
            
            // Afficher le message de chargement
            showMessage('info', rpg_ia_public.messages.logging_in);
            
            // Appeler l'API WordPress pour obtenir un token
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/auth/token',
                type: 'POST',
                data: {
                    username: username,
                    password: password
                },
                success: function(response) {
                    // Stocker le token
                    token = response.access_token;
                    localStorage.setItem('rpg_ia_token', token);
                    
                    // Stocker la date d'expiration du token
                    var tokenData = parseJwt(token);
                    if (tokenData && tokenData.exp) {
                        localStorage.setItem('rpg_ia_token_exp', tokenData.exp * 1000); // Convertir en millisecondes
                    }
                    
                    // Réinitialiser le compteur de tentatives de connexion
                    $.ajax({
                        url: rpg_ia_public.rest_url + 'rpg-ia/v1/auth/reset-attempts',
                        type: 'POST',
                        headers: {
                            'X-WP-Nonce': rpg_ia_public.nonce
                        }
                    });
                    
                    // Recharger la page actuelle
                    window.location.reload();
                },
                error: function(xhr) {
                    var errorMessage = rpg_ia_public.messages.login_error;
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += ': ' + xhr.responseJSON.message;
                    }
                    
                    // Incrémenter le compteur de tentatives de connexion
                    $.ajax({
                        url: rpg_ia_public.rest_url + 'rpg-ia/v1/auth/increment-attempts',
                        type: 'POST',
                        headers: {
                            'X-WP-Nonce': rpg_ia_public.nonce
                        }
                    });
                    
                    showMessage('error', errorMessage);
                }
            });
        });
        
        // Formulaire d'inscription API
        $('#rpg-ia-api-register-form').on('submit', function(e) {
            e.preventDefault();
            
            var username = $('#rpg-ia-api-reg-username').val();
            var email = $('#rpg-ia-api-reg-email').val();
            var password = $('#rpg-ia-api-reg-password').val();
            var passwordConfirm = $('#rpg-ia-api-reg-password-confirm').val();
            
            if (!username || !email || !password) {
                showMessage('error', rpg_ia_public.messages.enter_all_fields);
                return;
            }
            
            if (password !== passwordConfirm) {
                showMessage('error', rpg_ia_public.messages.passwords_not_match);
                return;
            }
            
            if (password.length < 8) {
                showMessage('error', 'Password must be at least 8 characters long.');
                return;
            }
            
            // Afficher le message de chargement
            showMessage('info', rpg_ia_public.messages.registering);
            
            // Appeler l'API WordPress pour créer un compte
            $.ajax({
                url: rpg_ia_public.rest_url + 'rpg-ia/v1/auth/register',
                type: 'POST',
                data: {
                    username: username,
                    email: email,
                    password: password
                },
                success: function(response) {
                    showMessage('success', rpg_ia_public.messages.register_success);
                    
                    // Recharger la page après un délai pour afficher le formulaire de connexion
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                },
                error: function(xhr) {
                    var errorMessage = rpg_ia_public.messages.register_error;
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += ': ' + xhr.responseJSON.message;
                    }
                    
                    showMessage('error', errorMessage);
                }
            });
        });
        
        // Afficher le formulaire d'inscription API
        $('#rpg-ia-api-show-register').on('click', function(e) {
            e.preventDefault();
            $('#rpg-ia-api-about-card').hide();
            $('#rpg-ia-api-register-card').show();
        });
        
        // Afficher le formulaire de connexion API
        $('#rpg-ia-api-show-login').on('click', function(e) {
            e.preventDefault();
            $('#rpg-ia-api-register-card').hide();
            $('#rpg-ia-api-about-card').show();
        });
    }
    
    /**
     * Initialise les formulaires
     */
    function initForms() {
        // Formulaire de création de personnage
        $('#rpg-ia-character-form').on('submit', function(e) {
            e.preventDefault();
            
            // Récupérer les données du formulaire
            var formData = $(this).serializeArray();
            var characterData = {};
            
            // Convertir les données du formulaire en objet
            $.each(formData, function(i, field) {
                characterData[field.name] = field.value;
            });
            
            // Vérifier les champs obligatoires
            if (!characterData.name) {
                showMessage('error', rpg_ia_public.enter_character_name);
                return;
            }
            
            // Afficher le message de chargement
            showMessage('info', rpg_ia_public.creating_character);
            
            // Appeler l'API pour créer un personnage
            $.ajax({
                url: rpg_ia_public.api_url + '/api/characters/',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                contentType: 'application/json',
                data: JSON.stringify(characterData),
                success: function(response) {
                    showMessage('success', rpg_ia_public.character_created);
                    
                    // Rediriger vers la liste des personnages après un délai
                    setTimeout(function() {
                        window.location.href = rpg_ia_public.characters_url;
                    }, 2000);
                },
                error: function(xhr) {
                    var errorMessage = rpg_ia_public.character_error;
                    
                    if (xhr.responseJSON && xhr.responseJSON.detail) {
                        errorMessage += ': ' + xhr.responseJSON.detail;
                    }
                    
                    showMessage('error', errorMessage);
                }
            });
        });
        
        // Formulaire de création de session
        $('#rpg-ia-session-form').on('submit', function(e) {
            e.preventDefault();
            
            // Récupérer les données du formulaire
            var formData = $(this).serializeArray();
            var sessionData = {};
            
            // Convertir les données du formulaire en objet
            $.each(formData, function(i, field) {
                sessionData[field.name] = field.value;
            });
            
            // Vérifier les champs obligatoires
            if (!sessionData.name) {
                showMessage('error', rpg_ia_public.enter_session_name);
                return;
            }
            
            // Afficher le message de chargement
            showMessage('info', rpg_ia_public.creating_session);
            
            // Appeler l'API pour créer une session
            $.ajax({
                url: rpg_ia_public.api_url + '/api/game-sessions/',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                contentType: 'application/json',
                data: JSON.stringify(sessionData),
                success: function(response) {
                    showMessage('success', rpg_ia_public.session_created);
                    
                    // Rediriger vers la liste des sessions après un délai
                    setTimeout(function() {
                        window.location.href = rpg_ia_public.sessions_url;
                    }, 2000);
                },
                error: function(xhr) {
                    var errorMessage = rpg_ia_public.session_error;
                    
                    if (xhr.responseJSON && xhr.responseJSON.detail) {
                        errorMessage += ': ' + xhr.responseJSON.detail;
                    }
                    
                    showMessage('error', errorMessage);
                }
            });
        });
    }
    
    /**
     * Initialise les listes
     */
    function initLists() {
        // Liste des personnages
        if ($('#rpg-ia-characters-list').length) {
            loadCharacters();
        }
        
        // Liste des sessions
        if ($('#rpg-ia-sessions-list').length) {
            loadSessions();
        }
    }
    
    /**
     * Charge la liste des personnages
     */
    function loadCharacters() {
        var charactersList = $('#rpg-ia-characters-list');
        
        // Afficher le message de chargement
        charactersList.html('<p class="rpg-ia-loading">' + rpg_ia_public.loading_characters + '</p>');
        
        // Appeler l'API pour récupérer les personnages
        $.ajax({
            url: rpg_ia_public.api_url + '/api/characters/',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.length === 0) {
                    charactersList.html('<p>' + rpg_ia_public.no_characters + '</p>');
                    return;
                }
                
                var html = '';
                
                // Générer le HTML pour chaque personnage
                $.each(response, function(i, character) {
                    html += '<div class="rpg-ia-card">';
                    html += '<div class="rpg-ia-card-header">';
                    html += '<h3 class="rpg-ia-card-title">' + character.name + '</h3>';
                    html += '<p class="rpg-ia-card-subtitle">' + character.character_class + ' - ' + rpg_ia_public.level + ' ' + character.level + '</p>';
                    html += '</div>';
                    html += '<div class="rpg-ia-card-content">';
                    html += '<p>' + character.description + '</p>';
                    html += '</div>';
                    html += '<div class="rpg-ia-card-footer">';
                    html += '<a href="' + rpg_ia_public.character_url.replace('{id}', character.id) + '" class="rpg-ia-button">' + rpg_ia_public.view_details + '</a>';
                    html += '</div>';
                    html += '</div>';
                });
                
                charactersList.html(html);
            },
            error: function() {
                charactersList.html('<p class="rpg-ia-error">' + rpg_ia_public.error_loading_characters + '</p>');
            }
        });
    }
    
    /**
     * Charge la liste des sessions
     */
    function loadSessions() {
        var sessionsList = $('#rpg-ia-sessions-list');
        
        // Afficher le message de chargement
        sessionsList.html('<p class="rpg-ia-loading">' + rpg_ia_public.loading_sessions + '</p>');
        
        // Appeler l'API pour récupérer les sessions
        $.ajax({
            url: rpg_ia_public.api_url + '/api/game-sessions/',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.length === 0) {
                    sessionsList.html('<p>' + rpg_ia_public.no_sessions + '</p>');
                    return;
                }
                
                var html = '';
                
                // Générer le HTML pour chaque session
                $.each(response, function(i, session) {
                    html += '<div class="rpg-ia-card">';
                    html += '<div class="rpg-ia-card-header">';
                    html += '<h3 class="rpg-ia-card-title">' + session.name + '</h3>';
                    html += '<p class="rpg-ia-card-subtitle">' + rpg_ia_public.gm + ': ' + session.game_master.username + ' - ' + rpg_ia_public.players + ': ' + session.players.length + '/' + session.max_players + '</p>';
                    html += '</div>';
                    html += '<div class="rpg-ia-card-content">';
                    html += '<p>' + session.description + '</p>';
                    html += '</div>';
                    html += '<div class="rpg-ia-card-footer">';
                    html += '<a href="' + rpg_ia_public.session_url.replace('{id}', session.id) + '" class="rpg-ia-button rpg-ia-button-secondary">' + rpg_ia_public.view_details + '</a> ';
                    html += '<a href="' + rpg_ia_public.play_url.replace('{id}', session.id) + '" class="rpg-ia-button">' + rpg_ia_public.join_session + '</a>';
                    html += '</div>';
                    html += '</div>';
                });
                
                sessionsList.html(html);
            },
            error: function() {
                sessionsList.html('<p class="rpg-ia-error">' + rpg_ia_public.error_loading_sessions + '</p>');
            }
        });
    }
    
    /**
     * Initialise l'interface de jeu
     */
    function initGameInterface() {
        // Récupérer l'ID de session depuis l'URL
        var urlParams = new URLSearchParams(window.location.search);
        var sessionId = urlParams.get('session_id');
        
        if (!sessionId) {
            showMessage('error', rpg_ia_public.no_session_specified);
            return;
        }
        
        // Charger la session
        loadGameSession(sessionId);
        
        // Formulaire d'action
        $('#rpg-ia-action-form').on('submit', function(e) {
            e.preventDefault();
            
            // Vérifier si une session est chargée
            if (!gameSession) {
                showMessage('error', rpg_ia_public.no_session_loaded);
                return;
            }
            
            // Vérifier si un personnage est sélectionné
            if (!currentCharacter) {
                showMessage('error', rpg_ia_public.no_character_selected);
                return;
            }
            
            // Récupérer les données du formulaire
            var actionType = $('#rpg-ia-action-type').val();
            var actionDescription = $('#rpg-ia-action-description').val();
            
            if (!actionDescription) {
                showMessage('error', rpg_ia_public.enter_action_description);
                return;
            }
            
            // Désactiver le formulaire pendant l'envoi
            $('#rpg-ia-action-form button').prop('disabled', true);
            $('#rpg-ia-action-description').prop('disabled', true);
            
            // Afficher le message de chargement
            $('.rpg-ia-narration').append('<p class="rpg-ia-loading">' + rpg_ia_public.processing_action + '</p>');
            
            // Appeler l'API pour soumettre l'action
            $.ajax({
                url: rpg_ia_public.api_url + '/api/actions/',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    session_id: gameSession.id,
                    character_id: currentCharacter.id,
                    action_type: actionType,
                    description: actionDescription
                }),
                success: function(response) {
                    // Mettre à jour le timestamp de la dernière action
                    lastActionTimestamp = new Date().getTime();
                    
                    // Réactiver le formulaire
                    $('#rpg-ia-action-form button').prop('disabled', false);
                    $('#rpg-ia-action-description').prop('disabled', false).val('');
                    
                    // Mettre à jour l'interface
                    updateGameInterface();
                },
                error: function(xhr) {
                    var errorMessage = rpg_ia_public.action_error;
                    
                    if (xhr.responseJSON && xhr.responseJSON.detail) {
                        errorMessage += ': ' + xhr.responseJSON.detail;
                    }
                    
                    showMessage('error', errorMessage);
                    
                    // Réactiver le formulaire
                    $('#rpg-ia-action-form button').prop('disabled', false);
                    $('#rpg-ia-action-description').prop('disabled', false);
                    
                    // Supprimer le message de chargement
                    $('.rpg-ia-narration .rpg-ia-loading').remove();
                }
            });
        });
        
        // Sélection de personnage
        $('#rpg-ia-character-select').on('change', function() {
            var characterId = $(this).val();
            
            if (!characterId) {
                currentCharacter = null;
                return;
            }
            
            // Charger les détails du personnage
            loadCharacterDetails(characterId);
        });
    }
    
    /**
     * Charge les détails d'une session de jeu
     */
    function loadGameSession(sessionId) {
        // Afficher le message de chargement
        $('.rpg-ia-narration').html('<p class="rpg-ia-loading">' + rpg_ia_public.loading_session + '</p>');
        
        // Appeler l'API pour récupérer les détails de la session
        $.ajax({
            url: rpg_ia_public.api_url + '/api/game-sessions/' + sessionId,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                gameSession = response;
                
                // Mettre à jour le titre de la session
                $('.rpg-ia-game-header h2').text(gameSession.name);
                
                // Charger les personnages de l'utilisateur
                loadUserCharacters();
                
                // Mettre à jour l'interface
                updateGameInterface();
                
                // Démarrer la mise à jour périodique
                startPeriodicUpdate();
            },
            error: function() {
                $('.rpg-ia-narration').html('<p class="rpg-ia-error">' + rpg_ia_public.error_loading_session + '</p>');
            }
        });
    }
    
    /**
     * Charge les personnages de l'utilisateur
     */
    function loadUserCharacters() {
        // Appeler l'API pour récupérer les personnages de l'utilisateur
        $.ajax({
            url: rpg_ia_public.api_url + '/api/characters/',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                var characterSelect = $('#rpg-ia-character-select');
                characterSelect.empty();
                
                // Ajouter l'option par défaut
                characterSelect.append('<option value="">' + rpg_ia_public.select_character + '</option>');
                
                // Ajouter les personnages
                $.each(response, function(i, character) {
                    characterSelect.append('<option value="' + character.id + '">' + character.name + ' (' + character.character_class + ' ' + rpg_ia_public.level + ' ' + character.level + ')</option>');
                });
                
                // Vérifier si un personnage est déjà associé à la session
                if (gameSession.players) {
                    $.each(gameSession.players, function(i, player) {
                        if (player.user_id === getCurrentUserId()) {
                            characterSelect.val(player.character_id);
                            loadCharacterDetails(player.character_id);
                        }
                    });
                }
            }
        });
    }
    
    /**
     * Récupère l'ID de l'utilisateur courant
     */
    function getCurrentUserId() {
        // Cette fonction devrait être implémentée selon la méthode utilisée pour stocker l'ID de l'utilisateur
        // Par exemple, on pourrait le récupérer depuis le token JWT décodé
        return rpg_ia_public.current_user_id;
    }
    
    /**
     * Charge les détails d'un personnage
     */
    function loadCharacterDetails(characterId) {
        // Appeler l'API pour récupérer les détails du personnage
        $.ajax({
            url: rpg_ia_public.api_url + '/api/characters/' + characterId,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                currentCharacter = response;
                
                // Mettre à jour les informations du personnage
                var characterInfo = $('.rpg-ia-character-info');
                
                var html = '<h3>' + currentCharacter.name + '</h3>';
                html += '<div class="rpg-ia-character-stats">';
                html += '<div class="rpg-ia-stat"><div class="rpg-ia-stat-name">' + rpg_ia_public.hp + '</div><div class="rpg-ia-stat-value">' + currentCharacter.current_hp + '/' + currentCharacter.max_hp + '</div></div>';
                html += '<div class="rpg-ia-stat"><div class="rpg-ia-stat-name">' + rpg_ia_public.ac + '</div><div class="rpg-ia-stat-value">' + currentCharacter.armor_class + '</div></div>';
                html += '<div class="rpg-ia-stat"><div class="rpg-ia-stat-name">' + rpg_ia_public.level + '</div><div class="rpg-ia-stat-value">' + currentCharacter.level + '</div></div>';
                html += '</div>';
                
                characterInfo.html(html);
                
                // Activer le formulaire d'action
                $('#rpg-ia-action-form button').prop('disabled', false);
                $('#rpg-ia-action-description').prop('disabled', false);
            }
        });
    }
    
    /**
     * Met à jour l'interface de jeu
     */
    function updateGameInterface() {
        // Vérifier si une session est chargée
        if (!gameSession) {
            return;
        }
        
        // Appeler l'API pour récupérer les dernières actions
        $.ajax({
            url: rpg_ia_public.api_url + '/api/game-sessions/' + gameSession.id + '/actions',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            data: {
                timestamp: lastActionTimestamp
            },
            success: function(response) {
                // Mettre à jour la narration
                updateNarration(response);
                
                // Mettre à jour la liste des joueurs
                updatePlayersList();
                
                // Mettre à jour les informations du personnage si nécessaire
                if (currentCharacter) {
                    updateCharacterInfo();
                }
            }
        });
    }
    
    /**
     * Met à jour la narration
     */
    function updateNarration(actions) {
        var narration = $('.rpg-ia-narration');
        
        // Supprimer le message de chargement
        narration.find('.rpg-ia-loading').remove();
        
        // Ajouter les nouvelles actions
        if (actions && actions.length > 0) {
            $.each(actions, function(i, action) {
                var actionHtml = '<div class="rpg-ia-action">';
                
                if (action.character) {
                    actionHtml += '<p class="rpg-ia-action-character"><strong>' + action.character.name + ':</strong> ' + action.description + '</p>';
                } else {
                    actionHtml += '<p class="rpg-ia-action-gm"><strong>' + rpg_ia_public.game_master + ':</strong> ' + action.description + '</p>';
                }
                
                if (action.result) {
                    actionHtml += '<p class="rpg-ia-action-result">' + action.result + '</p>';
                }
                
                actionHtml += '</div>';
                
                narration.append(actionHtml);
            });
            
            // Faire défiler vers le bas
            narration.scrollTop(narration[0].scrollHeight);
        }
    }
    
    /**
     * Met à jour la liste des joueurs
     */
    function updatePlayersList() {
        // Appeler l'API pour récupérer les détails de la session
        $.ajax({
            url: rpg_ia_public.api_url + '/api/game-sessions/' + gameSession.id,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                gameSession = response;
                
                var playersList = $('.rpg-ia-players-list');
                playersList.empty();
                
                // Ajouter le maître de jeu
                playersList.append('<div class="rpg-ia-player rpg-ia-player-gm"><strong>' + rpg_ia_public.game_master + ':</strong> ' + gameSession.game_master.username + '</div>');
                
                // Ajouter les joueurs
                if (gameSession.players && gameSession.players.length > 0) {
                    playersList.append('<h4>' + rpg_ia_public.players + '</h4>');
                    
                    $.each(gameSession.players, function(i, player) {
                        var playerHtml = '<div class="rpg-ia-player">';
                        playerHtml += '<strong>' + player.character.name + '</strong> (' + player.user.username + ')';
                        playerHtml += '<div class="rpg-ia-player-stats">';
                        playerHtml += '<span class="rpg-ia-player-hp">' + rpg_ia_public.hp + ': ' + player.character.current_hp + '/' + player.character.max_hp + '</span>';
                        playerHtml += '</div>';
                        playerHtml += '</div>';
                        
                        playersList.append(playerHtml);
                    });
                } else {
                    playersList.append('<p>' + rpg_ia_public.no_players + '</p>');
                }
            }
        });
    }
    
    /**
     * Met à jour les informations du personnage
     */
    function updateCharacterInfo() {
        // Appeler l'API pour récupérer les détails du personnage
        $.ajax({
            url: rpg_ia_public.api_url + '/api/characters/' + currentCharacter.id,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                currentCharacter = response;
                
                // Mettre à jour les points de vie
                $('.rpg-ia-character-info .rpg-ia-stat:first-child .rpg-ia-stat-value').text(currentCharacter.current_hp + '/' + currentCharacter.max_hp);
            }
        });
    }
    
    /**
     * Démarre la mise à jour périodique
     */
    function startPeriodicUpdate() {
        // Arrêter l'intervalle existant si présent
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        
        // Démarrer un nouvel intervalle
        refreshInterval = setInterval(function() {
            updateGameInterface();
        }, rpg_ia_public.update_interval);
    }
    
    /**
     * Affiche un message
     */
    function showMessage(type, message) {
        var messageContainer = $('.rpg-ia-messages');
        
        if (!messageContainer.length) {
            messageContainer = $('<div class="rpg-ia-messages"></div>');
            $('.rpg-ia-container').prepend(messageContainer);
        }
        
        var messageHtml = '<div class="rpg-ia-message rpg-ia-message-' + type + '">' + message + '</div>';
        
        messageContainer.html(messageHtml);
        
        // Faire disparaître le message après un délai (sauf pour les erreurs)
        if (type !== 'error') {
            setTimeout(function() {
                messageContainer.empty();
            }, 5000);
        }
    }

    /**
     * Vérifie si le token JWT est valide et le rafraîchit si nécessaire
     */
    function checkToken() {
        // Récupérer le token et sa date d'expiration
        var token = localStorage.getItem('rpg_ia_token');
        var tokenExp = localStorage.getItem('rpg_ia_token_exp');
        
        if (!token || !tokenExp) {
            // Si nous sommes sur une page qui nécessite l'authentification API, afficher le formulaire de connexion API
            if ($('#rpg-ia-api-login-form').length) {
                // Nous sommes déjà sur le formulaire de connexion API, ne rien faire
                return;
            }
            
            // Vérifier si nous sommes sur une page qui nécessite l'authentification API
            if ($('.rpg-ia-dashboard, .rpg-ia-character-list, .rpg-ia-session-list, .rpg-ia-game-interface, .rpg-ia-profile').length) {
                // Rediriger vers la page de tableau de bord qui affichera le formulaire de connexion API
                window.location.href = rpg_ia_public.dashboard_url;
            }
            
            return;
        }
        
        // Vérifier si le token est expiré
        var now = new Date().getTime();
        var expiresIn = parseInt(tokenExp) - now;
        
        if (expiresIn <= 0) {
            // Le token est expiré, le supprimer et rediriger vers la page de tableau de bord
            localStorage.removeItem('rpg_ia_token');
            localStorage.removeItem('rpg_ia_token_exp');
            
            if ($('.rpg-ia-dashboard, .rpg-ia-character-list, .rpg-ia-session-list, .rpg-ia-game-interface, .rpg-ia-profile').length) {
                // Rediriger vers la page de tableau de bord qui affichera le formulaire de connexion API
                window.location.href = rpg_ia_public.dashboard_url;
            }
            
            return;
        }
        
        // Vérifier si le token va expirer dans les 5 minutes
        if (expiresIn < 300000) { // 5 minutes en millisecondes
            refreshToken();
        }
    }
    
    /**
     * Rafraîchit le token JWT
     */
    function refreshToken() {
        // Récupérer le token actuel
        var currentToken = localStorage.getItem('rpg_ia_token');
        
        if (!currentToken) {
            return;
        }
        
        // Afficher le message de chargement
        showMessage('info', rpg_ia_public.messages.refreshing_token);
        
        // Appeler l'API WordPress pour rafraîchir le token
        $.ajax({
            url: rpg_ia_public.rest_url + 'rpg-ia/v1/auth/refresh',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + currentToken
            },
            success: function(response) {
                // Stocker le nouveau token
                token = response.access_token;
                localStorage.setItem('rpg_ia_token', token);
                
                // Stocker la nouvelle date d'expiration
                var tokenData = parseJwt(token);
                if (tokenData && tokenData.exp) {
                    localStorage.setItem('rpg_ia_token_exp', tokenData.exp * 1000); // Convertir en millisecondes
                }
                
                showMessage('success', rpg_ia_public.messages.token_refreshed);
            },
            error: function(xhr) {
                var errorMessage = rpg_ia_public.messages.token_refresh_error;
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                }
                
                showMessage('error', errorMessage);
                
                // Si le rafraîchissement échoue, rediriger vers la page de connexion
                setTimeout(function() {
                    localStorage.removeItem('rpg_ia_token');
                    localStorage.removeItem('rpg_ia_token_exp');
                    window.location.href = rpg_ia_public.login_url;
                }, 3000);
            }
        });
    }
    
    /**
     * Décode un token JWT
     */
    function parseJwt(token) {
        try {
            var base64Url = token.split('.')[1];
            var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            var jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
            
            return JSON.parse(jsonPayload);
        } catch (e) {
            return null;
        }
    }
    
    // Vérifier le token toutes les minutes
    setInterval(checkToken, 60000);
})(jQuery);