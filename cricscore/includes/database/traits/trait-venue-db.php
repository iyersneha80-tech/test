<?php
/**
 * Trait for Venue Database Operations.
 *
 * @package CricScore
 * @version 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Venue_DB_Trait {

    public function create_venue( $user_id, $name, $city = '', $country = '' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_venues';
        $data = [
            'user_id'    => $user_id,
            'name'       => $name,
            'city'       => $city,
            'country'    => $country,
            'created_at' => current_time( 'mysql' ),
        ];
        $format = [ '%d', '%s', '%s', '%s', '%s' ];
        $result = $wpdb->insert( $table_name, $data, $format );
        if ( ! $result ) { return false; }
        return $wpdb->insert_id;
    }
    /**
     * Get the user ID of the owner of a specific venue.
     *
     * @param int $venue_id The ID of the venue.
     * @return int|null The owner's user ID, or null if not found.
     */
    public function get_venue_owner( $venue_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_venues';
        return $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$table_name} WHERE id = %d", $venue_id ) );
    }

    public function get_user_venues( $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_venues';
        $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY name ASC", $user_id );
        return $wpdb->get_results( $query );
    }

    /**
     * Updates an existing venue.
     *
     * @param int   $venue_id The ID of the venue to update.
     * @param int   $user_id  The ID of the current user (for ownership check).
     * @param array $data     The data to update.
     * @return bool True on success, false on failure or if not owner.
     */
    public function update_venue( $venue_id, $user_id, $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_venues';
        $where = [ 'id' => $venue_id, 'user_id' => $user_id ];
        return $wpdb->update( $table_name, $data, $where ) !== false;
    }

    /**
     * Deletes a venue.
     *
     * @param int $venue_id The ID of the venue to delete.
     * @param int $user_id  The ID of the current user (for ownership check).
     * @return bool True on success, false on failure or if not owner.
     */
    public function delete_venue( $venue_id, $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_venues';
        $where = [ 'id' => $venue_id, 'user_id' => $user_id ];
        return $wpdb->delete( $table_name, $where ) !== false;
    }
}