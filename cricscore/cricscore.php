<?php
/**
 * Plugin Name: CricScore
 * Description: A comprehensive cricket scoring plugin.
 * Version: 1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define( 'CRICSCORE_VERSION', '1.5.0' );
define( 'CRICSCORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CRICSCORE_URL', plugin_dir_url( __FILE__ ) );
define( 'CRICSCORE_INCLUDES_PATH', CRICSCORE_PATH . 'includes/' );

// --- Include Core Files ---
require_once CRICSCORE_INCLUDES_PATH . 'core/class-cricscore-activator.php';
require_once CRICSCORE_INCLUDES_PATH . 'core/class-cricscore-deactivator.php';
require_once CRICSCORE_INCLUDES_PATH . 'database/class-cricscore-db.php';
require_once CRICSCORE_INCLUDES_PATH . 'api/class-cricscore-api-registrar.php';
require_once CRICSCORE_INCLUDES_PATH . 'class-cricscore-frontend.php';

// --- Hooks ---
register_activation_hook( __FILE__, [ 'CricScore_Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'CricScore_Deactivator', 'deactivate' ] );

/**
 * Enqueue a font icon stylesheet.
 */
function cricscore_enqueue_icon_stylesheet() {
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', [], '5.15.4' );
}
add_action( 'wp_enqueue_scripts', 'cricscore_enqueue_icon_stylesheet' );
// --- Initialize Classes ---
new CricScore_API_Registrar();
new CricScore_Frontend();

// The lines that required and initialized class-cricscore-dashboard-loader.php have been removed.

// === Offline Scoring Bootstrap (additive & safe) ===
if ( ! defined( 'CRICSCORE_API_NS' ) ) {
    define( 'CRICSCORE_API_NS', 'cricscore/v1' );
}
if ( ! defined( 'CRICSCORE_INCLUDES_PATH' ) ) {
    define( 'CRICSCORE_INCLUDES_PATH', CRICSCORE_PATH . 'includes/' );
}
if ( ! defined( 'CRICSCORE_TEMPLATES_URL' ) ) {
    define( 'CRICSCORE_TEMPLATES_URL', CRICSCORE_URL . 'templates/' );
}
if ( ! defined( 'CRICSCORE_OFFLINE_ENABLED_OPTION' ) ) {
    define( 'CRICSCORE_OFFLINE_ENABLED_OPTION', 'cricscore_offline_enabled' );
}
if ( file_exists( CRICSCORE_PATH . 'includes/offline/bootstrap.php' ) ) {
    require_once CRICSCORE_PATH . 'includes/offline/bootstrap.php';
}
