<?php
/**
 * Loader for the "Create Match" view.
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
$assets_dir_url = CRICSCORE_URL . 'templates/dashboard/create-match/assets/';

// 2. Enqueue the stylesheet with cache busting.
$css_file_path = $assets_dir_path . 'css/create-match-style.css';
if ( file_exists( $css_file_path ) ) {
    wp_enqueue_style(
        'cricscore-create-match-style',
        $assets_dir_url . 'css/create-match-style.css',
        ['cricscore-dashboard'], // Dependency on the main dashboard stylesheet
        filemtime( $css_file_path ) // Cache busting
    );
}
// NEW: Enqueue the dedicated stylesheet for the coin toss modal.
$coin_toss_css_path = $assets_dir_path . 'css/coin-toss.css';
if ( file_exists( $coin_toss_css_path ) ) {
    wp_enqueue_style(
        'cricscore-coin-toss-style', // A unique handle for the new stylesheet
        $assets_dir_url . 'css/coin-toss.css',
        ['cricscore-create-match-style'], // Depends on the main style to load first
        filemtime( $coin_toss_css_path ) // Cache busting
    );
}
// NEW: Enqueue the dedicated stylesheet for the rules toggle.
$rules_css_path = $assets_dir_path . 'css/create-match-rules-style.css';
if ( file_exists( $rules_css_path ) ) {
    wp_enqueue_style(
        'cricscore-create-match-rules-style', // A unique handle for the new stylesheet
        $assets_dir_url . 'css/create-match-rules-style.css',
        ['cricscore-create-match-style'], // Depends on the main style to load first
        filemtime( $rules_css_path ) // Cache busting
    );
}
// 3. Enqueue the script with cache busting and pass the nonce.
$js_file_path = $assets_dir_path . 'create-match-script.js';
if ( file_exists( $js_file_path ) ) {
    $handle = 'cricscore-create-match-script';
    wp_enqueue_script(
        $handle,
        $assets_dir_url . 'create-match-script.js',
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

// 4. Enqueue and localize the new script for the coin toss modal.
$coin_toss_js_path = $assets_dir_path . 'js/coin-toss.js';
if ( file_exists( $coin_toss_js_path ) ) {
    $handle = 'cricscore-coin-toss-script'; // Define a handle to reuse
    wp_enqueue_script(
        $handle,
        $assets_dir_url . 'js/coin-toss.js',
        [], // No dependencies
        filemtime( $coin_toss_js_path ), // Cache busting
        true // Load in the footer
    );

    // --- DYNAMIC IMAGE CACHING ---
    $image_urls = [];
    $image_dir_path = $assets_dir_path . 'images/';
    // Find all files in the images directory that end with .png
    $image_files = glob( $image_dir_path . '*.png' );

    if ( $image_files ) {
        foreach ( $image_files as $file_path ) {
            // Convert the server file path to a browser-accessible URL
            $image_urls[] = $assets_dir_url . 'images/' . basename( $file_path );
        }
    }

    // Pass the array of image URLs to the script
    wp_localize_script(
        $handle,
        'cricscore_coin_toss_data', // This will be the name of our JavaScript object
        [
            'image_urls' => $image_urls,
        ]
    );
}

// --- Template Inclusion ---

// Includes the view file.
include_once __DIR__ . '/create-match-view.php';