<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Installer
require_once __DIR__ . '/class-cricscore-offline-installer.php';
add_action( 'plugins_loaded', function() {
    \CricScore_Offline_Installer::maybe_install();
}, 1 );

// REST routes
require_once __DIR__ . '/db/class-cricscore-offline-db.php';
require_once __DIR__ . '/controllers/class-cricscore-events-controller.php';
require_once __DIR__ . '/controllers/class-cricscore-scorer-auth-controller.php';

// Frontend enqueue (auto on match pages)
add_action( 'wp_enqueue_scripts', function() {
    $ver = defined('CRICSCORE_VERSION') ? CRICSCORE_VERSION : '1.0.0';
    $base = trailingslashit( CRICSCORE_URL ) . 'templates/match/assets';

    $uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field($_SERVER['REQUEST_URI']) : '';
    $enabled = false;
    if ( strpos($uri, '/match/') !== false ) { $enabled = true; } // auto-enable on match pages
    if ( isset($_GET['cr_offline']) && '1' === $_GET['cr_offline'] ) { $enabled = true; } // dev flag

    if ( ! $enabled ) { return; }

    wp_enqueue_style( 'cricscore-status', $base . '/css/status.css', [], $ver );
    wp_enqueue_script( 'cricscore-offline-db', $base . '/js/offline/db.js', [], $ver, true );
    wp_enqueue_script( 'cricscore-offline-model', $base . '/js/offline/events-model.js', ['cricscore-offline-db'], $ver, true );
    wp_enqueue_script( 'cricscore-offline-sync', $base . '/js/offline/sync.js', ['cricscore-offline-model'], $ver, true );
    wp_enqueue_script( 'cricscore-offline-ui', $base . '/js/offline/ui-status.js', ['cricscore-offline-sync'], $ver, true );
    wp_enqueue_script( 'cricscore-offline-int', $base . '/js/offline/integrations.js', ['cricscore-offline-ui'], $ver, true );

    $match_id = isset($_GET['match_id']) ? sanitize_text_field($_GET['match_id']) : '';
    if ( empty($match_id) && $uri ) {
        if ( preg_match('~/(?:match)/([^/\?]+)~', $uri, $m) ) {
            $match_id = sanitize_text_field($m[1]);
        }
    }

    $cfg = [
        'apiRoot'   => esc_url_raw( rest_url( CRICSCORE_API_NS ) ),
        'matchId'   => $match_id,
        'deviceId'  => substr( wp_hash( wp_get_session_token() . '|' . get_current_user_id() ), 0, 16 ),
        'nonce'     => wp_create_nonce( 'wp_rest' ),
    ];
    wp_localize_script( 'cricscore-offline-sync', 'CricScoreCfg', $cfg );
}, 20 );
