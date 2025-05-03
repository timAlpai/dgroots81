<?php
/**
 * Classe responsable de la gestion de l'authentification
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Classe responsable de la gestion de l'authentification.
 *
 * Cette classe gère l'authentification des utilisateurs avec l'API backend.
 *
 * @since      1.0.0
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_Auth_Handler {

    /**
     * Le client API.
     *
     * @since    1.0.0
     * @access   private
     * @var      RPG_IA_API_Client    $api_client    Le client API.
     */
    private $api_client;

    /**
     * Initialise la classe et définit ses propriétés.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->api_client = new RPG_IA_API_Client();
    }

    /**
     * Authentifie un utilisateur avec l'API backend.
     *
     * @since    1.0.0
     * @param    string    $username       Le nom d'utilisateur.
     * @param    string    $password       Le mot de passe.
     * @param    bool      $refresh_token  Indique s'il s'agit d'un rafraîchissement de token.
     * @return   array|WP_Error            Les données d'authentification ou une erreur.
     */
    public function authenticate($username, $password, $refresh_token = false) {
        if ($refresh_token) {
            // Pour le rafraîchissement de token, on utilise une méthode spéciale
            $response = $this->api_client->refresh_token($username);
        } else {
            // Authentification normale avec mot de passe
            $response = $this->api_client->login($username, $password);
        }
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Stocker le token dans un cookie sécurisé
        $this->set_token_cookie($response['access_token']);
        
        return $response;
    }

    /**
     * Enregistre un nouvel utilisateur.
     *
     * @since    1.0.0
     * @param    string    $username    Le nom d'utilisateur.
     * @param    string    $email       L'adresse e-mail.
     * @param    string    $password    Le mot de passe.
     * @return   array|WP_Error         Les données de l'utilisateur ou une erreur.
     */
    /**
     * Enregistre un nouvel utilisateur API.
     *
     * @since    1.0.0
     * @param    string    $username    Le nom d'utilisateur.
     * @param    string    $email       L'adresse e-mail.
     * @param    string    $password    Le mot de passe.
     * @return   array|WP_Error         Les données de l'utilisateur ou une erreur.
     */
    public function register($username, $email, $password) {
        // Vérifier si l'utilisateur WordPress courant a déjà un compte API
        if ($this->has_api_account() && !current_user_can('administrator')) {
            return new WP_Error('account_exists', __('You already have an API account. Each WordPress user can only have one API account.', 'rpg-ia'));
        }
        
        // Valider les entrées utilisateur
        $username = sanitize_user($username);
        $email = sanitize_email($email);
        
        if (empty($username) || empty($email) || empty($password)) {
            return new WP_Error('invalid_input', __('Username, email and password are required.', 'rpg-ia'));
        }
        
        // Vérifier la force du mot de passe
        if (strlen($password) < 8) {
            return new WP_Error('weak_password', __('Password must be at least 8 characters long.', 'rpg-ia'));
        }
        
        // Vérifier si l'utilisateur existe déjà dans l'API
        $user_exists = $this->api_client->check_user_exists($username);
        
        // Si la vérification a renvoyé une erreur ou null, on continue avec la création
        // car l'API vérifiera à nouveau lors de la création
        if ($user_exists === true) {
            return new WP_Error('api_username_exists', __('This username already exists in the API. Please choose another username.', 'rpg-ia'));
        }
        
        // Enregistrer l'utilisateur via l'API
        $response = $this->api_client->register($username, $email, $password);
        
        if (is_wp_error($response)) {
            // Vérifier si l'erreur est due à un utilisateur existant
            $error_message = $response->get_error_message();
            if (strpos($error_message, 'already exists') !== false) {
                return new WP_Error('api_username_exists', __('This username already exists in the API. Please choose another username.', 'rpg-ia'));
            }
            return $response;
        }
        
        // Associer le compte API à l'utilisateur WordPress
        $this->associate_api_account($username);
        
        return $response;
    }

    /**
     * Déconnecte l'utilisateur.
     *
     * @since    1.0.0
     */
    public function logout() {
        // Supprimer le cookie de token
        $this->clear_token_cookie();
    }

    /**
     * Récupère les informations de l'utilisateur courant.
     *
     * @since    1.0.0
     * @return   array|WP_Error    Les informations de l'utilisateur ou une erreur.
     */
    public function get_current_user() {
        $token = $this->get_token();
        
        if (!$token) {
            return new WP_Error('no_token', __('No authentication token found.', 'rpg-ia'));
        }
        
        $this->api_client->set_token($token);
        
        return $this->api_client->get_current_user();
    }

    /**
     * Vérifie si l'utilisateur est authentifié.
     *
     * @since    1.0.0
     * @return   bool    True si l'utilisateur est authentifié, false sinon.
     */
    public function is_authenticated() {
        $token = $this->get_token();
        
        if (!$token) {
            return false;
        }
        
        // Vérifier si le token est valide
        return $this->validate_token($token);
    }

    /**
     * Vérifie si l'utilisateur est authentifié auprès de l'API et a un token valide.
     * Cette méthode vérifie à la fois l'authentification WordPress et l'authentification API.
     *
     * @since    1.0.0
     * @return   bool    True si l'utilisateur est authentifié auprès de l'API, false sinon.
     */
    public function is_api_authenticated() {
        // Vérifier d'abord si l'utilisateur est connecté à WordPress
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Ensuite, vérifier si l'utilisateur est authentifié auprès de l'API
        return $this->is_authenticated();
    }

    /**
     * Valide un token JWT.
     *
     * @since    1.0.0
     * @param    string    $token    Le token JWT à valider.
     * @return   bool                True si le token est valide, false sinon.
     */
    public function validate_token($token) {
        // Extraire le token du header Authorization si nécessaire
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        // Vérifier si le token est vide
        if (empty($token)) {
            return false;
        }
        
        // Vérifier si le token est expiré
        $token_parts = explode('.', $token);
        
        if (count($token_parts) !== 3) {
            return false;
        }
        
        $payload = json_decode(base64_decode(str_replace(
            array('-', '_'),
            array('+', '/'),
            $token_parts[1]
        )), true);
        
        if (!$payload || !isset($payload['exp'])) {
            return false;
        }
        
        // Vérifier si le token est expiré
        if ($payload['exp'] < time()) {
            return false;
        }
        
        return true;
    }

    /**
     * Récupère le token JWT.
     *
     * @since    1.0.0
     * @return   string|null    Le token JWT ou null s'il n'existe pas.
     */
    public function get_token() {
        // Essayer de récupérer le token depuis le cookie
        if (isset($_COOKIE['rpg_ia_token'])) {
            return $_COOKIE['rpg_ia_token'];
        }
        
        return null;
    }

    /**
     * Stocke le token JWT dans un cookie sécurisé.
     *
     * @since    1.0.0
     * @param    string    $token    Le token JWT.
     */
    private function set_token_cookie($token) {
        $secure = is_ssl();
        $httponly = true;
        $samesite = 'Strict';
        
        // Calculer la durée de validité du token
        $token_parts = explode('.', $token);
        $expiration = 0;
        
        if (count($token_parts) === 3) {
            $payload = json_decode(base64_decode(str_replace(
                array('-', '_'),
                array('+', '/'),
                $token_parts[1]
            )), true);
            
            if ($payload && isset($payload['exp'])) {
                $expiration = $payload['exp'] - time();
            }
        }
        
        // Si l'expiration n'a pas pu être déterminée, utiliser une valeur par défaut (1 jour)
        if ($expiration <= 0) {
            $expiration = 86400;
        }
        
        // Définir le cookie
        setcookie(
            'rpg_ia_token',
            $token,
            [
                'expires' => time() + $expiration,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ]
        );
    }

    /**
     * Supprime le cookie de token.
     *
     * @since    1.0.0
     */
    private function clear_token_cookie() {
        // Supprimer le cookie en définissant une date d'expiration dans le passé
        setcookie(
            'rpg_ia_token',
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => is_ssl(),
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    /**
     * Vérifie si l'utilisateur WordPress courant a déjà un compte API associé.
     *
     * @return bool True si un compte API existe, false sinon.
     */
    public function has_api_account() {
        // Récupérer l'ID de l'utilisateur WordPress courant
        $wp_user_id = get_current_user_id();
        
        // Vérifier si un compte API est déjà associé à cet utilisateur WordPress
        $api_username = get_user_meta($wp_user_id, 'rpg_ia_api_username', true);
        
        return !empty($api_username);
    }

    /**
     * Associe un compte API à l'utilisateur WordPress courant.
     *
     * @param string $api_username Le nom d'utilisateur du compte API.
     * @return bool True si l'association a réussi, false sinon.
     */
    public function associate_api_account($api_username) {
        // Récupérer l'ID de l'utilisateur WordPress courant
        $wp_user_id = get_current_user_id();
        
        // Vérifier si l'utilisateur a déjà un compte API associé
        if ($this->has_api_account() && !current_user_can('administrator')) {
            return false; // Un utilisateur non-admin ne peut avoir qu'un seul compte API
        }
        
        // Associer le compte API à l'utilisateur WordPress
        update_user_meta($wp_user_id, 'rpg_ia_api_username', sanitize_text_field($api_username));
        
        return true;
    }
}