<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CricScore_Scorer_Auth_Controller {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        register_rest_route( CRICSCORE_API_NS, '/matches/(?P<match_id>[a-zA-Z0-9\-]+)/scorer-token', [
            [
                'methods'  => 'POST',
                'callback' => [ $this, 'issue_token' ],
                'permission_callback' => [ $this, 'can_issue' ],
            ],
            [
                'methods'  => 'DELETE',
                'callback' => [ $this, 'revoke' ],
                'permission_callback' => [ $this, 'can_issue' ],
            ],
        ] );
    }

    public function can_issue( WP_REST_Request $req ) {
        return current_user_can('edit_posts');
    }

    public function issue_token( WP_REST_Request $req ) {
        $match_id = sanitize_text_field( $req['match_id'] );
        $user_id = get_current_user_id();
        $ttl = intval( $req->get_param('ttl') );
        if ( $ttl <= 0 || $ttl > 24 ) { $ttl = 8; }
        // revoke existing active tokens for strict lock
        \CricScore_Offline_DB::revoke_tokens( $match_id );
        $issued = \CricScore_Offline_DB::issue_token( $match_id, $user_id, $ttl );
        return new WP_REST_Response( $issued, 201 );
    }

    public function revoke( WP_REST_Request $req ) {
        $match_id = sanitize_text_field( $req['match_id'] );
        \CricScore_Offline_DB::revoke_tokens( $match_id );
        return new WP_REST_Response( [ 'ok' => true ], 200 );
    }
}
new CricScore_Scorer_Auth_Controller();
