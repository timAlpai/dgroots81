<?php
/**
 * Enregistre tous les hooks du plugin
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Enregistre tous les hooks du plugin.
 *
 * Maintient une liste de tous les hooks qui sont enregistrés dans WordPress
 * et les enregistre avec la API WordPress. Appelle la fonction run pour exécuter
 * la liste des actions et filtres.
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_Loader {

    /**
     * Le tableau des actions enregistrées avec WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    Les actions enregistrées avec WordPress pour être exécutées.
     */
    protected $actions;

    /**
     * Le tableau des filtres enregistrés avec WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    Les filtres enregistrés avec WordPress pour être exécutés.
     */
    protected $filters;

    /**
     * Initialise les collections utilisées pour maintenir les actions et filtres.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * Ajoute une nouvelle action au tableau des actions à enregistrer avec WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             Le nom de l'action WordPress qui est enregistrée.
     * @param    object               $component        Une référence à l'instance de l'objet sur lequel l'action est définie.
     * @param    string               $callback         Le nom de la fonction définie sur $component.
     * @param    int                  $priority         Optionnel. La priorité à laquelle l'action doit être exécutée. Par défaut 10.
     * @param    int                  $accepted_args    Optionnel. Le nombre d'arguments que la fonction accepte. Par défaut 1.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Ajoute un nouveau filtre au tableau des filtres à enregistrer avec WordPress.
     *
     * @since    1.0.0
     * @param    string               $hook             Le nom du filtre WordPress qui est enregistré.
     * @param    object               $component        Une référence à l'instance de l'objet sur lequel le filtre est défini.
     * @param    string               $callback         Le nom de la fonction définie sur $component.
     * @param    int                  $priority         Optionnel. La priorité à laquelle le filtre doit être exécuté. Par défaut 10.
     * @param    int                  $accepted_args    Optionnel. Le nombre d'arguments que la fonction accepte. Par défaut 1.
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Fonction utilitaire qui est utilisée pour enregistrer les actions et les hooks dans une collection.
     *
     * @since    1.0.0
     * @access   private
     * @param    array                $hooks            La collection de hooks à enregistrer.
     * @param    string               $hook             Le nom du filtre WordPress qui est enregistré.
     * @param    object               $component        Une référence à l'instance de l'objet sur lequel le filtre est défini.
     * @param    string               $callback         Le nom de la fonction définie sur $component.
     * @param    int                  $priority         La priorité à laquelle le filtre doit être exécuté.
     * @param    int                  $accepted_args    Le nombre d'arguments que la fonction accepte.
     * @return   array                                  La collection de hooks qui a été enregistrée.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Enregistre les filtres et actions avec WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }
}