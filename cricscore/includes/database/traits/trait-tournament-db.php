<?php
/**
 * Trait for Tournament Database Operations.
 *
 * @package CricScore
 * @version 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Tournament_DB_Trait {

    public function create_tournament( $user_id, $args ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_tournaments';
        $defaults = [ 'name' => '', 'format' => 'League', 'start_date' => null, 'end_date' => null ];
        $data = wp_parse_args( $args, $defaults );
        $data['user_id'] = $user_id;
        $result = $wpdb->insert( $table_name, $data );
        if ( ! $result ) { return false; }
        return $wpdb->insert_id;
    }

    public function get_user_tournaments( $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_tournaments';
        $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY start_date DESC, name ASC", $user_id );
        return $wpdb->get_results( $query );
    }

    /**
     * Updates an existing tournament.
     *
     * @param int   $tournament_id The ID of the tournament to update.
     * @param int   $user_id       The ID of the current user (for ownership check).
     * @param array $data          The data to update.
     * @return bool True on success, false on failure or if not owner.
     */
    public function update_tournament( $tournament_id, $user_id, $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_tournaments';
        $where = [ 'id' => $tournament_id, 'user_id' => $user_id ];
        return $wpdb->update( $table_name, $data, $where ) !== false;
    }

    /**
     * Deletes a tournament.
     *
     * @param int $tournament_id The ID of the tournament to delete.
     * @param int $user_id       The ID of the current user (for ownership check).
     * @return bool True on success, false on failure or if not owner.
     */
    public function delete_tournament( $tournament_id, $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_tournaments';
        $where = [ 'id' => $tournament_id, 'user_id' => $user_id ];
        return $wpdb->delete( $table_name, $where ) !== false;
    }
}