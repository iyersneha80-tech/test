<?php
/**
 * The view file for the "My Matches" page.
 *
 * @package CricScore
 * @version 2.0.0
 */
?>

<div class="cricscore-card page-header-card">
    <h2>Matches</h2>
    <a href="<?php echo esc_url( home_url( '/dashboard/create-match/' ) ); ?>" class="button-primary">
        <i class="fas fa-plus"></i>
        <span>Create New Match</span>
    </a>
</div>

<div id="my-matches-list" class="cricscore-matches-list">
    <p class="loading-message">Loading your matches...</p>
</div>