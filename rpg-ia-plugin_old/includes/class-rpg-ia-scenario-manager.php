<?php
/**
 * Classe responsable de la gestion des scénarios et des scènes
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Classe responsable de la gestion des scénarios et des scènes.
 *
 * Cette classe gère toutes les opérations liées aux scénarios et aux scènes,
 * y compris la création, la modification, la suppression et la récupération.
 *
 * @since      1.0.0
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_Scenario_Manager {

    /**
     * Le client API pour communiquer avec le backend.
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
     * @param    RPG_IA_API_Client    $api_client    Le client API.
     */
    public function __construct($api_client) {
        $this->api_client = $api_client;
    }

    /**
     * Récupère tous les scénarios.
     *
     * @since    1.0.0
     * @return   array|WP_Error    La liste des scénarios ou une erreur.
     */
    public function get_scenarios() {
        return $this->api_client->get_scenarios();
    }

    /**
     * Récupère un scénario spécifique.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @return   array|WP_Error            Les données du scénario ou une erreur.
     */
    public function get_scenario($scenario_id) {
        return $this->api_client->get_scenario($scenario_id);
    }

    /**
     * Crée un nouveau scénario.
     *
     * @since    1.0.0
     * @param    array     $data    Les données du scénario.
     * @return   array|WP_Error     Les données du scénario créé ou une erreur.
     */
    public function create_scenario($data) {
        return $this->api_client->create_scenario($data);
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
        return $this->api_client->update_scenario($scenario_id, $data);
    }

    /**
     * Supprime un scénario.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @return   array|WP_Error            La réponse de l'API ou une erreur.
     */
    public function delete_scenario($scenario_id) {
        return $this->api_client->delete_scenario($scenario_id);
    }

    /**
     * Récupère les scènes d'un scénario.
     *
     * @since    1.0.0
     * @param    int       $scenario_id    L'ID du scénario.
     * @return   array|WP_Error            La liste des scènes ou une erreur.
     */
    public function get_scenario_scenes($scenario_id) {
        return $this->api_client->get_scenario_scenes($scenario_id);
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
        return $this->api_client->get_scenario_scene($scenario_id, $scene_id);
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
        return $this->api_client->create_scenario_scene($scenario_id, $data);
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
        return $this->api_client->update_scenario_scene($scenario_id, $scene_id, $data);
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
        return $this->api_client->delete_scenario_scene($scenario_id, $scene_id);
    }

    /**
     * Récupère les scénarios créés par un utilisateur spécifique.
     *
     * @since    1.0.0
     * @param    int       $user_id    L'ID de l'utilisateur.
     * @return   array                 La liste des scénarios.
     */
    public function get_user_scenarios($user_id) {
        $scenarios = $this->get_scenarios();
        
        if (is_wp_error($scenarios)) {
            return array();
        }
        
        // Filtrer les scénarios par utilisateur
        $user_scenarios = array();
        foreach ($scenarios as $scenario) {
            if (isset($scenario['user_id']) && $scenario['user_id'] == $user_id) {
                $user_scenarios[] = $scenario;
            }
        }
        
        return $user_scenarios;
    }

    /**
     * Récupère les statistiques des scénarios pour un utilisateur.
     *
     * @since    1.0.0
     * @param    int       $user_id    L'ID de l'utilisateur.
     * @return   array                 Les statistiques des scénarios.
     */
    public function get_user_scenario_stats($user_id) {
        $scenarios = $this->get_user_scenarios($user_id);
        
        $stats = array(
            'total' => count($scenarios),
            'scenes_count' => 0,
            'types' => array()
        );
        
        foreach ($scenarios as $scenario) {
            // Compter les scènes
            if (isset($scenario['scenes'])) {
                $stats['scenes_count'] += count($scenario['scenes']);
            }
            
            // Compter les types de scénario
            if (isset($scenario['type'])) {
                if (!isset($stats['types'][$scenario['type']])) {
                    $stats['types'][$scenario['type']] = 0;
                }
                $stats['types'][$scenario['type']]++;
            }
        }
        
        return $stats;
    }

    /**
     * Vérifie si un utilisateur est le propriétaire d'un scénario.
     *
     * @since    1.0.0
     * @param    int       $user_id       L'ID de l'utilisateur.
     * @param    int       $scenario_id    L'ID du scénario.
     * @return   bool                      True si l'utilisateur est le propriétaire, false sinon.
     */
    public function is_scenario_owner($user_id, $scenario_id) {
        $scenario = $this->get_scenario($scenario_id);
        
        if (is_wp_error($scenario)) {
            return false;
        }
        
        return isset($scenario['user_id']) && $scenario['user_id'] == $user_id;
    }
}