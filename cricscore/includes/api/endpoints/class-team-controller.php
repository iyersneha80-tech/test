<?php
/**
 * REST API controller for Teams.
 *
 * @package CricScore
 * @version 1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CricScore_Team_Controller {

    protected $namespace = 'cricscore/v1';
    protected $rest_base = 'teams';

    public function register_routes() {
        // Route for getting all teams and creating a new one
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

        // --- NEW: Route for updating and deleting a single team ---
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                'args' => [
                    'id' => [
                        'description' => __( 'Unique identifier for the team.', 'cricscore' ),
                        'type'        => 'integer',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE, // PUT/PATCH
                    'callback'            => [ $this, 'update_item' ],
                    'permission_callback' => [ $this, 'item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( true ),
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE, // DELETE
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'item_permissions_check' ],
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

    /**
     * Permissions check for single items (edit/delete).
     * Ensures the user owns the team they are trying to modify.
     */
    public function item_permissions_check( $request ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $team_id = (int) $request['id'];
        $owner_id = (int) CricScore_DB()->get_team_owner( $team_id );

        return get_current_user_id() === $owner_id;
    }

    public function get_items( $request ) {
        $user_id = get_current_user_id();
        $teams = CricScore_DB()->get_user_teams( $user_id );
        return new WP_REST_Response( $teams, 200 );
    }

    public function create_item( $request ) {
        $user_id = get_current_user_id();
        $captain_id = $request->get_param( 'captain_id' );
        $vice_captain_id = $request->get_param( 'vice_captain_id' );

        $args = [
            'name'            => $request->get_param( 'name' ),
            'short_name'      => $request->get_param( 'short_name' ),
            'logo_url'        => $request->get_param( 'logo_url' ),
            'country'         => $request->get_param( 'country' ),
            'captain_id'      => ! empty( $captain_id ) ? (int) $captain_id : null,
            'vice_captain_id' => ! empty( $vice_captain_id ) ? (int) $vice_captain_id : null,
        ];
        
        $team_id = CricScore_DB()->create_team( $user_id, $args );

        if ( ! $team_id ) {
            return new WP_Error( 'cricscore_team_creation_failed', __( 'Failed to create team.', 'cricscore' ), [ 'status' => 500 ] );
        }
        return new WP_REST_Response( [ 'success' => true, 'team_id' => $team_id ], 201 );
    }

    /**
     * --- NEW: Updates a single team. ---
     */
    public function update_item( $request ) {
        $team_id = (int) $request['id'];
        $user_id = get_current_user_id();
        
        $data = [];
        $params = $request->get_params();

        // Build the data array only with parameters that were actually sent
        if ( isset( $params['name'] ) ) { $data['name'] = $params['name']; }
        if ( isset( $params['short_name'] ) ) { $data['short_name'] = $params['short_name']; }
        if ( isset( $params['logo_url'] ) ) { $data['logo_url'] = $params['logo_url']; }
        if ( isset( $params['country'] ) ) { $data['country'] = $params['country']; }
        if ( isset( $params['captain_id'] ) ) {
            $data['captain_id'] = ! empty( $params['captain_id'] ) ? (int) $params['captain_id'] : null;
        }
        if ( isset( $params['vice_captain_id'] ) ) {
            $data['vice_captain_id'] = ! empty( $params['vice_captain_id'] ) ? (int) $params['vice_captain_id'] : null;
        }

        $result = CricScore_DB()->update_team( $team_id, $user_id, $data );

        if ( ! $result ) {
            return new WP_Error( 'cricscore_team_update_failed', __( 'Failed to update team.', 'cricscore' ), [ 'status' => 500 ] );
        }

        return new WP_REST_Response( [ 'success' => true ], 200 );
    }

    /**
     * --- NEW: Deletes a single team. ---
     */
    public function delete_item( $request ) {
        $team_id = (int) $request['id'];
        $user_id = get_current_user_id();

        $result = CricScore_DB()->delete_team( $team_id, $user_id );

        if ( ! $result ) {
            return new WP_Error( 'cricscore_team_delete_failed', __( 'Failed to delete team or not authorized.', 'cricscore' ), [ 'status' => 403 ] );
        }

        return new WP_REST_Response( [ 'success' => true ], 200 );
    }

    public function get_endpoint_args_for_item_schema( $is_update = false ) {
        $args = [];
        $args['name'] = [
            'description'       => __( 'The full name of the team.', 'cricscore' ),
            'type'              => 'string',
            'required'          => ! $is_update,
            'sanitize_callback' => 'sanitize_text_field',
        ];
        $args['short_name'] = [
            'description'       => __( 'The short name or abbreviation for the team.', 'cricscore' ),
            'type'              => 'string',
            'required'          => false,
            'sanitize_callback' => 'sanitize_text_field',
        ];
        $args['logo_url'] = [
            'description'       => __( 'URL for the team logo.', 'cricscore' ),
            'type'              => 'string',
            'format'            => 'uri',
            'sanitize_callback' => 'esc_url_raw',
        ];
        $args['country'] = [
            'description'       => __( 'Team country or region.', 'cricscore' ),
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ];
        $args['captain_id'] = [
            'description'       => __( 'The player ID of the team captain.', 'cricscore' ),
            'type'              => 'integer',
        ];
        $args['vice_captain_id'] = [
            'description'       => __( 'The player ID of the team vice-captain.', 'cricscore' ),
            'type'              => 'integer',
        ];
        return $args;
    }
}