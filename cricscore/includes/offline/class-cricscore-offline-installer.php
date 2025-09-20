<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CricScore_Offline_Installer {
    const DB_VERSION = '1.0.0';
    const DB_VERSION_OPTION = 'cricscore_offline_db_version';

    public static function maybe_install() {
        $installed = get_option( self::DB_VERSION_OPTION );
        if ( $installed === self::DB_VERSION ) { return; }
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();

        $events = $wpdb->prefix . 'cs_events';
        $snapshots = $wpdb->prefix . 'cs_snapshots';
        $tokens = $wpdb->prefix . 'cs_scorer_tokens';

        $sql = "CREATE TABLE {$events} (
            server_seq BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            match_id VARCHAR(64) NOT NULL,
            event_id CHAR(36) NOT NULL,
            innings_no TINYINT UNSIGNED NOT NULL DEFAULT 1,
            over_no TINYINT UNSIGNED NOT NULL DEFAULT 0,
            ball_no TINYINT UNSIGNED NOT NULL DEFAULT 0,
            payload LONGTEXT NOT NULL,
            author_device_id VARCHAR(64) DEFAULT NULL,
            timestamp_client BIGINT(20) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (server_seq),
            UNIQUE KEY event_id (event_id),
            KEY match_seq (match_id, server_seq)
        ) {$charset_collate};

        CREATE TABLE {$snapshots} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            match_id VARCHAR(64) NOT NULL,
            innings_no TINYINT UNSIGNED NOT NULL DEFAULT 1,
            server_seq BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            payload LONGTEXT NOT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY match_innings (match_id, innings_no)
        ) {$charset_collate};

        CREATE TABLE {$tokens} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            match_id VARCHAR(64) NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            token_hash CHAR(64) NOT NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY match_active (match_id, active),
            UNIQUE KEY token_hash (token_hash)
        ) {$charset_collate};";

        dbDelta( $sql );
        update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
    }
}
