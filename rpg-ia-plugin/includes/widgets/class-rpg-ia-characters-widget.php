<?php
/**
 * Widget pour afficher les personnages de l'utilisateur
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes/widgets
 */

/**
 * Widget pour afficher les personnages de l'utilisateur.
 *
 * Ce widget affiche une liste des personnages créés par l'utilisateur connecté.
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/includes/widgets
 * @author     RPG-IA Team
 */
class RPG_IA_Characters_Widget extends WP_Widget {

    /**
     * Initialise le widget.
     *
     * @since    1.0.0
     */
    public function __construct() {
        parent::__construct(
            'rpgia_characters_widget', // ID de base
            __('RPG-IA Characters', 'rpg-ia'), // Nom
            array(
                'description' => __('Affiche les personnages de l\'utilisateur', 'rpg-ia'),
                'classname' => 'rpgia-characters-widget',
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
            echo '<p>' . __('Veuillez vous connecter pour voir vos personnages.', 'rpg-ia') . '</p>';
            echo $args['after_widget'];
            return;
        }
        
        // Récupérer les personnages de l'utilisateur
        $character_manager = new RPG_IA_Character_Manager();
        $characters = $character_manager->get_characters();
        
        if (is_wp_error($characters)) {
            echo '<p>' . __('Erreur lors du chargement des personnages.', 'rpg-ia') . '</p>';
            echo $args['after_widget'];
            return;
        }
        
        // Limiter le nombre de personnages à afficher
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 5;
        $characters = array_slice($characters, 0, $limit);
        
        // Afficher les personnages
        if (empty($characters)) {
            echo '<p>' . __('Vous n\'avez pas encore de personnage.', 'rpg-ia') . '</p>';
            
            // Afficher le lien pour créer un personnage
            if (!empty($instance['show_create_link']) && $instance['show_create_link'] == 1) {
                $create_url = get_permalink(get_option('rpg_ia_page_rpg-ia-characters')) . '?action=create';
                echo '<p class="rpgia-character-create"><a href="' . esc_url($create_url) . '" class="button">' . __('Créer un personnage', 'rpg-ia') . '</a></p>';
            }
        } else {
            echo '<ul class="rpgia-characters-list">';
            foreach ($characters as $character) {
                $character_url = get_permalink(get_option('rpg_ia_page_rpg-ia-characters')) . '?character_id=' . $character['id'];
                
                echo '<li class="rpgia-character-item">';
                
                // Afficher l'avatar du personnage si disponible
                if (!empty($instance['show_avatar']) && $instance['show_avatar'] == 1 && !empty($character['avatar_url'])) {
                    echo '<div class="rpgia-character-avatar">';
                    echo '<img src="' . esc_url($character['avatar_url']) . '" alt="' . esc_attr($character['name']) . '">';
                    echo '</div>';
                }
                
                echo '<div class="rpgia-character-info">';
                echo '<a href="' . esc_url($character_url) . '" class="rpgia-character-name">' . esc_html($character['name']) . '</a>';
                
                // Afficher la classe et le niveau si disponibles
                if (!empty($instance['show_details']) && $instance['show_details'] == 1) {
                    if (!empty($character['class']) || !empty($character['level'])) {
                        echo '<div class="rpgia-character-details">';
                        if (!empty($character['class'])) {
                            echo '<span class="rpgia-character-class">' . esc_html($character['class']) . '</span>';
                        }
                        if (!empty($character['level'])) {
                            echo '<span class="rpgia-character-level">' . __('Niveau', 'rpg-ia') . ' ' . esc_html($character['level']) . '</span>';
                        }
                        echo '</div>';
                    }
                }
                
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
            
            // Afficher le lien vers tous les personnages
            if (!empty($instance['show_all_link']) && $instance['show_all_link'] == 1) {
                $characters_url = get_permalink(get_option('rpg_ia_page_rpg-ia-characters'));
                echo '<p class="rpgia-characters-all"><a href="' . esc_url($characters_url) . '">' . __('Voir tous les personnages', 'rpg-ia') . '</a></p>';
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
        $title = !empty($instance['title']) ? $instance['title'] : __('Mes Personnages', 'rpg-ia');
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 5;
        $show_avatar = !empty($instance['show_avatar']) ? 1 : 0;
        $show_details = !empty($instance['show_details']) ? 1 : 0;
        $show_all_link = !empty($instance['show_all_link']) ? 1 : 0;
        $show_create_link = !empty($instance['show_create_link']) ? 1 : 0;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Titre:', 'rpg-ia'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php _e('Nombre de personnages à afficher:', 'rpg-ia'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" min="1" max="20" value="<?php echo esc_attr($limit); ?>">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_avatar, 1); ?> id="<?php echo esc_attr($this->get_field_id('show_avatar')); ?>" name="<?php echo esc_attr($this->get_field_name('show_avatar')); ?>" value="1">
            <label for="<?php echo esc_attr($this->get_field_id('show_avatar')); ?>"><?php _e('Afficher les avatars', 'rpg-ia'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_details, 1); ?> id="<?php echo esc_attr($this->get_field_id('show_details')); ?>" name="<?php echo esc_attr($this->get_field_name('show_details')); ?>" value="1">
            <label for="<?php echo esc_attr($this->get_field_id('show_details')); ?>"><?php _e('Afficher les détails (classe, niveau)', 'rpg-ia'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_all_link, 1); ?> id="<?php echo esc_attr($this->get_field_id('show_all_link')); ?>" name="<?php echo esc_attr($this->get_field_name('show_all_link')); ?>" value="1">
            <label for="<?php echo esc_attr($this->get_field_id('show_all_link')); ?>"><?php _e('Afficher le lien "Voir tous les personnages"', 'rpg-ia'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_create_link, 1); ?> id="<?php echo esc_attr($this->get_field_id('show_create_link')); ?>" name="<?php echo esc_attr($this->get_field_name('show_create_link')); ?>" value="1">
            <label for="<?php echo esc_attr($this->get_field_id('show_create_link')); ?>"><?php _e('Afficher le bouton "Créer un personnage" si aucun personnage', 'rpg-ia'); ?></label>
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
        $instance['show_avatar'] = (!empty($new_instance['show_avatar'])) ? 1 : 0;
        $instance['show_details'] = (!empty($new_instance['show_details'])) ? 1 : 0;
        $instance['show_all_link'] = (!empty($new_instance['show_all_link'])) ? 1 : 0;
        $instance['show_create_link'] = (!empty($new_instance['show_create_link'])) ? 1 : 0;
        
        return $instance;
    }
}