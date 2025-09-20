<?php
/**
 * Main database interaction class.
 *
 * This class acts as a central hub, loading and combining all database
 * functionality from individual trait files.
 *
 * @package CricScore
 * @version 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load all the database method traits.
require_once __DIR__ . '/traits/trait-team-db.php';
require_once __DIR__ . '/traits/trait-player-db.php';
require_once __DIR__ . '/traits/trait-venue-db.php';
require_once __DIR__ . '/traits/trait-tournament-db.php';
require_once __DIR__ . '/traits/trait-match-db.php';
require_once __DIR__ . '/traits/trait-ball-log-db.php'; // <-- New line

/**
 * Class CricScore_DB
 *
 * The main database access class. It uses traits to organize database
 * operations by entity.
 */
class CricScore_DB {

    use Team_DB_Trait,
        Player_DB_Trait,
        Venue_DB_Trait,
        Tournament_DB_Trait,
        Match_DB_Trait,
        Ball_Log_DB_Trait; // <-- New line

    /**
     * The single instance of the class.
     * @var CricScore_DB
     */
    protected static $_instance = null;

    /**
     * Main CricScore_DB Instance.
     *
     * Ensures only one instance of CricScore_DB is loaded or can be loaded.
     *
     * @static
     * @return CricScore_DB - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     *
     * Protected to prevent direct object creation.
     */
    protected function __construct() {
        // This is intentionally left empty.
    }
}

/**
 * Function to get the single instance of the CricScore_DB class.
 *
 * @return CricScore_DB
 */
function CricScore_DB() {
    return CricScore_DB::instance();
}

// Initialize the class.
CricScore_DB();