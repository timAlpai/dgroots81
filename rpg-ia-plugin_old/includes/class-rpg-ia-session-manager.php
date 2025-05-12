<?php
/**
 * Classe responsable de la gestion des sessions de jeu
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Classe responsable de la gestion des sessions de jeu.
 *
 * Cette classe gère toutes les opérations liées aux sessions de jeu:
 * - Création et édition de sessions
 * - Récupération des sessions
 * - Suppression des sessions
 * - Synchronisation avec l'API backend
 *
 * @since      1.0.0
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_Session_Manager {

    /**
     * L'API client pour communiquer avec le backend.
     *
     * @since    1.0.0
     * @access   private
     * @var      RPG_IA_API_Client    $api_client    L'API client.
     */
    private $api_client;

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
    }

    /**
     * Récupère toutes les sessions de jeu.
     *
     * @since    1.0.0
     * @return   array|WP_Error    La liste des sessions ou une erreur.
     */
    public function get_sessions() {
        return $this->api_client->get_game_sessions();
    }

    /**
     * Récupère une session de jeu spécifique.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @return   array|WP_Error           Les données de la session ou une erreur.
     */
    public function get_session($session_id) {
        return $this->api_client->get_game_session($session_id);
    }

    /**
     * Crée une nouvelle session de jeu.
     *
     * @since    1.0.0
     * @param    array     $data    Les données de la session.
     * @return   array|WP_Error     Les données de la session créée ou une erreur.
     */
    public function create_session($data) {
        return $this->api_client->create_game_session($data);
    }

    /**
     * Met à jour une session de jeu existante.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @param    array     $data          Les données de la session.
     * @return   array|WP_Error           Les données de la session mise à jour ou une erreur.
     */
    public function update_session($session_id, $data) {
        return $this->api_client->update_game_session($session_id, $data);
    }

    /**
     * Supprime une session de jeu.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @return   array|WP_Error           La réponse de l'API ou une erreur.
     */
    public function delete_session($session_id) {
        return $this->api_client->delete_game_session($session_id);
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
        return $this->api_client->get_session_actions($session_id, $timestamp);
    }

    /**
     * Crée une nouvelle action dans une session.
     *
     * @since    1.0.0
     * @param    array     $data    Les données de l'action.
     * @return   array|WP_Error     Les données de l'action créée ou une erreur.
     */
    public function create_action($data) {
        return $this->api_client->create_action($data);
    }

    /**
     * Valide les données d'une session de jeu.
     *
     * @since    1.0.0
     * @param    array     $data    Les données de la session.
     * @return   bool|WP_Error      True si les données sont valides, une erreur sinon.
     */
    public function validate_session_data($data) {
        // Vérifier les champs obligatoires
        $required_fields = array(
            'name',
            'description',
            'game_master_id'
        );
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return new WP_Error('missing_field', sprintf(__('Le champ %s est obligatoire.', 'rpg-ia'), $field));
            }
        }
        
        // Vérifier que le nom n'est pas trop court
        if (strlen($data['name']) < 3) {
            return new WP_Error('invalid_name', __('Le nom de la session doit contenir au moins 3 caractères.', 'rpg-ia'));
        }
        
        // Vérifier que la description n'est pas trop courte
        if (strlen($data['description']) < 10) {
            return new WP_Error('invalid_description', __('La description de la session doit contenir au moins 10 caractères.', 'rpg-ia'));
        }
        
        return true;
    }

    /**
     * Génère une liste de règles de jeu disponibles.
     *
     * @since    1.0.0
     * @return   array    La liste des règles de jeu disponibles.
     */
    public function get_available_rules() {
        return array(
            'ose' => __('Old School Essentials', 'rpg-ia'),
            'dnd5e' => __('Dungeons & Dragons 5e', 'rpg-ia'),
            'pathfinder' => __('Pathfinder', 'rpg-ia'),
            'custom' => __('Règles personnalisées', 'rpg-ia')
        );
    }

    /**
     * Génère une liste de niveaux de difficulté disponibles.
     *
     * @since    1.0.0
     * @return   array    La liste des niveaux de difficulté disponibles.
     */
    public function get_difficulty_levels() {
        return array(
            'easy' => __('Facile', 'rpg-ia'),
            'standard' => __('Standard', 'rpg-ia'),
            'hard' => __('Difficile', 'rpg-ia'),
            'deadly' => __('Mortel', 'rpg-ia')
        );
    }

    /**
     * Génère une liste de statuts de session disponibles.
     *
     * @since    1.0.0
     * @return   array    La liste des statuts de session disponibles.
     */
    public function get_session_statuses() {
        return array(
            'active' => __('Active', 'rpg-ia'),
            'paused' => __('En pause', 'rpg-ia'),
            'completed' => __('Terminée', 'rpg-ia'),
            'planning' => __('En préparation', 'rpg-ia')
        );
    }
}