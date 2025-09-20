<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CricScore_Offline_DB {

    protected static function table_events() { global $wpdb; return $wpdb->prefix . 'cs_events'; }
    protected static function table_snapshots() { global $wpdb; return $wpdb->prefix . 'cs_snapshots'; }
    protected static function table_tokens() { global $wpdb; return $wpdb->prefix . 'cs_scorer_tokens'; }

    public static function issue_token( $match_id, $user_id, $ttl_hours = 8 ) {
        global $wpdb;
        $token = wp_generate_password( 32, false, false );
        $hash = hash('sha256', $token);
        $expires_at = gmdate( 'Y-m-d H:i:s', time() + ( $ttl_hours * HOUR_IN_SECONDS ) );
        $wpdb->insert( self::table_tokens(), [
            'match_id' => $match_id,
            'user_id' => $user_id,
            'token_hash' => $hash,
            'active' => 1,
            'expires_at' => $expires_at,
        ] );
        return [ 'token' => $token, 'expires_at' => $expires_at ];
    }

    public static function revoke_tokens( $match_id ) {
        global $wpdb;
        return $wpdb->update( self::table_tokens(), ['active' => 0], ['match_id' => $match_id] );
    }

    public static function validate_token( $match_id, $token ) {
        global $wpdb;
        if ( empty( $token ) ) { return new WP_Error('forbidden', 'Missing scorer token', ['status'=>403]); }
        $hash = hash('sha256', $token);
        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::table_tokens() . " WHERE match_id=%s AND token_hash=%s AND active=1 LIMIT 1",
            $match_id, $hash
        ) );
        if ( ! $row ) { return new WP_Error('forbidden', 'Invalid or inactive scorer token', ['status'=>403]); }
        if ( strtotime( $row->expires_at ) < time() ) { return new WP_Error('forbidden', 'Token expired', ['status'=>403]); }
        return true;
    }

    public static function insert_events( $match_id, $events ) {
        global $wpdb;
        $table = self::table_events();
        $accepted = [];
        $rejected = [];
        foreach ( $events as $e ) {
            $event_id = isset($e['event_id']) ? $e['event_id'] : '';
            if ( empty($event_id) ) {
                $rejected[] = ['event_id' => null, 'reason' => 'missing_event_id'];
                continue;
            }
            $payload = wp_json_encode( $e );
            $data = [
                'match_id' => $match_id,
                'event_id' => $event_id,
                'innings_no' => intval($e['innings_no'] ?? 1),
                'over_no' => intval($e['over_no'] ?? 0),
                'ball_no' => intval($e['ball_no'] ?? 0),
                'payload' => $payload,
                'author_device_id' => sanitize_text_field($e['author_device_id'] ?? ''),
                'timestamp_client' => intval($e['timestamp_client'] ?? 0),
            ];
            $format = ['%s','%s','%d','%d','%d','%s','%s','%d'];
            $ok = $wpdb->insert( $table, $data, $format );
            if ( $ok ) {
                $accepted[] = $event_id;
            } else {
                // If duplicate (unique event_id), mark as accepted (idempotent)
                $err = $wpdb->last_error;
                if ( strpos( strtolower($err), 'duplicate' ) !== false ) {
                    $accepted[] = $event_id;
                } else {
                    $rejected[] = array('event_id'=>$event_id,'reason'=>'db_error');
                }
            }
        }
        // Get last server_seq for the match
        $last_seq = intval( $wpdb->get_var( $wpdb->prepare( "SELECT MAX(server_seq) FROM $table WHERE match_id=%s", $match_id ) ) );
        return [ $accepted, $rejected, $last_seq ];
    }

    public static function get_events_since( $match_id, $since_seq = 0, $limit = 500 ) {
        global $wpdb;
        $table = self::table_events();
        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT server_seq, payload FROM $table WHERE match_id=%s AND server_seq>%d ORDER BY server_seq ASC LIMIT %d",
            $match_id, $since_seq, $limit
        ) );
        $events = [];
        $last = $since_seq;
        foreach ( $rows as $r ) {
            $events[] = json_decode( $r->payload, true );
            $last = max( $last, intval($r->server_seq) );
        }
        return [ $events, $last ];
    }
}
