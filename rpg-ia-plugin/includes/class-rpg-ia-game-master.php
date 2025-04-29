<?php
/**
 * Classe responsable des fonctionnalités du maître de jeu
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Classe responsable des fonctionnalités du maître de jeu.
 *
 * Cette classe gère toutes les opérations spécifiques au maître de jeu,
 * y compris le contrôle des sessions et l'intervention dans la narration.
 *
 * @since      1.0.0
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_Game_Master {

    /**
     * Le client API pour communiquer avec le backend.
     *
     * @since    1.0.0
     * @access   private
     * @var      RPG_IA_API_Client    $api_client    Le client API.
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
     * Initialise la classe et définit ses propriétés.
     *
     * @since    1.0.0
     * @param    RPG_IA_API_Client         $api_client        Le client API.
     * @param    RPG_IA_Session_Manager    $session_manager    Le gestionnaire de sessions.
     */
    public function __construct($api_client, $session_manager) {
        $this->api_client = $api_client;
        $this->session_manager = $session_manager;
    }

    /**
     * Vérifie si un utilisateur est le maître de jeu d'une session.
     *
     * @since    1.0.0
     * @param    int       $user_id       L'ID de l'utilisateur.
     * @param    int       $session_id    L'ID de la session.
     * @return   bool                     True si l'utilisateur est le maître de jeu, false sinon.
     */
    public function is_game_master($user_id, $session_id) {
        $session = $this->session_manager->get_session($session_id);
        
        if (is_wp_error($session)) {
            return false;
        }
        
        return isset($session['game_master_id']) && $session['game_master_id'] == $user_id;
    }

    /**
     * Récupère les sessions dont un utilisateur est le maître de jeu.
     *
     * @since    1.0.0
     * @param    int       $user_id    L'ID de l'utilisateur.
     * @return   array                 La liste des sessions.
     */
    public function get_gm_sessions($user_id) {
        $sessions = $this->session_manager->get_sessions();
        
        if (is_wp_error($sessions)) {
            return array();
        }
        
        // Filtrer les sessions par maître de jeu
        $gm_sessions = array();
        foreach ($sessions as $session) {
            if (isset($session['game_master_id']) && $session['game_master_id'] == $user_id) {
                $gm_sessions[] = $session;
            }
        }
        
        return $gm_sessions;
    }

    /**
     * Récupère les statistiques des sessions pour un maître de jeu.
     *
     * @since    1.0.0
     * @param    int       $user_id    L'ID de l'utilisateur.
     * @return   array                 Les statistiques des sessions.
     */
    public function get_gm_session_stats($user_id) {
        $sessions = $this->get_gm_sessions($user_id);
        
        $stats = array(
            'total' => count($sessions),
            'active' => 0,
            'paused' => 0,
            'completed' => 0,
            'players' => 0,
            'actions' => 0,
            'tokens_used' => 0
        );
        
        foreach ($sessions as $session) {
            // Compter par statut
            if (isset($session['status'])) {
                if ($session['status'] == 'active') {
                    $stats['active']++;
                } elseif ($session['status'] == 'paused') {
                    $stats['paused']++;
                } elseif ($session['status'] == 'completed') {
                    $stats['completed']++;
                }
            }
            
            // Compter les joueurs
            if (isset($session['players'])) {
                $stats['players'] += count($session['players']);
            }
            
            // Compter les actions
            if (isset($session['action_count'])) {
                $stats['actions'] += $session['action_count'];
            }
            
            // Compter les tokens utilisés
            if (isset($session['tokens_used'])) {
                $stats['tokens_used'] += $session['tokens_used'];
            }
        }
        
        return $stats;
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
        return $this->api_client->update_session_status($session_id, $status);
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
    public function override_narration($session_id, $content, $type = 'narration') {
        return $this->api_client->override_session_narration($session_id, $content, $type);
    }

    /**
     * Récupère les joueurs d'une session.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @return   array                    La liste des joueurs.
     */
    public function get_session_players($session_id) {
        $session = $this->session_manager->get_session($session_id);
        
        if (is_wp_error($session) || !isset($session['players'])) {
            return array();
        }
        
        return $session['players'];
    }

    /**
     * Récupère les personnages des joueurs d'une session.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @return   array                    La liste des personnages.
     */
    public function get_session_characters($session_id) {
        $players = $this->get_session_players($session_id);
        $characters = array();
        
        foreach ($players as $player) {
            if (isset($player['character_id'])) {
                $character = $this->api_client->get_character($player['character_id']);
                if (!is_wp_error($character)) {
                    $characters[] = $character;
                }
            }
        }
        
        return $characters;
    }

    /**
     * Récupère le scénario actuel d'une session.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @return   array|null               Le scénario ou null si aucun scénario n'est associé.
     */
    public function get_session_scenario($session_id) {
        $session = $this->session_manager->get_session($session_id);
        
        if (is_wp_error($session) || !isset($session['scenario_id'])) {
            return null;
        }
        
        $scenario = $this->api_client->get_scenario($session['scenario_id']);
        
        if (is_wp_error($scenario)) {
            return null;
        }
        
        return $scenario;
    }

    /**
     * Récupère la scène actuelle d'une session.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @return   array|null               La scène ou null si aucune scène n'est active.
     */
    public function get_session_current_scene($session_id) {
        $session = $this->session_manager->get_session($session_id);
        
        if (is_wp_error($session) || !isset($session['current_scene_id']) || !isset($session['scenario_id'])) {
            return null;
        }
        
        $scene = $this->api_client->get_scenario_scene($session['scenario_id'], $session['current_scene_id']);
        
        if (is_wp_error($scene)) {
            return null;
        }
        
        return $scene;
    }

    /**
     * Change la scène actuelle d'une session.
     *
     * @since    1.0.0
     * @param    int       $session_id    L'ID de la session.
     * @param    int       $scene_id      L'ID de la nouvelle scène.
     * @return   array|WP_Error           La réponse de l'API ou une erreur.
     */
    public function change_session_scene($session_id, $scene_id) {
        $data = array(
            'current_scene_id' => $scene_id
        );
        
        return $this->session_manager->update_session($session_id, $data);
    }
}