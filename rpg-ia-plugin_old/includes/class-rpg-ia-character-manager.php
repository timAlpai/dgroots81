<?php
/**
 * Classe responsable de la gestion des personnages
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Classe responsable de la gestion des personnages.
 *
 * Cette classe gère toutes les opérations liées aux personnages:
 * - Création et édition de personnages
 * - Récupération des personnages
 * - Suppression des personnages
 * - Synchronisation avec l'API backend
 *
 * @since      1.0.0
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_Character_Manager {

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
     * Récupère tous les personnages de l'utilisateur courant.
     *
     * @since    1.0.0
     * @return   array|WP_Error    La liste des personnages ou une erreur.
     */
    public function get_characters() {
        return $this->api_client->get_characters();
    }

    /**
     * Récupère un personnage spécifique.
     *
     * @since    1.0.0
     * @param    int       $character_id    L'ID du personnage.
     * @return   array|WP_Error             Les données du personnage ou une erreur.
     */
    public function get_character($character_id) {
        return $this->api_client->get_character($character_id);
    }

    /**
     * Crée un nouveau personnage.
     *
     * @since    1.0.0
     * @param    array     $data    Les données du personnage.
     * @return   array|WP_Error     Les données du personnage créé ou une erreur.
     */
    public function create_character($data) {
        return $this->api_client->create_character($data);
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
        return $this->api_client->update_character($character_id, $data);
    }

    /**
     * Supprime un personnage.
     *
     * @since    1.0.0
     * @param    int       $character_id    L'ID du personnage.
     * @return   array|WP_Error             La réponse de l'API ou une erreur.
     */
    public function delete_character($character_id) {
        return $this->api_client->delete_character($character_id);
    }

    /**
     * Génère un personnage aléatoire selon les règles OSE.
     *
     * @since    1.0.0
     * @param    string    $name             Le nom du personnage.
     * @param    string    $character_class  La classe du personnage.
     * @return   array                       Les données du personnage généré.
     */
    public function generate_random_character($name, $character_class) {
        // Générer des caractéristiques aléatoires (3d6 pour chaque)
        $strength = $this->roll_3d6();
        $intelligence = $this->roll_3d6();
        $wisdom = $this->roll_3d6();
        $dexterity = $this->roll_3d6();
        $constitution = $this->roll_3d6();
        $charisma = $this->roll_3d6();
        
        // Calculer les points de vie selon la classe (d8 pour guerrier, d6 pour voleur, etc.)
        $max_hp = $this->calculate_starting_hp($character_class, $constitution);
        
        // Créer le personnage
        $character = array(
            'name' => $name,
            'character_class' => $character_class,
            'level' => 1,
            'experience' => 0,
            'strength' => $strength,
            'intelligence' => $intelligence,
            'wisdom' => $wisdom,
            'dexterity' => $dexterity,
            'constitution' => $constitution,
            'charisma' => $charisma,
            'max_hp' => $max_hp,
            'current_hp' => $max_hp,
            'armor_class' => 10 + $this->calculate_dexterity_modifier($dexterity),
            'equipment' => $this->get_starting_equipment($character_class),
            'inventory' => array(),
            'gold' => $this->roll_starting_gold(),
            'skills' => $this->get_class_skills($character_class),
            'spells' => $this->get_starting_spells($character_class),
            'background' => '',
            'appearance' => '',
            'is_alive' => true
        );
        
        return $character;
    }
    
    /**
     * Lance 3d6 pour générer une caractéristique.
     *
     * @since    1.0.0
     * @return   int    Le résultat du lancer de dés.
     */
    private function roll_3d6() {
        return rand(1, 6) + rand(1, 6) + rand(1, 6);
    }
    
    /**
     * Calcule les points de vie de départ selon la classe.
     *
     * @since    1.0.0
     * @param    string    $character_class    La classe du personnage.
     * @param    int       $constitution       La constitution du personnage.
     * @return   int                           Les points de vie de départ.
     */
    private function calculate_starting_hp($character_class, $constitution) {
        $con_modifier = $this->calculate_constitution_modifier($constitution);
        
        switch ($character_class) {
            case 'guerrier':
            case 'nain':
                $base_hp = rand(1, 8); // d8
                break;
            case 'clerc':
            case 'elfe':
            case 'halfelin':
                $base_hp = rand(1, 6); // d6
                break;
            case 'magicien':
                $base_hp = rand(1, 4); // d4
                break;
            case 'voleur':
                $base_hp = rand(1, 4); // d4
                break;
            default:
                $base_hp = rand(1, 6); // d6 par défaut
        }
        
        // Appliquer le modificateur de constitution (minimum 1 PV)
        return max(1, $base_hp + $con_modifier);
    }
    
    /**
     * Calcule le modificateur de dextérité selon les règles OSE.
     *
     * @since    1.0.0
     * @param    int    $dexterity    La dextérité du personnage.
     * @return   int                  Le modificateur de dextérité.
     */
    private function calculate_dexterity_modifier($dexterity) {
        if ($dexterity <= 3) {
            return -3;
        } elseif ($dexterity <= 5) {
            return -2;
        } elseif ($dexterity <= 8) {
            return -1;
        } elseif ($dexterity <= 12) {
            return 0;
        } elseif ($dexterity <= 15) {
            return 1;
        } elseif ($dexterity <= 17) {
            return 2;
        } else {
            return 3;
        }
    }
    
    /**
     * Calcule le modificateur de constitution selon les règles OSE.
     *
     * @since    1.0.0
     * @param    int    $constitution    La constitution du personnage.
     * @return   int                     Le modificateur de constitution.
     */
    private function calculate_constitution_modifier($constitution) {
        if ($constitution <= 3) {
            return -3;
        } elseif ($constitution <= 5) {
            return -2;
        } elseif ($constitution <= 8) {
            return -1;
        } elseif ($constitution <= 12) {
            return 0;
        } elseif ($constitution <= 15) {
            return 1;
        } elseif ($constitution <= 17) {
            return 2;
        } else {
            return 3;
        }
    }
    
    /**
     * Génère l'or de départ (3d6 x 10).
     *
     * @since    1.0.0
     * @return   int    L'or de départ.
     */
    private function roll_starting_gold() {
        return $this->roll_3d6() * 10;
    }
    
    /**
     * Récupère l'équipement de départ selon la classe.
     *
     * @since    1.0.0
     * @param    string    $character_class    La classe du personnage.
     * @return   array                         L'équipement de départ.
     */
    private function get_starting_equipment($character_class) {
        $common_equipment = array(
            'Sac à dos',
            'Torche (3)',
            'Silex et amorce',
            'Rations (3 jours)'
        );
        
        switch ($character_class) {
            case 'guerrier':
                return array_merge($common_equipment, array(
                    'Épée longue',
                    'Bouclier',
                    'Cotte de mailles',
                    'Dague'
                ));
            case 'clerc':
                return array_merge($common_equipment, array(
                    'Masse',
                    'Bouclier',
                    'Cotte de mailles',
                    'Symbole sacré'
                ));
            case 'magicien':
                return array_merge($common_equipment, array(
                    'Dague',
                    'Bâton',
                    'Grimoire',
                    'Composantes de sorts'
                ));
            case 'voleur':
                return array_merge($common_equipment, array(
                    'Épée courte',
                    'Armure de cuir',
                    'Outils de crochetage',
                    'Corde (15m)'
                ));
            case 'nain':
                return array_merge($common_equipment, array(
                    'Hache de bataille',
                    'Bouclier',
                    'Cotte de mailles',
                    'Pioche de mineur'
                ));
            case 'elfe':
                return array_merge($common_equipment, array(
                    'Épée longue',
                    'Arc long',
                    'Flèches (20)',
                    'Cotte de mailles',
                    'Grimoire'
                ));
            case 'halfelin':
                return array_merge($common_equipment, array(
                    'Épée courte',
                    'Fronde',
                    'Billes (20)',
                    'Armure de cuir',
                    'Pipe et tabac'
                ));
            default:
                return $common_equipment;
        }
    }
    
    /**
     * Récupère les compétences de classe.
     *
     * @since    1.0.0
     * @param    string    $character_class    La classe du personnage.
     * @return   array                         Les compétences de classe.
     */
    private function get_class_skills($character_class) {
        switch ($character_class) {
            case 'guerrier':
                return array(
                    'Combat à l\'épée',
                    'Tactique militaire'
                );
            case 'clerc':
                return array(
                    'Connaissance religieuse',
                    'Premiers soins'
                );
            case 'magicien':
                return array(
                    'Connaissance des arcanes',
                    'Identification des objets magiques'
                );
            case 'voleur':
                return array(
                    'Crochetage',
                    'Désamorçage de pièges',
                    'Escalade',
                    'Déplacement silencieux',
                    'Pickpocket'
                );
            case 'nain':
                return array(
                    'Détection des passages secrets',
                    'Évaluation des trésors',
                    'Résistance à la magie'
                );
            case 'elfe':
                return array(
                    'Détection des portes secrètes',
                    'Immunité au paralysie des goules',
                    'Vision dans le noir'
                );
            case 'halfelin':
                return array(
                    'Discrétion',
                    'Tir précis',
                    'Initiative améliorée'
                );
            default:
                return array();
        }
    }
    
    /**
     * Récupère les sorts de départ selon la classe.
     *
     * @since    1.0.0
     * @param    string    $character_class    La classe du personnage.
     * @return   array                         Les sorts de départ.
     */
    private function get_starting_spells($character_class) {
        switch ($character_class) {
            case 'magicien':
                // Choisir un sort aléatoire de niveau 1
                $level1_spells = array(
                    'Charme-personne',
                    'Détection de la magie',
                    'Lecture de la magie',
                    'Lumière',
                    'Projectile magique',
                    'Protection contre le mal',
                    'Bouclier',
                    'Sommeil'
                );
                return array(
                    $level1_spells[array_rand($level1_spells)]
                );
            case 'elfe':
                // Choisir un sort aléatoire de niveau 1
                $level1_spells = array(
                    'Charme-personne',
                    'Détection de la magie',
                    'Lumière',
                    'Protection contre le mal',
                    'Sommeil'
                );
                return array(
                    $level1_spells[array_rand($level1_spells)]
                );
            case 'clerc':
                // Les clercs de niveau 1 n'ont pas de sorts
                return array();
            default:
                return array();
        }
    }
    
    /**
     * Valide les données d'un personnage.
     *
     * @since    1.0.0
     * @param    array     $data    Les données du personnage.
     * @return   bool|WP_Error      True si les données sont valides, une erreur sinon.
     */
    public function validate_character_data($data) {
        // Vérifier les champs obligatoires
        $required_fields = array(
            'name',
            'character_class',
            'strength',
            'intelligence',
            'wisdom',
            'dexterity',
            'constitution',
            'charisma',
            'max_hp',
            'current_hp'
        );
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return new WP_Error('missing_field', sprintf(__('Le champ %s est obligatoire.', 'rpg-ia'), $field));
            }
        }
        
        // Vérifier que la classe est valide
        $valid_classes = array('clerc', 'guerrier', 'magicien', 'voleur', 'nain', 'elfe', 'halfelin');
        if (!in_array($data['character_class'], $valid_classes)) {
            return new WP_Error('invalid_class', __('La classe du personnage n\'est pas valide.', 'rpg-ia'));
        }
        
        // Vérifier que les caractéristiques sont dans les limites (3-18)
        $abilities = array('strength', 'intelligence', 'wisdom', 'dexterity', 'constitution', 'charisma');
        foreach ($abilities as $ability) {
            if ($data[$ability] < 3 || $data[$ability] > 18) {
                return new WP_Error('invalid_ability', sprintf(__('La caractéristique %s doit être comprise entre 3 et 18.', 'rpg-ia'), $ability));
            }
        }
        
        // Vérifier que les points de vie sont positifs
        if ($data['max_hp'] <= 0) {
            return new WP_Error('invalid_hp', __('Les points de vie maximum doivent être positifs.', 'rpg-ia'));
        }
        
        if ($data['current_hp'] < 0) {
            return new WP_Error('invalid_hp', __('Les points de vie actuels ne peuvent pas être négatifs.', 'rpg-ia'));
        }
        
        return true;
    }
}