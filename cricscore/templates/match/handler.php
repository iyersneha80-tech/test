<?php
/**
 * Handler for the new Match Template.
 * This is the single entry point for the live scoring view.
 *
 * @package CricScore
 * @version 2.0.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class CricScore_Match_Handler {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        $this->load_view();
    }

    /**
     * Enqueue styles and scripts for the match template.
     */
    public function enqueue_assets() {
                // --- NEW MODULAR ASSET ENQUEUEING ---
        // An array of all our new stylesheets. This makes managing them much easier.
        $styles_to_load = [
            'cricscore-match-global' => 'templates/match/assets/css/match-global.css',
            'cricscore-match-header' => 'templates/match/assets/css/match-header.css',
            'cricscore-pre-match'    => 'templates/match/assets/css/pre-match.css',
            'cricscore-innings-prep' => 'templates/match/assets/css/innings-prep.css',
            'cricscore-summary'      => 'templates/match/assets/css/summary.css',
            'cricscore-match-result' => 'templates/match/assets/css/match-result.css',
            'cricscore-live-scoring' => 'templates/match/assets/css/live-scoring.css',
            'cricscore-match-status' => 'templates/match/assets/css/status.css',
        ];

        $dependencies = []; // Start with no dependencies
        foreach ( $styles_to_load as $handle => $path_rel ) {
            $path_abs = CRICSCORE_PATH . $path_rel;
            if ( file_exists( $path_abs ) ) {
                wp_enqueue_style(
                    $handle,
                    CRICSCORE_URL . $path_rel,
                    $dependencies, // Make each style dependent on the previous one
                    filemtime( $path_abs )
                );
                // Add the current handle to the dependencies array for the *next* style in the loop.
                // This ensures they load in the correct order.
                $dependencies[] = $handle;
            }
        }

        // --- CORRECTED SCRIPT LOADING ORDER ---

        // 1. Enqueue the main controller script FIRST.
        $main_script_handle = 'cricscore-match-main-controller';
        $main_script_path_rel = 'templates/match/assets/js/main.js';
        $main_script_path_abs = CRICSCORE_PATH . $main_script_path_rel;
        if ( file_exists( $main_script_path_abs ) ) {
            wp_enqueue_script(
                $main_script_handle,
                CRICSCORE_URL . $main_script_path_rel,
                [], // No dependencies
                filemtime( $main_script_path_abs ),
                true // Load in footer
            );

            // Pass crucial data (nonce, match_id) to the main script
            wp_localize_script(
                $main_script_handle,
                'cricscore_match_data',
                [ 
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'match_id' => get_query_var( 'match_id' ),
                ]
            );
        }

        // 2. Enqueue the step-specific scripts and make them DEPENDENT on the main controller.
        $steps = [
            '1-pre-match-summary',
            '2-innings-preparation',
            '3-live-scoring',
            '4-mid-innings-summary',
            '5-post-match-summary',
            '6-match-result',
        ];

        foreach ( $steps as $step ) {
            $script_path_rel = "templates/match/assets/js/{$step}.js";
            $script_path_abs = CRICSCORE_PATH . $script_path_rel;
            if ( file_exists( $script_path_abs ) ) {
                wp_enqueue_script(
                    "cricscore-match-step-{$step}",
                    CRICSCORE_URL . $script_path_rel,
                    [ $main_script_handle ], // This script now depends on main.js
                    filemtime( $script_path_abs ),
                    true
                );
            }
        }
    }

    /**
     * Includes the main wrapper view file for the match template.
     */
    public function load_view() {
        include_once CRICSCORE_PATH . 'templates/match/wrapper.php';
    }
}

new CricScore_Match_Handler();