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

// Flush des permaliens à l’activation du plugin pour enregistrer l’endpoint WooCommerce
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// Chargement des fichiers admin si dans l'admin
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/options-page.php';
    require_once plugin_dir_path(__FILE__) . 'admin/user-management-page.php';

}

// Chargement des fichiers publics (WooCommerce)
require_once plugin_dir_path(__FILE__) . 'public/woocommerce-dashboard.php';
// Ajout de la classe body "dgroots81-user-profile" uniquement sur la page profil utilisateur (endpoint WooCommerce dgroots81)
add_filter('body_class', function($classes) {
    if (function_exists('is_account_page') && function_exists('is_wc_endpoint_url')) {
        if (is_account_page() && is_wc_endpoint_url('dgroots81')) {
            $classes[] = 'dgroots81-user-profile';
        }
// Ajout des menus admin du plugin
add_action('admin_menu', 'dgroots81_add_admin_menu');
// Hook AJAX admin pour suppression utilisateur via OSE

    }
    return $classes;
});
add_action('wp_ajax_supprimer_api_ose', 'dgroots81_handle_supprimer_api');
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