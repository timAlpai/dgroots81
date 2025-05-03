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
        // Utiliser l'URL du proxy Apache par défaut si aucune URL n'est fournie
        $this->api_base_url = $api_base_url ?: get_option('rpg_ia_api_url', 'https://dgroots81.mandragore.ai/api/v1');
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
     * Récupère le token JWT actuel.
     *
     * @since    1.0.0
     * @return   string    Le token JWT ou une chaîne vide si aucun token n'est défini.
     */
    public function get_token() {
        return $this->token ? $this->token : '';
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
    public function request($endpoint, $method = 'GET', $data = null, $headers = array(), $form_data = false) {
        // Vérifier si l'URL de base contient déjà /api/v1
        $base_url_has_api_v1 = (strpos($this->api_base_url, '/api/v1') !== false);
        
        // Vérifier si l'endpoint commence par api/
        $endpoint_starts_with_api = (strpos($endpoint, 'api/') === 0);
        
        // Cas spécial pour le proxy Apache: si l'URL de base contient /api/v1 et l'endpoint commence par api/,
        // ne pas ajouter de préfixe supplémentaire pour éviter la duplication
        if ($base_url_has_api_v1 && $endpoint_starts_with_api) {
            // Ne rien faire, utiliser l'endpoint tel quel
        }
        // Si l'URL de base contient déjà /api/v1, ne pas ajouter de préfixe api/v1/
        else if ($base_url_has_api_v1) {
            // Ajouter seulement api/ au début si nécessaire et si ce n'est pas health
            if (strpos($endpoint, 'api/') === false && strpos($endpoint, 'health') === false) {
                $endpoint = 'api/' . ltrim($endpoint, '/');
            }
        } else {
            // Sinon, ajouter le préfixe api/v1/ si nécessaire
            if (strpos($endpoint, 'api/v1/') === false && strpos($endpoint, 'health') === false) {
                $endpoint = 'api/v1/' . ltrim($endpoint, '/');
            }
        }
        
        // Construire l'URL complète
        $url = rtrim($this->api_base_url, '/') . '/' . ltrim($endpoint, '/');
        
        // Construire l'URL complète
        
        // Préparer les arguments de la requête
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array(
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
            if ($form_data) {
                // Envoyer les données au format de formulaire
                $args['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
                $args['body'] = http_build_query($data);
            } else {
                // Envoyer les données au format JSON
                $args['headers']['Content-Type'] = 'application/json';
                $args['body'] = json_encode($data);
            }
        }
        
        // Effectuer la requête
        
        // Effectuer la requête
        $response = wp_remote_request($url, $args);
        
        // Vérifier s'il y a une erreur
        if (is_wp_error($response)) {
            error_log('Erreur de requête: ' . $response->get_error_message());
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
            
            // Formater le message d'erreur pour le log
            if (is_array($error_message) || is_object($error_message)) {
                $formatted_error = json_encode($error_message);
                error_log('Erreur de requête (JSON): ' . $formatted_error);
            } else {
                error_log('Erreur de requête: ' . $error_message);
            }
            
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
        // Préparer les données au format de formulaire
        
        // Préparer les données au format de formulaire
        $data = array(
            'username' => $username,
            'password' => $password,
            'grant_type' => 'password'
        );
        
        // Utiliser l'endpoint d'authentification avec le format form-urlencoded
        $response = $this->request('api/auth/token', 'POST', $data, array(), true);
        
        // Ajouter des logs pour le débogage
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $error_code = $response->get_error_code();
            error_log('Échec de connexion: ' . $error_message);
            error_log('Code d\'erreur: ' . $error_code);
        }
        
        // Vérifier si la réponse contient un token d'accès
        if (!is_wp_error($response)) {
            // Vérifier différents formats possibles de token
            if (isset($response['access_token'])) {
                $this->token = $response['access_token'];
            } else if (isset($response['token'])) {
                $this->token = $response['token'];
            } else if (isset($response['accessToken'])) {
                $this->token = $response['accessToken'];
            } else if (isset($response['access'])) {
                $this->token = $response['access'];
            } else {
                // Aucun token trouvé dans la réponse
            }
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
        $response = $this->request('auth/refresh', 'POST', array('username' => $username));
        
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
            'password' => $password,
            'is_active' => true,
            'is_superuser' => false
        );
        
        // Utiliser l'endpoint standard avec le format JSON
        
        // Utiliser l'endpoint standard avec le format JSON
        $response = $this->request('api/auth/register', 'POST', $data);
        
        // Ajouter des logs pour le débogage
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('Échec de l\'enregistrement: ' . $error_message);
        }
        
        return $response;
    }

    /**
     * Récupère les informations de l'utilisateur courant.
     *
     * @since    1.0.0
     * @return   array|WP_Error    Les informations de l'utilisateur ou une erreur.
     */
    public function get_current_user() {
        // Vérifier si un token est disponible
        if (!$this->token) {
            return new WP_Error('no_token', __('No token available for user info.', 'rpg-ia'));
        }
        
        // Décoder le token JWT pour extraire l'ID de l'utilisateur
        $token_parts = explode('.', $this->token);
        if (count($token_parts) != 3) {
            return new WP_Error('invalid_token', __('Invalid token format.', 'rpg-ia'));
        }
        
        try {
            $payload = json_decode(base64_decode(str_replace(
                ['-', '_'],
                ['+', '/'],
                $token_parts[1]
            )), true);
            
            if (!isset($payload['user_id'])) {
                return new WP_Error('invalid_token', __('Token does not contain user_id.', 'rpg-ia'));
            }
            
            $user_id = $payload['user_id'];
            
            // Utiliser l'endpoint avec l'ID de l'utilisateur au lieu de 'me'
            $response = $this->request('api/users/' . $user_id, 'GET');
            
            return $response;
        } catch (Exception $e) {
            error_log('Erreur lors du décodage du token: ' . $e->getMessage());
            return new WP_Error('token_decode_error', $e->getMessage());
        }
    }

    /**
     * Récupère la liste des personnages.
     *
     * @since    1.0.0
     * @return   array|WP_Error    La liste des personnages ou une erreur.
     */
    public function get_characters() {
        return $this->request('characters/', 'GET');
    }

    /**
     * Récupère un personnage spécifique.
     *
     * @since    1.0.0
     * @param    int       $character_id    L'ID du personnage.
     * @return   array|WP_Error             Les données du personnage ou une erreur.
     */
    public function get_character($character_id) {
        return $this->request('characters/' . $character_id, 'GET');
    }

    /**
     * Crée un nouveau personnage.
     *
     * @since    1.0.0
     * @param    array     $data    Les données du personnage.
     * @return   array|WP_Error     Les données du personnage créé ou une erreur.
     */
    public function create_character($data) {
        // Adapter les données au format attendu par le backend
        $formatted_data = array();
        
        // Champs de base
        $formatted_data['name'] = isset($data['name']) ? $data['name'] : '';
        
        // Convertir 'class' en 'character_class' et s'assurer qu'il est au format attendu par l'API (minuscules)
        if (isset($data['class'])) {
            // Convertir en majuscules pour le mapping, puis le résultat en minuscules
            $class = strtoupper($data['class']);
            // Mapper les classes en anglais vers les valeurs de l'enum CharacterClass (en minuscules)
            $class_mapping = array(
                'WARRIOR' => 'guerrier',
                'CLERIC' => 'clerc',
                'WIZARD' => 'magicien',
                'THIEF' => 'voleur',
                'DWARF' => 'nain',
                'ELF' => 'elfe',
                'HALFLING' => 'halfelin'
            );
            // Utiliser la valeur mappée ou convertir la classe originale en minuscules
            $formatted_data['character_class'] = isset($class_mapping[$class]) ? $class_mapping[$class] : strtolower($class);
        }
        
        // Niveau
        $formatted_data['level'] = isset($data['level']) ? $data['level'] : 1;
        
        // Extraire les attributs du sous-objet 'attributes' s'il existe
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attr => $value) {
                $formatted_data[$attr] = $value;
            }
        }
        
        // Ajouter les champs requis manquants
        if (!isset($formatted_data['strength'])) $formatted_data['strength'] = 10;
        if (!isset($formatted_data['intelligence'])) $formatted_data['intelligence'] = 10;
        if (!isset($formatted_data['wisdom'])) $formatted_data['wisdom'] = 10;
        if (!isset($formatted_data['dexterity'])) $formatted_data['dexterity'] = 10;
        if (!isset($formatted_data['constitution'])) $formatted_data['constitution'] = 10;
        if (!isset($formatted_data['charisma'])) $formatted_data['charisma'] = 10;
        
        // Points de vie
        $formatted_data['max_hp'] = isset($data['max_hp']) ? $data['max_hp'] : 10;
        $formatted_data['current_hp'] = isset($data['current_hp']) ? $data['current_hp'] : $formatted_data['max_hp'];
        
        // Récupérer l'ID de l'utilisateur à partir du token JWT
        $user_id = $this->get_user_id_from_token();
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        $formatted_data['user_id'] = $user_id;
        
        // Pour le game_session_id, utiliser celui fourni ou créer une session temporaire
        if (isset($data['game_session_id'])) {
            $formatted_data['game_session_id'] = $data['game_session_id'];
        } else {
            // Créer une session temporaire pour les tests
            $session_data = array(
                'name' => 'Test Session ' . time(),
                'description' => 'Session temporaire pour les tests',
                'game_master_id' => $user_id
            );
            $session_response = $this->create_game_session($session_data);
            if (is_wp_error($session_response)) {
                return $session_response;
            }
            $formatted_data['game_session_id'] = $session_response['id'];
        }
        
        // Envoyer la requête à l'API
        
        return $this->request('characters/', 'POST', $formatted_data);
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
        return $this->request('characters/' . $character_id, 'PUT', $data);
    }

    /**
     * Supprime un personnage.
     *
     * @since    1.0.0
     * @param    int       $character_id    L'ID du personnage.
     * @return   array|WP_Error             La réponse de l'API ou une erreur.
     */
    public function delete_character($character_id) {
        return $this->request('characters/' . $character_id, 'DELETE');
    }

    /**
     * Récupère la liste des sessions de jeu.
     *
     * @since    1.0.0
     * @return   array|WP_Error    La liste des sessions ou une erreur.
     */
    public function get_game_sessions() {
        return $this->request('game-sessions/', 'GET');
    }

    /**
     * Récupère une session de jeu spécifique.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @return   array|WP_Error           Les données de la session ou une erreur.
     */
    public function get_game_session($session_id) {
        return $this->request('game-sessions/' . $session_id, 'GET');
    }

    /**
     * Crée une nouvelle session de jeu.
     *
     * @since    1.0.0
     * @param    array     $data    Les données de la session.
     * @return   array|WP_Error     Les données de la session créée ou une erreur.
     */
    public function create_game_session($data) {
        // Adapter les données au format attendu par le backend
        $formatted_data = array();
        
        // Champs de base
        $formatted_data['name'] = isset($data['name']) ? $data['name'] : 'Session ' . time();
        $formatted_data['description'] = isset($data['description']) ? $data['description'] : '';
        $formatted_data['is_active'] = isset($data['is_active']) ? $data['is_active'] : true;
        
        // Règles du jeu et niveau de difficulté
        $formatted_data['game_rules'] = isset($data['game_rules']) ? $data['game_rules'] : 'OSE';
        $formatted_data['difficulty_level'] = isset($data['difficulty_level']) ? $data['difficulty_level'] : 'standard';
        
        // Récupérer l'ID du maître de jeu (game_master_id)
        if (isset($data['game_master_id'])) {
            $formatted_data['game_master_id'] = $data['game_master_id'];
        } else {
            // Si non fourni, utiliser l'ID de l'utilisateur actuel
            $user_id = $this->get_user_id_from_token();
            if (is_wp_error($user_id)) {
                return $user_id;
            }
            $formatted_data['game_master_id'] = $user_id;
        }
        
        // Gérer le scénario si fourni
        if (isset($data['scenario_id'])) {
            $formatted_data['current_scenario_id'] = $data['scenario_id'];
        }
        
        // Envoyer la requête à l'API
        
        return $this->request('game-sessions/', 'POST', $formatted_data);
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
        return $this->request('game-sessions/' . $session_id, 'PUT', $data);
    }

    /**
     * Supprime une session de jeu.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @return   array|WP_Error           La réponse de l'API ou une erreur.
     */
    public function delete_game_session($session_id) {
        return $this->request('game-sessions/' . $session_id, 'DELETE');
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
        $endpoint = 'game-sessions/' . $session_id . '/actions';
        
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
        // Transformer les données pour correspondre au format attendu par l'API
        $formatted_data = array();
        
        // Mapper 'type' vers 'action_type'
        if (isset($data['type'])) {
            $formatted_data['action_type'] = $data['type'];
        }
        
        // Mapper 'content' vers 'description'
        if (isset($data['content'])) {
            $formatted_data['description'] = $data['content'];
        }
        
        // Conserver 'character_id'
        if (isset($data['character_id'])) {
            $formatted_data['character_id'] = $data['character_id'];
        }
        
        // Ajouter 'scene_id' s'il existe
        if (isset($data['scene_id'])) {
            $formatted_data['scene_id'] = $data['scene_id'];
        }
        
        // Ajouter 'game_data' s'il existe
        if (isset($data['game_data'])) {
            $formatted_data['game_data'] = $data['game_data'];
        }
        
        // Envoyer la requête à l'API
        
        return $this->request('actions/', 'POST', $formatted_data);
    }

    /**
     * Vérifie la connexion à l'API.
     *
     * @since    1.0.0
     * @return   bool|WP_Error    True si la connexion est établie, une erreur sinon.
     */
    public function check_connection() {
        $response = $this->request('health', 'GET');
        
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
        return $this->request('scenarios/', 'GET');
    }

    /**
     * Récupère un scénario spécifique.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @return   array|WP_Error            Les données du scénario ou une erreur.
     */
    public function get_scenario($scenario_id) {
        return $this->request('scenarios/' . $scenario_id, 'GET');
    }

    /**
     * Crée un nouveau scénario.
     *
     * @since    1.0.0
     * @param    array     $data    Les données du scénario.
     * @return   array|WP_Error     Les données du scénario créé ou une erreur.
     */
    public function create_scenario($data) {
        // Adapter les données au format attendu par le backend
        $formatted_data = array();
        
        // Transformer 'name' en 'title'
        $formatted_data['title'] = isset($data['name']) ? $data['name'] : '';
        
        // Copier les autres champs
        if (isset($data['description'])) {
            $formatted_data['description'] = $data['description'];
        }
        
        // Ajouter les champs optionnels s'ils existent
        if (isset($data['recommended_level'])) {
            $formatted_data['recommended_level'] = $data['recommended_level'];
        }
        
        if (isset($data['difficulty'])) {
            $formatted_data['difficulty'] = $data['difficulty'];
        }
        
        if (isset($data['introduction'])) {
            $formatted_data['introduction'] = $data['introduction'];
        }
        
        if (isset($data['conclusion'])) {
            $formatted_data['conclusion'] = $data['conclusion'];
        }
        
        if (isset($data['tags'])) {
            $formatted_data['tags'] = $data['tags'];
        }
        
        if (isset($data['is_published'])) {
            $formatted_data['is_published'] = $data['is_published'];
        }
        
        // Extraire l'ID de l'utilisateur à partir du token JWT
        if ($this->token) {
            $token_parts = explode('.', $this->token);
            if (count($token_parts) == 3) {
                try {
                    $payload = json_decode(base64_decode(str_replace(
                        ['-', '_'],
                        ['+', '/'],
                        $token_parts[1]
                    )), true);
                    
                    if (isset($payload['user_id'])) {
                        $formatted_data['creator_id'] = $payload['user_id'];
                    }
                } catch (Exception $e) {
                    error_log('Erreur lors du décodage du token pour creator_id: ' . $e->getMessage());
                }
            }
        }
        
        // Si creator_id n'a pas pu être extrait du token, essayer de l'obtenir via l'API
        if (!isset($formatted_data['creator_id'])) {
            $user_info = $this->get_current_user();
            if (!is_wp_error($user_info) && isset($user_info['id'])) {
                $formatted_data['creator_id'] = $user_info['id'];
            }
        }
        
        return $this->request('scenarios/', 'POST', $formatted_data);
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
        return $this->request('scenarios/' . $scenario_id, 'PUT', $data);
    }

    /**
     * Supprime un scénario.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @return   array|WP_Error            La réponse de l'API ou une erreur.
     */
    public function delete_scenario($scenario_id) {
        return $this->request('scenarios/' . $scenario_id, 'DELETE');
    }

    /**
     * Récupère les scènes d'un scénario.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @return   array|WP_Error            La liste des scènes ou une erreur.
     */
    public function get_scenario_scenes($scenario_id) {
        return $this->request('scenarios/' . $scenario_id . '/scenes', 'GET');
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
        return $this->request('scenarios/' . $scenario_id . '/scenes/' . $scene_id, 'GET');
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
        return $this->request('scenarios/' . $scenario_id . '/scenes', 'POST', $data);
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
        return $this->request('scenarios/' . $scenario_id . '/scenes/' . $scene_id, 'PUT', $data);
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
        return $this->request('scenarios/' . $scenario_id . '/scenes/' . $scene_id, 'DELETE');
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
        
        return $this->request('game-sessions/' . $session_id . '/status', 'PUT', $data);
    }
    
    /**
     * Récupère l'ID de l'utilisateur à partir du token JWT.
     *
     * @since    1.0.0
     * @return   int|WP_Error    L'ID de l'utilisateur ou une erreur.
     */
    private function get_user_id_from_token() {
        if (!$this->token) {
            return new WP_Error('no_token', __('No token available for user info.', 'rpg-ia'));
        }
        
        // Décoder le token JWT pour extraire l'ID de l'utilisateur
        $token_parts = explode('.', $this->token);
        if (count($token_parts) != 3) {
            return new WP_Error('invalid_token', __('Invalid token format.', 'rpg-ia'));
        }
        
        try {
            $payload = json_decode(base64_decode(str_replace(
                ['-', '_'],
                ['+', '/'],
                $token_parts[1]
            )), true);
            
            if (!isset($payload['user_id'])) {
                return new WP_Error('invalid_token', __('Token does not contain user_id.', 'rpg-ia'));
            }
            
            return $payload['user_id'];
        } catch (Exception $e) {
            error_log('Erreur lors du décodage du token: ' . $e->getMessage());
            return new WP_Error('token_decode_error', $e->getMessage());
        }
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
        
        return $this->request('game-sessions/' . $session_id . '/override', 'POST', $data);
    }
    
    /**
     * Vérifie l'état de l'API et récupère des informations sur la version.
     *
     * @since    1.0.0
     * @return   array|WP_Error    Les informations sur l'API ou une erreur.
     */
    public function check_api_status() {
        // Utiliser l'endpoint health qui fonctionne
        $response = $this->request('health', 'GET');
        
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
    
    /**
     * Vérifie si un utilisateur existe par son nom d'utilisateur.
     *
     * @since    1.0.0
     * @param    string    $username    Le nom d'utilisateur à vérifier.
     * @return   bool|WP_Error          True si l'utilisateur existe, false sinon, ou une erreur.
     */
    public function check_user_exists($username) {
        $url = 'api/users/check-exists/' . urlencode($username);
        
        $response = $this->request($url, 'GET');
        
        if (is_wp_error($response)) {
            error_log('Erreur lors de la vérification de l\'existence de l\'utilisateur: ' . $response->get_error_message());
            return null; // En cas d'erreur, on ne peut pas déterminer avec certitude
        }
        
        // Si la réponse est un tableau et contient la clé 'exists'
        if (is_array($response) && isset($response['exists'])) {
            return $response['exists'] === true;
        }
        
        // Par défaut, on considère que l'utilisateur n'existe pas
        return false;
    }
}