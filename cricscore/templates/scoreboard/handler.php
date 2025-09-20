<?php
/**
 * Handler for the Scoreboard Template.
 *
 * This file is loaded by the main plugin controller when the /score/ URL is accessed.
 * Its job is to enqueue assets and load the main view for this specific template.
 *
 * @package CricScore
 * @author  Gemini
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class CricScore_Scoreboard_Handler {

    /**
     * Constructor.
     */
    public function __construct() {
        // Hook into WordPress to load our assets.
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // Load the main view file for this template.
        $this->load_view();
    }

    /**
     * Enqueue styles and scripts for the scoreboard template.
     */
    public function enqueue_assets() {
        // --- STYLES ---

        // External libraries
        wp_enqueue_style( 'cricscore-fa', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', [], '6.4.2' );
        wp_enqueue_style( 'cricscore-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap', [], null );

        // Local stylesheet with cache busting
        $style_path = CRICSCORE_PATH . 'templates/scoreboard/assets/css/scoreboard.css';
        wp_enqueue_style( 'cricscore-scoreboard-style', CRICSCORE_URL . 'templates/scoreboard/assets/css/scoreboard.css', [], file_exists( $style_path ) ? filemtime( $style_path ) : false );


        // --- SCRIPTS ---

        // External libraries
        wp_enqueue_script( 'tailwindcss', 'https://cdn.tailwindcss.com', [], '3.3.3', false );
        
        // **NEW**: Add jsPDF and html2canvas for PDF generation
        wp_enqueue_script( 'cricscore-jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', [], '2.5.1', true );
        wp_enqueue_script( 'cricscore-html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', [], '1.4.1', true );


        // Local scripts with cache busting. 'true' at the end defers loading to the footer.
        $alpine_path = CRICSCORE_PATH . 'templates/scoreboard/assets/js/alpine.min.js';
        $module_path = CRICSCORE_PATH . 'templates/scoreboard/assets/js/module-scoreboard.js';
        $summary_js_path = CRICSCORE_PATH . 'templates/scoreboard/assets/js/module-match-summary.js';

        // Enqueue scripts in the correct order of dependency
        wp_enqueue_script( 'alpinejs', CRICSCORE_URL . 'templates/scoreboard/assets/js/alpine.min.js', [], file_exists( $alpine_path ) ? filemtime( $alpine_path ) : false, true );
        
        wp_enqueue_script( 'cricscore-module-scoreboard', CRICSCORE_URL . 'templates/scoreboard/assets/js/module-scoreboard.js', ['alpinejs'], file_exists( $module_path ) ? filemtime( $module_path ) : false, true );
        
        // **CORRECTED:** Load the new summary script last, as it depends on the scoreboard module.
        wp_enqueue_script( 'cricscore-module-summary', CRICSCORE_URL . 'templates/scoreboard/assets/js/module-match-summary.js', ['cricscore-module-scoreboard'], file_exists( $summary_js_path ) ? filemtime( $summary_js_path ) : false, true );
        
        // **REMOVED:** The conflicting main.js file is no longer enqueued.
    }

    /**
     * Includes the main wrapper view file.
     */
    public function load_view() {
        $view_path = CRICSCORE_PATH . 'templates/scoreboard/wrapper.php';
        if ( file_exists( $view_path ) ) {
            include_once $view_path;
        }
    }
}

// Instantiate the handler to kick things off.
new CricScore_Scoreboard_Handler();