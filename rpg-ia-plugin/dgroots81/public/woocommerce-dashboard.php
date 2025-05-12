<?php
// Sécurité : Bloquer l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Ajout d'un nouvel onglet "dgroots81" dans le menu Mon Compte WooCommerce
add_filter('woocommerce_account_menu_items', 'dgroots81_add_account_menu_item');
function dgroots81_add_account_menu_item($items) {
    // Ajoute l'onglet avant "Déconnexion"
    $logout = $items['customer-logout'];
    unset($items['customer-logout']);
    $items['dgroots81'] = __('dgroots81', 'dgroots81');
    $items['customer-logout'] = $logout;
    return $items;
}

// Enregistrement de l'endpoint pour l'onglet "dgroots81"
add_action('init', 'dgroots81_add_endpoint', 0);
function dgroots81_add_endpoint() {
    add_rewrite_endpoint('dgroots81', EP_PAGES);
}

// Flush des permaliens à l’activation pour l’endpoint WooCommerce
register_activation_hook(dirname(__DIR__) . '/dgroots81.php', function() {
    dgroots81_add_endpoint();
    flush_rewrite_rules();
});

// Affichage du contenu de l'onglet "dgroots81"
add_action('woocommerce_account_dgroots81_endpoint', 'dgroots81_account_content');
function dgroots81_account_content() {
    $handle = 'dgroots81-user-profile';
    $src = plugins_url('public/js/user-profile.js', dirname(__DIR__) . '/dgroots81.php');
    wp_enqueue_script($handle, $src, array('jquery'), null, true);

    // Générer le nonce pour l'action AJAX
    $nonce = wp_create_nonce('dgroots81_update_username');

    // Passer ajaxurl et le nonce au JS
    wp_localize_script($handle, 'dgroots81UserProfile', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => $nonce,
    ));
    ?>
    <style>
    .dgroots81-tabs { display: flex; border-bottom: 1px solid #ccc; margin-bottom: 1em; }
    .dgroots81-tab-btn {
        background: none; border: none; padding: 1em 2em; cursor: pointer; font-weight: bold; font-size: 1em;
        border-bottom: 3px solid transparent; transition: border-color 0.2s;
    }
    .dgroots81-tab-btn.active { border-bottom: 3px solid #0073aa; color: #0073aa; }
    .dgroots81-tab-content { display: none; }
    .dgroots81-tab-content.active { display: block; }
    </style>
    <div class="dgroots81-tabs">
        <button class="dgroots81-tab-btn active" data-tab="profil"><?php echo esc_html(__('Profil', 'dgroots81')); ?></button>
        <button class="dgroots81-tab-btn" data-tab="joueur"><?php echo esc_html(__('Joueur', 'dgroots81')); ?></button>
    </div>
    <div class="dgroots81-tab-content active" id="dgroots81-tab-profil">
        <?php
        // --- Début contenu Profil (ancien contenu de la fonction) ---
        echo '<h3>' . __('Profile dgroots81', 'dgroots81') . '</h3>';
        echo '<p>' . __('Bienvenue dans votre espace dgroots81', 'dgroots81') . '</p>';

        // Vérification/refresh centralisée du token OSE pour l'utilisateur courant
        $ose_token = dgroots81_get_valid_ose_jwt_token();

        // Gestion de la soumission du formulaire Connexion API
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
            $username = sanitize_text_field($_POST['username']);
            $password = sanitize_text_field($_POST['password']);

            $response = wp_remote_post(rtrim(get_option('dgroots81_ose_server_base_api_url', ''), '/') . '/api/auth/token', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'username' => $username,
                    'password' => $password,
                ],
                'timeout' => 15,
            ]);

            if (is_wp_error($response)) {
                $message = '<div style="color:red;margin-bottom:1em;">' . __('API connection error:', 'dgroots81') . '</div>';
            } else {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                if (!empty($body['access_token'])) {
                    if (function_exists('WC') && WC()->session) {
                        WC()->session->set('dgroots81_api_token', $body['access_token']);
                    }
                    // Sauvegarde du token JWT dans la user_meta WordPress
                    $user_id = get_current_user_id();
                    if ($user_id) {
                        update_user_meta($user_id, 'dgroots81_ose_jwt_token', $body['access_token']);
                    }
                    if (defined('DROOTS81_DEBUG') && DROOTS81_DEBUG) {
                        $message = '<div style="color:green;margin-bottom:1em;">' . __('API connection successful.', 'dgroots81') . '</div>';
                    }
                    // Après connexion, on tente de récupérer le nouveau token pour masquer le formulaire si succès
                    $ose_token = $body['access_token'];
                } else {
                    $message = '<div style="color:red;margin-bottom:1em;">' . __('API authentication failed.', 'dgroots81') . '</div>';
                }
            }
        }

        // Affichage du message de succès/échec
        if (!empty($message)) {
            echo $message;
        }

        // Affichage conditionnel du formulaire Connexion API
        if (!$ose_token) {
            echo '<div style="margin-top:2em;max-width:400px;">';
            echo '<form id="dgroots81-api-login-form" method="post" autocomplete="off">';
            echo '<fieldset style="border:1px solid #ccc;padding:1em;">';
            echo '<legend style="font-weight:bold;">' . __('API Login', 'dgroots81') . '</legend>';
            echo '<p><label for="api-username">' . __('Username', 'dgroots81') . '</label><br>';
            echo '<input type="text" id="api-username" name="username" required style="width:100%;"></p>';
            echo '<p><label for="api-password">' . __('Password', 'dgroots81') . '</label><br>';
            echo '<input type="password" id="api-password" name="password" required style="width:100%;"></p>';
            echo '<button type="submit" style="margin-top:1em;">' . __('Log in', 'dgroots81') . '</button>';
            echo '</fieldset>';
            echo '</form>';
            echo '</div>';
        } else {
            // Afficher un message de confirmation de connexion à l'API OSE
            if (defined('DROOTS81_DEBUG') && DROOTS81_DEBUG) {
                echo '<div style="color:green;margin-top:2em;font-weight:bold;">' . __('Server connected OK', 'dgroots81') . '</div>';
            }

            // Appel au endpoint /api/auth/me pour récupérer les infos utilisateur OSE
            $base_url = rtrim(get_option('dgroots81_ose_server_base_api_url', ''), '/');
            $me_url = $base_url . '/api/auth/me';
            $response = wp_remote_get($me_url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $ose_token,
                    'Accept' => 'application/json',
                ],
                'timeout' => 15,
            ]);

            if (is_wp_error($response)) {
                echo '<div style="color:red;margin-top:1em;">' . __('Erreur lors de la récupération des informations utilisateur OSE.', 'dgroots81') . '</div>';
            } else {
                $code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                if ($code === 200 && !empty($body)) {
                    $user_info = json_decode($body, true);
                    if (is_array($user_info)) {
                        // Présentation améliorée du profil utilisateur OSE
                        echo '<style>
                        .dgroots81-user-card {
                            margin-top: 1em;
                            width: 100%;
                            padding: 1.5em 2em;
                            border-radius: 12px;
                            background: #f9f9f9;
                            box-shadow: 0 2px 8px #0001;
                            box-sizing: border-box;
                            max-width: none;
                        }
                        .dgroots81-user-card .user-info-row {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            padding: 0.7em 0;
                            border-bottom: 1px solid #ececec;
                            width: 100%;
                            gap: 1em;
                        }
                        .dgroots81-user-card .user-info-row:last-child {
                            border-bottom: none;
                        }
                        .dgroots81-user-card .user-info-label {
                            font-weight: bold;
                            flex: 1 1 40%;
                            text-align: left;
                            min-width: 120px;
                            word-break: break-word;
                        }
                        .dgroots81-user-card .user-info-value {
                            flex: 1 1 60%;
                            text-align: right;
                            word-break: break-word;
                        }
                        .dgroots81-editable-username-wrapper:hover .dgroots81-editable-username,
                        .dgroots81-editable-username:focus {
                            background: #eef6fa;
                            border-radius: 4px;
                            cursor: text;
                        }
                        .dgroots81-editable-username[readonly] {
                            cursor: pointer;
                            color: #444;
                            background: transparent;
                        }
                        .dgroots81-edit-icon {
                            opacity: 0.7;
                            transition: opacity 0.2s;
                        }
                        .dgroots81-editable-username-wrapper:hover .dgroots81-edit-icon,
                        .dgroots81-editable-username:focus + .dgroots81-edit-icon {
                            opacity: 1;
                        }
                        .dgroots81-edit-icon svg {
                            pointer-events: none;
                        }
                        @media (max-width: 600px) {
                            .dgroots81-user-card {
                                padding: 1em 0.5em;
                            }
                            .dgroots81-user-card .user-info-row {
                                flex-direction: column;
                                align-items: flex-start;
                                gap: 0.2em;
                            }
                            .dgroots81-user-card .user-info-label,
                            .dgroots81-user-card .user-info-value {
                                text-align: left;
                                width: 100%;
                            }
                        }
                        </style>';
                        echo '<div class="dgroots81-user-card">';
                        
                        echo '<div class="user-info-row">
                            <div class="user-info-label">' . __('Nom d’utilisateur', 'dgroots81') . '</div>
                            <div class="user-info-value">
                                <span class="dgroots81-editable-username-wrapper" style="display:inline-flex;align-items:center;gap:0.5em;">
                                    <input
                                        type="text"
                                        class="js-edit-username dgroots81-editable-username"
                                        value="' . esc_attr($user_info['username'] ?? '') . '"
                                        readonly
                                        data-editable="username"
                                        aria-label="' . esc_attr(__('Nom d’utilisateur', 'dgroots81')) . '"
                                        style="border:none;background:transparent;font:inherit;width:auto;min-width:80px;max-width:180px;outline:none;cursor:pointer;padding:0;"
                                    />
                                    <span class="dgroots81-edit-icon dgroots81-edit-username-pencil" title="' . esc_attr(__('Modifier le nom d’utilisateur', 'dgroots81')) . '" style="display:inline-block;cursor:pointer;">
                                        <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;">
                                            <path d="M14.85 2.85a2.5 2.5 0 0 1 3.54 3.54l-10.1 10.1a1 1 0 0 1-.44.25l-4 1a1 1 0 0 1-1.22-1.22l1-4a1 1 0 0 1 .25-.44l10.1-10.1zm2.12 1.42a.5.5 0 0 0-.7 0l-1.18 1.18 1.7 1.7 1.18-1.18a.5.5 0 0 0 0-.7l-1-1zm-2.18 2.88l-9.1 9.1-.5 2 2-.5 9.1-9.1-1.6-1.5z" fill="#888"/>
                                        </svg>
                                    </span>
                                </span>
                            </div>
                        </div>';
                        
                        echo '<div class="user-info-row"><div class="user-info-label">' . __('E-mail', 'dgroots81') . '</div><div class="user-info-value">' . esc_html($user_info['email'] ?? __('Non renseigné', 'dgroots81')) . '</div></div>';
                        
                        $date = !empty($user_info['created_at']) ? date_i18n('d/m/Y H:i', strtotime($user_info['created_at'])) : __('Non renseigné', 'dgroots81');
                        echo '<div class="user-info-row"><div class="user-info-label">' . __('Date d’inscription', 'dgroots81') . '</div><div class="user-info-value">' . esc_html($date) . '</div></div>';
                        
                        $is_active = isset($user_info['is_active']) ? (bool)$user_info['is_active'] : null;
                        $statut_html = '';
                        if ($is_active === true) {
                            $statut_html = '<span style="color:#fff;background:#28a745;padding:0.2em 0.7em;border-radius:8px;font-size:0.95em;">' . __('Actif', 'dgroots81') . '</span>';
                        } elseif ($is_active === false) {
                            $statut_html = '<span style="color:#fff;background:#dc3545;padding:0.2em 0.7em;border-radius:8px;font-size:0.95em;">' . __('Inactif', 'dgroots81') . '</span>';
                        } else {
                            $statut_html = __('Non renseigné', 'dgroots81');
                        }
                        echo '<div class="user-info-row"><div class="user-info-label">' . __('Statut', 'dgroots81') . '</div><div class="user-info-value">' . $statut_html . '</div></div>';
                        
                        $is_superuser = isset($user_info['is_superuser']) ? (bool)$user_info['is_superuser'] : null;
                        $role_html = '';
                        if ($is_superuser === true) {
                            $role_html = '<span style="color:#fff;background:#0073aa;padding:0.2em 0.7em;border-radius:8px;font-size:0.95em;">' . __('Superutilisateur', 'dgroots81') . '</span>';
                        } elseif ($is_superuser === false) {
                            $role_html = '<span style="color:#fff;background:#6c757d;padding:0.2em 0.7em;border-radius:8px;font-size:0.95em;">' . __('Utilisateur standard', 'dgroots81') . '</span>';
                        } else {
                            $role_html = __('Non renseigné', 'dgroots81');
                        }
                        echo '<div class="user-info-row"><div class="user-info-label">' . __('Rôle', 'dgroots81') . '</div><div class="user-info-value">' . $role_html . '</div></div>';
                        // Nombre de sessions
                        $sessions = $user_info['total_sessions'] ?? $user_info['sessions_count'] ?? null;
                        echo '<div class="user-info-row"><div class="user-info-label">' . __('Nombre de sessions', 'dgroots81') . '</div><div class="user-info-value">' . (is_numeric($sessions) ? intval($sessions) : __('Non renseigné', 'dgroots81')) . '</div></div>';
                        // Nombre de personnages
                        $characters = $user_info['total_characters'] ?? $user_info['characters_count'] ?? null;
                        echo '<div class="user-info-row"><div class="user-info-label">' . __('Nombre de personnages', 'dgroots81') . '</div><div class="user-info-value">' . (is_numeric($characters) ? intval($characters) : __('Non renseigné', 'dgroots81')) . '</div></div>';
                        // Temps total de jeu
                        $play_time = $user_info['total_game_time'] ?? null;
                        if (is_numeric($play_time)) {
                            $hours = floor($play_time / 3600);
                            $minutes = floor(($play_time % 3600) / 60);
                            $seconds = $play_time % 60;
                            $play_time_str = sprintf('%02dh %02dm %02ds', $hours, $minutes, $seconds);
                        } else {
                            $play_time_str = __('Non renseigné', 'dgroots81');
                        }
                        echo '<div class="user-info-row"><div class="user-info-label">' . __('Temps total de jeu', 'dgroots81') . '</div><div class="user-info-value">' . esc_html($play_time_str) . '</div></div>';
                        // Classe favorite (optionnelle)
                        $fav_class = $user_info['favorite_character_class'] ?? $user_info['favorite_class'] ?? null;
                        echo '<div class="user-info-row"><div class="user-info-label">' . __('Classe favorite', 'dgroots81') . '</div><div class="user-info-value">' . ($fav_class ? esc_html($fav_class) : __('Aucune', 'dgroots81')) . '</div></div>';
                        // Total d’actions (optionnel)
                        $total_actions = $user_info['total_actions'] ?? null;
                        echo '<div class="user-info-row"><div class="user-info-label">' . __('Total d’actions', 'dgroots81') . '</div><div class="user-info-value">' . (is_numeric($total_actions) ? intval($total_actions) : __('Non renseigné', 'dgroots81')) . '</div></div>';
                        echo '</div>';
                    } else {
                        echo '<div style="color:red;margin-top:1em;">' . __('Réponse inattendue du serveur OSE.', 'dgroots81') . '</div>';
                    }
                } else {
                    echo '<div style="color:red;margin-top:1em;">' . __('Impossible de récupérer les informations utilisateur OSE.', 'dgroots81') . '</div>';
                }
            }
        }
        // --- Fin contenu Profil ---
        ?>
    </div>
    <div class="dgroots81-tab-content" id="dgroots81-tab-joueur">
        <!-- Contenu de l'onglet Joueur (vide pour l’instant) -->
    </div>
    <script>
    (function() {
        const tabBtns = document.querySelectorAll('.dgroots81-tab-btn');
        const tabContents = document.querySelectorAll('.dgroots81-tab-content');
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                const tab = this.getAttribute('data-tab');
                document.getElementById('dgroots81-tab-' + tab).classList.add('active');
            });
        });
    })();
    </script>
    <?php
}

/**
 * Récupère un token JWT OSE valide pour l'utilisateur courant.
 * - Vérifie la validité du token stocké (user_meta).
 * - Si expiré/invalide, tente un refresh via /api/auth/refresh.
 * - Met à jour user_meta si besoin.
 * - Retourne toujours un access_token valide ou false si impossible.
 */
function dgroots81_get_valid_ose_jwt_token() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return false;
    }
    $token = get_user_meta($user_id, 'dgroots81_ose_jwt_token', true);
    if (empty($token)) {
        return false;
    }

    // Décodage du JWT pour vérifier l'expiration (champ exp)
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }
    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    if (!$payload || empty($payload['exp'])) {
        return false;
    }
    $now = time();
    // On considère le token expiré s'il reste moins de 60s
    if ($payload['exp'] > $now + 60) {
        return $token;
    }

    // Token expiré ou proche de l'expiration : tentative de refresh
    $base_url = rtrim(get_option('dgroots81_ose_server_base_api_url', ''), '/');
    $refresh_url = $base_url . '/api/auth/refresh';
    $response = wp_remote_post($refresh_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ],
        'timeout' => 15,
    ]);
    if (is_wp_error($response)) {
        return false;
    }
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!empty($body['access_token'])) {
        update_user_meta($user_id, 'dgroots81_ose_jwt_token', $body['access_token']);
        return $body['access_token'];
    }
    return false;
}