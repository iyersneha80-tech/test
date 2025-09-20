<?php
/**
 * Trait for Team Database Operations.
 *
 * @package CricScore
 * @version 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Team_DB_Trait {

    /**
     * Create a new team for a user.
     *
     * @param int    $user_id    The ID of the user creating the team.
     * @param string $name       The full name of the team.
     * @param string $short_name The short name of the team.
     * @return int|false The ID of the newly created team, or false on failure.
     */
    public function create_team( $user_id, $args ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_teams';

        $defaults = [
            'name'            => '',
            'short_name'      => '',
            'logo_url'        => null,
            'country'         => null,
            'captain_id'      => null,
            'vice_captain_id' => null,
        ];
        $data = wp_parse_args( $args, $defaults );

        // Add user_id, timestamp, and the new share_slug
        $data['user_id'] = $user_id;
        $data['created_at'] = current_time( 'mysql' );
        $data['share_slug'] = substr( str_shuffle( 'abcdefghijklmnopqrstuvwxyz0123456789' ), 0, 6 );

        // Define the format of each piece of data
        $format = [
            'name'            => '%s',
            'short_name'      => '%s',
            'logo_url'        => '%s',
            'country'         => '%s',
            'captain_id'      => '%d',
            'vice_captain_id' => '%d',
            'user_id'         => '%d',
            'created_at'      => '%s',
            'share_slug'      => '%s',
        ];

        $result = $wpdb->insert( $table_name, $data, $format );

        if ( ! $result ) {
            return false;
        }
        return $wpdb->insert_id;
    }
    /**
     * Get the user ID of the owner of a specific team.
     *
     * @param int $team_id The ID of the team.
     * @return int|null The owner's user ID, or null if not found.
     */
    public function get_team_owner( $team_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_teams';
        return $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$table_name} WHERE id = %d", $team_id ) );
    }

    /**
     * Get all teams for a specific user.
     *
     * @param int $user_id The ID of the user.
     * @return array An array of team objects.
     */
    public function get_user_teams( $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_teams';
        // Select all team columns so the edit modal has the full data
        $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY name ASC", $user_id );
        return $wpdb->get_results( $query );
    }

    /**
     * Updates an existing team.
     *
     * @param int   $team_id The ID of the team to update.
     * @param int   $user_id The ID of the current user (for ownership check).
     * @param array $data    The data to update.
     * @return bool True on success, false on failure or if not owner.
     */
    public function update_team( $team_id, $user_id, $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_teams';

        $where = [
            'id'      => $team_id,
            'user_id' => $user_id,
        ];

        // Define the format of ALL possible data columns
        $format = [
            'name'            => '%s',
            'short_name'      => '%s',
            'logo_url'        => '%s',
            'country'         => '%s',
            'captain_id'      => '%d',
            'vice_captain_id' => '%d',
        ];

        // Get the formats only for the data we are actually updating
        $data_format = array_intersect_key( $format, $data );
        
        // Define the format for the WHERE clause
        $where_format = [ '%d', '%d' ];

        $result = $wpdb->update( $table_name, $data, $where, $data_format, $where_format );

        return $result !== false;
    }
    
    /**
     * Updates the summary stats for a team (wins, losses, etc.).
     *
     * @param int    $team_id The ID of the team to update.
     * @param string $result  The result for this team ('win', 'loss', 'tie').
     */
    public function update_team_stats( $team_id, $result ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_teams';

        // Determine which column to increment
        $column_to_increment = '';
        switch ( $result ) {
            case 'win':
                $column_to_increment = 'wins';
                break;
            case 'loss':
                $column_to_increment = 'losses';
                break;
            case 'tie':
                $column_to_increment = 'ties';
                break;
        }

        if ( empty( $column_to_increment ) ) {
            return; // Do nothing if result is not one of the expected values
        }

        // Use a direct query to increment the values
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table_name}
                 SET matches_played = matches_played + 1,
                     {$column_to_increment} = {$column_to_increment} + 1
                 WHERE id = %d",
                $team_id
            )
        );
    }

    /**
     * Deletes a team.
     *
     * @param int $team_id The ID of the team to delete.
     * @param int $user_id The ID of the current user (for ownership check).
     * @return bool True on success, false on failure or if not owner.
     */
    public function delete_team( $team_id, $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_teams';

        // Security Check: Ensure the user owns this team before deleting.
        $where = [
            'id'      => $team_id,
            'user_id' => $user_id,
        ];

        $result = $wpdb->delete( $table_name, $where );

        return $result !== false;
    }
}