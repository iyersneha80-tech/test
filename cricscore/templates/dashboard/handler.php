<?php
/**
 * Handler for the Dashboard Template.
 * This file is the single entry point for all dashboard views.
 *
 * @package CricScore
 * @version 2.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class CricScore_Dashboard_Handler {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        $this->load_view();
    }

    /**
     * Enqueue the correct script and styles based on the current view.
     */
    public function enqueue_assets() {
        // --- Get File Paths and Versions for Cache Busting ---
        $dashboard_css_path = CRICSCORE_PATH . 'templates/dashboard/assets/css/dashboard.css';
        $header_desktop_css_path = CRICSCORE_PATH . 'templates/dashboard/assets/css/header-desktop.css';
        $header_mobile_css_path = CRICSCORE_PATH . 'templates/dashboard/assets/css/header-mobile.css';
        $bottom_nav_css_path = CRICSCORE_PATH . 'templates/dashboard/assets/css/bottom-nav.css';
        $header_mobile_js_path = CRICSCORE_PATH . 'templates/dashboard/assets/js/header-mobile.js';
        $dashboard_global_js_path = CRICSCORE_PATH . 'templates/dashboard/assets/js/dashboard-global.js';

        $dashboard_css_ver = file_exists($dashboard_css_path) ? filemtime($dashboard_css_path) : CRICSCORE_VERSION;
        $header_desktop_css_ver = file_exists($header_desktop_css_path) ? filemtime($header_desktop_css_path) : CRICSCORE_VERSION;
        $header_mobile_css_ver = file_exists($header_mobile_css_path) ? filemtime($header_mobile_css_path) : CRICSCORE_VERSION;
        $bottom_nav_css_ver = file_exists($bottom_nav_css_path) ? filemtime($bottom_nav_css_path) : CRICSCORE_VERSION;
        $header_mobile_js_ver = file_exists($header_mobile_js_path) ? filemtime($header_mobile_js_path) : CRICSCORE_VERSION;
        $dashboard_global_js_ver = file_exists($dashboard_global_js_path) ? filemtime($dashboard_global_js_path) : CRICSCORE_VERSION;

        // --- Enqueue Core and New Global Styles ---
        wp_enqueue_style('cricscore-dashboard', CRICSCORE_URL . 'templates/dashboard/assets/css/dashboard.css', [], $dashboard_css_ver);
        wp_enqueue_style('cricscore-header-desktop', CRICSCORE_URL . 'templates/dashboard/assets/css/header-desktop.css', ['cricscore-dashboard'], $header_desktop_css_ver);
        wp_enqueue_script('cricscore-dashboard-global', CRICSCORE_URL . 'templates/dashboard/assets/js/dashboard-global.js', [], $dashboard_global_js_ver, true);
        
        // --- Conditionally Enqueue Mobile-Specific Assets ---
        if (wp_is_mobile()) {
            wp_enqueue_style('cricscore-header-mobile', CRICSCORE_URL . 'templates/dashboard/assets/css/header-mobile.css', ['cricscore-dashboard'], $header_mobile_css_ver);
            wp_enqueue_style('cricscore-bottom-nav', CRICSCORE_URL . 'templates/dashboard/assets/css/bottom-nav.css', ['cricscore-dashboard'], $bottom_nav_css_ver);
            wp_enqueue_script('cricscore-header-mobile', CRICSCORE_URL . 'templates/dashboard/assets/js/header-mobile.js', [], $header_mobile_js_ver, true);
        }
    }

    /**
     * Includes the main dashboard view file.
     */
    public function load_view() {
        include_once CRICSCORE_PATH . 'templates/dashboard/dashboard.php';
    }
}

new CricScore_Dashboard_Handler();