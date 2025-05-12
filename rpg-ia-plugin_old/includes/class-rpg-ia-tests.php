<?php
/**
 * Classe responsable des tests automatiques du plugin
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Classe responsable des tests automatiques du plugin.
 *
 * Cette classe définit toutes les méthodes nécessaires pour tester
 * les différentes fonctionnalités du plugin.
 *
 * @since      1.0.0
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_Tests {

    /**
     * L'API client pour communiquer avec le backend.
     *
     * @since    1.0.0
     * @access   private
     * @var      RPG_IA_API_Client    $api_client    L'API client.
     */
    private $api_client;

    /**
     * Les résultats des tests.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $results    Les résultats des tests.
     */
    private $results = array();

    /**
     * Les données de test.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $test_data    Les données de test.
     */
    private $test_data = array();

    /**
     * Initialise la classe et définit ses propriétés.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->api_client = new RPG_IA_API_Client();
        
        // Charger les données de test existantes
        $this->test_data = get_option('rpg_ia_test_data', array());
    }

    /**
     * Exécute tous les tests disponibles.
     *
     * @since    1.0.0
     * @return   array    Les résultats des tests.
     */
    public function run_all_tests() {
        $this->results = array();
        
        // Vérifier si le mode test est activé
        if (get_option('rpg_ia_test_mode', 'no') === 'yes') {
            // Créer les données de test si nécessaire
            $this->seed_test_data();
            
            // Test de connexion à l'API
            $this->test_api_connection();
            
            // Test des fonctionnalités d'authentification
            $this->test_auth_functionality();
            
            // Test des fonctionnalités de gestion des personnages
            $this->test_character_functionality();
            
            // Test des fonctionnalités de gestion des sessions
            $this->test_session_functionality();
            
            // Test des fonctionnalités de l'interface de jeu
            $this->test_game_interface_functionality();
            
            // Test des fonctionnalités d'administration
            $this->test_admin_functionality();
        } else {
            // Si le mode test n'est pas activé, effectuer uniquement des tests de base
            $this->add_test_result(
                'test_mode_disabled',
                __('Mode test désactivé', 'rpg-ia'),
                true,
                __('Le mode test est désactivé. Activez-le dans les réglages pour exécuter tous les tests.', 'rpg-ia')
            );
            
            // Test de connexion à l'API (toujours exécuté)
            $this->test_api_connection();
            
            // Tests de chargement des classes
            $this->test_classes_loading();
        }
        
        // Enregistrer les résultats des tests
        $this->save_test_results();
        
        return $this->results;
    }

    /**
     * Crée ou met à jour les données de test.
     *
     * @since    1.0.0
     */
    private function seed_test_data() {
        // Vérifier si les données de test existent déjà
        if (!empty($this->test_data) && isset($this->test_data['created_at']) &&
            (time() - strtotime($this->test_data['created_at'])) < 86400) {
            // Les données de test existent et ont moins de 24 heures, les utiliser
            return;
        }
        
        // Créer un nouvel ensemble de données de test
        $this->test_data = array(
            'created_at' => current_time('mysql'),
            'user' => array(
                'username' => 'test_user_' . time(),
                'email' => 'test_' . time() . '@example.com',
                'password' => 'Test@' . time(),
                'token' => null,
                'id' => null
            ),
            'character' => array(
                'name' => 'Test Character ' . time(),
                'class' => 'Warrior',
                'level' => 1,
                'id' => null
            ),
            'session' => array(
                'name' => 'Test Session ' . time(),
                'description' => 'Test session created for automated tests',
                'id' => null
            ),
            'scenario' => array(
                'name' => 'Test Scenario ' . time(),
                'description' => 'Test scenario created for automated tests',
                'id' => null
            )
        );
        
        // Enregistrer les données de test
        update_option('rpg_ia_test_data', $this->test_data);
        
        // Créer les données dans l'API
        $this->create_test_data_in_api();
    }
    
    /**
     * Crée les données de test dans l'API.
     *
     * @since    1.0.0
     */
    private function create_test_data_in_api() {
       // Créer un utilisateur de test
       try {
           // Générer un nom d'utilisateur et un mot de passe uniques pour éviter les conflits
           $timestamp = time();
           $user_data = array(
               'username' => 'test_user_' . $timestamp,
               'email' => 'test_' . $timestamp . '@example.com',
               'password' => 'Test@' . $timestamp
           );
           
           // Mettre à jour les données de test
           $this->test_data['user']['username'] = $user_data['username'];
           $this->test_data['user']['email'] = $user_data['email'];
           $this->test_data['user']['password'] = $user_data['password'];
           
           // Initialiser les informations de débogage
           $debug_info = array();
           $this->test_data['debug_info'] = $debug_info;
           
           error_log('=== DÉBUT DU TEST D\'AUTHENTIFICATION ===');
           error_log('Utilisateur de test: ' . $user_data['username']);
           error_log('Email de test: ' . $user_data['email']);
           error_log('Mot de passe de test: ' . $user_data['password']);
           
           // Vérifier d'abord si l'utilisateur existe en utilisant le nouvel endpoint
           error_log('Vérification de l\'existence de l\'utilisateur...');
           $user_exists = $this->api_client->check_user_exists($user_data['username']);
           $this->test_data['debug_info']['user_exists_check'] = $user_exists;
           
           if (is_wp_error($user_exists)) {
               error_log('Erreur lors de la vérification de l\'existence de l\'utilisateur: ' . $user_exists->get_error_message());
               // Si l'endpoint n'est pas disponible, utiliser la méthode précédente
               $this->test_data['debug_info']['fallback_to_login_check'] = true;
               error_log('Fallback à la méthode de vérification par login');
               
               // Essayer de se connecter pour voir si l'utilisateur existe
               $login_response = $this->api_client->login(
                   $user_data['username'],
                   $user_data['password']
               );
               
               $this->test_data['debug_info']['initial_login_response'] = $login_response;
               
               // Si la connexion réussit, l'utilisateur existe déjà
               if (!is_wp_error($login_response) && isset($login_response['access_token'])) {
                   error_log('Connexion réussie, l\'utilisateur existe déjà');
                   $user_exists = true;
                   $this->test_data['user']['token'] = $login_response['access_token'];
                   $this->api_client->set_token($login_response['access_token']);
                   
                   // Récupérer les informations de l'utilisateur
                   $user_info = $this->api_client->get_current_user();
                   if (!is_wp_error($user_info) && isset($user_info['id'])) {
                       $this->test_data['user']['id'] = $user_info['id'];
                       error_log('ID de l\'utilisateur récupéré: ' . $user_info['id']);
                   }
               } else {
                   error_log('Connexion échouée, l\'utilisateur n\'existe probablement pas');
                   $user_exists = false;
               }
           } else {
               error_log('Résultat de la vérification d\'existence: ' . ($user_exists ? 'L\'utilisateur existe' : 'L\'utilisateur n\'existe pas'));
           }
           
           // Si l'utilisateur existe
           if ($user_exists === true) {
               error_log('L\'utilisateur existe déjà');
               $this->test_data['debug_info']['user_exists'] = true;
               
               // Essayer de se connecter avec les identifiants fournis
               error_log('Tentative de connexion avec l\'utilisateur existant');
               $login_response = $this->api_client->login(
                   $user_data['username'],
                   $user_data['password']
               );
               
               $this->test_data['debug_info']['login_existing_user'] = $login_response;
               
               if (!is_wp_error($login_response) && isset($login_response['access_token'])) {
                   // Connexion réussie
                   error_log('Connexion réussie avec l\'utilisateur existant');
                   $this->test_data['user']['token'] = $login_response['access_token'];
                   $this->api_client->set_token($login_response['access_token']);
                   
                   // Récupérer les informations de l'utilisateur
                   $user_info = $this->api_client->get_current_user();
                   if (!is_wp_error($user_info) && isset($user_info['id'])) {
                       $this->test_data['user']['id'] = $user_info['id'];
                       error_log('ID de l\'utilisateur récupéré: ' . $user_info['id']);
                   }
               } else {
                   // La connexion a échoué, probablement parce que le mot de passe est incorrect
                   error_log('Connexion échouée avec l\'utilisateur existant, création d\'un nouvel utilisateur');
                   $this->test_data['debug_info']['password_mismatch'] = true;
                   
                   // Générer un nouveau nom d'utilisateur unique
                   $new_timestamp = time();
                   $user_data['username'] = 'test_user_' . $new_timestamp;
                   $user_data['email'] = 'test_' . $new_timestamp . '@example.com';
                   $user_data['password'] = 'Test@' . $new_timestamp;
                   
                   $this->test_data['user']['username'] = $user_data['username'];
                   $this->test_data['user']['email'] = $user_data['email'];
                   $this->test_data['user']['password'] = $user_data['password'];
                   
                   error_log('Nouvel utilisateur: ' . $user_data['username']);
                   error_log('Nouvel email: ' . $user_data['email']);
                   error_log('Nouveau mot de passe: ' . $user_data['password']);
                   
                   // Enregistrer le nouvel utilisateur
                   error_log('Enregistrement du nouvel utilisateur');
                   $register_response = $this->api_client->register(
                       $user_data['username'],
                       $user_data['email'],
                       $user_data['password']
                   );
                   
                   $this->test_data['debug_info']['register_new_user'] = $register_response;
                   
                   if (!is_wp_error($register_response) && isset($register_response['id'])) {
                       // Enregistrement réussi
                       error_log('Enregistrement réussi, ID: ' . $register_response['id']);
                       $this->test_data['user']['id'] = $register_response['id'];
                       
                       // Attendre un court instant pour s'assurer que l'utilisateur est bien enregistré
                       error_log('Attente de 2 secondes pour s\'assurer que l\'utilisateur est bien enregistré');
                       sleep(2);
                       
                       // Se connecter pour obtenir un token
                       error_log('Tentative de connexion après enregistrement');
                       $login_after_register = $this->api_client->login(
                           $user_data['username'],
                           $user_data['password']
                       );
                       
                       $this->test_data['debug_info']['login_after_register'] = $login_after_register;
                       
                       if (!is_wp_error($login_after_register) && isset($login_after_register['access_token'])) {
                           error_log('Connexion réussie après enregistrement');
                           $this->test_data['user']['token'] = $login_after_register['access_token'];
                           $this->api_client->set_token($login_after_register['access_token']);
                       } else {
                           error_log('Échec de connexion après enregistrement: ' .
                               (is_wp_error($login_after_register) ? $login_after_register->get_error_message() : 'Pas de token dans la réponse'));
                       }
                   } else {
                       error_log('Échec de l\'enregistrement: ' .
                           (is_wp_error($register_response) ? $register_response->get_error_message() : 'Réponse invalide'));
                   }
               }
           } else {
               // L'utilisateur n'existe pas, l'enregistrer
               error_log('L\'utilisateur n\'existe pas, enregistrement d\'un nouvel utilisateur');
               $this->test_data['debug_info']['user_exists'] = false;
               
               $register_response = $this->api_client->register(
                   $user_data['username'],
                   $user_data['email'],
                   $user_data['password']
               );
               
               $this->test_data['debug_info']['register_response'] = $register_response;
               
               if (!is_wp_error($register_response) && isset($register_response['id'])) {
                   // Enregistrement réussi
                   error_log('Enregistrement réussi, ID: ' . $register_response['id']);
                   $this->test_data['user']['id'] = $register_response['id'];
                   
                   // Attendre un court instant pour s'assurer que l'utilisateur est bien enregistré
                   error_log('Attente de 2 secondes pour s\'assurer que l\'utilisateur est bien enregistré');
                   sleep(2);
                   
                   // Se connecter pour obtenir un token
                   error_log('Tentative de connexion après enregistrement');
                   $login_after_register = $this->api_client->login(
                       $user_data['username'],
                       $user_data['password']
                   );
                   
                   $this->test_data['debug_info']['login_after_register'] = $login_after_register;
                   
                   if (!is_wp_error($login_after_register) && isset($login_after_register['access_token'])) {
                       error_log('Connexion réussie après enregistrement');
                       $this->test_data['user']['token'] = $login_after_register['access_token'];
                       $this->api_client->set_token($login_after_register['access_token']);
                   } else {
                       error_log('Échec de connexion après enregistrement: ' .
                           (is_wp_error($login_after_register) ? $login_after_register->get_error_message() : 'Pas de token dans la réponse'));
                   }
               } else {
                   error_log('Échec de l\'enregistrement: ' .
                       (is_wp_error($register_response) ? $register_response->get_error_message() : 'Réponse invalide'));
               }
           }
           
           // Enregistrer les données de test mises à jour
           update_option('rpg_ia_test_data', $this->test_data);
           error_log('Données de test mises à jour et enregistrées');
           error_log('=== FIN DU TEST D\'AUTHENTIFICATION ===');
            
            // Mettre à jour les données de test avec les informations de débogage
            update_option('rpg_ia_test_data', $this->test_data);
        } catch (Exception $e) {
            // Enregistrer l'erreur dans les informations de débogage
            $this->test_data['debug_info']['create_user_exception'] = $e->getMessage();
            update_option('rpg_ia_test_data', $this->test_data);
        }
        
        // Créer un personnage de test
        if ($this->test_data['user']['token']) {
            try {
                $character_data = array(
                    'name' => $this->test_data['character']['name'],
                    'class' => $this->test_data['character']['class'],
                    'level' => $this->test_data['character']['level'],
                    'attributes' => array(
                        'strength' => 10,
                        'dexterity' => 10,
                        'constitution' => 10,
                        'intelligence' => 10,
                        'wisdom' => 10,
                        'charisma' => 10
                    )
                );
                
                $response = $this->api_client->create_character($character_data);
                
                if (!is_wp_error($response) && isset($response['id'])) {
                    $this->test_data['character']['id'] = $response['id'];
                }
            } catch (Exception $e) {
                // Ignorer les erreurs, le test de gestion des personnages les détectera
            }
        }
        
        // Créer un scénario de test
        if ($this->test_data['user']['token']) {
            try {
                $scenario_data = array(
                    'name' => $this->test_data['scenario']['name'],
                    'description' => $this->test_data['scenario']['description']
                );
                
                $response = $this->api_client->create_scenario($scenario_data);
                
                if (!is_wp_error($response) && isset($response['id'])) {
                    $this->test_data['scenario']['id'] = $response['id'];
                }
            } catch (Exception $e) {
                // Ignorer les erreurs
            }
        }
        
        // Créer une session de test
        if ($this->test_data['user']['token'] && $this->test_data['scenario']['id']) {
            try {
                $session_data = array(
                    'name' => $this->test_data['session']['name'],
                    'description' => $this->test_data['session']['description'],
                    'scenario_id' => $this->test_data['scenario']['id']
                );
                
                $response = $this->api_client->create_game_session($session_data);
                
                if (!is_wp_error($response) && isset($response['id'])) {
                    $this->test_data['session']['id'] = $response['id'];
                }
            } catch (Exception $e) {
                // Ignorer les erreurs, le test de gestion des sessions les détectera
            }
        }
        
        // Mettre à jour les données de test
        update_option('rpg_ia_test_data', $this->test_data);
    }
    
    /**
     * Teste le chargement des classes.
     *
     * @since    1.0.0
     */
    private function test_classes_loading() {
        // Test des fonctionnalités d'authentification (chargement de classe uniquement)
        $test_name = 'auth_class_loading';
        $test_description = __('Test de chargement de la classe d\'authentification', 'rpg-ia');
        
        try {
            if (class_exists('RPG_IA_Auth_Handler')) {
                $this->add_test_result($test_name, $test_description, true, __('Classe d\'authentification chargée avec succès', 'rpg-ia'));
            } else {
                $this->add_test_result($test_name, $test_description, false, __('La classe d\'authentification n\'existe pas', 'rpg-ia'));
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, $test_description, false, $e->getMessage());
        }
        
        // Test des fonctionnalités de gestion des personnages (chargement de classe uniquement)
        $test_name = 'character_class_loading';
        $test_description = __('Test de chargement de la classe de gestion des personnages', 'rpg-ia');
        
        try {
            if (class_exists('RPG_IA_Character_Manager')) {
                $this->add_test_result($test_name, $test_description, true, __('Classe de gestion des personnages chargée avec succès', 'rpg-ia'));
            } else {
                $this->add_test_result($test_name, $test_description, false, __('La classe de gestion des personnages n\'existe pas', 'rpg-ia'));
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, $test_description, false, $e->getMessage());
        }
        
        // Test des fonctionnalités de gestion des sessions (chargement de classe uniquement)
        $test_name = 'session_class_loading';
        $test_description = __('Test de chargement de la classe de gestion des sessions', 'rpg-ia');
        
        try {
            if (class_exists('RPG_IA_Session_Manager')) {
                $this->add_test_result($test_name, $test_description, true, __('Classe de gestion des sessions chargée avec succès', 'rpg-ia'));
            } else {
                $this->add_test_result($test_name, $test_description, false, __('La classe de gestion des sessions n\'existe pas', 'rpg-ia'));
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, $test_description, false, $e->getMessage());
        }
        
        // Test des fonctionnalités de l'interface de jeu (chargement de classe uniquement)
        $test_name = 'game_interface_class_loading';
        $test_description = __('Test de chargement de la classe de l\'interface de jeu', 'rpg-ia');
        
        try {
            if (class_exists('RPG_IA_Game_Interface')) {
                $this->add_test_result($test_name, $test_description, true, __('Classe de l\'interface de jeu chargée avec succès', 'rpg-ia'));
            } else {
                $this->add_test_result($test_name, $test_description, false, __('La classe de l\'interface de jeu n\'existe pas', 'rpg-ia'));
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, $test_description, false, $e->getMessage());
        }
        
        // Test des fonctionnalités d'administration (chargement de classe uniquement)
        $test_name = 'admin_class_loading';
        $test_description = __('Test de chargement de la classe d\'administration', 'rpg-ia');
        
        try {
            if (class_exists('RPG_IA_Admin')) {
                $this->add_test_result($test_name, $test_description, true, __('Classe d\'administration chargée avec succès', 'rpg-ia'));
            } else {
                $this->add_test_result($test_name, $test_description, false, __('La classe d\'administration n\'existe pas', 'rpg-ia'));
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, $test_description, false, $e->getMessage());
        }
    }
    
    /**
     * Teste la connexion à l'API.
     *
     * @since    1.0.0
     */
    private function test_api_connection() {
        $test_name = 'api_connection';
        $test_description = __('Test de connexion à l\'API', 'rpg-ia');
        
        try {
            $result = $this->api_client->check_api_status();
            
            if (is_wp_error($result)) {
                $this->add_test_result($test_name, $test_description, false, $result->get_error_message());
            } else {
                $this->add_test_result($test_name, $test_description, true, __('Connexion à l\'API réussie', 'rpg-ia') . ' - ' . __('Version', 'rpg-ia') . ': ' . $result['version']);
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, $test_description, false, $e->getMessage());
        }
    }

    /**
     * Teste les fonctionnalités d'authentification.
     *
     * @since    1.0.0
     */
    private function test_auth_functionality() {
        $test_name = 'auth_functionality';
        $test_description = __('Test des fonctionnalités d\'authentification', 'rpg-ia');
        
        error_log('=== DÉBUT DU TEST DES FONCTIONNALITÉS D\'AUTHENTIFICATION ===');
        
        // Vérifier si les données de test sont disponibles
        if (empty($this->test_data['user']) || empty($this->test_data['user']['username']) || empty($this->test_data['user']['password'])) {
            error_log('Données de test manquantes pour l\'authentification');
            $this->add_test_result($test_name, $test_description, false, __('Données de test manquantes pour l\'authentification', 'rpg-ia'));
            return;
        }
        
        error_log('Données de test disponibles:');
        error_log('Username: ' . $this->test_data['user']['username']);
        error_log('Password: ' . $this->test_data['user']['password']);
        error_log('Token existant: ' . (!empty($this->test_data['user']['token']) ? 'Oui' : 'Non'));
        
        // S'assurer que l'utilisateur de test existe
        error_log('Vérification de l\'existence de l\'utilisateur de test...');
        $this->create_test_data_in_api();
        
        // Vérifier si un token est déjà disponible après la création des données de test
        if (!empty($this->test_data['user']['token'])) {
            error_log('Token disponible, vérification de sa validité...');
            // Vérifier si le token est valide en récupérant les informations de l'utilisateur
            $this->api_client->set_token($this->test_data['user']['token']);
            $user_info = $this->api_client->get_current_user();
            
            if (!is_wp_error($user_info)) {
                error_log('Token valide, authentification réussie');
                $this->add_test_result($test_name, $test_description, true, __('Authentification réussie avec le token existant', 'rpg-ia'));
                return;
            } else {
                $error_message = $user_info->get_error_message();
                error_log('Token invalide: ' . $error_message);
                // Si le token n'est pas valide, continuer avec le test de connexion
            }
        }
        
        try {
            error_log('Tentative de connexion avec les identifiants de test...');
            // Tester la connexion avec les identifiants de test
            $response = $this->api_client->login(
                $this->test_data['user']['username'],
                $this->test_data['user']['password']
            );
            
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $error_code = $response->get_error_code();
                error_log('Échec de connexion: ' . $error_message);
                error_log('Code d\'erreur: ' . $error_code);
                
                // Si la connexion échoue, essayer de créer un nouvel utilisateur
                error_log('Tentative de création d\'un nouvel utilisateur...');
                
                // Générer un nouveau nom d'utilisateur unique
                $timestamp = time();
                $new_username = 'test_user_' . $timestamp;
                $new_email = 'test_' . $timestamp . '@example.com';
                $new_password = 'Test@' . $timestamp;
                
                error_log('Nouvel utilisateur: ' . $new_username);
                error_log('Nouvel email: ' . $new_email);
                error_log('Nouveau mot de passe: ' . $new_password);
                
                // Mettre à jour les données de test
                $this->test_data['user']['username'] = $new_username;
                $this->test_data['user']['email'] = $new_email;
                $this->test_data['user']['password'] = $new_password;
                
                // Enregistrer le nouvel utilisateur
                $register_response = $this->api_client->register(
                    $new_username,
                    $new_email,
                    $new_password
                );
                
                if (is_wp_error($register_response)) {
                    $error_message = $register_response->get_error_message();
                    error_log('Échec de création de l\'utilisateur: ' . $error_message);
                    $this->add_test_result($test_name, $test_description, false, __('Échec de connexion à l\'API et de création d\'utilisateur: ', 'rpg-ia') . $error_message);
                } else {
                    error_log('Utilisateur créé avec succès, ID: ' . $register_response['id']);
                    
                    // Attendre un court instant pour s'assurer que l'utilisateur est bien enregistré
                    error_log('Attente de 2 secondes pour s\'assurer que l\'utilisateur est bien enregistré');
                    sleep(2);
                    
                    // Tenter de se connecter avec le nouvel utilisateur
                    error_log('Tentative de connexion avec le nouvel utilisateur...');
                    $login_response = $this->api_client->login(
                        $new_username,
                        $new_password
                    );
                    
                    if (is_wp_error($login_response)) {
                        $error_message = $login_response->get_error_message();
                        error_log('Échec de connexion avec le nouvel utilisateur: ' . $error_message);
                        $this->add_test_result($test_name, $test_description, false, __('Échec de connexion avec le nouvel utilisateur: ', 'rpg-ia') . $error_message);
                    } else {
                        error_log('Connexion réussie avec le nouvel utilisateur');
                        
                        // Mettre à jour le token dans les données de test
                        $this->test_data['user']['token'] = $login_response['access_token'];
                        $this->api_client->set_token($login_response['access_token']);
                        update_option('rpg_ia_test_data', $this->test_data);
                        
                        $this->add_test_result($test_name, $test_description, true, __('Authentification réussie avec le nouvel utilisateur', 'rpg-ia'));
                    }
                }
            } else {
                error_log('Connexion réussie');
                
                // Vérifier si un token a été défini dans l'API client
                $token = $this->api_client->get_token();
                
                if (!empty($token)) {
                    error_log('Token défini dans l\'API client: ' . substr($token, 0, 10) . '...');
                    // Un token a été défini, donc l'authentification a réussi
                    // Mettre à jour le token dans les données de test
                    $this->test_data['user']['token'] = $token;
                    update_option('rpg_ia_test_data', $this->test_data);
                    
                    // Tester la récupération des informations de l'utilisateur
                    error_log('Récupération des informations de l\'utilisateur...');
                    $user_info = $this->api_client->get_current_user();
                    
                    if (is_wp_error($user_info)) {
                        $error_message = $user_info->get_error_message();
                        error_log('Échec de récupération des informations utilisateur: ' . $error_message);
                        $this->add_test_result($test_name, $test_description, false, __('Échec de récupération des informations utilisateur: ', 'rpg-ia') . $error_message);
                    } else {
                        error_log('Informations utilisateur récupérées avec succès: ' . print_r($user_info, true));
                        $this->add_test_result($test_name, $test_description, true, __('Authentification réussie et informations utilisateur récupérées', 'rpg-ia'));
                    }
                } else if (isset($response['access_token'])) {
                    error_log('Token dans la réponse mais non défini dans l\'API client: ' . substr($response['access_token'], 0, 10) . '...');
                    // Le token est dans la réponse mais n'a pas été défini dans l'API client
                    $this->test_data['user']['token'] = $response['access_token'];
                    update_option('rpg_ia_test_data', $this->test_data);
                    
                    // Mettre à jour le token dans l'API client
                    $this->api_client->set_token($response['access_token']);
                    
                    // Tester la récupération des informations de l'utilisateur
                    error_log('Récupération des informations de l\'utilisateur...');
                    $user_info = $this->api_client->get_current_user();
                    
                    if (is_wp_error($user_info)) {
                        $error_message = $user_info->get_error_message();
                        error_log('Échec de récupération des informations utilisateur: ' . $error_message);
                        $this->add_test_result($test_name, $test_description, false, __('Échec de récupération des informations utilisateur: ', 'rpg-ia') . $error_message);
                    } else {
                        error_log('Informations utilisateur récupérées avec succès: ' . print_r($user_info, true));
                        $this->add_test_result($test_name, $test_description, true, __('Authentification réussie et informations utilisateur récupérées', 'rpg-ia'));
                    }
                } else {
                    error_log('Aucun token trouvé dans la réponse');
                    // Afficher plus d'informations sur la réponse pour le débogage
                    $response_info = '';
                    if (is_array($response)) {
                        $response_info = 'Tableau: ' . print_r($response, true);
                        error_log('Réponse (tableau): ' . print_r($response, true));
                    } else if (is_object($response)) {
                        $response_info = 'Objet: ' . print_r($response, true);
                        error_log('Réponse (objet): ' . print_r($response, true));
                    } else {
                        $response_info = 'Type: ' . gettype($response) . ', Valeur: ' . print_r($response, true);
                        error_log('Réponse (autre): Type: ' . gettype($response) . ', Valeur: ' . print_r($response, true));
                    }
                    $this->add_test_result($test_name, $test_description, false, __('Réponse d\'authentification invalide. ', 'rpg-ia') . $response_info);
                }
            }
        } catch (Exception $e) {
            error_log('Exception lors du test d\'authentification: ' . $e->getMessage());
            $this->add_test_result($test_name, $test_description, false, $e->getMessage());
        }
        
        error_log('=== FIN DU TEST DES FONCTIONNALITÉS D\'AUTHENTIFICATION ===');
    }

    /**
     * Teste les fonctionnalités de gestion des personnages.
     *
     * @since    1.0.0
     */
    private function test_character_functionality() {
        $test_name = 'character_functionality';
        $test_description = __('Test des fonctionnalités de gestion des personnages', 'rpg-ia');
        
        // Vérifier si l'authentification a réussi
        if (empty($this->test_data['user']['token'])) {
            $this->add_test_result($test_name, $test_description, false, __('Authentification requise pour tester la gestion des personnages', 'rpg-ia'));
            return;
        }
        
        try {
            // Tester la récupération des personnages
            $characters = $this->api_client->get_characters();
            
            if (is_wp_error($characters)) {
                $this->add_test_result($test_name, $test_description, false, __('Échec de récupération des personnages: ', 'rpg-ia') . $characters->get_error_message());
                return;
            }
            
            // Vérifier si un personnage de test existe
            if (!empty($this->test_data['character']['id'])) {
                // Tester la récupération d'un personnage spécifique
                $character = $this->api_client->get_character($this->test_data['character']['id']);
                
                if (is_wp_error($character)) {
                    $this->add_test_result($test_name, $test_description, false, __('Échec de récupération du personnage de test: ', 'rpg-ia') . $character->get_error_message());
                } else {
                    $this->add_test_result($test_name, $test_description, true, __('Récupération des personnages réussie', 'rpg-ia'));
                }
            } else {
                // Créer un nouveau personnage de test
                $character_data = array(
                    'name' => 'Test Character ' . time(),
                    'class' => 'Warrior',
                    'level' => 1,
                    'attributes' => array(
                        'strength' => 10,
                        'dexterity' => 10,
                        'constitution' => 10,
                        'intelligence' => 10,
                        'wisdom' => 10,
                        'charisma' => 10
                    )
                );
                
                $response = $this->api_client->create_character($character_data);
                
                if (is_wp_error($response)) {
                    $this->add_test_result($test_name, $test_description, false, __('Échec de création d\'un personnage: ', 'rpg-ia') . $response->get_error_message());
                } else {
                    // Mettre à jour l'ID du personnage dans les données de test
                    $this->test_data['character']['id'] = $response['id'];
                    update_option('rpg_ia_test_data', $this->test_data);
                    
                    $this->add_test_result($test_name, $test_description, true, __('Création et récupération des personnages réussies', 'rpg-ia'));
                }
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, $test_description, false, $e->getMessage());
        }
    }

    /**
     * Teste les fonctionnalités de gestion des sessions.
     *
     * @since    1.0.0
     */
    private function test_session_functionality() {
        $test_name = 'session_functionality';
        $test_description = __('Test des fonctionnalités de gestion des sessions', 'rpg-ia');
        
        // Vérifier si l'authentification a réussi
        if (empty($this->test_data['user']['token'])) {
            $this->add_test_result($test_name, $test_description, false, __('Authentification requise pour tester la gestion des sessions', 'rpg-ia'));
            return;
        }
        
        try {
            // Tester la récupération des sessions
            $sessions = $this->api_client->get_game_sessions();
            
            if (is_wp_error($sessions)) {
                $this->add_test_result($test_name, $test_description, false, __('Échec de récupération des sessions: ', 'rpg-ia') . $sessions->get_error_message());
                return;
            }
            
            // Vérifier si une session de test existe
            if (!empty($this->test_data['session']['id'])) {
                // Tester la récupération d'une session spécifique
                $session = $this->api_client->get_game_session($this->test_data['session']['id']);
                
                if (is_wp_error($session)) {
                    $this->add_test_result($test_name, $test_description, false, __('Échec de récupération de la session de test: ', 'rpg-ia') . $session->get_error_message());
                } else {
                    $this->add_test_result($test_name, $test_description, true, __('Récupération des sessions réussie', 'rpg-ia'));
                }
            } else if (!empty($this->test_data['scenario']['id'])) {
                // Créer une nouvelle session de test
                $session_data = array(
                    'name' => 'Test Session ' . time(),
                    'description' => 'Test session created for automated tests',
                    'scenario_id' => $this->test_data['scenario']['id']
                );
                
                $response = $this->api_client->create_game_session($session_data);
                
                if (is_wp_error($response)) {
                    $this->add_test_result($test_name, $test_description, false, __('Échec de création d\'une session: ', 'rpg-ia') . $response->get_error_message());
                } else {
                    // Mettre à jour l'ID de la session dans les données de test
                    $this->test_data['session']['id'] = $response['id'];
                    update_option('rpg_ia_test_data', $this->test_data);
                    
                    $this->add_test_result($test_name, $test_description, true, __('Création et récupération des sessions réussies', 'rpg-ia'));
                }
            } else {
                $this->add_test_result($test_name, $test_description, false, __('Scénario de test requis pour créer une session', 'rpg-ia'));
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, $test_description, false, $e->getMessage());
        }
    }

    /**
     * Teste les fonctionnalités de l'interface de jeu.
     *
     * @since    1.0.0
     */
    private function test_game_interface_functionality() {
        $test_name = 'game_interface_functionality';
        $test_description = __('Test des fonctionnalités de l\'interface de jeu', 'rpg-ia');
        
        // Vérifier si l'authentification a réussi et si une session de test existe
        if (empty($this->test_data['user']['token']) || empty($this->test_data['session']['id']) || empty($this->test_data['character']['id'])) {
            $this->add_test_result($test_name, $test_description, false, __('Authentification, session et personnage requis pour tester l\'interface de jeu', 'rpg-ia'));
            return;
        }
        
        try {
            // Créer une instance de l'interface de jeu
            $game_interface = new RPG_IA_Game_Interface($this->api_client);
            
            // Tester la récupération des données de jeu
            $game_data = $game_interface->get_game_data($this->test_data['session']['id']);
            
            if (is_wp_error($game_data)) {
                $this->add_test_result($test_name, $test_description, false, __('Échec de récupération des données de jeu: ', 'rpg-ia') . $game_data->get_error_message());
                return;
            }
            
            // Tester la récupération des actions de jeu
            $game_actions = $game_interface->get_game_actions($this->test_data['session']['id']);
            
            if (is_wp_error($game_actions)) {
                $this->add_test_result($test_name, $test_description, false, __('Échec de récupération des actions de jeu: ', 'rpg-ia') . $game_actions->get_error_message());
                return;
            }
            
            // Tester la soumission d'une action de joueur
            $action_data = array(
                'session_id' => $this->test_data['session']['id'],
                'character_id' => $this->test_data['character']['id'],
                'content' => 'Test action from automated tests',
                'type' => 'dialogue'
            );
            
            $submit_result = $game_interface->submit_player_action($action_data);
            
            if (is_wp_error($submit_result)) {
                $this->add_test_result($test_name, $test_description, false, __('Échec de soumission d\'une action: ', 'rpg-ia') . $submit_result->get_error_message());
            } else {
                $this->add_test_result($test_name, $test_description, true, __('Interface de jeu testée avec succès', 'rpg-ia'));
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, $test_description, false, $e->getMessage());
        }
    }

    /**
     * Teste les fonctionnalités d'administration.
     *
     * @since    1.0.0
     */
    private function test_admin_functionality() {
        $test_name = 'admin_functionality';
        $test_description = __('Test des fonctionnalités d\'administration', 'rpg-ia');
        
        try {
            // Vérifier si la classe d'administration existe
            if (class_exists('RPG_IA_Admin')) {
                $plugin_admin = new RPG_IA_Admin('rpg-ia', RPG_IA_VERSION);
                
                // Vérifier si les méthodes principales existent
                if (method_exists($plugin_admin, 'enqueue_styles') &&
                    method_exists($plugin_admin, 'enqueue_scripts') &&
                    method_exists($plugin_admin, 'add_admin_menu') &&
                    method_exists($plugin_admin, 'register_settings')) {
                    
                    // Vérifier les options du plugin
                    $api_url = get_option('rpg_ia_api_url');
                    $max_players = get_option('rpg_ia_max_players');
                    $update_interval = get_option('rpg_ia_update_interval');
                    $enable_chat = get_option('rpg_ia_enable_chat');
                    
                    if ($api_url && $max_players && $update_interval && $enable_chat) {
                        $this->add_test_result($test_name, $test_description, true, __('Fonctionnalités d\'administration testées avec succès', 'rpg-ia'));
                    } else {
                        $this->add_test_result($test_name, $test_description, false, __('Certaines options du plugin sont manquantes', 'rpg-ia'));
                    }
                } else {
                    $this->add_test_result($test_name, $test_description, false, __('Certaines méthodes d\'administration sont manquantes', 'rpg-ia'));
                }
            } else {
                $this->add_test_result($test_name, $test_description, false, __('La classe d\'administration n\'existe pas', 'rpg-ia'));
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, $test_description, false, $e->getMessage());
        }
    }

    /**
     * Ajoute un résultat de test.
     *
     * @since    1.0.0
     * @param    string    $name           Le nom du test.
     * @param    string    $description    La description du test.
     * @param    bool      $success        Le résultat du test (true = succès, false = échec).
     * @param    string    $message        Le message du test.
     */
    private function add_test_result($name, $description, $success, $message) {
        $this->results[] = array(
            'name' => $name,
            'description' => $description,
            'success' => $success,
            'message' => $message,
            'timestamp' => current_time('mysql')
        );
    }

    /**
     * Enregistre les résultats des tests dans la base de données.
     *
     * @since    1.0.0
     */
    private function save_test_results() {
        update_option('rpg_ia_test_results', $this->results);
        update_option('rpg_ia_last_test_run', current_time('mysql'));
    }

    /**
     * Récupère les résultats des tests.
     *
     * @since    1.0.0
     * @return   array    Les résultats des tests.
     */
    public static function get_test_results() {
        return get_option('rpg_ia_test_results', array());
    }

    /**
     * Récupère la date du dernier test.
     *
     * @since    1.0.0
     * @return   string    La date du dernier test.
     */
    public static function get_last_test_run() {
        return get_option('rpg_ia_last_test_run', '');
    }

    /**
     * Vérifie si tous les tests ont réussi.
     *
     * @since    1.0.0
     * @return   bool    True si tous les tests ont réussi, false sinon.
     */
    public static function all_tests_passed() {
        $results = self::get_test_results();
        
        if (empty($results)) {
            return false;
        }
        
        foreach ($results as $result) {
            if (!$result['success']) {
                return false;
            }
        }
        
        return true;
    }
}