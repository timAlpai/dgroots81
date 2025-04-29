<?php
/**
 * Widget pour afficher les sessions actives
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes/widgets
 */

/**
 * Widget pour afficher les sessions actives.
 *
 * Ce widget affiche une liste des sessions de jeu actives pour l'utilisateur connecté.
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes/widgets
 * @author     RPG-IA Team
 */
class RPG_IA_Sessions_Widget extends WP_Widget {

    /**
     * Initialise le widget.
     *
     * @since    1.0.0
     */
    public function __construct() {
        parent::__construct(
            'rpgia_sessions_widget', // ID de base
            __('RPG-IA Sessions', 'rpg-ia'), // Nom
            array(
                'description' => __('Affiche les sessions de jeu actives', 'rpg-ia'),
                'classname' => 'rpgia-sessions-widget',
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
            echo '<p>' . __('Veuillez vous connecter pour voir vos sessions.', 'rpg-ia') . '</p>';
            echo $args['after_widget'];
            return;
        }
        
        // Récupérer les sessions actives
        $session_manager = new RPG_IA_Session_Manager();
        $sessions = $session_manager->get_sessions(array('status' => 'active'));
        
        if (is_wp_error($sessions)) {
            echo '<p>' . __('Erreur lors du chargement des sessions.', 'rpg-ia') . '</p>';
            echo $args['after_widget'];
            return;
        }
        
        // Limiter le nombre de sessions à afficher
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 5;
        $sessions = array_slice($sessions, 0, $limit);
        
        // Afficher les sessions
        if (empty($sessions)) {
            echo '<p>' . __('Aucune session active.', 'rpg-ia') . '</p>';
        } else {
            echo '<ul class="rpgia-sessions-list">';
            foreach ($sessions as $session) {
                $session_url = get_permalink(get_option('rpg_ia_page_rpg-ia-play')) . '?session_id=' . $session['id'];
                echo '<li class="rpgia-session-item">';
                echo '<a href="' . esc_url($session_url) . '">' . esc_html($session['title']) . '</a>';
                echo '<span class="rpgia-session-date">' . date_i18n(get_option('date_format'), strtotime($session['created_at'])) . '</span>';
                echo '</li>';
            }
            echo '</ul>';
            
            // Afficher le lien vers toutes les sessions
            if (!empty($instance['show_all_link']) && $instance['show_all_link'] == 1) {
                $sessions_url = get_permalink(get_option('rpg_ia_page_rpg-ia-sessions'));
                echo '<p class="rpgia-sessions-all"><a href="' . esc_url($sessions_url) . '">' . __('Voir toutes les sessions', 'rpg-ia') . '</a></p>';
            }
        }
        
        echo $args['after_widget'];
    }

    /**
     * Affiche le formulaire de paramètres dans l'administration.
     *
     * @since    1.0.0
     * @param    array    $instance    Valeurs sauvegardées.
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Sessions Actives', 'rpg-ia');
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 5;
        $show_all_link = !empty($instance['show_all_link']) ? 1 : 0;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Titre:', 'rpg-ia'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php _e('Nombre de sessions à afficher:', 'rpg-ia'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" min="1" max="20" value="<?php echo esc_attr($limit); ?>">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_all_link, 1); ?> id="<?php echo esc_attr($this->get_field_id('show_all_link')); ?>" name="<?php echo esc_attr($this->get_field_name('show_all_link')); ?>" value="1">
            <label for="<?php echo esc_attr($this->get_field_id('show_all_link')); ?>"><?php _e('Afficher le lien "Voir toutes les sessions"', 'rpg-ia'); ?></label>
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
        $instance['limit'] = (!empty($new_instance['limit'])) ? intval($new_instance['limit']) : 5;
        $instance['show_all_link'] = (!empty($new_instance['show_all_link'])) ? 1 : 0;
        
        return $instance;
    }
}