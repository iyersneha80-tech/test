<?php
/**
 * Registers all REST API routes for the CricScore plugin.
 *
 * @package CricScore
 * @version 1.2.2 (Stable Fallback)
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CricScore_API_Registrar {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        // --- Include ONLY the stable, core controllers ---
        require_once CRICSCORE_INCLUDES_PATH . 'api/endpoints/class-team-controller.php';
        require_once CRICSCORE_INCLUDES_PATH . 'api/endpoints/class-player-controller.php';
        require_once CRICSCORE_INCLUDES_PATH . 'api/endpoints/class-venue-controller.php';
        require_once CRICSCORE_INCLUDES_PATH . 'api/endpoints/class-tournament-controller.php';
        require_once CRICSCORE_INCLUDES_PATH . 'api/endpoints/class-match-controller.php';
        require_once CRICSCORE_INCLUDES_PATH . 'api/endpoints/class-media-controller.php';
        
        // --- Register ONLY the stable, core controllers ---
        (new CricScore_Team_Controller())->register_routes();
        (new CricScore_Player_Controller())->register_routes();
        (new CricScore_Venue_Controller())->register_routes();
        (new CricScore_Tournament_Controller())->register_routes();
        (new CricScore_Match_Controller())->register_routes();
        (new CricScore_Media_Controller())->register_routes();
    }
}