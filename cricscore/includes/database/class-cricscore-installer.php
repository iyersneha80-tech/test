<?php
/**
 * Handles the creation of database tables on plugin activation.
 *
 * @package CricScore
 * @version 1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CricScore_Installer {

    /**
     * The main installation method.
     */
    public static function install() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Table Names
        $teams_table              = $wpdb->prefix . 'cricscore_teams';
        $players_table            = $wpdb->prefix . 'cricscore_players';
        $venues_table             = $wpdb->prefix . 'cricscore_venues';
        $tournaments_table        = $wpdb->prefix . 'cricscore_tournaments';
        $matches_table            = $wpdb->prefix . 'cricscore_matches';
        $innings_table            = $wpdb->prefix . 'cricscore_innings';
        $batting_stats_table      = $wpdb->prefix . 'cricscore_batting_stats';
        $bowling_stats_table      = $wpdb->prefix . 'cricscore_bowling_stats';
        $tournament_points_table  = $wpdb->prefix . 'cricscore_tournament_points';
        $match_players_table      = $wpdb->prefix . 'cricscore_match_players'; // <-- New table name
        $ball_log_table           = $wpdb->prefix . 'cricscore_ball_log';

        $sql = "
        CREATE TABLE {$teams_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            short_name VARCHAR(10) DEFAULT '' NOT NULL,
            logo_url VARCHAR(255),
            country VARCHAR(100),
            captain_id BIGINT(20) UNSIGNED,
            vice_captain_id BIGINT(20) UNSIGNED,
            matches_played INT(11) DEFAULT 0 NOT NULL,
            wins INT(11) DEFAULT 0 NOT NULL,
            losses INT(11) DEFAULT 0 NOT NULL,
            ties INT(11) DEFAULT 0 NOT NULL,
            share_slug VARCHAR(10),
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) {$charset_collate};

        -- UPDATED TABLE SCHEMA FOR PLAYERS --
        CREATE TABLE {$players_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            dob DATE,
            role VARCHAR(50),
            batting_style VARCHAR(50),
            bowling_style VARCHAR(50),
            country VARCHAR(100),
            profile_image_url VARCHAR(255),
            share_slug VARCHAR(10),
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) {$charset_collate};

        CREATE TABLE {$venues_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            city VARCHAR(255) DEFAULT '' NOT NULL,
            country VARCHAR(255) DEFAULT '' NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) {$charset_collate};

        CREATE TABLE {$tournaments_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            format VARCHAR(50) DEFAULT 'League' NOT NULL,
            status VARCHAR(50) DEFAULT 'Upcoming' NOT NULL,
            start_date DATE,
            end_date DATE,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) {$charset_collate};

        CREATE TABLE {$matches_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            team1_id BIGINT(20) UNSIGNED NOT NULL,
            team2_id BIGINT(20) UNSIGNED NOT NULL,
            venue_id BIGINT(20) UNSIGNED,
            tournament_id BIGINT(20) UNSIGNED,
            match_format VARCHAR(50) NOT NULL,
            overs_per_innings INT(11),
            status VARCHAR(20) DEFAULT 'pending' NOT NULL,
            winner_team_id BIGINT(20) UNSIGNED,
            result_summary VARCHAR(255) DEFAULT '' NOT NULL,
            toss_data JSON,
            live_state JSON,
            share_slug VARCHAR(10),
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY tournament_id (tournament_id)
        ) {$charset_collate};

        CREATE TABLE {$innings_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            match_id BIGINT(20) UNSIGNED NOT NULL,
            innings_number TINYINT UNSIGNED NOT NULL,
            batting_team_id BIGINT(20) UNSIGNED NOT NULL,
            total_score INT,
            total_wickets INT,
            total_overs DECIMAL(4,1),
            extras_data JSON,
            PRIMARY KEY (id),
            KEY match_id (match_id)
        ) {$charset_collate};

        CREATE TABLE {$batting_stats_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            player_id BIGINT(20) UNSIGNED NOT NULL,
            match_id BIGINT(20) UNSIGNED NOT NULL,
            innings_id BIGINT(20) UNSIGNED NOT NULL,
            team_id BIGINT(20) UNSIGNED NOT NULL,
            runs_scored INT,
            balls_faced INT,
            fours INT,
            sixes INT,
            dot_balls INT,
            dismissal_type VARCHAR(50) DEFAULT 'not out' NOT NULL,
            bowler_id BIGINT(20) UNSIGNED,
            fielder_id BIGINT(20) UNSIGNED,
            PRIMARY KEY (id),
            KEY player_id (player_id),
            KEY match_id (match_id)
        ) {$charset_collate};

        CREATE TABLE {$bowling_stats_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            player_id BIGINT(20) UNSIGNED NOT NULL,
            match_id BIGINT(20) UNSIGNED NOT NULL,
            innings_id BIGINT(20) UNSIGNED NOT NULL,
            team_id BIGINT(20) UNSIGNED NOT NULL,
            overs_bowled DECIMAL(3,1),
            maidens INT,
            runs_conceded INT,
            wickets_taken INT,
            wides INT,
            no_balls INT,
            PRIMARY KEY (id),
            KEY player_id (player_id),
            KEY match_id (match_id)
        ) {$charset_collate};
        
        CREATE TABLE {$match_players_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            match_id BIGINT(20) UNSIGNED NOT NULL,
            team_id BIGINT(20) UNSIGNED NOT NULL,
            player_id BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            KEY match_id (match_id),
            KEY team_id (team_id)
        ) {$charset_collate};

        CREATE TABLE {$tournament_points_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            tournament_id BIGINT(20) UNSIGNED NOT NULL,
            team_id BIGINT(20) UNSIGNED NOT NULL,
            matches_played INT DEFAULT 0 NOT NULL,
            wins INT DEFAULT 0 NOT NULL,
            losses INT DEFAULT 0 NOT NULL,
            points INT DEFAULT 0 NOT NULL,
            net_run_rate DECIMAL(6,3),
            PRIMARY KEY (id),
            UNIQUE KEY tournament_team (tournament_id, team_id)
        ) {$charset_collate};

        CREATE TABLE {$ball_log_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            match_id BIGINT(20) UNSIGNED NOT NULL,
            innings_id BIGINT(20) UNSIGNED NOT NULL,
            over_number INT NOT NULL,
            ball_number INT NOT NULL,
            batsman_id BIGINT(20) UNSIGNED NOT NULL,
            bowler_id BIGINT(20) UNSIGNED NOT NULL,
            runs_scored INT,
            extras_type VARCHAR(20),
            extras_runs INT,
            is_wicket BOOLEAN,
            wicket_data JSON,
            timestamp DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY match_id_innings_over (match_id, innings_id, over_number)
        ) {$charset_collate};
        ";

        dbDelta( $sql );
    }
}