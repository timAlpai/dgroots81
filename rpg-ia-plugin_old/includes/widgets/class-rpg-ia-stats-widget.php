<?php
/**
 * Widget pour afficher les statistiques de jeu
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes/widgets
 */

/**
 * Widget pour afficher les statistiques de jeu.
 *
 * Ce widget affiche diverses statistiques liées aux activités de jeu de l'utilisateur.
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes/widgets
 * @author     RPG-IA Team
 */
class RPG_IA_Stats_Widget extends WP_Widget {

    /**
     * Initialise le widget.
     *
     * @since    1.0.0
     */
    public function __construct() {
        parent::__construct(
            'rpgia_stats_widget', // ID de base
            __('RPG-IA Stats', 'rpg-ia'), // Nom
            array(
                'description' => __('Affiche les statistiques de jeu', 'rpg-ia'),
                'classname' => 'rpgia-stats-widget',
            )
        );
    }

    /**
     * Affiche le contenu du widget dans le front-end.
     *
     * @since    1.0.0
     * @param    array    $args        Arguments du widget.
     * @param    array    $instance    Valeurs sauvegardées.
     */
    public function widget($args, $instance) {
        // Récupérer les arguments du widget
        echo $args['before_widget'];
        
        // Afficher le titre du widget
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        // Vérifier si l'utilisateur est connecté
        if (!is_user_logged_in()) {
            echo '<p>' . __('Veuillez vous connecter pour voir vos statistiques.', 'rpg-ia') . '</p>';
            echo $args['after_widget'];
            return;
        }
        
        // Récupérer l'utilisateur courant
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        
        // Récupérer les statistiques de l'utilisateur
        $stats = $this->get_user_stats($user_id, $instance);
        
        // Afficher les statistiques
        echo '<div class="rpgia-stats-container">';
        
        // Afficher les statistiques sélectionnées
        foreach ($stats as $key => $stat) {
            if (!empty($instance['show_' . $key]) && $instance['show_' . $key] == 1) {
                echo '<div class="rpgia-stat-item">';
                echo '<span class="rpgia-stat-label">' . esc_html($stat['label']) . ':</span> ';
                echo '<span class="rpgia-stat-value">' . esc_html($stat['value']) . '</span>';
                echo '</div>';
            }
        }
        
        echo '</div>';
        
        // Afficher le lien vers le tableau de bord
        if (!empty($instance['show_dashboard_link']) && $instance['show_dashboard_link'] == 1) {
            $dashboard_url = get_permalink(get_option('rpg_ia_page_rpg-ia-dashboard'));
            echo '<p class="rpgia-stats-dashboard"><a href="' . esc_url($dashboard_url) . '">' . __('Voir le tableau de bord complet', 'rpg-ia') . '</a></p>';
        }
        
        echo $args['after_widget'];
    }

    /**
     * Récupère les statistiques de l'utilisateur.
     *
     * @since    1.0.0
     * @param    int      $user_id     ID de l'utilisateur.
     * @param    array    $instance    Valeurs sauvegardées.
     * @return   array                 Statistiques de l'utilisateur.
     */
    private function get_user_stats($user_id, $instance) {
        $stats = array();
        
        // Récupérer le nombre de personnages
        $character_manager = new RPG_IA_Character_Manager();
        $characters = $character_manager->get_characters();
        $characters_count = is_wp_error($characters) ? 0 : count($characters);
        $stats['characters'] = array(
            'label' => __('Personnages', 'rpg-ia'),
            'value' => $characters_count
        );
        
        // Récupérer le nombre de sessions
        $session_manager = new RPG_IA_Session_Manager();
        $sessions = $session_manager->get_sessions();
        $sessions_count = is_wp_error($sessions) ? 0 : count($sessions);
        $stats['sessions'] = array(
            'label' => __('Sessions', 'rpg-ia'),
            'value' => $sessions_count
        );
        
        // Récupérer le nombre de sessions actives
        $active_sessions = $session_manager->get_sessions(array('status' => 'active'));
        $active_sessions_count = is_wp_error($active_sessions) ? 0 : count($active_sessions);
        $stats['active_sessions'] = array(
            'label' => __('Sessions actives', 'rpg-ia'),
            'value' => $active_sessions_count
        );
        
        // Récupérer le nombre total d'actions
        $actions_count = get_user_meta($user_id, 'rpgia_actions_count', true);
        $actions_count = !empty($actions_count) ? intval($actions_count) : 0;
        $stats['actions'] = array(
            'label' => __('Actions totales', 'rpg-ia'),
            'value' => $actions_count
        );
        
        // Récupérer le temps de jeu total (en heures)
        $playtime = get_user_meta($user_id, 'rpgia_playtime', true);
        $playtime = !empty($playtime) ? intval($playtime) : 0;
        $playtime_hours = round($playtime / 60, 1); // Convertir les minutes en heures
        $stats['playtime'] = array(
            'label' => __('Temps de jeu', 'rpg-ia'),
            'value' => $playtime_hours . ' ' . __('heures', 'rpg-ia')
        );
        
        // Récupérer la date de dernière activité
        $last_activity = get_user_meta($user_id, 'rpgia_last_activity', true);
        if (!empty($last_activity)) {
            $last_activity_formatted = date_i18n(get_option('date_format'), strtotime($last_activity));
        } else {
            $last_activity_formatted = __('Jamais', 'rpg-ia');
        }
        $stats['last_activity'] = array(
            'label' => __('Dernière activité', 'rpg-ia'),
            'value' => $last_activity_formatted
        );
        
        return $stats;
    }

    /**
     * Affiche le formulaire de paramètres dans l'administration.
     *
     * @since    1.0.0
     * @param    array    $instance    Valeurs sauvegardées.
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Mes Statistiques', 'rpg-ia');
        $show_characters = !empty($instance['show_characters']) ? 1 : 0;
        $show_sessions = !empty($instance['show_sessions']) ? 1 : 0;
        $show_active_sessions = !empty($instance['show_active_sessions']) ? 1 : 0;
        $show_actions = !empty($instance['show_actions']) ? 1 : 0;
        $show_playtime = !empty($instance['show_playtime']) ? 1 : 0;
        $show_last_activity = !empty($instance['show_last_activity']) ? 1 : 0;
        $show_dashboard_link = !empty($instance['show_dashboard_link']) ? 1 : 0;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Titre:', 'rpg-ia'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p><?php _e('Statistiques à afficher:', 'rpg-ia'); ?></p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_characters, 1); ?> id="<?php echo esc_attr($this->get_field_id('show_characters')); ?>" name="<?php echo esc_attr($this->get_field_name('show_characters')); ?>" value="1">
            <label for="<?php echo esc_attr($this->get_field_id('show_characters')); ?>"><?php _e('Nombre de personnages', 'rpg-ia'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_sessions, 1); ?> id="<?php echo esc_attr($this->get_field_id('show_sessions')); ?>" name="<?php echo esc_attr($this->get_field_name('show_sessions')); ?>" value="1">
            <label for="<?php echo esc_attr($this->get_field_id('show_sessions')); ?>"><?php _e('Nombre de sessions', 'rpg-ia'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_active_sessions, 1); ?> id="<?php echo esc_attr($this->get_field_id('show_active_sessions')); ?>" name="<?php echo esc_attr($this->get_field_name('show_active_sessions')); ?>" value="1">
            <label for="<?php echo esc_attr($this->get_field_id('show_active_sessions')); ?>"><?php _e('Nombre de sessions actives', 'rpg-ia'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_actions, 1); ?> id="<?php echo esc_attr($this->get_field_id('show_actions')); ?>" name="<?php echo esc_attr($this->get_field_name('show_actions')); ?>" value="1">
            <label for="<?php echo esc_attr($this->get_field_id('show_actions')); ?>"><?php _e('Nombre total d\'actions', 'rpg-ia'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_playtime, 1); ?> id="<?php echo esc_attr($this->get_field_id('show_playtime')); ?>" name="<?php echo esc_attr($this->get_field_name('show_playtime')); ?>" value="1">
            <label for="<?php echo esc_attr($this->get_field_id('show_playtime')); ?>"><?php _e('Temps de jeu total', 'rpg-ia'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_last_activity, 1); ?> id="<?php echo esc_attr($this->get_field_id('show_last_activity')); ?>" name="<?php echo esc_attr($this->get_field_name('show_last_activity')); ?>" value="1">
            <label for="<?php echo esc_attr($this->get_field_id('show_last_activity')); ?>"><?php _e('Date de dernière activité', 'rpg-ia'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_dashboard_link, 1); ?> id="<?php echo esc_attr($this->get_field_id('show_dashboard_link')); ?>" name="<?php echo esc_attr($this->get_field_name('show_dashboard_link')); ?>" value="1">
            <label for="<?php echo esc_attr($this->get_field_id('show_dashboard_link')); ?>"><?php _e('Afficher le lien vers le tableau de bord', 'rpg-ia'); ?></label>
        </p>
        <?php
    }

    /**
     * Traite les options du widget lors de l'enregistrement.
     *
     * @since    1.0.0
     * @param    array    $new_instance    Nouvelles valeurs.
     * @param    array    $old_instance    Anciennes valeurs.
     * @return   array                     Valeurs à sauvegarder.
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['show_characters'] = (!empty($new_instance['show_characters'])) ? 1 : 0;
        $instance['show_sessions'] = (!empty($new_instance['show_sessions'])) ? 1 : 0;
        $instance['show_active_sessions'] = (!empty($new_instance['show_active_sessions'])) ? 1 : 0;
        $instance['show_actions'] = (!empty($new_instance['show_actions'])) ? 1 : 0;
        $instance['show_playtime'] = (!empty($new_instance['show_playtime'])) ? 1 : 0;
        $instance['show_last_activity'] = (!empty($new_instance['show_last_activity'])) ? 1 : 0;
        $instance['show_dashboard_link'] = (!empty($new_instance['show_dashboard_link'])) ? 1 : 0;
        
        return $instance;
    }
}