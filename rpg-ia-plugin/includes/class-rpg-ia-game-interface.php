<?php
/**
 * Classe responsable de gérer l'interface de jeu
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Classe responsable de gérer l'interface de jeu.
 *
 * Cette classe gère toutes les opérations liées à l'interface de jeu:
 * - Récupération des données de jeu
 * - Soumission des actions des joueurs
 * - Mise à jour de l'interface en temps réel
 * - Gestion des interactions avec l'API backend
 *
 * @since      1.0.0
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_Game_Interface {

    /**
     * L'API client pour communiquer avec le backend.
     *
     * @since    1.0.0
     * @access   private
     * @var      RPG_IA_API_Client    $api_client    L'API client.
     */
    private $api_client;

    /**
     * Le gestionnaire de sessions.
     *
     * @since    1.0.0
     * @access   private
     * @var      RPG_IA_Session_Manager    $session_manager    Le gestionnaire de sessions.
     */
    private $session_manager;

    /**
     * Le gestionnaire de personnages.
     *
     * @since    1.0.0
     * @access   private
     * @var      RPG_IA_Character_Manager    $character_manager    Le gestionnaire de personnages.
     */
    private $character_manager;

    /**
     * Initialise la classe et définit ses propriétés.
     *
     * @since    1.0.0
     * @param    RPG_IA_API_Client    $api_client    L'API client (optionnel).
     */
    public function __construct($api_client = null) {
        if ($api_client === null) {
            $token = isset($_COOKIE['rpg_ia_token']) ? $_COOKIE['rpg_ia_token'] : null;
            $this->api_client = new RPG_IA_API_Client(null, $token);
        } else {
            $this->api_client = $api_client;
        }

        $this->session_manager = new RPG_IA_Session_Manager($this->api_client);
        $this->character_manager = new RPG_IA_Character_Manager($this->api_client);
    }

    /**
     * Récupère les données d'une session de jeu.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @return   array|WP_Error           Les données de la session ou une erreur.
     */
    public function get_game_data($session_id) {
        return $this->session_manager->get_session($session_id);
    }

    /**
     * Récupère les actions d'une session de jeu.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @param    int       $timestamp     Le timestamp à partir duquel récupérer les actions (optionnel).
     * @return   array|WP_Error           La liste des actions ou une erreur.
     */
    public function get_game_actions($session_id, $timestamp = null) {
        return $this->session_manager->get_session_actions($session_id, $timestamp);
    }

    /**
     * Soumet une action de joueur.
     *
     * @since    1.0.0
     * @param    array     $action_data    Les données de l'action.
     * @return   array|WP_Error            Les données de l'action créée ou une erreur.
     */
    public function submit_player_action($action_data) {
        return $this->session_manager->create_action($action_data);
    }

    /**
     * Rejoint une session de jeu avec un personnage.
     *
     * @since    1.0.0
     * @param    int       $session_id      L'ID de la session.
     * @param    int       $character_id    L'ID du personnage.
     * @return   array|WP_Error             La réponse de l'API ou une erreur.
     */
    public function join_game_session($session_id, $character_id) {
        $data = array(
            'session_id' => $session_id,
            'character_id' => $character_id
        );
        
        return $this->api_client->join_game_session($data);
    }

    /**
     * Quitte une session de jeu.
     *
     * @since    1.0.0
     * @param    int       $session_id      L'ID de la session.
     * @param    int       $character_id    L'ID du personnage.
     * @return   array|WP_Error             La réponse de l'API ou une erreur.
     */
    public function leave_game_session($session_id, $character_id) {
        $data = array(
            'session_id' => $session_id,
            'character_id' => $character_id
        );
        
        return $this->api_client->leave_game_session($data);
    }

    /**
     * Récupère les personnages disponibles pour une session.
     *
     * @since    1.0.0
     * @param    int       $user_id    L'ID de l'utilisateur (optionnel).
     * @return   array|WP_Error        La liste des personnages ou une erreur.
     */
    public function get_available_characters($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        return $this->character_manager->get_user_characters($user_id);
    }

    /**
     * Récupère les joueurs d'une session de jeu.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @return   array|WP_Error           La liste des joueurs ou une erreur.
     */
    public function get_session_players($session_id) {
        return $this->api_client->get_session_players($session_id);
    }

    /**
     * Envoie un message de chat dans une session de jeu.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @param    int       $character_id  L'ID du personnage.
     * @param    string    $message       Le message à envoyer.
     * @return   array|WP_Error           La réponse de l'API ou une erreur.
     */
    public function send_chat_message($session_id, $character_id, $message) {
        $data = array(
            'session_id' => $session_id,
            'character_id' => $character_id,
            'message' => $message,
            'type' => 'chat'
        );
        
        return $this->api_client->create_action($data);
    }

    /**
     * Récupère les messages de chat d'une session de jeu.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @param    int       $timestamp     Le timestamp à partir duquel récupérer les messages (optionnel).
     * @return   array|WP_Error           La liste des messages ou une erreur.
     */
    public function get_chat_messages($session_id, $timestamp = null) {
        $actions = $this->get_game_actions($session_id, $timestamp);
        
        if (is_wp_error($actions)) {
            return $actions;
        }
        
        $chat_messages = array();
        
        foreach ($actions as $action) {
            if (isset($action['type']) && $action['type'] === 'chat') {
                $chat_messages[] = $action;
            }
        }
        
        return $chat_messages;
    }

    /**
     * Enregistre les notes personnelles d'un joueur pour une session.
     *
     * @since    1.0.0
     * @param    int       $session_id     L'ID de la session.
     * @param    int       $character_id   L'ID du personnage.
     * @param    string    $notes          Les notes à enregistrer.
     * @return   bool|WP_Error             True si les notes sont enregistrées, une erreur sinon.
     */
    public function save_personal_notes($session_id, $character_id, $notes) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return new WP_Error('not_logged_in', __('Vous devez être connecté pour enregistrer des notes.', 'rpg-ia'));
        }
        
        $meta_key = 'rpg_ia_notes_' . $session_id . '_' . $character_id;
        
        return update_user_meta($user_id, $meta_key, $notes);
    }

    /**
     * Récupère les notes personnelles d'un joueur pour une session.
     *
     * @since    1.0.0
     * @param    int       $session_id     L'ID de la session.
     * @param    int       $character_id   L'ID du personnage.
     * @return   string                    Les notes du joueur.
     */
    public function get_personal_notes($session_id, $character_id) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return '';
        }
        
        $meta_key = 'rpg_ia_notes_' . $session_id . '_' . $character_id;
        
        return get_user_meta($user_id, $meta_key, true);
    }

    /**
     * Génère une liste des types d'actions disponibles.
     *
     * @since    1.0.0
     * @return   array    La liste des types d'actions disponibles.
     */
    public function get_action_types() {
        return array(
            'dialogue' => __('Dialogue', 'rpg-ia'),
            'movement' => __('Déplacement', 'rpg-ia'),
            'combat' => __('Combat', 'rpg-ia'),
            'skill' => __('Compétence', 'rpg-ia'),
            'item' => __('Utiliser un objet', 'rpg-ia'),
            'spell' => __('Lancer un sort', 'rpg-ia'),
            'other' => __('Autre', 'rpg-ia')
        );
    }

    /**
     * Génère une liste des types de dés disponibles.
     *
     * @since    1.0.0
     * @return   array    La liste des types de dés disponibles.
     */
    public function get_dice_types() {
        return array(
            'd20' => __('d20 (jet de compétence)', 'rpg-ia'),
            'd20adv' => __('d20 avec avantage', 'rpg-ia'),
            'd20dis' => __('d20 avec désavantage', 'rpg-ia'),
            'd4' => __('d4', 'rpg-ia'),
            'd6' => __('d6', 'rpg-ia'),
            'd8' => __('d8', 'rpg-ia'),
            'd10' => __('d10', 'rpg-ia'),
            'd12' => __('d12', 'rpg-ia'),
            'd100' => __('d100 (pourcentage)', 'rpg-ia'),
            'custom' => __('Personnalisé', 'rpg-ia')
        );
    }
}