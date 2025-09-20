<?php
/**
 * Trait for Match Database Operations.
 *
 * @package CricScore
 * @version 1.3.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Match_DB_Trait {

    // ... (create_match, get_match_owner functions remain unchanged) ...
    public function create_match( $user_id, $args ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_matches';
        
        $team1_players = $args['team1_players'] ?? [];
        $team2_players = $args['team2_players'] ?? [];
        $rules = $args['rules'] ?? [];
        unset( $args['team1_players'], $args['team2_players'] );
        unset( $args['rules'] );

        $defaults = [ 'team1_id' => 0, 'team2_id' => 0, 'venue_id' => null, 'tournament_id' => null, 'match_format' => 'T20', 'overs_per_innings' => 20, 'toss_data' => [] ];
        $data = wp_parse_args( $args, $defaults );
        $data['user_id'] = $user_id;
        $data['status'] = 'pending';
        $data['created_at'] = current_time( 'mysql' );
        $data['toss_data'] = wp_json_encode( $data['toss_data'] );
        $data['share_slug'] = substr( str_shuffle( 'abcdefghijklmnopqrstuvwxyz0123456789' ), 0, 6 );

        // --- FIX: Construct and save the initial live_state with the rules ---
        $initial_live_state = [
            'rules' => $rules,
        ];
        $data['live_state'] = wp_json_encode( $initial_live_state );
        
        $result = $wpdb->insert( $table_name, $data );
        if ( ! $result ) { return false; }
        
        $match_id = $wpdb->insert_id;

        $this->add_players_to_match( $match_id, $data['team1_id'], $team1_players );
        $this->add_players_to_match( $match_id, $data['team2_id'], $team2_players );

        return $match_id;
    }

    public function get_match_owner( $match_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_matches';
        return $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$table_name} WHERE id = %d", $match_id ) );
    }


    public function save_match_state( $match_id, $state ) {
        global $wpdb;
        $matches_table       = $wpdb->prefix . 'cricscore_matches';
        $innings_table       = $wpdb->prefix . 'cricscore_innings';
        $batting_stats_table = $wpdb->prefix . 'cricscore_batting_stats';
        $bowling_stats_table = $wpdb->prefix . 'cricscore_bowling_stats';

        $wpdb->query('START TRANSACTION');

        try {
            if ( ! isset( $state['result'] ) ) {
                $wpdb->update(
                    $matches_table,
                    [
                        'status' => 'live',
                        'live_state' => wp_json_encode( $state ),
                    ],
                    [ 'id' => $match_id ]
                );
            }

            $all_innings = [];
            if (isset($state['firstInnings'])) $all_innings[] = $state['firstInnings'];
            if (isset($state['currentInnings']) && !empty($state['currentInnings']['isComplete'])) {
                $all_innings[] = $state['currentInnings'];
            }

            foreach ( $all_innings as $innings_data ) {
                if (empty($innings_data)) continue;

                $extras_json = [
                    'extras' => $innings_data['extras'] ?? [],
                    'battingOrder' => $innings_data['battingOrder'] ?? []
                ];

                $innings_record = [ 
                    'match_id' => $match_id, 
                    'innings_number' => $innings_data['number'], 
                    'batting_team_id' => $innings_data['batting_team_id'], 
                    'total_score' => $innings_data['score'], 
                    'total_wickets' => $innings_data['wickets'], 
                    'total_overs' => ($innings_data['overs_completed'] ?? 0) . '.' . count($innings_data['log'] ?? []), 
                    'extras_data' => wp_json_encode( $extras_json )
                ];

                $existing_innings_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $innings_table WHERE match_id = %d AND innings_number = %d", $match_id, $innings_data['number'] ) );
                if ( $existing_innings_id ) {
                    $wpdb->update( $innings_table, $innings_record, [ 'id' => $existing_innings_id ] );
                    $innings_id = $existing_innings_id;
                } else {
                    $wpdb->insert( $innings_table, $innings_record );
                    $innings_id = $wpdb->insert_id;
                }

                if ( ! empty( $innings_data['batsmen'] ) ) {
                    $wpdb->delete( $batting_stats_table, [ 'innings_id' => $innings_id ] );
                    foreach ( (array) $innings_data['batsmen'] as $player_id => $stats ) {
                        if ( empty( $player_id ) || ! is_array( $stats ) ) {
                            continue;
                        }

                        // --- ROBUST DISMISSAL PARSING LOGIC ---
                        $bowler_id = null;
                        $fielder_id = null;
                        // Use the simple dismissal_type as a fallback for older data
                        $dismissal_type = $stats['dismissal_type'] ?? 'not out'; 

                        // Check if the new, structured dismissal_info object exists
                        if ( isset( $stats['dismissal_info'] ) && is_array( $stats['dismissal_info'] ) ) {
                            $info = $stats['dismissal_info'];
                            $dismissal_type = $info['type'] ?? $dismissal_type;
                            if ( isset( $info['bowler_id'] ) ) {
                                $bowler_id = (int) $info['bowler_id'];
                            }
                            if ( isset( $info['fielder_id'] ) ) {
                                $fielder_id = (int) $info['fielder_id'];
                            }
                        }

                        $wpdb->insert( $batting_stats_table, [ 
                            'player_id'      => (int) $player_id, 
                            'match_id'       => (int) $match_id, 
                            'innings_id'     => (int) $innings_id, 
                            'team_id'        => (int) $innings_data['batting_team_id'], 
                            'runs_scored'    => (int) ($stats['runs'] ?? 0), 
                            'balls_faced'    => (int) ($stats['balls'] ?? 0),
                            'fours'          => (int) ($stats['fours'] ?? 0),
                            'sixes'          => (int) ($stats['sixes'] ?? 0),
                            'dismissal_type' => $dismissal_type,
                            'bowler_id'      => $bowler_id,
                            'fielder_id'     => $fielder_id,
                        ]);
                    }
                }
                if ( ! empty( $innings_data['bowlers'] ) ) {
                    $wpdb->delete( $bowling_stats_table, [ 'innings_id' => $innings_id ] );
                    foreach ( (array) $innings_data['bowlers'] as $player_id => $stats ) {
                        if ( empty( $player_id ) || ! is_array( $stats ) ) {
                            continue;
                        }
                        $overs = (int) ($stats['overs'] ?? 0);
                        $balls = (int) ($stats['balls'] ?? 0);
                        $wpdb->insert( $bowling_stats_table, [ 
                            'player_id' => (int) $player_id, 
                            'match_id' => (int) $match_id, 
                            'innings_id' => (int) $innings_id, 
                            'team_id' => (int) $innings_data['bowling_team_id'], 
                            'overs_bowled' => $overs . '.' . $balls, 
                            'runs_conceded' => (int) ($stats['runs'] ?? 0), 
                            'wickets_taken' => (int) ($stats['wickets'] ?? 0), 
                            'maidens' => (int) ($stats['maidens'] ?? 0) 
                        ]);
                    }
                }
            }
            
            if ( isset( $state['result'] ) ) {
                $result_data = $state['result'];
                $match_data = $state['match'];
                $wpdb->update( $matches_table, [ 'status' => 'completed', 'winner_team_id' => $result_data['winner_id'], 'result_summary' => $result_data['text'], 'live_state' => null ], [ 'id' => $match_id ] );
                $team1_id = (int) $match_data['team1_id'];
                $team2_id = (int) $match_data['team2_id'];
                $winner_id = (int) ($result_data['winner_id'] ?? 0);
                if ( $winner_id ) {
                    $loser_id = ( $winner_id === $team1_id ) ? $team2_id : $team1_id;
                    $this->update_team_stats( $winner_id, 'win' );
                    $this->update_team_stats( $loser_id, 'loss' );
                } else {
                    $this->update_team_stats( $team1_id, 'tie' );
                    $this->update_team_stats( $team2_id, 'tie' );
                }
            }
        } catch ( Exception $e ) {
            $wpdb->query('ROLLBACK');
            return false;
        }
        $wpdb->query('COMMIT');
        return true;
    }

    public function get_user_matches( $user_id, $args = [] ) {
        global $wpdb;
        $matches_table = $wpdb->prefix . 'cricscore_matches';
        $teams_table = $wpdb->prefix . 'cricscore_teams';
        $venues_table = $wpdb->prefix . 'cricscore_venues';
        $defaults = [ 'page' => 1, 'per_page' => 20 ];
        $args = wp_parse_args( $args, $defaults );
        $offset = ( $args['page'] - 1 ) * $args['per_page'];
        $query = $wpdb->prepare( "SELECT m.*, t1.name as team1_name, t1.logo_url as team1_logo_url, t2.name as team2_name, t2.logo_url as team2_logo_url, v.name as venue_name FROM {$matches_table} m LEFT JOIN {$teams_table} t1 ON m.team1_id = t1.id LEFT JOIN {$teams_table} t2 ON m.team2_id = t2.id LEFT JOIN {$venues_table} v ON m.venue_id = v.id WHERE m.user_id = %d ORDER BY m.created_at DESC LIMIT %d, %d", $user_id, $offset, $args['per_page'] );
        return $wpdb->get_results( $query );
    }

    public function get_match_data( $match_id ) {
        global $wpdb;
        $matches_table       = $wpdb->prefix . 'cricscore_matches';
        $teams_table         = $wpdb->prefix . 'cricscore_teams';
        $venues_table        = $wpdb->prefix . 'cricscore_venues';
        $tournaments_table   = $wpdb->prefix . 'cricscore_tournaments';
        $innings_table       = $wpdb->prefix . 'cricscore_innings';
        $batting_stats_table = $wpdb->prefix . 'cricscore_batting_stats';
        $bowling_stats_table = $wpdb->prefix . 'cricscore_bowling_stats';
        $match_players_table = $wpdb->prefix . 'cricscore_match_players';
        $players_table       = $wpdb->prefix . 'cricscore_players';

        $query = $wpdb->prepare( "SELECT m.*, t1.name as team1_name, t1.short_name as team1_short_name, t1.logo_url as team1_logo_url, t2.name as team2_name, t2.short_name as team2_short_name, t2.logo_url as team2_logo_url, v.name as venue_name, tour.name as tournament_name FROM {$matches_table} m LEFT JOIN {$teams_table} t1 ON m.team1_id = t1.id LEFT JOIN {$teams_table} t2 ON m.team2_id = t2.id LEFT JOIN {$venues_table} v ON m.venue_id = v.id LEFT JOIN {$tournaments_table} tour ON m.tournament_id = tour.id WHERE m.id = %d", $match_id );
        $match_data = $wpdb->get_row( $query );

        if ( ! $match_data ) { return null; }
        $match_data->toss_data = json_decode($match_data->toss_data);
        $match_data->live_state = json_decode($match_data->live_state);

        $match_data->team1_players = $wpdb->get_results($wpdb->prepare("SELECT p.id, p.name, p.profile_image_url FROM {$match_players_table} mp JOIN {$players_table} p ON mp.player_id = p.id WHERE mp.match_id = %d AND mp.team_id = %d", $match_id, $match_data->team1_id));
        $match_data->team2_players = $wpdb->get_results($wpdb->prepare("SELECT p.id, p.name, p.profile_image_url FROM {$match_players_table} mp JOIN {$players_table} p ON mp.player_id = p.id WHERE mp.match_id = %d AND mp.team_id = %d", $match_id, $match_data->team2_id));

        $innings_data = [];
        if ($match_data->status === 'completed' || $match_data->status === 'abandoned') {
            $all_innings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$innings_table} WHERE match_id = %d ORDER BY innings_number ASC", $match_id ) );
            foreach ($all_innings as $inning) {
                $extras_data = json_decode($inning->extras_data, true);
                $inning->battingOrder = $extras_data['battingOrder'] ?? [];
                
                $inning->batsmen = $wpdb->get_results( $wpdb->prepare( "SELECT bs.*, p.name, p.profile_image_url FROM {$batting_stats_table} bs JOIN {$players_table} p ON bs.player_id = p.id WHERE bs.innings_id = %d", $inning->id ) );
                $inning->bowlers = $wpdb->get_results( $wpdb->prepare( "SELECT bos.*, p.name, p.profile_image_url FROM {$bowling_stats_table} bos JOIN {$players_table} p ON bos.player_id = p.id WHERE bos.innings_id = %d", $inning->id ) );
                $innings_data[] = $inning;
            }
        }

        return [ 'match' => $match_data, 'innings' => $innings_data ];
    }

    public function add_players_to_match( $match_id, $team_id, $players ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cricscore_match_players';

        if ( empty( $players ) ) {
            return;
        }

        foreach ( $players as $player_id ) {
            $wpdb->insert(
                $table_name,
                [
                    'match_id'  => $match_id,
                    'team_id'   => $team_id,
                    'player_id' => (int) $player_id,
                ],
                [ '%d', '%d', '%d' ]
            );
        }
    }
}