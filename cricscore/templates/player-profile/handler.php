<?php
/**
 * Handler for the Player Profile Template.
 *
 * @package CricScore
 * @version 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class CricScore_Player_Profile_Handler {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        $this->load_view();
    }

    /**
     * Enqueue styles and scripts for the player profile template.
     */
    public function enqueue_assets() {
        // Enqueue the main stylesheet
        $style_path_rel = 'templates/player-profile/assets/css/style.css';
        $style_path_abs = CRICSCORE_PATH . $style_path_rel;
        if ( file_exists( $style_path_abs ) ) {
            wp_enqueue_style(
                'cricscore-player-profile-style',
                CRICSCORE_URL . $style_path_rel,
                [],
                filemtime( $style_path_abs )
            );
        }

        // Enqueue required external libraries from CDN
        wp_enqueue_style( 'cricscore-fa', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1' );
        wp_enqueue_style( 'cricscore-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null );

        // Enqueue the main script
        $script_path_rel = 'templates/player-profile/assets/js/script.js';
        $script_path_abs = CRICSCORE_PATH . $script_path_rel;
        if ( file_exists( $script_path_abs ) ) {
            $handle = 'cricscore-player-profile-script';
            wp_enqueue_script(
                $handle,
                CRICSCORE_URL . $script_path_rel,
                [],
                filemtime( $script_path_abs ),
                true
            );

            // Pass crucial data to the script
            wp_localize_script(
                $handle,
                'cricscore_player_data',
                [
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'player_id' => get_query_var( 'player_id' ),
                    'api_base' => esc_url_raw( rest_url( 'cricscore/v1' ) ),
                ]
            );
        }
    }

    /**
     * Includes the main view file for the profile template.
     */
    public function load_view() {
        include_once CRICSCORE_PATH . 'templates/player-profile/profile-view.php';
    }
}

new CricScore_Player_Profile_Handler();