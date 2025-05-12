/**
 * Scripts pour l'administration du plugin RPG-IA
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/admin/js
 */

(function($) {
    'use strict';

    /**
     * Toutes les fonctions personnalisées pour l'administration doivent être incluses ici
     */

    $(document).ready(function() {
        
        // Vérification de l'état de l'API sur le tableau de bord
        if ($('#rpg-ia-api-status').length) {
            checkApiStatus();
            
            $('#rpg-ia-check-api').on('click', function() {
                checkApiStatus();
            });
        }
        
        // Exécution des tests manuellement
        $('#rpg-ia-run-tests').on('click', function() {
            var button = $(this);
            var result = $('#rpg-ia-tests-result');
            
            button.prop('disabled', true);
            result.html('<p>Exécution des tests en cours...</p>');
            
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_run_tests',
                    nonce: rpg_ia_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        result.html('<p class="rpg-ia-success">Tests terminés avec succès.</p>');
                        
                        // Afficher les résultats des tests
                        var tests = response.data;
                        var html = '<table class="widefat striped">';
                        html += '<thead><tr><th>Test</th><th>Description</th><th>Statut</th><th>Message</th></tr></thead>';
                        html += '<tbody>';
                        
                        var allPassed = true;
                        
                        for (var i = 0; i < tests.length; i++) {
                            var test = tests[i];
                            var status = test.success ? '<span class="rpg-ia-success">Succès</span>' : '<span class="rpg-ia-error">Échec</span>';
                            
                            if (!test.success) {
                                allPassed = false;
                            }
                            
                            html += '<tr>';
                            html += '<td>' + test.name + '</td>';
                            html += '<td>' + test.description + '</td>';
                            html += '<td>' + status + '</td>';
                            html += '<td>' + test.message + '</td>';
                            html += '</tr>';
                        }
                        
                        html += '</tbody></table>';
                        
                        result.append(html);
                        
                        // Afficher le statut global
                        var overallStatus = allPassed ? '<p class="rpg-ia-success">Tous les tests ont réussi</p>' : '<p class="rpg-ia-error">Certains tests ont échoué</p>';
                        result.append(overallStatus);
                    } else {
                        result.html('<p class="rpg-ia-error">Erreur lors de l\'exécution des tests: ' + response.data + '</p>');
                    }
                    button.prop('disabled', false);
                },
                error: function() {
                    result.html('<p class="rpg-ia-error">Une erreur s\'est produite lors de l\'exécution des tests.</p>');
                    button.prop('disabled', false);
                }
            });
        });
        
        // Gestion des métaboxes pour les types de post personnalisés
        setupCharacterMetaboxes();
        setupSessionMetaboxes();
        setupScenarioMetaboxes();
        
        // Initialisation des tooltips (seulement si jQuery UI est disponible)
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
        
        // Confirmation pour les actions destructives
        $('.rpg-ia-confirm-action').on('click', function(e) {
            if (!confirm($(this).data('confirm-message') || 'Are you sure?')) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    /**
     * Vérifie l'état de l'API backend
     */
    function checkApiStatus() {
        var statusContainer = $('#rpg-ia-api-status');
        
        if (!statusContainer.length) {
            return;
        }
        
        statusContainer.html('<p class="rpg-ia-info">' + rpg_ia_admin_l10n.checking_api + '</p>');
        
        $.ajax({
            url: rpg_ia_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'rpg_ia_check_api_status',
                nonce: rpg_ia_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    statusContainer.html(
                        '<p class="rpg-ia-success">' + rpg_ia_admin_l10n.api_connected + '</p>' +
                        '<p>' + rpg_ia_admin_l10n.api_version + ': ' + response.data.version + '</p>'
                    );
                } else {
                    statusContainer.html(
                        '<p class="rpg-ia-error">' + rpg_ia_admin_l10n.api_error + '</p>' +
                        '<p>' + response.data + '</p>'
                    );
                }
            },
            error: function() {
                statusContainer.html(
                    '<p class="rpg-ia-error">' + rpg_ia_admin_l10n.api_error + '</p>' +
                    '<p>' + rpg_ia_admin_l10n.ajax_error + '</p>'
                );
            }
        });
    }
    
    /**
     * Configure les métaboxes pour les personnages
     */
    function setupCharacterMetaboxes() {
        // Seulement sur les pages d'édition de personnage
        if ($('body').hasClass('post-type-rpgia_character')) {
            
            // Calcul automatique des modificateurs de caractéristiques
            $('.rpg-ia-ability-score').on('change', function() {
                var score = parseInt($(this).val()) || 0;
                var modifier = Math.floor((score - 10) / 2);
                var modifierText = (modifier >= 0) ? '+' + modifier : modifier;
                
                $(this).closest('.rpg-ia-ability-row').find('.rpg-ia-ability-modifier').text(modifierText);
            });
            
            // Initialiser les modificateurs au chargement
            $('.rpg-ia-ability-score').trigger('change');
            
            // Gestion de l'inventaire
            setupInventoryManager();
        }
    }
    
    /**
     * Configure les métaboxes pour les sessions
     */
    function setupSessionMetaboxes() {
        // Seulement sur les pages d'édition de session
        if ($('body').hasClass('post-type-rpgia_session')) {
            
            // Sélection de scénario
            $('#rpg_ia_scenario_select').on('change', function() {
                var scenarioId = $(this).val();
                
                if (!scenarioId) {
                    return;
                }
                
                $.ajax({
                    url: rpg_ia_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'rpg_ia_get_scenario_details',
                        nonce: rpg_ia_admin.nonce,
                        scenario_id: scenarioId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#rpg_ia_scenario_description').html(response.data.description);
                        }
                    }
                });
            });
            
            // Gestion des joueurs
            setupPlayerManager();
        }
    }
    
    /**
     * Configure les métaboxes pour les scénarios
     */
    function setupScenarioMetaboxes() {
        // Seulement sur les pages d'édition de scénario
        if ($('body').hasClass('post-type-rpgia_scenario')) {
            
            // Gestion des scènes
            setupSceneManager();
        }
    }
    
    /**
     * Configure le gestionnaire d'inventaire
     */
    function setupInventoryManager() {
        var inventoryContainer = $('#rpg-ia-inventory-manager');
        
        if (!inventoryContainer.length) {
            return;
        }
        
        // Ajouter un item
        $('#rpg-ia-add-item').on('click', function() {
            var template = $('#rpg-ia-item-template').html();
            var itemCount = $('.rpg-ia-item-row').length;
            
            // Remplacer l'index par le nombre actuel d'items
            template = template.replace(/\{index\}/g, itemCount);
            
            $('#rpg-ia-items-container').append(template);
            
            // Initialiser les contrôles du nouvel item
            initItemControls($('.rpg-ia-item-row').last());
        });
        
        // Initialiser les contrôles pour les items existants
        $('.rpg-ia-item-row').each(function() {
            initItemControls($(this));
        });
        
        function initItemControls(itemRow) {
            // Supprimer un item
            itemRow.find('.rpg-ia-remove-item').on('click', function() {
                $(this).closest('.rpg-ia-item-row').remove();
            });
            
            // Type d'item change
            itemRow.find('.rpg-ia-item-type').on('change', function() {
                var type = $(this).val();
                var row = $(this).closest('.rpg-ia-item-row');
                
                // Afficher/masquer les champs spécifiques au type
                row.find('.rpg-ia-item-field').hide();
                row.find('.rpg-ia-item-field-' + type).show();
            }).trigger('change');
        }
    }
    
    /**
     * Configure le gestionnaire de joueurs
     */
    function setupPlayerManager() {
        var playerContainer = $('#rpg-ia-player-manager');
        
        if (!playerContainer.length) {
            return;
        }
        
        // Recherche d'utilisateurs
        $('#rpg-ia-search-user').on('input', function() {
            var searchTerm = $(this).val();
            
            if (searchTerm.length < 3) {
                $('#rpg-ia-user-search-results').empty();
                return;
            }
            
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_search_users',
                    nonce: rpg_ia_admin.nonce,
                    search: searchTerm
                },
                success: function(response) {
                    if (response.success) {
                        var resultsHtml = '';
                        
                        if (response.data.length) {
                            $.each(response.data, function(index, user) {
                                resultsHtml += '<div class="rpg-ia-user-result" data-user-id="' + user.id + '">';
                                resultsHtml += '<span class="rpg-ia-user-name">' + user.name + '</span>';
                                resultsHtml += '<button type="button" class="button rpg-ia-add-player">Add</button>';
                                resultsHtml += '</div>';
                            });
                        } else {
                            resultsHtml = '<p>' + rpg_ia_admin_l10n.no_users_found + '</p>';
                        }
                        
                        $('#rpg-ia-user-search-results').html(resultsHtml);
                        
                        // Ajouter un joueur
                        $('.rpg-ia-add-player').on('click', function() {
                            var userId = $(this).closest('.rpg-ia-user-result').data('user-id');
                            var userName = $(this).closest('.rpg-ia-user-result').find('.rpg-ia-user-name').text();
                            
                            addPlayer(userId, userName);
                            $('#rpg-ia-user-search-results').empty();
                            $('#rpg-ia-search-user').val('');
                        });
                    }
                }
            });
        });
        
        // Initialiser les contrôles pour les joueurs existants
        $('.rpg-ia-player-row').each(function() {
            initPlayerControls($(this));
        });
        
        function addPlayer(userId, userName) {
            // Vérifier si le joueur est déjà ajouté
            if ($('.rpg-ia-player-row[data-user-id="' + userId + '"]').length) {
                alert(rpg_ia_admin_l10n.player_already_added);
                return;
            }
            
            var template = $('#rpg-ia-player-template').html();
            var playerCount = $('.rpg-ia-player-row').length;
            
            // Remplacer les placeholders
            template = template.replace(/\{index\}/g, playerCount);
            template = template.replace(/\{user_id\}/g, userId);
            template = template.replace(/\{user_name\}/g, userName);
            
            $('#rpg-ia-players-container').append(template);
            
            // Initialiser les contrôles du nouveau joueur
            initPlayerControls($('.rpg-ia-player-row').last());
        }
        
        function initPlayerControls(playerRow) {
            // Supprimer un joueur
            playerRow.find('.rpg-ia-remove-player').on('click', function() {
                $(this).closest('.rpg-ia-player-row').remove();
            });
            
            // Charger les personnages de l'utilisateur
            var userId = playerRow.data('user-id');
            var characterSelect = playerRow.find('.rpg-ia-player-character');
            
            $.ajax({
                url: rpg_ia_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'rpg_ia_get_user_characters',
                    nonce: rpg_ia_admin.nonce,
                    user_id: userId
                },
                success: function(response) {
                    if (response.success) {
                        var optionsHtml = '<option value="">' + rpg_ia_admin_l10n.select_character + '</option>';
                        
                        $.each(response.data, function(index, character) {
                            optionsHtml += '<option value="' + character.id + '">' + character.name + '</option>';
                        });
                        
                        characterSelect.html(optionsHtml);
                        
                        // Sélectionner le personnage actuel si défini
                        var currentCharacter = characterSelect.data('current-character');
                        if (currentCharacter) {
                            characterSelect.val(currentCharacter);
                        }
                    }
                }
            });
        }
    }
    
    /**
     * Configure le gestionnaire de scènes
     */
    function setupSceneManager() {
        var sceneContainer = $('#rpg-ia-scene-manager');
        
        if (!sceneContainer.length) {
            return;
        }
        
        // Rendre les scènes triables
        $('#rpg-ia-scenes-container').sortable({
            handle: '.rpg-ia-scene-handle',
            update: function() {
                updateSceneOrder();
            }
        });
        
        // Ajouter une scène
        $('#rpg-ia-add-scene').on('click', function() {
            var template = $('#rpg-ia-scene-template').html();
            var sceneCount = $('.rpg-ia-scene-row').length;
            
            // Remplacer l'index par le nombre actuel de scènes
            template = template.replace(/\{index\}/g, sceneCount);
            
            $('#rpg-ia-scenes-container').append(template);
            
            // Initialiser les contrôles de la nouvelle scène
            initSceneControls($('.rpg-ia-scene-row').last());
            
            // Mettre à jour l'ordre des scènes
            updateSceneOrder();
        });
        
        // Initialiser les contrôles pour les scènes existantes
        $('.rpg-ia-scene-row').each(function() {
            initSceneControls($(this));
        });
        
        function initSceneControls(sceneRow) {
            // Supprimer une scène
            sceneRow.find('.rpg-ia-remove-scene').on('click', function() {
                $(this).closest('.rpg-ia-scene-row').remove();
                updateSceneOrder();
            });
            
            // Afficher/masquer les détails de la scène
            sceneRow.find('.rpg-ia-scene-toggle').on('click', function() {
                $(this).closest('.rpg-ia-scene-row').find('.rpg-ia-scene-details').toggle();
                $(this).find('span').toggleClass('dashicons-arrow-down dashicons-arrow-up');
            });
            
            // Type de scène change
            sceneRow.find('.rpg-ia-scene-type').on('change', function() {
                var type = $(this).val();
                var row = $(this).closest('.rpg-ia-scene-row');
                
                // Afficher/masquer les champs spécifiques au type
                row.find('.rpg-ia-scene-field').hide();
                row.find('.rpg-ia-scene-field-' + type).show();
            }).trigger('change');
        }
        
        function updateSceneOrder() {
            $('.rpg-ia-scene-row').each(function(index) {
                $(this).find('.rpg-ia-scene-order').val(index + 1);
                $(this).find('.rpg-ia-scene-number').text(index + 1);
            });
        }
    }

})(jQuery);