<?php
/**
 * Loader for the "My Matches" view.
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
$assets_dir_url = CRICSCORE_URL . 'templates/dashboard/my-matches/assets/';

// 2. Enqueue the stylesheet with cache busting.
$css_file_path = $assets_dir_path . 'css/my-matches-style.css';
if ( file_exists( $css_file_path ) ) {
    wp_enqueue_style(
        'cricscore-my-matches-style',
        $assets_dir_url . 'css/my-matches-style.css',
        ['cricscore-dashboard'], // Dependency on the main dashboard stylesheet
        filemtime( $css_file_path ) // Cache busting
    );
}

// 3. Enqueue the script with cache busting and pass the nonce.
$js_file_path = $assets_dir_path . 'my-matches-script.js';
if ( file_exists( $js_file_path ) ) {
    $handle = 'cricscore-my-matches-script';
    wp_enqueue_script(
        $handle,
        $assets_dir_url . 'my-matches-script.js',
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

// This loader simply includes the view file.
// In the future, any data preparation for this view would go here.
include_once __DIR__ . '/my-matches-view.php';