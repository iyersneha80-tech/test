<?php
/**
 * REST API controller for Matches.
 *
 * @package CricScore
 * @version 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CricScore_Match_Controller {

    protected $namespace = 'cricscore/v1';
    protected $rest_base = 'matches';

    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'get_items_permissions_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'create_item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema(),
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                'args' => [
                    'id' => [
                        'description' => __( 'Unique identifier for the match.', 'cricscore' ),
                        'type'        => 'integer',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'item_permissions_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_item' ],
                    'permission_callback' => [ $this, 'item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( true ),
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)/log',
            [
                'args' => [
                    'id' => [
                        'description' => __( 'Unique identifier for the match.', 'cricscore' ),
                        'type'        => 'integer',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'log_ball' ],
                    'permission_callback' => [ $this, 'item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_log_schema(),
                ],
            ]
        );
    }

    public function get_items_permissions_check( $request ) {
        return is_user_logged_in();
    }

    public function create_item_permissions_check( $request ) {
        return is_user_logged_in();
    }

    public function item_permissions_check( $request ) {
        if ( ! is_user_logged_in() ) { return false; }
        $match_id = (int) $request['id'];
        $owner_id = (int) CricScore_DB()->get_match_owner( $match_id );
        return get_current_user_id() === $owner_id;
    }

    public function get_items( $request ) {
        $user_id = get_current_user_id();
        $args = [
            'page'     => $request->get_param( 'page' ) ?: 1,
            'per_page' => $request->get_param( 'per_page' ) ?: 20,
        ];
        $matches = CricScore_DB()->get_user_matches( $user_id, $args );
        return new WP_REST_Response( $matches, 200 );
    }

    public function get_item( $request ) {
        $match_id = (int) $request['id'];
        $scorecard = CricScore_DB()->get_match_data( $match_id );

        if ( is_null( $scorecard ) ) {
            return new WP_Error( 'cricscore_match_not_found', __( 'Match not found.', 'cricscore' ), [ 'status' => 404 ] );
        }

        return new WP_REST_Response( $scorecard, 200 );
    }

    public function create_item( $request ) {
        $user_id = get_current_user_id();
        $args = [
            'team1_id'          => $request->get_param( 'team1_id' ),
            'team2_id'          => $request->get_param( 'team2_id' ),
            'venue_id'          => $request->get_param( 'venue_id' ),
            'tournament_id'     => $request->get_param( 'tournament_id' ),
            'match_format'      => $request->get_param( 'match_format' ),
            'overs_per_innings' => $request->get_param( 'overs_per_innings' ),
            'toss_data'         => $request->get_param( 'toss_data' ),
            // --- NEW: Pass player arrays to the database layer ---
            'team1_players'     => $request->get_param( 'team1_players' ),
            'team2_players'     => $request->get_param( 'team2_players' ),
            'rules'             => $request->get_param( 'rules' ),
        ];
        $match_id = CricScore_DB()->create_match( $user_id, $args );

        if ( ! $match_id ) {
            return new WP_Error( 'cricscore_match_creation_failed', __( 'Failed to create the match.', 'cricscore' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [ 'success' => true, 'message' => 'Match created successfully.', 'match_id' => $match_id ], 201 );
    }

    public function update_item( $request ) {
        $match_id    = (int) $request['id'];
        $match_state = $request->get_json_params();
        $result = CricScore_DB()->save_match_state( $match_id, $match_state );

        if ( ! $result ) {
            return new WP_Error( 'cricscore_match_save_failed', __( 'Failed to save match state.', 'cricscore' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [ 'success' => true, 'message' => 'Match progress saved successfully.' ], 200 );
    }

    public function log_ball( $request ) {
        $log_data = $request->get_json_params();
        $log_id = CricScore_DB()->log_ball_event( $log_data );

        if ( ! $log_id ) {
            return new WP_Error( 'cricscore_ball_log_failed', __( 'Failed to log the ball event.', 'cricscore' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [ 'success' => true, 'message' => 'Ball event logged successfully.', 'log_id' => $log_id ], 201 );
    }

    public function get_endpoint_args_for_item_schema( $is_update = false ) {
        if ( $is_update ) { return []; }
        $args = [
            'team1_id'          => [ 'type' => 'integer', 'required' => true ],
            'team2_id'          => [ 'type' => 'integer', 'required' => true ],
            'venue_id'          => [ 'type' => 'integer', 'required' => false, 'default' => null ],
            'tournament_id'     => [ 'type' => 'integer', 'required' => false, 'default' => null ],
            'match_format'      => [ 'type' => 'string', 'required' => true ],
            'overs_per_innings' => [ 'type' => 'integer', 'required' => false, 'default' => null ],
            'toss_data'         => [ 'type' => 'object', 'required' => true ],
            // --- NEW: Define arguments for player arrays ---
            'team1_players'     => [ 'type' => 'array', 'items' => [ 'type' => 'integer' ] ],
            'team2_players'     => [ 'type' => 'array', 'items' => [ 'type' => 'integer' ] ],
            'rules'             => [ 'type' => 'object', 'required' => false ],
        ];
        return $args;
    }

    public function get_endpoint_args_for_log_schema() {
        return [
            'match_id'      => [ 'type' => 'integer', 'required' => true ],
            'innings_id'    => [ 'type' => 'integer', 'required' => true ],
            'over_number'   => [ 'type' => 'integer', 'required' => true ],
            'ball_number'   => [ 'type' => 'integer', 'required' => true ],
            'batsman_id'    => [ 'type' => 'integer', 'required' => true ],
            'bowler_id'     => [ 'type' => 'integer', 'required' => true ],
            'runs_scored'   => [ 'type' => 'integer', 'required' => true ],
            'extras_type'   => [ 'type' => 'string', 'required' => false, 'default' => null ],
            'extras_runs'   => [ 'type' => 'integer', 'required' => false, 'default' => 0 ],
            'is_wicket'     => [ 'type' => 'boolean', 'required' => false, 'default' => false ],
            'wicket_data'   => [ 'type' => 'object', 'required' => false, 'default' => [] ],
        ];
    }
}