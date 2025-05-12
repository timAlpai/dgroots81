<?php
// Sécurité : Bloquer l'accès direct
if (!defined('ABSPATH')) {
    exit;

}

add_action('admin_enqueue_scripts', function($hook) {
    // Inclusion JS uniquement sur la page "Gestion des utilisateurs"
    if ($hook === 'dgroots81_page_dgroots81-user-management') {
        wp_enqueue_script(
            'dgroots81-user-management',
            plugin_dir_url(__FILE__) . 'user-management.js',
            ['jquery'],
            filemtime(plugin_dir_path(__FILE__) . 'user-management.js'),
            true
        );
        $nonce = wp_create_nonce('supprimer_api_ose');
        wp_localize_script('dgroots81-user-management', 'dgroots81AdminData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => $nonce,
        ]);
    }
add_action('wp_ajax_supprimer_api_ose', 'dgroots81_handle_supprimer_api');
});
/**
 * Affiche la page "Gestion des utilisateurs" pour le plugin dgroots81.
 */
function dgroots81_user_management_page() {
    // Récupérer tous les utilisateurs WordPress
    $users = get_users();

    ?>
    <?php
        // Récupérer l'utilisateur WP courant pour injection JS
        $current_user = wp_get_current_user();
        $current_username = esc_js($current_user->user_login);
        $current_email = esc_js($current_user->user_email);
    ?>
    <div class="wrap">
        <h1><?php _e('User management', 'dgroots81'); ?></h1>
        <table class="widefat striped" style="margin-top: 24px; max-width: 900px;">
            <thead>
                <tr>
                    <th><?php _e('ID', 'dgroots81'); ?></th>
                    <th><?php _e('Name', 'dgroots81'); ?></th>
                    <th><?php _e('Email', 'dgroots81'); ?></th>
                    <th><?php _e('Role', 'dgroots81'); ?></th>
                    <th><?php _e('Actions', 'dgroots81'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Récupérer l’URL de base de l’API OSE une seule fois
                $ose_base_url = rtrim(get_option('dgroots81_ose_server_base_api_url', ''), '/');
                foreach ($users as $user):
                    // Vérifier existence côté API
                    $api_exists = false;
                    if (!empty($ose_base_url) && !empty($user->user_login)) {
                        $check_url = $ose_base_url . '/api/users/check-exists/' . urlencode($user->user_login);
                        $response = wp_remote_get($check_url, [
                            'timeout' => 8,
                            'headers' => [
                                'Accept' => 'application/json'
                            ]
                        ]);
                        if (!is_wp_error($response)) {
                            $body = wp_remote_retrieve_body($response);
                            $data = json_decode($body, true);
                            if (isset($data['exists']) && ($data['exists'] === true || $data['exists'] === "true")) {
                                $api_exists = true;
                            }
                        }
                    }
                ?>
                    <tr>
                        <td><?php echo esc_html($user->ID); ?></td>
                        <td><?php echo esc_html($user->display_name); ?></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td><?php echo esc_html(implode(', ', $user->roles)); ?></td>
                        <td>
                            <button
                                class="button button-primary inscrire-api-btn"
                                data-user-id="<?php echo esc_attr($user->ID); ?>"
                                data-username="<?php echo esc_attr($user->user_login); ?>"
                                data-email="<?php echo esc_attr($user->user_email); ?>"
                                style="margin-right:5px;"<?php echo $api_exists ? ' disabled' : ''; ?>>
                                <?php _e('Register to API', 'dgroots81'); ?>
                            </button>
                            <button class="button" disabled style="margin-right:5px;"><?php _e('Ban', 'dgroots81'); ?></button>
                            <?php if ($api_exists): ?>
                                <button
                                    class="button button-danger supprimer-api-btn"
                                    data-user-id="<?php echo esc_attr($user->ID); ?>"
                                    data-username="<?php echo esc_attr($user->user_login); ?>"
                                    data-email="<?php echo esc_attr($user->user_email); ?>"
                                ><?php _e('Delete', 'dgroots81'); ?></button>
                            <?php else: ?>
                                <button class="button button-danger" disabled><?php _e('Delete', 'dgroots81'); ?></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <style>
            .button-danger {
                background: #dc3232;
                border-color: #a00;
                color: #fff;
            }
            .button-danger[disabled] {
                opacity: 0.6;
            }
        </style>

        <!-- Modale d'inscription à l'API -->
        <div id="inscrire-api-modal" class="inscrire-api-modal">
            <div class="inscrire-api-modal-content">
                <span class="inscrire-api-close" id="inscrire-api-close">&times;</span>
                <h2><?php _e('API Registration', 'dgroots81'); ?></h2>
                <form id="inscrire-api-form">
                    <label for="register_username"><?php _e('Username', 'dgroots81'); ?></label>
                    <input type="text" id="register_username" name="register_username" required>

                    <label for="register_email"><?php _e('Email', 'dgroots81'); ?></label>
                    <input type="email" id="register_email" name="register_email" required>

                    <label for="register_password"><?php _e('Password', 'dgroots81'); ?></label>
                    <input type="password" id="register_password" name="register_password" required>

                    <!-- Afficher le mot de passe -->
                    <label class="checkbox-row" style="margin-top: 8px; margin-bottom: 0;">
                        <input type="checkbox" id="toggle_register_password" style="margin-right: 8px;">
                        <?php _e('Show password', 'dgroots81'); ?>
                    </label>
                    
                    <label class="checkbox-row" style="margin-top: 12px;">
                        <input type="checkbox" id="register_is_active" name="register_is_active" checked style="margin-right: 8px;">
                        <?php _e('Active (is_active)', 'dgroots81'); ?>
                    </label>
                    <label class="checkbox-row">
                        <input type="checkbox" id="register_is_superuser" name="register_is_superuser" style="margin-right: 8px;">
                        <?php _e('Superuser (is_superuser)', 'dgroots81'); ?>
                    </label>

                    <div style="margin-top: 20px; text-align: right;">
                        <button type="button" class="button" id="inscrire-api-cancel"><?php _e('Cancel', 'dgroots81'); ?></button>
                        <button type="submit" class="button button-primary" disabled><?php _e('Submit', 'dgroots81'); ?></button>
                    </div>
                </form>
            </div>
        </div>

        <style>
        /* Modale API */
        .inscrire-api-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.45);
            justify-content: center;
            align-items: center;
        }
        .inscrire-api-modal.active {
            display: flex;
        }
        .inscrire-api-modal-content {
            background: #fff;
            border-radius: 8px;
            max-width: 400px;
            width: 90vw;
            padding: 32px 24px 24px 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            position: relative;
            animation: fadeInModal 0.2s;
        }
        @keyframes fadeInModal {
            from { transform: translateY(40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .inscrire-api-close {
            position: absolute;
            top: 16px; right: 18px;
            font-size: 1.6em;
            color: #888;
            cursor: pointer;
        }
        #inscrire-api-form label {
            display: block;
            margin-top: 12px;
            font-weight: 500;
        }
        /* Style spécifique pour les cases à cocher du formulaire d'inscription */
        #inscrire-api-form .checkbox-row {
            display: flex !important;
            align-items: center;
            width: fit-content;
            max-width: 260px;
            min-width: 0;
            margin-top: 12px;
            margin-bottom: 0;
            font-weight: 500;
            gap: 8px;
            white-space: nowrap;
        }
        #inscrire-api-form .checkbox-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            min-width: 18px;
        }
        #inscrire-api-form .checkbox-row span,
        #inscrire-api-form .checkbox-row label {
            white-space: nowrap;
        }
        #inscrire-api-form input,
        #inscrire-api-form textarea {
            width: 100%;
            margin-top: 4px;
            margin-bottom: 8px;
            padding: 8px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            font-size: 1em;
            background: #f9f9f9;
        }
        #inscrire-api-form button {
            min-width: 90px;
        }
        /* Responsive */
        @media (max-width: 500px) {
            .inscrire-api-modal-content {
                padding: 18px 6vw 18px 6vw;
            }
            #inscrire-api-form .checkbox-row {
                max-width: 90vw;
            }
        }
        </style>
        <script>
        // Injection de la base URL OSE côté JS
        window.dgroots81OseBaseUrl = '<?php echo esc_js(get_option('dgroots81_ose_server_base_api_url', '')); ?>';
        // Injection des infos utilisateur WP courant côté JS
        window.dgroots81CurrentUser = {
            username: '<?php echo $current_username; ?>',
            email: '<?php echo $current_email; ?>'
        };
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('inscrire-api-modal');
            const closeBtn = document.getElementById('inscrire-api-close');
            const cancelBtn = document.getElementById('inscrire-api-cancel');
            const inscrireBtns = document.querySelectorAll('.inscrire-api-btn');
            const form = document.getElementById('inscrire-api-form');
            const submitBtn = form.querySelector('button[type="submit"]');
            const passwordInput = form.register_password;
            const usernameInput = form.register_username;
            const emailInput = form.register_email;
            const togglePassword = document.getElementById('toggle_register_password');
            if (togglePassword) {
                togglePassword.addEventListener('change', function() {
                    passwordInput.type = this.checked ? 'text' : 'password';
                });
            }

          

            // Message de retour
            let messageDiv = document.createElement('div');
            messageDiv.id = 'inscrire-api-message';
            messageDiv.style.marginTop = '12px';
            form.appendChild(messageDiv);

            // Générateur de mot de passe sécurisé
            function generateSecurePassword(length = 14) {
                const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+";
                let password = "";
                const array = new Uint32Array(length);
                window.crypto.getRandomValues(array);
                for (let i = 0; i < length; i++) {
                    password += charset[array[i] % charset.length];
                }
                return password;
            }

            // Pré-remplissage et génération à l'ouverture de la modale
            // Variable pour mémoriser le bouton "Inscrire à l'API" cliqué
            let lastInscrireBtnClicked = null;
            inscrireBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    lastInscrireBtnClicked = btn; // Mémoriser le bouton cliqué
                    // Pré-remplir username/email avec les data-attributes du bouton cliqué (utilisateur de la ligne)
                    usernameInput.value = btn.dataset.username || '';
                    emailInput.value = btn.dataset.email || '';
                    // Générer un mot de passe sécurisé et l'afficher (readonly)
                    const generatedPassword = generateSecurePassword();
                    passwordInput.value = generatedPassword;
                    // Sauvegarder temporairement côté client (window, sessionStorage ou localStorage)
                    window._inscrireApiPassword = generatedPassword;
                    // Optionnel : sessionStorage.setItem('inscrireApiPassword', generatedPassword);
                    // Vérifier la validité du formulaire
                    checkFormValidity();
                    modal.classList.add('active');
                });
            });

            // Activation du bouton submit si tous les champs requis sont remplis
            function checkFormValidity() {
                const username = usernameInput.value.trim();
                const email = emailInput.value.trim();
                const password = passwordInput.value.trim();
                submitBtn.disabled = !(username && email && password);
            }
            usernameInput.addEventListener('input', checkFormValidity);
            emailInput.addEventListener('input', checkFormValidity);
            passwordInput.addEventListener('input', checkFormValidity);

            function closeModal() {
                modal.classList.remove('active');
                form.reset();
                submitBtn.disabled = true;
                messageDiv.textContent = '';
                messageDiv.style.color = '';
            }

            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            // Envoi AJAX du formulaire
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitBtn.disabled = true;
                messageDiv.textContent = 'Envoi en cours...';
                messageDiv.style.color = '#333';

                // Construction dynamique des champs à envoyer
                const params = {
                    action: 'dgroots81_inscrire_api',
                    register_username: usernameInput.value.trim(),
                    register_email: emailInput.value.trim(),
                    register_password: passwordInput.value.trim()
                };
                if (form.register_is_active.checked) {
                    params.register_is_active = '1';
                }
                if (form.register_is_superuser.checked) {
                    params.register_is_superuser = '1';
                }

                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams(params)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageDiv.textContent = data.message || 'Inscription réussie.';
                        messageDiv.style.color = 'green';

                        // Désactiver le bouton "Inscrire à l'API" et activer "Supprimer" sur la même ligne
                        if (lastInscrireBtnClicked) {
                            lastInscrireBtnClicked.disabled = true;
                            const row = lastInscrireBtnClicked.closest('tr');
                            if (row) {
                                // Chercher le bouton "Delete" désactivé (sans classe supprimer-api-btn)
                                const deleteBtnDisabled = Array.from(row.querySelectorAll('button.button-danger')).find(
                                    b => !b.classList.contains('supprimer-api-btn') && b.disabled
                                );
                                // Remplacer le bouton désactivé par un bouton actif avec la classe supprimer-api-btn
                                if (deleteBtnDisabled) {
                                    const newDeleteBtn = document.createElement('button');
                                    newDeleteBtn.className = 'button button-danger supprimer-api-btn';
                                    newDeleteBtn.textContent = 'Delete';
                                    newDeleteBtn.disabled = false;
                                    newDeleteBtn.setAttribute('data-user-id', lastInscrireBtnClicked.dataset.userId);
                                    newDeleteBtn.setAttribute('data-username', lastInscrireBtnClicked.dataset.username);
                                    newDeleteBtn.setAttribute('data-email', lastInscrireBtnClicked.dataset.email);
                                    deleteBtnDisabled.replaceWith(newDeleteBtn);
                                } else {
                                    // Sinon, activer le bouton existant s'il existe
                                    let deleteBtn = row.querySelector('.supprimer-api-btn');
                                    if (deleteBtn) {
                                        deleteBtn.disabled = false;
                                    }
                                }
                            }
                        }

                        setTimeout(closeModal, 1800);
                    } else {
                        messageDiv.textContent = data.message || 'Erreur lors de l\'inscription.';
                        messageDiv.style.color = 'red';
                        submitBtn.disabled = false;
                    }
                })
                .catch(() => {
                    messageDiv.textContent = 'Erreur de communication avec le serveur.';
                    messageDiv.style.color = 'red';
                    submitBtn.disabled = false;
                });
            });

            // Fermer la modale si clic en dehors du contenu
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Initial state
            submitBtn.disabled = true;
        });
        </script>
    </div>
    <?php
}

// --- Endpoint AJAX pour inscription API ---
add_action('wp_ajax_dgroots81_inscrire_api', 'dgroots81_handle_inscrire_api');
function dgroots81_handle_inscrire_api() {
    // Vérifier les droits (admin)
    if (!current_user_can('manage_options')) {
        wp_send_json(['success' => false, 'message' => 'Accès refusé.']);
    }

    // Récupérer et nettoyer les données du formulaire
    $username = isset($_POST['register_username']) ? trim(sanitize_text_field($_POST['register_username'])) : '';
    $email = isset($_POST['register_email']) ? trim(sanitize_email($_POST['register_email'])) : '';
    $password = isset($_POST['register_password']) ? trim($_POST['register_password']) : '';
    $is_active = isset($_POST['register_is_active']) ? true : false;
    $is_superuser = isset($_POST['register_is_superuser']) ? true : false;

    // Validation stricte
    if (empty($username) || empty($email) || empty($password)) {
        wp_send_json(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis.']);
    }
    if (!is_email($email)) {
        wp_send_json(['success' => false, 'message' => 'Adresse email invalide.']);
    }

    // Construire le payload pour l’API register
    $payload = [
        'username' => $username,
        'email' => $email,
        'password' => $password
    ];
    // Ajouter les champs optionnels uniquement s’ils sont cochés
    if ($is_active) {
        $payload['is_active'] = true;
    }
    if ($is_superuser) {
        $payload['is_superuser'] = true;
    }
    
        // Récupérer l’URL de base de l’API OSE depuis les options
        $base_url = rtrim(get_option('dgroots81_ose_server_base_api_url'), '/');
        $users_url = $base_url . '/api/users/';

        // Récupérer le token admin stocké dans les options
        $admin_token = get_option('dgroots81_ose_server_admin_token', '');

        // Préparer les headers avec authentification si token présent
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        if (!empty($admin_token)) {
            $headers['Authorization'] = 'Bearer ' . $admin_token;
        }

        // Appeler l’API pour créer l’utilisateur
        $response = wp_remote_post($users_url, [
            'headers' => $headers,
            'body' => json_encode($payload),
            'timeout' => 15
        ]);
    
        if (is_wp_error($response)) {
            wp_send_json(['success' => false, 'message' => 'Erreur de connexion à l’API distante : ' . $response->get_error_message()]);
        }
    
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
    
        // Tenter de décoder la réponse JSON
        $data = json_decode($body, true);
    
        if ($code === 200 && isset($data['username'])) {
            // Enregistrement de l'ID OSE dans les meta utilisateur WordPress
            $wp_user = get_user_by('email', $email);
            if ($wp_user && isset($data['id'])) {
                update_user_meta($wp_user->ID, 'ose_user_id', $data['id']);
                error_log('[OSE REGISTER] Utilisateur WP #' . $wp_user->ID . ' (' . $username . ') inscrit à l\'API OSE avec id=' . $data['id']);
            } else {
                error_log('[OSE REGISTER] Utilisateur WP non trouvé ou id OSE absent. username=' . $username . ', email=' . $email . ', id OSE=' . (isset($data['id']) ? $data['id'] : 'null'));
            }

            // Envoi de l'email récapitulatif à l'utilisateur
            $to = $email;
            $subject = 'Votre accès API – Identifiants';
            $message = "Bonjour,\n\nVotre compte API a été créé avec succès.\n\nIdentifiant : " . $username . "\nMot de passe : " . $password . "\n\nConservez ces informations en lieu sûr.\n\nCordialement,\nL'équipe DungeonRoots";
            $headers = array('Content-Type: text/plain; charset=UTF-8');
            wp_mail($to, $subject, $message, $headers);

            wp_send_json(['success' => true, 'message' => 'Inscription réussie sur l’API OSE. Identifiant : ' . esc_html($data['username'])]);
        } elseif ($code === 422 && isset($data['detail'])) {
            // Erreur de validation détaillée
            $msg = is_array($data['detail']) ? implode(' ', array_map(function($e) {
                return isset($e['msg']) ? $e['msg'] : '';
            }, $data['detail'])) : $data['detail'];
            wp_send_json(['success' => false, 'message' => 'Erreur de validation : ' . esc_html($msg)]);
        } else {
            // Autre erreur
            $msg = isset($data['message']) ? $data['message'] : $body;
            wp_send_json(['success' => false, 'message' => 'Erreur API : ' . esc_html($msg)]);
        }
}
/**
 * Handler AJAX pour suppression d’un utilisateur via l’API OSE et nettoyage WordPress.
 */
function dgroots81_handle_supprimer_api() {
    // Vérifier les droits (admin)
    if (!current_user_can('manage_options')) {
        wp_send_json(['success' => false, 'message' => 'Accès refusé.']);
    }

    // Vérification du nonce AJAX pour la sécurité
    if (!isset($_POST['_ajax_nonce'])) {
        wp_send_json(['success' => false, 'message' => 'Nonce absent dans la requête AJAX.']);
    }
    $nonce_valid = wp_verify_nonce($_POST['_ajax_nonce'], 'supprimer_api_ose');
    if (!$nonce_valid) {
        wp_send_json(['success' => false, 'message' => 'Nonce invalide ou expiré.']);
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    if (!$user_id) {
        wp_send_json(['success' => false, 'message' => 'ID utilisateur manquant.']);
    }

    // Récupérer l’ID OSE associé (stocké en meta user, ex : ose_user_id)
    $ose_user_id = get_user_meta($user_id, 'ose_user_id', true);
    if (!$ose_user_id) {
        error_log('[OSE DELETE] Suppression échouée : Aucun ID OSE associé à l\'utilisateur WP #' . $user_id);
        wp_send_json(['success' => false, 'message' => 'Aucun ID OSE associé à cet utilisateur.']);
    }

    // Appeler l’API OSE pour suppression
    $base_url = rtrim(get_option('dgroots81_ose_server_base_api_url'), '/');
    $users_url = rtrim($base_url, '/') . '/api/users/' . urlencode($ose_user_id);
    $admin_token = get_option('dgroots81_ose_server_admin_token', '');

    $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ];
    if (!empty($admin_token)) {
        $headers['Authorization'] = 'Bearer ' . $admin_token;
    }

    error_log('[OSE DELETE] Tentative suppression utilisateur WP #' . $user_id . ' (OSE id=' . $ose_user_id . ') via ' . $users_url);

    $response = wp_remote_request($users_url, [
        'method' => 'DELETE',
        'headers' => $headers,
        'timeout' => 15
    ]);

    if (is_wp_error($response)) {
        error_log('[OSE DELETE] Erreur de connexion à l’API distante : ' . $response->get_error_message());
        wp_send_json(['success' => false, 'message' => 'Erreur de connexion à l’API distante : ' . $response->get_error_message()]);
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    error_log('[OSE DELETE] Réponse HTTP=' . $code . ' pour utilisateur WP #' . $user_id . ' (OSE id=' . $ose_user_id . ')');

    if ($code === 204) {
        error_log('[OSE DELETE] Suppression réussie pour utilisateur WP #' . $user_id . ' (OSE id=' . $ose_user_id . ')');
        // Suppression côté OSE réussie, on NE supprime PAS l’utilisateur WordPress
        wp_send_json(['success' => true, 'message' => 'Utilisateur supprimé côté OSE.']);
    } elseif ($code === 404) {
        error_log('[OSE DELETE] Utilisateur OSE introuvable pour WP #' . $user_id . ' (OSE id=' . $ose_user_id . ')');
        wp_send_json(['success' => false, 'message' => 'Utilisateur OSE introuvable.']);
    } else {
        $msg = isset($data['message']) ? $data['message'] : $body;
        error_log('[OSE DELETE] Erreur API OSE : ' . $msg);
        wp_send_json(['success' => false, 'message' => 'Erreur API OSE : ' . esc_html($msg)]);
    }
}