<?php
/**
 * REST API controller for Tournaments.
 *
 * @package CricScore
 * @version 1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CricScore_Tournament_Controller
 *
 * This class handles all API requests for the 'tournaments' endpoint.
 */
class CricScore_Tournament_Controller {

    protected $namespace = 'cricscore/v1';
    protected $rest_base = 'tournaments';

    /**
     * Registers the routes for this controller.
     */
    public function register_routes() {
        // Route for getting all tournaments and creating a new one
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

        // Route for updating and deleting a single tournament
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                'args' => [
                    'id' => [
                        'description' => 'Unique identifier for the tournament.',
                        'type'        => 'integer',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_item' ],
                    'permission_callback' => [ $this, 'item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema(true),
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'item_permissions_check' ],
                ],
            ]
        );
    }

    /**
     * Generic permissions check for collections.
     */
    public function permissions_check( $request ) {
        return is_user_logged_in();
    }

    /**
     * Permissions check for single items.
     */
    public function item_permissions_check( $request ) {
        // A robust check would verify ownership here.
        return is_user_logged_in();
    }

    /**
     * Retrieves a collection of tournaments.
     */
    public function get_items( $request ) {
        $user_id = get_current_user_id();
        $tournaments = CricScore_DB()->get_user_tournaments( $user_id );
        return new WP_REST_Response( $tournaments, 200 );
    }

    /**
     * Creates a single tournament.
     */
    public function create_item( $request ) {
        $user_id = get_current_user_id();
        $args = [
            'name'       => $request->get_param( 'name' ),
            'format'     => $request->get_param( 'format' ),
            'start_date' => $request->get_param( 'start_date' ),
            'end_date'   => $request->get_param( 'end_date' ),
        ];

        $tournament_id = CricScore_DB()->create_tournament( $user_id, $args );

        if ( ! $tournament_id ) {
            return new WP_Error( 'cricscore_tournament_creation_failed', __( 'Failed to create tournament.', 'cricscore' ), [ 'status' => 500 ] );
        }
        return new WP_REST_Response( [ 'success' => true, 'tournament_id' => $tournament_id ], 201 );
    }

    /**
     * Updates a single tournament.
     */
    public function update_item( $request ) {
        $tournament_id = (int) $request['id'];
        $user_id       = get_current_user_id();
        $data = [
            'name'       => $request->get_param( 'name' ),
            'format'     => $request->get_param( 'format' ),
            'start_date' => $request->get_param( 'start_date' ),
            'end_date'   => $request->get_param( 'end_date' ),
        ];

        $result = CricScore_DB()->update_tournament( $tournament_id, $user_id, $data );

        if ( ! $result ) {
            return new WP_Error( 'cricscore_tournament_update_failed', __( 'Failed to update tournament or not authorized.', 'cricscore' ), [ 'status' => 500 ] );
        }
        return new WP_REST_Response( [ 'success' => true ], 200 );
    }

    /**
     * Deletes a single tournament.
     */
    public function delete_item( $request ) {
        $tournament_id = (int) $request['id'];
        $user_id       = get_current_user_id();

        $result = CricScore_DB()->delete_tournament( $tournament_id, $user_id );

        if ( ! $result ) {
            return new WP_Error( 'cricscore_tournament_delete_failed', __( 'Failed to delete tournament or not authorized.', 'cricscore' ), [ 'status' => 403 ] );
        }
        return new WP_REST_Response( [ 'success' => true ], 200 );
    }

    /**
     * Retrieves the endpoint arguments for the controller.
     */
    public function get_endpoint_args_for_item_schema( $is_update = false ) {
        $args = [];
        $args['name'] = [
            'description'       => __( 'The name of the tournament or series.', 'cricscore' ),
            'type'              => 'string',
            'required'          => ! $is_update,
            'sanitize_callback' => 'sanitize_text_field',
        ];
        $args['format'] = [
            'description'       => __( 'The format of the tournament.', 'cricscore' ),
            'type'              => 'string',
            'required'          => false,
        ];
        $args['start_date'] = [
            'description'       => __( 'The start date of the tournament.', 'cricscore' ),
            'type'              => 'string',
            'format'            => 'date',
            'required'          => false,
        ];
        $args['end_date'] = [
            'description'       => __( 'The end date of the tournament.', 'cricscore' ),
            'type'              => 'string',
            'format'            => 'date',
            'required'          => false,
        ];
        return $args;
    }
}