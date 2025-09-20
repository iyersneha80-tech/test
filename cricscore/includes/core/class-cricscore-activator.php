<?php
/**
 * Fired during plugin activation.
 *
 * @package CricScore
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CricScore_Activator
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class CricScore_Activator {

    /**
     * The main activation method.
     *
     * This method is called when the plugin is activated. It triggers the
     * database installer to set up the necessary tables.
     */
    public static function activate() {
        // We need the installer file to create the tables.
        require_once CRICSCORE_INCLUDES_PATH . 'database/class-cricscore-installer.php';
        CricScore_Installer::install();
    }

}