<?php
/**
 * Partial for the Live Scoring screen.
 * This is the main interface for scoring the match.
 *
 * @package CricScore
 * @version 3.0.0 (Mobile First Refactor)
 */
?>

<!-- This new structure replaces the old .live-scoring-container -->
<!-- 1. NEW APP HEADER -->
<header class="app-header">
    <div class="app-header-nav">
        <a href="/dashboard/">
            <i class="fas fa-home"></i>
        </a>
    </div>
    <div class="app-header-status">
        <span class="dot"></span>
        <span>Online</span>
    </div>
</header>

<!-- 1. STICKY HEADER -->
<header class="live-header-sticky">
    <div class="scoreboard-top">
        <div id="live-batting-team">Batting Team</div>
        <div class="live-score">
            <span id="live-score-runs">0</span> / <span id="live-score-wickets">0</span>
        </div>
        <div class="live-overs">
            (<span id="live-overs-completed">0</span>.<span id="live-balls-completed">0</span>)
        </div>
    </div>
    <div id="chase-info" style="display: none;">
        Target: <span id="target-score">0</span>
        <span id="runs-needed-summary"></span>
    </div>
</header>

<!-- 2. SCROLLABLE CONTENT -->
<main class="live-content-scrollable">
    <div class="info-card">
        <h4>On Field</h4>
        <div class="player-row on-strike" id="striker-details">
            <span class="player-name" id="striker-name">Striker</span>
            <span class="player-score"><span id="striker-runs">0</span> (<span id="striker-balls">0</span>)</span>
        </div>
        <div class="player-row" id="non-striker-details">
            <span class="player-name" id="non-striker-name">Non-Striker</span>
            <span class="player-score"><span id="non-striker-runs">0</span> (<span id="non-striker-balls">0</span>)</span>
        </div>
        <hr style="border: none; border-top: 1px dashed var(--border-color); margin: 12px 0;">
        <div class="player-row">
            <span class="player-name" id="current-bowler-name">Bowler</span>
            <span class="player-score" id="current-bowler-figures">0-0-0-0</span>
        </div>
    </div>

    <div class="info-card">
        <div class="info-card-header">
    <h4>This Over</h4>
    <div id="save-status" class="save-status"></div>
</div>
        <div id="this-over-summary" class="ball-summary">
            <!-- Ball logs will be dynamically inserted here by JS -->
        </div>
        <div class="actions-row">
            <button id="undo-btn" class="score-btn action-btn" disabled>Undo</button>
        </div>
    </div>

<!-- This container is hidden by default and shown by JS when the innings is complete -->
<div id="confirm-innings-container" style="display: none;">
    <button id="confirm-innings-btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
            <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd" />
        </svg>
        <span>Confirm & End Innings</span>
    </button>
    <p class="helper-text">This action cannot be undone.</p>
</div>
</main>

<!-- 3. STICKY CONTROLS -->
<footer class="live-controls-sticky">
    <div class="button-grid">
        <!-- Row 1 -->
        <button class="score-btn runs" data-run="1">1</button>
        <button class="score-btn runs" data-run="2">2</button>
        <button class="score-btn runs" data-run="3">3</button>
        <button class="score-btn runs" data-run="4">4</button>
        <!-- Row 2 -->
        <button class="score-btn runs" data-run="6">6</button>
        <button class="score-btn runs" data-run="0">0</button>
        <button class="score-btn extra" id="extra-btn">Extra</button>
        <button class="score-btn wicket" id="wicket-btn">Wicket</button>
    </div>
</footer>


<!-- MODALS (These are preserved from the original file as their functionality is unchanged) -->

<div id="wicket-modal" class="cricscore-modal" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="wicket-modal-close">&times;</span>
        <h2>Wicket</h2>
        <div id="wicket-error" class="error-message" style="display: none; margin-bottom: 10px;"></div>

        <div class="wicket-modal-section">
            <h4>Dismissal Type</h4>
            <div id="dismissal-type-grid" class="dismissal-type-grid">
                <!-- Dismissal type buttons will be dynamically inserted here -->
            </div>
        </div>

        <div class="wicket-modal-section" id="fielder-selection-container" style="display: none;">
            <h4>Fielder / Catcher</h4>
            <div id="fielder-roster-grid" class="player-roster-grid small">
                <!-- Fielder player cards will be dynamically inserted here -->
            </div>
        </div>

        <div class="wicket-modal-section" id="next-batsman-selection-container">
            <h4>Next Batsman</h4>
            <div id="next-batsman-roster-grid" class="player-roster-grid small">
                <!-- Next batsman player cards will be dynamically inserted here -->
            </div>
        </div>

        <div class="form-group">
            <button id="confirm-wicket-btn" class="button-primary">Confirm Wicket</button>
        </div>
    </div>
</div>

<div id="new-bowler-modal" class="cricscore-modal" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="new-bowler-modal-close">&times;</span>
        <h2>End of Over</h2>
        <p>Please select the next bowler.</p>
        
        <div class="wicket-modal-section">
             <div id="new-bowler-roster-grid" class="player-roster-grid">
                <!-- New bowler player cards will be dynamically inserted here -->
            </div>
        </div>

        <div class="form-group">
            <button id="confirm-new-bowler-btn" class="button-primary">Start Next Over</button>
        </div>
    </div>
</div>

<div id="extras-modal" class="cricscore-modal" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="extras-modal-close">&times;</span>
        <h2>Add Extra</h2>
        <div id="extras-error" class="error-message" style="display: none;"></div>

        <div class="form-group">
            <label>Type of Extra</label>
            <div id="extras-type-btns" class="button-group">
                <button class="extra-type-btn" data-type="wd">Wide</button>
                <button class="extra-type-btn" data-type="nb">No Ball</button>
                <button class="extra-type-btn" data-type="b">Bye</button>
                <button class="extra-type-btn" data-type="lb">Leg Bye</button>
            </div>
        </div>

        <div class="form-group">
            <label>Additional Runs (off the bat for NB, or byes for others)</label>
            <div id="extras-runs-btns" class="button-group extras-runs">
                <button class="extra-run-btn" data-runs="0">0</button>
                <button class="extra-run-btn" data-runs="1">1</button>
                <button class="extra-run-btn" data-runs="2">2</button>
                <button class="extra-run-btn" data-runs="3">3</button>
                <button class="extra-run-btn" data-runs="4">4</button>
                <button class="extra-run-btn" data-runs="6">6</button>
            </div>
        </div>

        <div class="form-group">
            <button id="confirm-extra-btn" class="button-primary">Confirm Extra</button>
        </div>
    </div>
</div>