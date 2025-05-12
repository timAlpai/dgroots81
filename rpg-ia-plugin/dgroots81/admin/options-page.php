<?php
// Sécurité : Bloquer l'accès direct
if (!defined('ABSPATH')) {
    exit;
}



// Ajout du menu d'administration
add_action('admin_menu', 'dgroots81_add_admin_menu');
function dgroots81_add_admin_menu() {
    add_menu_page(
        __('dgroots81 Plugin Options', 'dgroots81'),         // Page title
        __('dgroots81', 'dgroots81'),                 // Menu title
        'manage_options',            // Capacité requise
        'dgroots81-options',         // Slug du menu
        'dgroots81_options_page',    // Fonction d'affichage
        'dashicons-admin-generic'    // Icône
    );
    // Ajout du sous-menu "Gestion des utilisateurs"
    add_submenu_page(
        'dgroots81-options', // parent slug
        __('User management', 'dgroots81'), // page title
        __('User management', 'dgroots81'), // menu title
        'manage_options', // capability
        'dgroots81-user-management', // menu slug
        'dgroots81_user_management_page' // function
    );
}

//
// Handler AJAX pour sauvegarder le token admin côté WordPress
//
add_action('wp_ajax_dgroots81_save_admin_token', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Non autorisé.']);
    }
    $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
    if (empty($token)) {
        wp_send_json_error(['message' => 'Token manquant.']);
    }
    update_option('dgroots81_ose_server_admin_token', $token);
    wp_send_json_success(['message' => 'Token admin sauvegardé.']);
});

// Enqueue et localisation des chaînes JS pour la section Tests API
add_action('admin_enqueue_scripts', function($hook) {
    // On ne charge que sur la page d'options du plugin
    if ($hook !== 'toplevel_page_dgroots81-options') return;

    // Enregistre un script vide (sert juste à la localisation)
    wp_register_script('dgroots81-admin-api-tests', '', [], false, true);

    // Chaînes traduites pour JS
    $i18n = [
        'testing'        => __('Test en cours...', 'dgroots81'),
        'success_prefix' => __('Succès :', 'dgroots81'),
        'response_prefix'=> __('Réponse :', 'dgroots81'),
        'http_error'     => __('Erreur HTTP :', 'dgroots81'),
        'unexpected'     => __('Erreur inattendue :', 'dgroots81'),
        'network_error'  => __('Erreur réseau ou serveur AJAX inaccessible.', 'dgroots81')
    ];
    wp_localize_script('dgroots81-admin-api-tests', 'dgroots81ApiTestI18n', $i18n);

    // Ajoute le script (même vide) pour que la localisation soit disponible
    wp_enqueue_script('dgroots81-admin-api-tests');
});

// Affichage de la page d'options
function dgroots81_options_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('dgroots81 Plugin Options', 'dgroots81'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('dgroots81_options_group');
            do_settings_sections('dgroots81-options');
            submit_button();
            ?>
        </form>
    </div>

    <!-- Section Tests API -->
    <div class="dgroots81-api-tests" style="margin-top:40px;">
        <h2><?php _e('Tests API', 'dgroots81'); ?></h2>
        <button id="dgroots81-test-health" class="button button-secondary">
            <?php _e('Tester /health', 'dgroots81'); ?>
        </button>
        <div id="dgroots81-health-result" style="margin-top:15px; font-weight:bold;"></div>
    </div>

    <!-- Section Création du compte administrateur API -->
    <div class="dgroots81-api-admin-creation" style="margin-top:40px;">
        <h2><?php _e('API admin account creation', 'dgroots81'); ?></h2>
        <?php
        // Message de retour
        $dgroots81_api_admin_message = '';
        $dgroots81_api_admin_message_type = '';

        // Traitement du formulaire
        if (isset($_POST['dgroots81_api_admin_submit'])) {
            // Sécurité nonce
            if (!isset($_POST['dgroots81_api_admin_nonce']) || !wp_verify_nonce($_POST['dgroots81_api_admin_nonce'], 'dgroots81_api_admin_action')) {
                $dgroots81_api_admin_message = __('Security failed. Please try again.', 'dgroots81');
                $dgroots81_api_admin_message_type = 'error';
            } else {
                $username = sanitize_text_field($_POST['dgroots81_api_admin_username'] ?? '');
                $email = sanitize_email($_POST['dgroots81_api_admin_email'] ?? '');
                $password = $_POST['dgroots81_api_admin_password'] ?? '';

                if (empty($username) || empty($email) || empty($password)) {
                    $dgroots81_api_admin_message = __('Please fill in all fields.', 'dgroots81');
                    $dgroots81_api_admin_message_type = 'error';
                } else {
                    // Enregistrement des options
                    update_option('admin_api_username', $username);
                    update_option('admin_api_email', $email);
                    update_option('admin_api_password', $password);

                    // Préparation de la requête API
                    $api_base_url = get_option('dgroots81_ose_server_base_api_url', '');
                    if (!$api_base_url) {
                        $dgroots81_api_admin_message = __('API URL not configured.', 'dgroots81');
                        $dgroots81_api_admin_message_type = 'error';
                    } else {
                        $endpoint = rtrim($api_base_url, '/') . '/api/auth/register';
                        $body = json_encode([
                            'username' => $username,
                            'email' => $email,
                            'password' => $password,
                            'is_superuser' => true
                        ]);
                        $args = [
                            'headers' => [
                                'Content-Type' => 'application/json'
                            ],
                            'body' => $body,
                            'timeout' => 15
                        ];
                        $response = wp_remote_post($endpoint, $args);
                        if (is_wp_error($response)) {
                            $dgroots81_api_admin_message = __('API connection error: ', 'dgroots81') . esc_html($response->get_error_message());
                            $dgroots81_api_admin_message_type = 'error';
                        } else {
                            $code = wp_remote_retrieve_response_code($response);
                            $resp_body = wp_remote_retrieve_body($response);
                            $json = json_decode($resp_body, true);
                            if ($code >= 200 && $code < 300) {
                                $dgroots81_api_admin_message = __('API admin account successfully created/updated.', 'dgroots81');
                                $dgroots81_api_admin_message_type = 'success';
                            } else {
                                $detail = '';
                                if (is_array($json) && isset($json['detail'])) {
                                    $detail = $json['detail'];
                                } elseif (!empty($resp_body)) {
                                    $detail = $resp_body;
                                }
                                $dgroots81_api_admin_message = __('API error: ', 'dgroots81') . esc_html($detail);
                                $dgroots81_api_admin_message_type = 'error';
                            }
                        }
                    }
                }
            }
        }

        // Pré-remplissage des champs
        $admin_api_username = esc_attr(get_option('admin_api_username', ''));
        $admin_api_email = esc_attr(get_option('admin_api_email', ''));
        $admin_api_password = esc_attr(get_option('admin_api_password', ''));
        ?>

        <?php if (!empty($dgroots81_api_admin_message)): ?>
            <div class="notice notice-<?php echo ($dgroots81_api_admin_message_type === 'success') ? 'success' : 'error'; ?>" style="padding:12px; margin-bottom:16px;">
                <?php echo $dgroots81_api_admin_message; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('dgroots81_api_admin_action', 'dgroots81_api_admin_nonce'); ?>
            <table class="form-table" style="max-width:500px;">
                <tr>
                    <th scope="row"><label for="dgroots81-api-admin-username"><?php _e('Username', 'dgroots81'); ?></label></th>
                    <td><input type="text" id="dgroots81-api-admin-username" name="dgroots81_api_admin_username" class="regular-text" autocomplete="off" value="<?php echo $admin_api_username; ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="dgroots81-api-admin-email"><?php _e('Email', 'dgroots81'); ?></label></th>
                    <td><input type="email" id="dgroots81-api-admin-email" name="dgroots81_api_admin_email" class="regular-text" autocomplete="off" value="<?php echo $admin_api_email; ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="dgroots81-api-admin-password"><?php _e('Password', 'dgroots81'); ?></label></th>
                    <td><input type="password" id="dgroots81-api-admin-password" name="dgroots81_api_admin_password" class="regular-text" autocomplete="off" value="<?php echo $admin_api_password; ?>"></td>
                </tr>
            </table>
            <div class="dgroots81-api-admin-actions" style="display:flex; gap:12px; align-items:center; margin-top:16px;">
                <button type="submit" name="dgroots81_api_admin_submit" class="button button-primary"><?php _e('Create / Edit API admin', 'dgroots81'); ?></button>
                <button type="button" id="dgroots81-api-admin-login" class="button button-secondary primary"><?php _e('Login', 'dgroots81'); ?></button>
            </div>
            <div id="dgroots81-api-admin-login-result" style="margin-top:24px; padding:16px 20px 16px 12px; font-weight:bold; border:1px solid #ccd0d4; border-radius:4px; background:#f9f9f9; min-height:32px;"></div>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    var loginBtn = document.getElementById('dgroots81-api-admin-login');
                    var resultDiv = document.getElementById('dgroots81-api-admin-login-result');
                    if (loginBtn) {
                        loginBtn.addEventListener('click', function() {
                            resultDiv.textContent = '<?php echo esc_js(__('Logging in...', 'dgroots81')); ?>';
                            resultDiv.style.color = '';
                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', '<?php
                                $api_base_url = get_option('dgroots81_ose_server_base_api_url', '');
                                echo esc_url_raw(rtrim($api_base_url, '/')) . '/api/auth/token';
                            ?>', true);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === XMLHttpRequest.DONE) {
                                    var colorSuccess = '#2ecc40';
                                    var colorError = '#e74c3c';
                                    try {
                                        var resp = xhr.responseText;
                                        var json = JSON.parse(resp);
                                        if (xhr.status >= 200 && xhr.status < 300 && json.access_token && json.token_type) {
                                            var html = '<div class="notice notice-success" style="padding:16px 20px 16px 12px;margin:0;">';
                                            html += '<strong><?php echo esc_js(__('Success:', 'dgroots81')); ?></strong><br>';
                                            html += '<b>access_token</b>: <code>' + json.access_token + '</code><br>';
                                            html += '<b>token_type</b>: <code>' + json.token_type + '</code>';
                                            html += '</div>';
                                            resultDiv.innerHTML = html;
                                            resultDiv.style.color = '';
                                           // Sauvegarde automatique du token admin côté WP via AJAX
                                           var saveTokenXhr = new XMLHttpRequest();
                                           saveTokenXhr.open('POST', ajaxurl, true);
                                           saveTokenXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                                           saveTokenXhr.onreadystatechange = function() {
                                               if (saveTokenXhr.readyState === XMLHttpRequest.DONE) {
                                                   // Optionnel : afficher un message de succès/échec
                                                   // var resp = JSON.parse(saveTokenXhr.responseText);
                                               }
                                           };
                                           saveTokenXhr.send('action=dgroots81_save_admin_token&token=' + encodeURIComponent(json.access_token));
                                        } else if (json.detail) {
                                            var html = '<div class="notice notice-error" style="padding:16px 20px 16px 12px;margin:0;">';
                                            html += '<strong><?php echo esc_js(__('Error:', 'dgroots81')); ?></strong><br>';
                                            html += '<span>' + json.detail + '</span>';
                                            html += '</div>';
                                            resultDiv.innerHTML = html;
                                            resultDiv.style.color = colorError;
                                        } else {
                                            var html = '<div class="notice notice-error" style="padding:16px 20px 16px 12px;margin:0;">';
                                            html += '<strong><?php echo esc_js(__('Unexpected error', 'dgroots81')); ?></strong><br>';
                                            html += '<pre style="margin:0;">' + resp + '</pre>';
                                            html += '</div>';
                                            resultDiv.innerHTML = html;
                                            resultDiv.style.color = colorError;
                                        }
                                    } catch (e) {
                                        var html = '<div class="notice notice-error" style="padding:16px 20px 16px 12px;margin:0;">';
                                        html += '<strong><?php echo esc_js(__('Unexpected error', 'dgroots81')); ?></strong><br>';
                                        html += '<pre style="margin:0;">' + xhr.responseText + '</pre>';
                                        html += '</div>';
                                        resultDiv.innerHTML = html;
                                        resultDiv.style.color = colorError;
                                    }
                                }
                            };
                            xhr.onerror = function() {
                                resultDiv.innerHTML = '<span style="color:#e74c3c;"><?php echo esc_js(__('Network error or AJAX server unreachable.', 'dgroots81')); ?></span>';
                                resultDiv.style.color = '#e74c3c';
                            };
                            var params = 'username=' + encodeURIComponent('<?php echo esc_js($admin_api_username); ?>') +
                                         '&password=' + encodeURIComponent('<?php echo esc_js($admin_api_password); ?>');
                            xhr.send(params);
                        });
                    }
                });
            </script>
        </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var testBtn = document.getElementById('dgroots81-test-health');
            var resultDiv = document.getElementById('dgroots81-health-result');
            testBtn.addEventListener('click', function() {
                // Appel AJAX WordPress pour tester /health
                resultDiv.textContent = window.dgroots81ApiTestI18n.testing;
                resultDiv.style.color = '';
                var xhr = new XMLHttpRequest();
                xhr.open('POST', ajaxurl, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        var colorSuccess = '#2ecc40';
                        var colorError = '#e74c3c';
                        try {
                            if (xhr.status === 200) {
                                var resp = xhr.responseText;
                                var json;
                                try {
                                    json = JSON.parse(resp);

                                    // Fonction utilitaire pour styliser les booléens
                                    function renderBool(val) {
                                        if (val === true) {
                                            return '<span class="dashicons dashicons-yes" style="color: #46b450; vertical-align: middle;" title="true"></span> <span style="color:#46b450;font-weight:bold;">Oui</span>';
                                        } else if (val === false) {
                                            return '<span class="dashicons dashicons-no-alt" style="color: #dc3232; vertical-align: middle;" title="false"></span> <span style="color:#dc3232;font-weight:bold;">Non</span>';
                                        }
                                        return val;
                                    }

                                    // Fonction pour générer une table HTML structurée
                                    function renderTable(obj) {
                                        var html = '<table class="widefat striped" style="max-width:500px;">';
                                        html += '<tbody>';
                                        for (var key in obj) {
                                            if (!obj.hasOwnProperty(key)) continue;
                                            var label = key;
                                            // Utilisation des labels i18n si disponibles
                                            if (window.dgroots81ApiTestI18n[key]) {
                                                label = window.dgroots81ApiTestI18n[key];
                                            }
                                            var value = obj[key];
                                            if (typeof value === 'boolean') {
                                                value = renderBool(value);
                                            } else if (typeof value === 'object' && value !== null) {
                                                value = renderTable(value);
                                            } else {
                                                value = '<span>' + value + '</span>';
                                            }
                                            html += '<tr><th style="text-align:left;">' + label + '</th><td>' + value + '</td></tr>';
                                        }
                                        html += '</tbody></table>';
                                        return html;
                                    }

                                    // Bloc principal WordPress admin
                                    var noticeClass = 'notice notice-success';
                                    var html = '<div class="' + noticeClass + '" style="padding:16px 20px 16px 12px;margin:0;">';
                                    html += '<strong>' + window.dgroots81ApiTestI18n.response_prefix + '</strong><br>';

                                    // Si la structure attendue (success/data)
                                    if (typeof json === 'object' && json !== null && json.data) {
                                        html += renderTable(json.data);
                                    } else {
                                        html += renderTable(json);
                                    }
                                    html += '</div>';
                                    resultDiv.innerHTML = html;
                                    resultDiv.style.color = '';
                                } catch(e) {
                                    // Pas du JSON, afficher brut dans une notice d'erreur WordPress
                                    var html = '<div class="notice notice-error" style="padding:16px 20px 16px 12px;margin:0;">';
                                    html += '<strong>' + window.dgroots81ApiTestI18n.response_prefix + '</strong><br>';
                                    html += '<pre style="margin:0;">' + resp + '</pre>';
                                    html += '</div>';
                                    resultDiv.innerHTML = html;
                                    resultDiv.style.color = '';
                                }
                            } else {
                                var html = '<div class="notice notice-error" style="padding:16px 20px 16px 12px;margin:0;">';
                                html += '<strong>' + window.dgroots81ApiTestI18n.http_error + ' ' + xhr.status + ' – ' + xhr.statusText + '</strong>';
                                html += '</div>';
                                resultDiv.innerHTML = html;
                                resultDiv.style.color = '';
                            }
                        } catch (err) {
                            resultDiv.innerHTML = '<span style="color:' + colorError + ';">' +
                                window.dgroots81ApiTestI18n.unexpected + ' ' + err +
                                '</span>';
                            resultDiv.style.color = colorError;
                        }
                    }
                };
                xhr.onerror = function() {
                    resultDiv.innerHTML = '<span style="color:#e74c3c;">' +
                        window.dgroots81ApiTestI18n.network_error +
                        '</span>';
                    resultDiv.style.color = '#e74c3c';
                };
                xhr.send('action=test_api_health');
            });
        });
        </script>
    <?php
}

// Enregistrement des paramètres
add_action('admin_init', 'dgroots81_settings_init');
function dgroots81_settings_init() {

    add_settings_section(
        'dgroots81_section_main',
        __('Main Settings', 'dgroots81'),
        null,
        'dgroots81-options'
    );

    // Enregistrement de l’option
    register_setting(
        'dgroots81_options_group',
        'dgroots81_ose_server_base_api_url',
        array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => ''
        )
    );

    // Ajout du champ "OSE server base api url"
    add_settings_field(
        'dgroots81_ose_server_base_api_url',
        __('OSE server base api url', 'dgroots81'),
        'dgroots81_ose_server_base_api_url_render',
        'dgroots81-options',
        'dgroots81_section_main'
    );
}

// Fonction d’affichage du champ "OSE server base api url"
function dgroots81_ose_server_base_api_url_render() {
    $value = get_option('dgroots81_ose_server_base_api_url', '');
    ?>
    <input type="url"
           name="dgroots81_ose_server_base_api_url"
           value="<?php echo esc_attr($value); ?>"
           style="width: 400px;"
           placeholder="<?php echo esc_attr(__('https://api.example.com', 'dgroots81')); ?>"
    />
    <p class="description"><?php _e('OSE server base api url', 'dgroots81'); ?></p>
    <?php
}
