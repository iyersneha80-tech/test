<?php
/**
 * REST API controller for Venues.
 *
 * @package CricScore
 * @version 1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CricScore_Venue_Controller
 *
 * This class handles all API requests for the 'venues' endpoint.
 */
class CricScore_Venue_Controller {

    protected $namespace = 'cricscore/v1';
    protected $rest_base = 'venues';

    /**
     * Registers the routes for this controller.
     */
    public function register_routes() {
        // Route for getting all venues and creating a new one
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

        // Route for updating and deleting a single venue
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                'args' => [
                    'id' => [
                        'description' => 'Unique identifier for the venue.',
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
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $venue_id = (int) $request['id'];
        $owner_id = (int) CricScore_DB()->get_venue_owner( $venue_id );

        return get_current_user_id() === $owner_id;
    }

    /**
     * Retrieves a collection of venues.
     */
    public function get_items( $request ) {
        $user_id = get_current_user_id();
        $venues = CricScore_DB()->get_user_venues( $user_id );
        return new WP_REST_Response( $venues, 200 );
    }

    /**
     * Creates a single venue.
     */
    public function create_item( $request ) {
        $user_id = get_current_user_id();
        $name    = $request->get_param( 'name' );
        $city    = $request->get_param( 'city' );
        $country = $request->get_param( 'country' );

        $venue_id = CricScore_DB()->create_venue( $user_id, $name, $city, $country );

        if ( ! $venue_id ) {
            return new WP_Error( 'cricscore_venue_creation_failed', __( 'Failed to create venue.', 'cricscore' ), [ 'status' => 500 ] );
        }
        return new WP_REST_Response( [ 'success' => true, 'venue_id' => $venue_id ], 201 );
    }

    /**
     * Updates a single venue.
     */
    public function update_item( $request ) {
        $venue_id = (int) $request['id'];
        $user_id  = get_current_user_id();
        $data = [
            'name'    => $request->get_param( 'name' ),
            'city'    => $request->get_param( 'city' ),
            'country' => $request->get_param( 'country' ),
        ];

        $result = CricScore_DB()->update_venue( $venue_id, $user_id, $data );

        if ( ! $result ) {
            return new WP_Error( 'cricscore_venue_update_failed', __( 'Failed to update venue or not authorized.', 'cricscore' ), [ 'status' => 500 ] );
        }
        return new WP_REST_Response( [ 'success' => true ], 200 );
    }

    /**
     * Deletes a single venue.
     */
    public function delete_item( $request ) {
        $venue_id = (int) $request['id'];
        $user_id  = get_current_user_id();

        $result = CricScore_DB()->delete_venue( $venue_id, $user_id );

        if ( ! $result ) {
            return new WP_Error( 'cricscore_venue_delete_failed', __( 'Failed to delete venue or not authorized.', 'cricscore' ), [ 'status' => 403 ] );
        }
        return new WP_REST_Response( [ 'success' => true ], 200 );
    }

    /**
     * Retrieves the endpoint arguments for the controller.
     */
    public function get_endpoint_args_for_item_schema( $is_update = false ) {
        $args = [];
        $args['name'] = [
            'description'       => __( 'The name of the venue or ground.', 'cricscore' ),
            'type'              => 'string',
            'required'          => ! $is_update,
            'sanitize_callback' => 'sanitize_text_field',
        ];
        $args['city'] = [
            'description'       => __( 'The city where the venue is located.', 'cricscore' ),
            'type'              => 'string',
            'required'          => false,
            'sanitize_callback' => 'sanitize_text_field',
        ];
        $args['country'] = [
            'description'       => __( 'The country where the venue is located.', 'cricscore' ),
            'type'              => 'string',
            'required'          => false,
            'sanitize_callback' => 'sanitize_text_field',
        ];
        return $args;
    }
}