<?php
/**
 * Affiche le formulaire de connexion du plugin.
 *
 * @link       https://dgroots81.mandragore.ai
 * @since      1.0.0
 *
 * @package    RPG_IA
 * @subpackage RPG_IA/public/partials
 */

// Si ce fichier est appelé directement, on sort
if (!defined('WPINC')) {
    die;
}
?>

<div class="rpg-ia-container">
    <h1><?php _e('RPG-IA Login', 'rpg-ia'); ?></h1>
    
    <div class="rpg-ia-messages"></div>
    
    <div class="rpg-ia-row">
        <div class="rpg-ia-column">
            <div class="rpg-ia-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('Login', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <form id="rpg-ia-login-form" class="rpg-ia-form">
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-username" class="rpg-ia-form-label"><?php _e('Username', 'rpg-ia'); ?></label>
                            <input type="text" id="rpg-ia-username" name="username" class="rpg-ia-form-input" required>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-password" class="rpg-ia-form-label"><?php _e('Password', 'rpg-ia'); ?></label>
                            <input type="password" id="rpg-ia-password" name="password" class="rpg-ia-form-input" required>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <button type="submit" class="rpg-ia-button"><?php _e('Login', 'rpg-ia'); ?></button>
                        </div>
                    </form>
                    
                    <p><?php _e('Don\'t have an account?', 'rpg-ia'); ?> <a href="#" id="rpg-ia-show-register"><?php _e('Register', 'rpg-ia'); ?></a></p>
                </div>
            </div>
        </div>
        
        <div class="rpg-ia-column">
            <div class="rpg-ia-card" id="rpg-ia-register-card" style="display: none;">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('Register', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <form id="rpg-ia-register-form" class="rpg-ia-form">
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-reg-username" class="rpg-ia-form-label"><?php _e('Username', 'rpg-ia'); ?></label>
                            <input type="text" id="rpg-ia-reg-username" name="username" class="rpg-ia-form-input" required>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-reg-email" class="rpg-ia-form-label"><?php _e('Email', 'rpg-ia'); ?></label>
                            <input type="email" id="rpg-ia-reg-email" name="email" class="rpg-ia-form-input" required>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-reg-password" class="rpg-ia-form-label"><?php _e('Password', 'rpg-ia'); ?></label>
                            <input type="password" id="rpg-ia-reg-password" name="password" class="rpg-ia-form-input" required>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <label for="rpg-ia-reg-password-confirm" class="rpg-ia-form-label"><?php _e('Confirm Password', 'rpg-ia'); ?></label>
                            <input type="password" id="rpg-ia-reg-password-confirm" name="password_confirm" class="rpg-ia-form-input" required>
                        </div>
                        
                        <div class="rpg-ia-form-row">
                            <button type="submit" class="rpg-ia-button"><?php _e('Register', 'rpg-ia'); ?></button>
                        </div>
                    </form>
                    
                    <p><?php _e('Already have an account?', 'rpg-ia'); ?> <a href="#" id="rpg-ia-show-login"><?php _e('Login', 'rpg-ia'); ?></a></p>
                </div>
            </div>
            
            <div class="rpg-ia-card" id="rpg-ia-about-card">
                <div class="rpg-ia-card-header">
                    <h2 class="rpg-ia-card-title"><?php _e('About RPG-IA', 'rpg-ia'); ?></h2>
                </div>
                <div class="rpg-ia-card-content">
                    <p><?php _e('RPG-IA is a role-playing game with an AI as the game master.', 'rpg-ia'); ?></p>
                    <p><?php _e('Create your character, join a session, and start playing with other players.', 'rpg-ia'); ?></p>
                    <p><?php _e('The AI will guide you through the adventure, responding to your actions and creating a unique story.', 'rpg-ia'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Afficher le formulaire d'inscription
        $('#rpg-ia-show-register').on('click', function(e) {
            e.preventDefault();
            $('#rpg-ia-about-card').hide();
            $('#rpg-ia-register-card').show();
        });
        
        // Afficher le formulaire de connexion
        $('#rpg-ia-show-login').on('click', function(e) {
            e.preventDefault();
            $('#rpg-ia-register-card').hide();
            $('#rpg-ia-about-card').show();
        });
    });
</script>