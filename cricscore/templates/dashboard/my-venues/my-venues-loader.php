<?php
/**
 * Loader for the "My Venues" view.
 * This file handles the enqueuing of view-specific assets and then
 * includes the view template.
 *
 * @package CricScore
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// --- Asset Enqueuing ---

// 1. Get the path and URL for the assets directory.
$assets_dir_path = __DIR__ . '/assets/';
$assets_dir_url = CRICSCORE_URL . 'templates/dashboard/my-venues/assets/';

// 2. Enqueue the stylesheet with cache busting.
$css_file_path = $assets_dir_path . 'css/style.css';
if ( file_exists( $css_file_path ) ) {
    wp_enqueue_style(
        'cricscore-my-venues-style',
        $assets_dir_url . 'css/style.css',
        ['cricscore-dashboard'], // Dependency on the main dashboard stylesheet
        filemtime( $css_file_path ) // Cache busting
    );
}

// 3. Enqueue the script with cache busting and pass the nonce.
$js_file_path = $assets_dir_path . 'my-venues-script.js';
if ( file_exists( $js_file_path ) ) {
    $handle = 'cricscore-my-venues-script';
    wp_enqueue_script(
        $handle,
        $assets_dir_url . 'my-venues-script.js',
        [], // No specific JS dependencies for this script
        filemtime( $js_file_path ), // Cache busting
        true // Load in the footer
    );
    // Pass the security nonce to the script.
    wp_localize_script(
        $handle,
        'cricscore_dashboard',
        [ 'nonce' => wp_create_nonce( 'wp_rest' ) ]
    );
}

// --- Template Inclusion ---

// Includes the view file.
include_once __DIR__ . '/my-venues-view.php';