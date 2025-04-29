<?php
/**
 * Classe responsable de la communication avec l'API backend
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Classe responsable de la communication avec l'API backend.
 *
 * Cette classe gère toutes les requêtes vers l'API backend FastAPI.
 *
 * @since      1.0.0
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_API_Client {

    /**
     * L'URL de base de l'API.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_base_url    L'URL de base de l'API.
     */
    private $api_base_url;

    /**
     * Le token JWT pour l'authentification.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $token    Le token JWT pour l'authentification.
     */
    private $token;

    /**
     * Initialise la classe et définit ses propriétés.
     *
     * @since    1.0.0
     * @param    string    $api_base_url    L'URL de base de l'API.
     * @param    string    $token           Le token JWT pour l'authentification (optionnel).
     */
    public function __construct($api_base_url = null, $token = null) {
        $this->api_base_url = $api_base_url ?: get_option('rpg_ia_api_url', 'http://localhost:8000');
        $this->token = $token;
    }

    /**
     * Définit le token JWT.
     *
     * @since    1.0.0
     * @param    string    $token    Le token JWT.
     */
    public function set_token($token) {
        $this->token = $token;
    }

    /**
     * Effectue une requête vers l'API.
     *
     * @since    1.0.0
     * @param    string    $endpoint    L'endpoint de l'API.
     * @param    string    $method      La méthode HTTP (GET, POST, PUT, DELETE).
     * @param    array     $data        Les données à envoyer (optionnel).
     * @param    array     $headers     Les en-têtes supplémentaires (optionnel).
     * @return   array|WP_Error         La réponse de l'API ou une erreur.
     */
    public function request($endpoint, $method = 'GET', $data = null, $headers = array()) {
        // Construire l'URL complète
        $url = rtrim($this->api_base_url, '/') . '/' . ltrim($endpoint, '/');
        
        // Préparer les arguments de la requête
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'cookies' => array()
        );
        
        // Ajouter le token d'authentification si disponible
        if ($this->token) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->token;
        }
        
        // Ajouter les en-têtes supplémentaires
        if (!empty($headers)) {
            $args['headers'] = array_merge($args['headers'], $headers);
        }
        
        // Ajouter les données si nécessaire
        if ($data !== null) {
            $args['body'] = json_encode($data);
        }
        
        // Effectuer la requête
        $response = wp_remote_request($url, $args);
        
        // Vérifier s'il y a une erreur
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Récupérer le code de statut
        $status_code = wp_remote_retrieve_response_code($response);
        
        // Récupérer le corps de la réponse
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Vérifier si la réponse est un succès
        if ($status_code >= 200 && $status_code < 300) {
            return $data;
        } else {
            // Créer une erreur avec les détails de la réponse
            $error_message = isset($data['detail']) ? $data['detail'] : 'Unknown error';
            return new WP_Error($status_code, $error_message, $data);
        }
    }

    /**
     * Obtient un token JWT en échange d'identifiants.
     *
     * @since    1.0.0
     * @param    string    $username    Le nom d'utilisateur.
     * @param    string    $password    Le mot de passe.
     * @return   array|WP_Error         Le token JWT ou une erreur.
     */
    public function login($username, $password) {
        $data = array(
            'username' => $username,
            'password' => $password
        );
        
        $response = $this->request('api/auth/token', 'POST', $data);
        
        if (!is_wp_error($response) && isset($response['access_token'])) {
            $this->token = $response['access_token'];
        }
        
        return $response;
    }
    
    /**
     * Rafraîchit un token JWT.
     *
     * @since    1.0.0
     * @param    string    $username    Le nom d'utilisateur.
     * @return   array|WP_Error         Le nouveau token JWT ou une erreur.
     */
    public function refresh_token($username) {
        // Vérifier si un token est disponible
        if (!$this->token) {
            return new WP_Error('no_token', __('No token available for refresh.', 'rpg-ia'));
        }
        
        // Appeler l'API pour rafraîchir le token
        $response = $this->request('api/auth/refresh', 'POST', array('username' => $username));
        
        if (!is_wp_error($response) && isset($response['access_token'])) {
            $this->token = $response['access_token'];
        }
        
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
    public function register($username, $email, $password) {
        $data = array(
            'username' => $username,
            'email' => $email,
            'password' => $password
        );
        
        return $this->request('api/auth/register', 'POST', $data);
    }

    /**
     * Récupère les informations de l'utilisateur courant.
     *
     * @since    1.0.0
     * @return   array|WP_Error    Les informations de l'utilisateur ou une erreur.
     */
    public function get_current_user() {
        return $this->request('api/auth/me', 'GET');
    }

    /**
     * Récupère la liste des personnages.
     *
     * @since    1.0.0
     * @return   array|WP_Error    La liste des personnages ou une erreur.
     */
    public function get_characters() {
        return $this->request('api/characters/', 'GET');
    }

    /**
     * Récupère un personnage spécifique.
     *
     * @since    1.0.0
     * @param    int       $character_id    L'ID du personnage.
     * @return   array|WP_Error             Les données du personnage ou une erreur.
     */
    public function get_character($character_id) {
        return $this->request('api/characters/' . $character_id, 'GET');
    }

    /**
     * Crée un nouveau personnage.
     *
     * @since    1.0.0
     * @param    array     $data    Les données du personnage.
     * @return   array|WP_Error     Les données du personnage créé ou une erreur.
     */
    public function create_character($data) {
        return $this->request('api/characters/', 'POST', $data);
    }

    /**
     * Met à jour un personnage existant.
     *
     * @since    1.0.0
     * @param    int       $character_id    L'ID du personnage.
     * @param    array     $data            Les données du personnage.
     * @return   array|WP_Error             Les données du personnage mis à jour ou une erreur.
     */
    public function update_character($character_id, $data) {
        return $this->request('api/characters/' . $character_id, 'PUT', $data);
    }

    /**
     * Supprime un personnage.
     *
     * @since    1.0.0
     * @param    int       $character_id    L'ID du personnage.
     * @return   array|WP_Error             La réponse de l'API ou une erreur.
     */
    public function delete_character($character_id) {
        return $this->request('api/characters/' . $character_id, 'DELETE');
    }

    /**
     * Récupère la liste des sessions de jeu.
     *
     * @since    1.0.0
     * @return   array|WP_Error    La liste des sessions ou une erreur.
     */
    public function get_game_sessions() {
        return $this->request('api/game-sessions/', 'GET');
    }

    /**
     * Récupère une session de jeu spécifique.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @return   array|WP_Error           Les données de la session ou une erreur.
     */
    public function get_game_session($session_id) {
        return $this->request('api/game-sessions/' . $session_id, 'GET');
    }

    /**
     * Crée une nouvelle session de jeu.
     *
     * @since    1.0.0
     * @param    array     $data    Les données de la session.
     * @return   array|WP_Error     Les données de la session créée ou une erreur.
     */
    public function create_game_session($data) {
        return $this->request('api/game-sessions/', 'POST', $data);
    }

    /**
     * Met à jour une session de jeu existante.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @param    array     $data          Les données de la session.
     * @return   array|WP_Error           Les données de la session mise à jour ou une erreur.
     */
    public function update_game_session($session_id, $data) {
        return $this->request('api/game-sessions/' . $session_id, 'PUT', $data);
    }

    /**
     * Supprime une session de jeu.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @return   array|WP_Error           La réponse de l'API ou une erreur.
     */
    public function delete_game_session($session_id) {
        return $this->request('api/game-sessions/' . $session_id, 'DELETE');
    }

    /**
     * Récupère les actions d'une session de jeu.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @param    int       $timestamp     Le timestamp à partir duquel récupérer les actions (optionnel).
     * @return   array|WP_Error           La liste des actions ou une erreur.
     */
    public function get_session_actions($session_id, $timestamp = null) {
        $endpoint = 'api/game-sessions/' . $session_id . '/actions';
        
        if ($timestamp !== null) {
            $endpoint .= '?timestamp=' . $timestamp;
        }
        
        return $this->request($endpoint, 'GET');
    }

    /**
     * Crée une nouvelle action.
     *
     * @since    1.0.0
     * @param    array     $data    Les données de l'action.
     * @return   array|WP_Error     Les données de l'action créée ou une erreur.
     */
    public function create_action($data) {
        return $this->request('api/actions/', 'POST', $data);
    }

    /**
     * Vérifie la connexion à l'API.
     *
     * @since    1.0.0
     * @return   bool|WP_Error    True si la connexion est établie, une erreur sinon.
     */
    public function check_connection() {
        $response = $this->request('api/health', 'GET');
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return true;
    }
    /**
     * Récupère la liste des scénarios.
     *
     * @since    1.0.0
     * @return   array|WP_Error    La liste des scénarios ou une erreur.
     */
    public function get_scenarios() {
        return $this->request('api/scenarios/', 'GET');
    }

    /**
     * Récupère un scénario spécifique.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @return   array|WP_Error            Les données du scénario ou une erreur.
     */
    public function get_scenario($scenario_id) {
        return $this->request('api/scenarios/' . $scenario_id, 'GET');
    }

    /**
     * Crée un nouveau scénario.
     *
     * @since    1.0.0
     * @param    array     $data    Les données du scénario.
     * @return   array|WP_Error     Les données du scénario créé ou une erreur.
     */
    public function create_scenario($data) {
        return $this->request('api/scenarios/', 'POST', $data);
    }

    /**
     * Met à jour un scénario existant.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @param    array     $data           Les données du scénario.
     * @return   array|WP_Error            Les données du scénario mis à jour ou une erreur.
     */
    public function update_scenario($scenario_id, $data) {
        return $this->request('api/scenarios/' . $scenario_id, 'PUT', $data);
    }

    /**
     * Supprime un scénario.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @return   array|WP_Error            La réponse de l'API ou une erreur.
     */
    public function delete_scenario($scenario_id) {
        return $this->request('api/scenarios/' . $scenario_id, 'DELETE');
    }

    /**
     * Récupère les scènes d'un scénario.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @return   array|WP_Error            La liste des scènes ou une erreur.
     */
    public function get_scenario_scenes($scenario_id) {
        return $this->request('api/scenarios/' . $scenario_id . '/scenes', 'GET');
    }

    /**
     * Récupère une scène spécifique d'un scénario.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @param    int       $scene_id       L'ID de la scène.
     * @return   array|WP_Error            Les données de la scène ou une erreur.
     */
    public function get_scenario_scene($scenario_id, $scene_id) {
        return $this->request('api/scenarios/' . $scenario_id . '/scenes/' . $scene_id, 'GET');
    }

    /**
     * Crée une nouvelle scène dans un scénario.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @param    array     $data           Les données de la scène.
     * @return   array|WP_Error            Les données de la scène créée ou une erreur.
     */
    public function create_scenario_scene($scenario_id, $data) {
        return $this->request('api/scenarios/' . $scenario_id . '/scenes', 'POST', $data);
    }

    /**
     * Met à jour une scène existante dans un scénario.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @param    int       $scene_id       L'ID de la scène.
     * @param    array     $data           Les données de la scène.
     * @return   array|WP_Error            Les données de la scène mise à jour ou une erreur.
     */
    public function update_scenario_scene($scenario_id, $scene_id, $data) {
        return $this->request('api/scenarios/' . $scenario_id . '/scenes/' . $scene_id, 'PUT', $data);
    }

    /**
     * Supprime une scène d'un scénario.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @param    int       $scene_id       L'ID de la scène.
     * @return   array|WP_Error            La réponse de l'API ou une erreur.
     */
    public function delete_scenario_scene($scenario_id, $scene_id) {
        return $this->request('api/scenarios/' . $scenario_id . '/scenes/' . $scene_id, 'DELETE');
    }

    /**
     * Modifie le statut d'une session de jeu.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @param    string    $status        Le nouveau statut (active, paused, completed).
     * @return   array|WP_Error           La réponse de l'API ou une erreur.
     */
    public function update_session_status($session_id, $status) {
        $data = array(
            'status' => $status
        );
        
        return $this->request('api/game-sessions/' . $session_id . '/status', 'PUT', $data);
    }

    /**
     * Intervient dans la narration d'une session de jeu.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @param    string    $content       Le contenu de l'intervention.
     * @param    string    $type          Le type d'intervention (narration, dialog, event).
     * @return   array|WP_Error           La réponse de l'API ou une erreur.
     */
    public function override_session_narration($session_id, $content, $type = 'narration') {
        $data = array(
            'content' => $content,
            'type' => $type
        );
        
        return $this->request('api/game-sessions/' . $session_id . '/override', 'POST', $data);
    }
    
    /**
     * Vérifie l'état de l'API et récupère des informations sur la version.
     *
     * @since    1.0.0
     * @return   array|WP_Error    Les informations sur l'API ou une erreur.
     */
    public function check_api_status() {
        // Essayer d'abord avec l'endpoint 'health'
        $response = $this->request('health', 'GET');
        
        // Si cela échoue, essayer avec 'api/v1/health'
        if (is_wp_error($response)) {
            $response = $this->request('api/v1/health', 'GET');
            
            // Si cela échoue aussi, essayer avec 'api/health'
            if (is_wp_error($response)) {
                $response = $this->request('api/health', 'GET');
            }
        }
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Si la réponse est vide ou n'est pas un tableau, créer un tableau par défaut
        if (empty($response) || !is_array($response)) {
            $response = array(
                'status' => 'connected',
                'version' => '1.0.0' // Version par défaut
            );
        }
        
        // S'assurer que la réponse contient au moins les champs status et version
        if (!isset($response['status'])) {
            $response['status'] = 'connected';
        }
        
        if (!isset($response['version'])) {
            $response['version'] = '1.0.0'; // Version par défaut
        }
        
        return $response;
    }
}