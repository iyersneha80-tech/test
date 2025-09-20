<?php
/**
 * Fired during plugin deactivation.
 *
 * @package CricScore
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class CricScore_Deactivator
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class CricScore_Deactivator {

    /**
     * The main deactivation method.
     *
     * This method is called when the plugin is deactivated.
     * We can add any cleanup tasks here in the future.
     */
    public static function deactivate() {
        // This space is reserved for any future deactivation logic.
    }

}