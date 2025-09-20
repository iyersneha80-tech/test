<?php
/**
 * Partial for the Innings Preparation screen.
 * Allows the scorer to select openers and the opening bowler.
 *
 * @package CricScore
 * @version 2.1.0
 */
?>

<div class="innings-prep-container">
    <header class="innings-prep-header">
        <h1 id="innings-prep-title">Prepare First Innings</h1>
    </header>

    <div class="innings-prep-body">
        <div id="innings-prep-error" class="error-message" style="display: none;"></div>

        <div class="team-selection-card">
            <h3>Batting Team: <span id="prep-batting-team-name">Batting Team</span></h3>
            <!-- --- CHANGE: Replaced dropdowns with a single roster container --- -->
            <div id="prep-batsman-roster" class="player-roster-grid">
                <!-- Player cards will be dynamically inserted here by JavaScript -->
            </div>
        </div>

        <div class="team-selection-card">
            <h3>Bowling Team: <span id="prep-bowling-team-name">Bowling Team</span></h3>
            <!-- --- CHANGE: Replaced dropdowns with a single roster container --- -->
            <div id="prep-bowler-roster" class="player-roster-grid">
                <!-- Player cards will be dynamically inserted here by JavaScript -->
            </div>
        </div>
    </div>

    <footer class="innings-prep-actions">
        <button id="start-innings-btn" class="button-premium-brand">Start Innings</button>
    </footer>
</div>