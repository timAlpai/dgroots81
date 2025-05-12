<?php
/**
 * Définit la fonctionnalité d'internationalisation
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 */

/**
 * Définit la fonctionnalité d'internationalisation.
 *
 * Charge le domaine de texte du plugin pour la traduction.
 *
 * @since      1.0.0
 * @package    RPG_IA
 * @subpackage RPG_IA/includes
 * @author     RPG-IA Team
 */
class RPG_IA_i18n {

    /**
     * Le domaine de texte du plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $domain    Le domaine de texte du plugin.
     */
    private $domain;

    /**
     * Charge le domaine de texte du plugin pour la traduction.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            $this->domain,
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

    /**
     * Définit le domaine de texte du plugin.
     *
     * @since    1.0.0
     * @param    string    $domain    Le domaine de texte du plugin.
     */
    public function set_domain($domain) {
        $this->domain = $domain;
    }
}