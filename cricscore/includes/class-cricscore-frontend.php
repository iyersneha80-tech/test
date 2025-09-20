<?php
/**
 * Frontend handler for CricScore.
 * @package CricScore
 * @version 1.2.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class CricScore_Frontend {

    public function __construct() {
        add_action( 'init', [ $this, 'add_rewrite_rules' ] );
        add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
        add_action( 'template_include', [ $this, 'template_loader' ] );
    }

    public function add_rewrite_rules() {
        // Rule for the scoreboard
        add_rewrite_rule( '^score/?$', 'index.php?cricscore_page=score', 'top' );

        // Rules for the dashboard
        add_rewrite_rule( '^dashboard/([^/]*)/?$', 'index.php?cricscore_page=dashboard&dashboard_view=$matches[1]', 'top' );
        add_rewrite_rule( '^dashboard/?$', 'index.php?cricscore_page=dashboard&dashboard_view=main', 'top' );
        add_rewrite_rule( '^dashboard/create-match/?$', 'index.php?cricscore_page=dashboard&dashboard_view=create-match', 'top' );
        add_rewrite_rule( '^match/(\d+)-([^/]*)/?$', 'index.php?cricscore_page=match&match_id=$matches[1]', 'top' );
        
        // --- NEW: Rule for the public-facing team page ---
        add_rewrite_rule( '^team/(\d+)-([^/]*)/?$', 'index.php?cricscore_page=team&team_id=$matches[1]', 'top' );
        add_rewrite_rule( '^player/(\d+)-([^/]*)/?$', 'index.php?cricscore_page=player&player_id=$matches[1]', 'top' );
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'cricscore_page';
        $vars[] = 'dashboard_view';
        $vars[] = 'match_id';
        $vars[] = 'team_id'; // <-- New line
        $vars[] = 'player_id';
        return $vars;
    }

    public function template_loader( $template ) {
        $page = get_query_var( 'cricscore_page' );

        if ( 'score' === $page ) {
            return CRICSCORE_PATH . 'templates/scoreboard/handler.php';
        }

        if ( 'dashboard' === $page ) {
            // This is the crucial part that loads our new handler
            return CRICSCORE_PATH . 'templates/dashboard/handler.php';
        }
        
        if ( 'match' === $page ) {
            $new_template = CRICSCORE_PATH . 'templates/match/handler.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }
        if ( 'player' === $page ) {
        $new_template = CRICSCORE_PATH . 'templates/player-profile/handler.php';
        if ( file_exists( $new_template ) ) {
            return $new_template;
        }
    }

        return $template;
    }
}