<?php
/**
 * Index des widgets du plugin RPG-IA
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes/widgets
 */

// Si ce fichier est appelé directement, on sort
if (!defined('WPINC')) {
    die;
}

// Charger les classes de widgets
require_once plugin_dir_path(__FILE__) . 'class-rpg-ia-sessions-widget.php';
require_once plugin_dir_path(__FILE__) . 'class-rpg-ia-characters-widget.php';
require_once plugin_dir_path(__FILE__) . 'class-rpg-ia-stats-widget.php';