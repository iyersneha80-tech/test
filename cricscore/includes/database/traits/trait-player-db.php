<?php
/**
 * Trait for Player Database Operations.
 *
 * @package CricScore
 * @version 1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Player_DB_Trait {

    /**
     * Create a new player for a user with advanced details.
     *
     * @param int   $user_id The ID of the user creating the player.
     * @param array $args    An array of player data.
     * @return int|false The ID of the newly created player, or false on failure.
     */
    public function create_player( $user_id, $args ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_players';

        $defaults = [
            'name'              => '',
            'dob'               => null,
            'role'              => null,
            'batting_style'     => null,
            'bowling_style'     => null,
            'country'           => null,
            'profile_image_url' => null,
        ];
        $data = wp_parse_args( $args, $defaults );

        // Add user_id, timestamp, and the new share_slug
        $data['user_id'] = $user_id;
        $data['created_at'] = current_time( 'mysql' );
        $data['share_slug'] = substr( str_shuffle( 'abcdefghijklmnopqrstuvwxyz0123456789' ), 0, 6 );

        $result = $wpdb->insert( $table_name, $data );

        if ( ! $result ) {
            return false;
        }
        return $wpdb->insert_id;
    }
    /**
     * Get the user ID of the owner of a specific player.
     *
     * @param int $player_id The ID of the player.
     * @return int|null The owner's user ID, or null if not found.
     */
    public function get_player_owner( $player_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_players';
        return $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$table_name} WHERE id = %d", $player_id ) );
    }

    /**
     * Get all players for a specific user.
     *
     * @param int $user_id The ID of the user.
     * @return array An array of player objects.
     */
    public function get_user_players( $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_players';
        $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY name ASC", $user_id );
        return $wpdb->get_results( $query );
    }

    /**
     * Updates an existing player with advanced details.
     *
     * @param int   $player_id The ID of the player to update.
     * @param int   $user_id   The ID of the current user (for ownership check).
     * @param array $data      The data to update.
     * @return bool True on success, false on failure or if not owner.
     */
    public function update_player( $player_id, $user_id, $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_players';

        $where = [
            'id'      => $player_id,
            'user_id' => $user_id,
        ];

        $result = $wpdb->update( $table_name, $data, $where );

        return $result !== false;
    }

    /**
     * Deletes a player.
     *
     * @param int $player_id The ID of the player to delete.
     * @param int $user_id   The ID of the current user (for ownership check).
     * @return bool True on success, false on failure or if not owner.
     */
    public function delete_player( $player_id, $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_players';

        $where = [
            'id'      => $player_id,
            'user_id' => $user_id,
        ];

        $result = $wpdb->delete( $table_name, $where );

        return $result !== false;
    }
    /**
 * Get all public-facing data for a single player, including aggregated stats.
 *
 * @param int $player_id The ID of the player.
 * @return array|null An array of player data, or null if not found.
 */
/**
 * Get all public-facing data for a single player, including aggregated stats.
 *
 * @param int $player_id The ID of the player.
 * @return array|null An array of player data, or null if not found.
 */
public function get_player_profile_data( $player_id ) {
    global $wpdb;
    $players_table = $wpdb->prefix . 'cricscore_players';
    $batting_stats_table = $wpdb->prefix . 'cricscore_batting_stats';
    $bowling_stats_table = $wpdb->prefix . 'cricscore_bowling_stats';
    $matches_table = $wpdb->prefix . 'cricscore_matches';
    $venues_table = $wpdb->prefix . 'cricscore_venues';
    $teams_table = $wpdb->prefix . 'cricscore_teams';

    // 1. Get basic player info
    $player_info = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$players_table} WHERE id = %d", $player_id ), ARRAY_A );
    if ( ! $player_info ) {
        return null;
    }

    // 2. Get aggregated batting stats
    $batting_stats = $wpdb->get_row( $wpdb->prepare( "
        SELECT
            COUNT(DISTINCT match_id) as matches,
            COUNT(id) as innings,
            SUM(runs_scored) as runs,
            MAX(runs_scored) as high_score,
            SUM(balls_faced) as balls_faced,
            SUM(fours) as fours,
            SUM(sixes) as sixes,
            SUM(CASE WHEN dismissal_type != 'not out' AND dismissal_type != '' THEN 1 ELSE 0 END) as dismissals,
            COUNT(CASE WHEN runs_scored >= 100 THEN 1 ELSE NULL END) as hundreds,
            COUNT(CASE WHEN runs_scored >= 50 AND runs_scored < 100 THEN 1 ELSE NULL END) as fifties
        FROM {$batting_stats_table}
        WHERE player_id = %d
    ", $player_id ), ARRAY_A );

    // 3. Get aggregated bowling stats
    $bowling_stats = $wpdb->get_row( $wpdb->prepare( "
        SELECT
            COUNT(DISTINCT innings_id) as innings,
            SUM(wickets_taken) as wickets,
            SUM(runs_conceded) as runs_conceded,
            SUM(FLOOR(overs_bowled) * 6 + (overs_bowled - FLOOR(overs_bowled)) * 10) as balls_bowled,
            SUM(maidens) as maidens,
            SUM(CASE WHEN wickets_taken >= 5 THEN 1 ELSE NULL END) as five_fers,
            SUM(CASE WHEN wickets_taken = 3 THEN 1 ELSE NULL END) as three_fers
        FROM {$bowling_stats_table}
        WHERE player_id = %d
    ", $player_id ), ARRAY_A );

    // 4. Get Best Bowling Figures
    $best_bowling = $wpdb->get_row( $wpdb->prepare( "
        SELECT wickets_taken, runs_conceded
        FROM {$bowling_stats_table}
        WHERE player_id = %d
        ORDER BY wickets_taken DESC, runs_conceded ASC
        LIMIT 1
    ", $player_id ), ARRAY_A );

    $best_figures_str = 'N/A';
    if ( $best_bowling && $best_bowling['wickets_taken'] > 0 ) {
        $best_figures_str = "{$best_bowling['wickets_taken']}/{$best_bowling['runs_conceded']}";
    }

    // 5. Get Match Log
    $match_log = $wpdb->get_results( $wpdb->prepare( "
        SELECT
            m.id,
            m.share_slug, -- THIS LINE IS ADDED
            m.created_at as date,
            v.name as venue,
            t1.name as team1,
            t2.name as team2
        FROM {$matches_table} m
        JOIN {$batting_stats_table} bs ON m.id = bs.match_id
        LEFT JOIN {$venues_table} v ON m.venue_id = v.id
        LEFT JOIN {$teams_table} t1 ON m.team1_id = t1.id
        LEFT JOIN {$teams_table} t2 ON m.team2_id = t2.id
        WHERE bs.player_id = %d
        GROUP BY m.id
        ORDER BY m.created_at DESC
    ", $player_id ), ARRAY_A );

    // 6. Calculate final stats to avoid division by zero
    $batting_avg = ( $batting_stats['dismissals'] > 0 ) ? ( $batting_stats['runs'] / $batting_stats['dismissals'] ) : null;
    $batting_sr = ( $batting_stats['balls_faced'] > 0 ) ? ( $batting_stats['runs'] * 100 / $batting_stats['balls_faced'] ) : 0;

    $bowling_avg = ( $bowling_stats['wickets'] > 0 ) ? ( $bowling_stats['runs_conceded'] / $bowling_stats['wickets'] ) : 0;
    $bowling_econ = ( $bowling_stats['balls_bowled'] > 0 ) ? ( $bowling_stats['runs_conceded'] * 6 / $bowling_stats['balls_bowled'] ) : 0;
    $bowling_sr = ( $bowling_stats['wickets'] > 0 ) ? ( $bowling_stats['balls_bowled'] / $bowling_stats['wickets'] ) : 0;

    $overs_bowled_formatted = floor( $bowling_stats['balls_bowled'] / 6 ) . '.' . ( $bowling_stats['balls_bowled'] % 6 );

    // 7. Combine everything into a single response
    return [
        'info' => $player_info,
        'stats' => [
            'batting' => [
                'matches' => (int) $batting_stats['matches'],
                'innings' => (int) $batting_stats['innings'],
                'runs' => (int) $batting_stats['runs'],
                'high_score' => (int) $batting_stats['high_score'],
                'average' => is_null($batting_avg) ? null : round( $batting_avg, 2 ),
                'strike_rate' => round( $batting_sr, 2 ),
                'hundreds' => (int) $batting_stats['hundreds'],
                'fifties' => (int) $batting_stats['fifties'],
            ],
            'bowling' => [
                'innings' => (int) $bowling_stats['innings'],
                'wickets' => (int) $bowling_stats['wickets'],
                'overs_bowled' => (float) $overs_bowled_formatted,
                'average' => round( $bowling_avg, 2 ),
                'economy' => round( $bowling_econ, 2 ),
                'strike_rate' => round( $bowling_sr, 2 ),
                'best_figures' => $best_figures_str,
                'five_fers' => (int) $bowling_stats['five_fers'],
                'three_fers' => (int) $bowling_stats['three_fers'],
            ]
        ],
        'match_log' => $match_log
    ];
}
}