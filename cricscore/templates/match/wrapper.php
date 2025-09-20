<?php
/**
 * The main wrapper for the new Match Template.
 * This is the HTML shell for the live scoring single-page application.
 *
 * @package CricScore
 * @version 2.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Cricket Score</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'cricscore-match-live' ); ?>>

        <div id="match-content-container">

            <div id="match-loading" class="match-step">
                <p>Loading Match Data...</p>
            </div>

            <div id="step-pre-match-summary" class="match-step" style="display: none;">
                <?php @include_once __DIR__ . '/partials/1-pre-match-summary.php'; ?>
            </div>

            <div id="step-innings-preparation" class="match-step" style="display: none;">
                <?php @include_once __DIR__ . '/partials/2-innings-preparation.php'; ?>
            </div>

            <div id="step-live-scoring" class="match-step" style="display: none;">
                <?php @include_once __DIR__ . '/partials/3-live-scoring.php'; ?>
            </div>

            <div id="step-mid-innings-summary" class="match-step" style="display: none;">
                <?php @include_once __DIR__ . '/partials/4-mid-innings-summary.php'; ?>
            </div>

            <div id="step-post-match-summary" class="match-step" style="display: none;">
                <?php @include_once __DIR__ . '/partials/5-post-match-summary.php'; ?>
            </div>

            <div id="step-match-result" class="match-step" style="display: none;">
                <?php @include_once __DIR__ . '/partials/6-match-result.php'; ?>
            </div>

        </div>


    <?php wp_footer(); ?>
</body>
</html>