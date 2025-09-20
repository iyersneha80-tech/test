<?php
/**
 * REST API controller for Media Uploads.
 *
 * @package CricScore
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CricScore_Media_Controller {

    protected $namespace = 'cricscore/v1';
    protected $rest_base = 'media';

    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/upload',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'upload_image' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
            ]
        );
    }

    public function permissions_check( $request ) {
        return is_user_logged_in();
    }

    public function upload_image( $request ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        if ( empty( $_FILES['file'] ) ) {
             return new WP_Error( 'cricscore_upload_failed', 'No file was uploaded.', [ 'status' => 400 ] );
        }
        
        $file = $_FILES['file'];
        $upload_overrides = [ 'test_form' => false ];
        $movefile = wp_handle_upload( $file, $upload_overrides );

        if ( $movefile && ! isset( $movefile['error'] ) ) {
            return new WP_REST_Response( [ 'success' => true, 'url' => $movefile['url'] ], 200 );
        } else {
            return new WP_Error( 'cricscore_upload_failed', $movefile['error'], [ 'status' => 500 ] );
        }
    }
}