<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CricScore_Events_Controller {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( CRICSCORE_API_NS, '/matches/(?P<match_id>[a-zA-Z0-9\-]+)/events', [
            [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_events' ],
                'permission_callback' => '__return_true',
                'args' => [
                    'since' => ['type'=>'integer', 'default'=>0],
                    'limit' => ['type'=>'integer', 'default'=>500],
                ]
            ],
            [
                'methods'  => 'POST',
                'callback' => [ $this, 'post_events' ],
                'permission_callback' => '__return_true',
            ],
        ] );
    }

    public function get_events( WP_REST_Request $req ) {
        $match_id = sanitize_text_field( $req['match_id'] );
        $since = intval( $req->get_param('since') );
        $limit = max(1, min(1000, intval($req->get_param('limit')) ));
        list($events, $last) = \CricScore_Offline_DB::get_events_since( $match_id, $since, $limit );
        return new WP_REST_Response([ 'events' => $events, 'last_server_seq' => $last ], 200);
    }

    public function post_events( WP_REST_Request $req ) {
        $match_id = sanitize_text_field( $req['match_id'] );
        $body = $req->get_json_params();
        $token = isset($body['scorer_token']) ? $body['scorer_token'] : '';
        $events = isset($body['events']) && is_array($body['events']) ? $body['events'] : [];

        $valid = \CricScore_Offline_DB::validate_token( $match_id, $token );
        if ( is_wp_error($valid) ) { return $valid; }

        list($accepted, $rejected, $last) = \CricScore_Offline_DB::insert_events( $match_id, $events );
        return new WP_REST_Response([
            'accepted' => $accepted,
            'rejected' => $rejected,
            'last_server_seq' => $last
        ], 200);
    }
}

new CricScore_Events_Controller();
