<?php
/**
 * Trait for Ball Log Database Operations.
 *
 * @package CricScore
 * @version 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Ball_Log_DB_Trait {

    /**
     * Logs a single ball event to the database.
     *
     * @param array $args An array of data for the ball event.
     * @return int|false The ID of the newly created log entry, or false on failure.
     */
    public function log_ball_event( $args ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_ball_log';

        $defaults = [
            'match_id'      => 0,
            'innings_id'    => 0,
            'over_number'   => 0,
            'ball_number'   => 0,
            'batsman_id'    => 0,
            'bowler_id'     => 0,
            'runs_scored'   => 0,
            'extras_type'   => null,
            'extras_runs'   => 0,
            'is_wicket'     => false,
            'wicket_data'   => [],
        ];

        $data = wp_parse_args( $args, $defaults );

        // Add timestamp and encode JSON data
        $data['timestamp']   = current_time( 'mysql' );
        $data['wicket_data'] = wp_json_encode( $data['wicket_data'] );
        $data['is_wicket']   = (bool) $data['is_wicket'];

        $result = $wpdb->insert( $table_name, $data );

        if ( ! $result ) {
            return false;
        }

        return $wpdb->insert_id;
    }
}