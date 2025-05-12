<?php
/*
Plugin Name: dgroots81
Description: plugin pour dgroots81(OSE).
Version: 0.1A
Author: Tim de Almeida alpai llp
*/

// Sécurité : Bloquer l'accès direct
if (!defined('ABSPATH')) {
    exit;
}
// Chargement du text domain pour l'internationalisation
add_action('plugins_loaded', function() {
    load_plugin_textdomain(
        'dgroots81',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
});
// Ajout d’un intervalle cron personnalisé (30 minutes)
add_filter('cron_schedules', function($schedules) {
    $schedules['dgroots81_every_15min'] = array(
        'interval' => 15 * 60, // 15 minutes en secondes
        'display'  => __('Toutes les 15 minutes', 'dgroots81')
    );
    return $schedules;
});

// Flush des permaliens à l’activation du plugin pour enregistrer l’endpoint WooCommerce
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// Chargement des fichiers admin si dans l'admin
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/options-page.php';
    require_once plugin_dir_path(__FILE__) . 'admin/user-management-page.php';

}

add_action('wp_ajax_supprimer_api_ose', 'dgroots81_handle_supprimer_api');

// Chargement des fichiers publics (WooCommerce)
// Chargement du dashboard WooCommerce
require_once plugin_dir_path(__FILE__) . 'public/woocommerce-dashboard.php';

// --- Authentification automatique et Cron pour le token admin API OSE ---

/**
 * Tente de rafraîchir le token admin OSE si absent ou invalide.
 * Appelée à chaque chargement du plugin et par le cron.
 */
function dgroots81_refresh_admin_api_token() {
    $now = date('Y-m-d H:i:s');
    error_log("[$now] [dgroots81] Entrée dans dgroots81_refresh_admin_api_token");

    $api_base_url = get_option('dgroots81_ose_server_base_api_url', '');
    $username = get_option('admin_api_username', '');
    $password = get_option('admin_api_password', '');

    if (!$api_base_url || !$username || !$password) {
        error_log("[$now] [dgroots81] Config incomplète : base_url=[$api_base_url], username=[$username], password=" . ($password ? '[OK]' : '[VIDE]'));
        return; // Config incomplète
    }

    // Vérifier si le token existe déjà et s'il est expiré
    $token = get_option('dgroots81_ose_server_admin_token', '');
    $token_expiry = get_option('dgroots81_ose_server_admin_token_expiry', 0);
    $current_time = time();

    if (!empty($token) && strlen($token) >= 16 && $token_expiry > $current_time) {
        $expire_str = date('Y-m-d H:i:s', $token_expiry);
        error_log("[$now] [dgroots81] Token déjà présent, semble valide et non expiré (".strlen($token)." caractères, expire à $expire_str). Pas de refresh nécessaire.");
        return;
    } elseif (!empty($token) && strlen($token) >= 16 && $token_expiry <= $current_time) {
        $expire_str = date('Y-m-d H:i:s', $token_expiry);
        error_log("[$now] [dgroots81] Token présent mais expiré (expire à $expire_str, maintenant $now). Rafraîchissement nécessaire.");
    } else {
        error_log("[$now] [dgroots81] Token absent ou trop court (".strlen($token)." caractères). Tentative de récupération d'un nouveau token...");
    }

    $endpoint = rtrim($api_base_url, '/') . '/api/auth/token';
    $body = http_build_query([
        'username' => $username,
        'password' => $password
    ]);
    $args = [
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ],
        'body' => $body,
        'timeout' => 15
    ];
    $response = wp_remote_post($endpoint, $args);

    if (is_wp_error($response)) {
        error_log("[$now] [dgroots81] Erreur lors de l'appel API : " . $response->get_error_message());
        return;
    }

    $code = wp_remote_retrieve_response_code($response);
    $resp_body = wp_remote_retrieve_body($response);
    error_log("[$now] [dgroots81] Réponse API code=$code, body=$resp_body");

    $json = json_decode($resp_body, true);
    if ($code >= 200 && $code < 300 && !empty($json['access_token'])) {
        update_option('dgroots81_ose_server_admin_token', $json['access_token']);
        // Gestion du TTL/expiration
        $ttl_api = 3600; // Par défaut 1h si non précisé
        if (!empty($json['expires_in'])) {
            $ttl_api = intval($json['expires_in']);
        } elseif (!empty($json['ttl'])) {
            $ttl_api = intval($json['ttl']);
        }
        $ttl = min($ttl_api, 1800); // Jamais plus de 30min
        $expiry = time() + $ttl - 30; // marge de sécurité de 30s
        update_option('dgroots81_ose_server_admin_token_expiry', $expiry);
        $expire_str = date('Y-m-d H:i:s', $expiry);
        error_log("[$now] [dgroots81] Nouveau token reçu et enregistré (" . strlen($json['access_token']) . " caractères, expire à $expire_str, TTL réel=$ttl_api s, TTL appliqué=$ttl s).");
    } else {
        error_log("[$now] [dgroots81] Échec récupération token : code=$code, access_token=" . (isset($json['access_token']) ? '[PRÉSENT]' : '[ABSENT]'));
    }
}

// Hook : à chaque chargement du plugin (admin et public)
add_action('plugins_loaded', 'dgroots81_refresh_admin_api_token');

// Cron WordPress : vérification régulière du token admin
/* Nettoyage de l’ancien cron hourly si existant */
$timestamp = wp_next_scheduled('dgroots81_cron_check_admin_token');
if ($timestamp) {
    $cron = _get_cron_array();
    if (isset($cron[$timestamp]['dgroots81_cron_check_admin_token'])) {
        // Si l’ancien cron est en hourly, on le déprogramme
        $event = $cron[$timestamp]['dgroots81_cron_check_admin_token'][0] ?? [];
        if (isset($event['schedule']) && $event['schedule'] === 'hourly') {
            wp_clear_scheduled_hook('dgroots81_cron_check_admin_token');
        }
    }
}
// Programmation sur l’intervalle personnalisé (30min)
/* Nettoyage de l’ancien cron 30min si existant */
$timestamp = wp_next_scheduled('dgroots81_cron_check_admin_token');
if ($timestamp) {
    $cron = _get_cron_array();
    if (isset($cron[$timestamp]['dgroots81_cron_check_admin_token'])) {
        $event = $cron[$timestamp]['dgroots81_cron_check_admin_token'][0] ?? [];
        if (isset($event['schedule']) && $event['schedule'] === 'dgroots81_every_30min') {
            wp_clear_scheduled_hook('dgroots81_cron_check_admin_token');
        }
    }
}
// Programmation sur l’intervalle personnalisé (15min)
if (!wp_next_scheduled('dgroots81_cron_check_admin_token')) {
    wp_schedule_event(time(), 'dgroots81_every_15min', 'dgroots81_cron_check_admin_token');
}
add_action('dgroots81_cron_check_admin_token', 'dgroots81_refresh_admin_api_token');
// Ajout de la classe body "dgroots81-user-profile" uniquement sur la page profil utilisateur (endpoint WooCommerce dgroots81)
add_filter('body_class', function($classes) {
    if (function_exists('is_account_page') && function_exists('is_wc_endpoint_url')) {
        if (is_account_page() && is_wc_endpoint_url('dgroots81')) {
            $classes[] = 'dgroots81-user-profile';
        }
// Ajout des menus admin du plugin
add_action('admin_menu', 'dgroots81_add_admin_menu');

// Inclusion du JS admin pour la gestion des utilisateurs



    }
    return $classes;
});


// Endpoint AJAX pour tester /health de l’API distante
add_action('wp_ajax_test_api_health', function() {
    // Lire la baseurl depuis les options
    $baseurl = get_option('dgroots81_ose_server_base_api_url', '');
    if (empty($baseurl)) {
        wp_send_json_error(['error' => 'API base URL non configurée.'], 400);
    }

    // Construire l’URL complète
    $url = rtrim($baseurl, '/') . '/health';

    // Appel HTTP GET
    $response = wp_remote_get($url, [
        'timeout' => 10,
        'headers' => [
            'Accept' => 'application/json'
        ]
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['error' => $response->get_error_message()], 502);
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    // Retourne la réponse brute de l’API, ou une erreur si le code n’est pas 2xx
    if ($code >= 200 && $code < 300) {
        // Tente de décoder la réponse JSON, sinon renvoie brute
        $data = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            wp_send_json_success($data, $code);
        } else {
            wp_send_json_success(['raw' => $body], $code);
        }
    } else {
        wp_send_json_error([
            'error' => 'Erreur API distante',
            'status' => $code,
            'raw' => $body
        ], $code);
    }
});

// Action AJAX pour modifier le nom d'utilisateur et synchroniser avec OSE
add_action('wp_ajax_dgroots81_update_username', function() {
    // 1. Vérification du nonce et de l'authentification
    if (!is_user_logged_in()) {
        wp_send_json_error(['error' => __('Vous devez être connecté.', 'dgroots81')], 401);
    }
    // Nonce : le nom attendu doit correspondre à celui utilisé côté JS
    $nonce_action = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';
    if (!$nonce_action || !wp_verify_nonce($nonce_action, 'dgroots81_update_username')) {
        wp_send_json_error(['error' => __('Nonce invalide.', 'dgroots81')], 403);
    }

    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    if (!$user) {
        wp_send_json_error(['error' => __('Utilisateur introuvable.', 'dgroots81')], 404);
    }

    // Vérification des droits (modifier son profil)
    if (!current_user_can('edit_user', $user_id)) {
        wp_send_json_error(['error' => __('Vous n\'avez pas la permission de modifier ce profil.', 'dgroots81')], 403);
    }

    // 2. Récupération de la nouvelle valeur
    $new_username = isset($_POST['new_username']) ? sanitize_user($_POST['new_username']) : '';
    if (empty($new_username)) {
        wp_send_json_error(['error' => __('Nouveau nom d\'utilisateur manquant.', 'dgroots81')], 400);
    }

    // 3. Mise à jour du profil WordPress (display_name et user_nicename)
    $update_data = [
        'ID' => $user_id,
        'display_name' => $new_username,
        'user_nicename' => sanitize_title($new_username),
    ];
    $wp_result = wp_update_user($update_data);

    if (is_wp_error($wp_result)) {
        wp_send_json_error(['error' => $wp_result->get_error_message()], 500);
    }

    // 4. Synchronisation avec l'API OSE
    $baseurl = get_option('dgroots81_ose_server_base_api_url', '');
    if (empty($baseurl)) {
        wp_send_json_error(['error' => __('URL de l\'API OSE non configurée.', 'dgroots81')], 500);
    }
    // Récupérer l'id OSE de l'utilisateur courant (stocké en user_meta ou via /api/auth/me)
    $ose_user_id = get_user_meta($user_id, 'dgroots81_ose_user_id', true);
    $ose_jwt = get_user_meta($user_id, 'dgroots81_ose_jwt_token', true);

    if (empty($ose_user_id)) {
        // Appel /api/auth/me pour récupérer l'id OSE
        $me_url = rtrim($baseurl, '/') . '/api/auth/me';
        $me_headers = [
            'Authorization' => 'Bearer ' . $ose_jwt,
            'Accept' => 'application/json',
        ];
        $me_response = wp_remote_get($me_url, [
            'headers' => $me_headers,
            'timeout' => 10,
        ]);
        if (is_wp_error($me_response)) {
            wp_send_json_error(['error' => __('Impossible d\'obtenir l\'ID OSE via /api/auth/me : ', 'dgroots81') . $me_response->get_error_message()], 502);
        }
        $me_code = wp_remote_retrieve_response_code($me_response);
        $me_body = json_decode(wp_remote_retrieve_body($me_response), true);
        if ($me_code !== 200 || empty($me_body['id'])) {
            wp_send_json_error(['error' => __('Impossible d\'obtenir l\'ID OSE via /api/auth/me.', 'dgroots81'), 'ose_response' => $me_body], 502);
        }
        $ose_user_id = intval($me_body['id']);
        update_user_meta($user_id, 'dgroots81_ose_user_id', $ose_user_id);
    }
    $ose_url = rtrim($baseurl, '/') . '/api/users/' . intval($ose_user_id);

    // Préparation du body JSON selon openapi.json (UserUpdate)
    $body = json_encode([
        'username' => $new_username
    ]);
    $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];

    // Si un token JWT OSE est stocké dans les options, l'ajouter
    // Utiliser le token JWT stocké pour l'utilisateur courant (user_meta)
    $ose_jwt = get_user_meta($user_id, 'dgroots81_ose_jwt_token', true);
    if (!empty($ose_jwt)) {
        $headers['Authorization'] = 'Bearer ' . $ose_jwt;
    }

    $response = wp_remote_request($ose_url, [
        'method' => 'PUT',
        'timeout' => 10,
        'headers' => $headers,
        'body' => $body,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['error' => __('Erreur lors de la synchronisation OSE : ', 'dgroots81') . $response->get_error_message()], 502);
    }

    $code = wp_remote_retrieve_response_code($response);

    // DEBUG : Ajouter l'URL et la réponse brute dans la réponse AJAX pour faciliter le debug
    if ($code !== 200) {
        wp_send_json_error([
            'error' => __('Erreur lors de la synchronisation OSE.', 'dgroots81'),
            'status' => $code,
            'ose_url' => $ose_url,
            'ose_body' => $body,
            'ose_response' => wp_remote_retrieve_body($response),
            'ose_headers' => wp_remote_retrieve_headers($response),
        ], $code);
    }
    $resp_body = wp_remote_retrieve_body($response);

    if ($code >= 200 && $code < 300) {
        wp_send_json_success(['message' => __('Nom d\'utilisateur mis à jour et synchronisé.', 'dgroots81')]);
    } else {
        // Optionnel : rollback WP si la synchro échoue ?
        wp_send_json_error([
            'error' => __('Erreur lors de la synchronisation OSE.', 'dgroots81'),
            'status' => $code,
            'raw' => $resp_body
        ], $code);
    }
});