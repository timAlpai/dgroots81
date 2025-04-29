<?php
/**
 * Plugin Name: RPG-IA
 * Plugin URI: https://dgroots81.mandragore.ai/
 * Description: Interface utilisateur pour le jeu de rôle en ligne avec IA comme maître de jeu
 * Version: 1.0.0
 * Author: RPG-IA Team
 * Author URI: https://dgroots81.mandragore.ai/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: rpg-ia
 * Domain Path: /languages
 */

// Si ce fichier est appelé directement, on sort
if (!defined('WPINC')) {
    die;
}

/**
 * Version actuelle du plugin.
 */
define('RPG_IA_VERSION', '1.0.0');

/**
 * Chemin du répertoire du plugin.
 */
define('RPG_IA_PATH', plugin_dir_path(__FILE__));

/**
 * URL du répertoire du plugin.
 */
define('RPG_IA_URL', plugin_dir_url(__FILE__));

/**
 * URL de l'API backend.
 */
define('RPG_IA_API_URL', get_option('rpg_ia_api_url', 'http://localhost:8000'));

/**
 * Fonction exécutée lors de l'activation du plugin.
 */
function activate_rpg_ia() {
    require_once RPG_IA_PATH . 'includes/class-rpg-ia-activator.php';
    RPG_IA_Activator::activate();
}

/**
 * Fonction exécutée lors de la désactivation du plugin.
 */
function deactivate_rpg_ia() {
    require_once RPG_IA_PATH . 'includes/class-rpg-ia-deactivator.php';
    RPG_IA_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_rpg_ia');
register_deactivation_hook(__FILE__, 'deactivate_rpg_ia');

/**
 * Inclut la classe principale du plugin.
 */
require_once RPG_IA_PATH . 'includes/class-rpg-ia-plugin.php';

/**
 * Démarre l'exécution du plugin.
 */
function run_rpg_ia() {
    $plugin = new RPG_IA_Plugin();
    $plugin->run();
}

run_rpg_ia();