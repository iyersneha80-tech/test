<?php
/**
 * REST API controller for Players.
 *
 * @package CricScore
 * @version 1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CricScore_Player_Controller {

    protected $namespace = 'cricscore/v1';
    protected $rest_base = 'players';

    public function register_routes() {
        // Route for getting all players and creating a new one
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema(),
                ],
            ]
        );

        // Route for updating and deleting a single player
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_item_public' ],
                'permission_callback' => '__return_true', // Publicly accessible
            ],
            [
                'args' => [ 'id' => [ 'description' => 'Unique identifier for the player.', 'type' => 'integer' ] ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_item' ],
                    'permission_callback' => [ $this, 'item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( true ),
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'item_permissions_check' ],
                ],
            ]
        );
    }

    public function permissions_check( $request ) {
        return is_user_logged_in();
    }
    
    public function item_permissions_check( $request ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $player_id = (int) $request['id'];
        $owner_id = (int) CricScore_DB()->get_player_owner( $player_id );

        return get_current_user_id() === $owner_id;
    }

    public function get_items( $request ) {
        $user_id = get_current_user_id();
        $players = CricScore_DB()->get_user_players( $user_id );
        return new WP_REST_Response( $players, 200 );
    }

    public function create_item( $request ) {
        $user_id = get_current_user_id();
        $args = [
            'name'              => $request->get_param( 'name' ),
            'dob'               => $request->get_param( 'dob' ),
            'role'              => $request->get_param( 'role' ),
            'batting_style'     => $request->get_param( 'batting_style' ),
            'bowling_style'     => $request->get_param( 'bowling_style' ),
            'country'           => $request->get_param( 'country' ),
            'profile_image_url' => $request->get_param( 'profile_image_url' ),
        ];
        
        $player_id = CricScore_DB()->create_player( $user_id, $args );

        if ( ! $player_id ) {
            return new WP_Error( 'cricscore_player_creation_failed', __( 'Failed to create player.', 'cricscore' ), [ 'status' => 500 ] );
        }
        return new WP_REST_Response( [ 'success' => true, 'player_id' => $player_id ], 201 );
    }

    public function update_item( $request ) {
        $player_id = (int) $request['id'];
        $user_id   = get_current_user_id();
        $data      = [
            'name'              => $request->get_param( 'name' ),
            'dob'               => $request->get_param( 'dob' ),
            'role'              => $request->get_param( 'role' ),
            'batting_style'     => $request->get_param( 'batting_style' ),
            'bowling_style'     => $request->get_param( 'bowling_style' ),
            'country'           => $request->get_param( 'country' ),
            'profile_image_url' => $request->get_param( 'profile_image_url' ),
        ];

        // Remove any null parameters so we don't overwrite existing data with nulls
        $data = array_filter( $data, function( $value ) {
            return $value !== null;
        });

        $result = CricScore_DB()->update_player( $player_id, $user_id, $data );

        if ( ! $result ) {
            return new WP_Error( 'cricscore_player_update_failed', __( 'Failed to update player or not authorized.', 'cricscore' ), [ 'status' => 500 ] );
        }
        return new WP_REST_Response( [ 'success' => true ], 200 );
    }

    public function delete_item( $request ) {
        $player_id = (int) $request['id'];
        $user_id   = get_current_user_id();
        $result = CricScore_DB()->delete_player( $player_id, $user_id );
        if ( ! $result ) {
            return new WP_Error( 'cricscore_player_delete_failed', __( 'Failed to delete player or not authorized.', 'cricscore' ), [ 'status' => 403 ] );
        }
        return new WP_REST_Response( [ 'success' => true ], 200 );
    }

    public function get_endpoint_args_for_item_schema( $is_update = false ) {
        $args['name'] = [
            'description'       => 'The full name of the player.',
            'type'              => 'string',
            'required'          => ! $is_update,
            'sanitize_callback' => 'sanitize_text_field',
        ];
        $args['dob'] = [
            'description'       => 'Date of Birth (YYYY-MM-DD).',
            'type'              => 'string',
            'format'            => 'date',
        ];
        $args['role'] = [
            'description'       => 'Player role.',
            'type'              => 'string',
        ];
        $args['batting_style'] = [
            'description'       => 'Batting style.',
            'type'              => 'string',
        ];
        $args['bowling_style'] = [
            'description'       => 'Bowling style.',
            'type'              => 'string',
        ];
        $args['country'] = [
            'description'       => 'Player country.',
            'type'              => 'string',
        ];
        $args['profile_image_url'] = [
            'description'       => 'URL for the profile image.',
            'type'              => 'string',
            'format'            => 'uri',
        ];
        return $args;
    }
    /**
 * Retrieves a single player for public viewing, including aggregated stats.
 */
public function get_item_public( $request ) {
    $player_id = (int) $request['id'];
    $player_data = CricScore_DB()->get_player_profile_data( $player_id );

    if ( is_null( $player_data ) ) {
        return new WP_Error( 'cricscore_player_not_found', __( 'Player not found.', 'cricscore' ), [ 'status' => 404 ] );
    }

    return new WP_REST_Response( $player_data, 200 );
}
}