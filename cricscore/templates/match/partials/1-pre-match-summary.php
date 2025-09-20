<?php
/**
 * Partial for the Pre-Match Summary screen.
 * Displays all match details before the game starts.
 *
 * @package CricScore
 * @version 2.0.0
 */
?>

<div class="pre-match-container">
    <header class="pre-match-header">
        <h1 id="pre-match-title">Team A vs Team B</h1>
        <p id="pre-match-toss-result">Toss result will be displayed here.</p>
    </header>

    <div class="pre-match-body">
        <div class="match-info-card">
            <h3>Match Information</h3>
            <ul>
                <li><strong>Tournament:</strong> <span id="pre-match-tournament">N/A</span></li>
                <li><strong>Format:</strong> <span id="pre-match-format">T20</span> (<span id="pre-match-overs">20</span> Overs)</li>
                <li><strong>Venue:</strong> <span id="pre-match-venue">Venue Name</span></li>
                <li><strong>Date:</strong> <span id="pre-match-date">January 1, 2025</span></li>
            </ul>
        </div>

        <div class="squads-container">
            <div class="squad-card">
                <h4 id="pre-match-team1-name">Team A</h4>
                <ul id="pre-match-team1-squad">
                    </ul>
            </div>
            <div class="squad-card">
                <h4 id="pre-match-team2-name">Team B</h4>
                <ul id="pre-match-team2-squad">
                    </ul>
            </div>
        </div>
    </div>

    <footer class="pre-match-actions">
        <button id="start-match-btn" class="button-primary">Start Match</button>
        <button id="abandon-match-btn" class="button-danger">Abandon Match</button>
    </footer>
</div>

<div id="abandon-confirm-modal" class="cricscore-modal" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" id="abandon-modal-close">&times;</span>
        <h2>Abandon Match</h2>
        <p>Are you sure you want to abandon this match? This action cannot be undone.</p>
        <form id="abandon-form">
            <div class="form-group">
                <label for="abandon-reason">Reason:</label>
                <select id="abandon-reason" required>
                    <option value="Rain">Rain</option>
                    <option value="Bad Light">Bad Light</option>
                    <option value="Pitch Condition">Pitch Condition</option>
                    <option value="Mutual Agreement">Mutual Agreement</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="button-danger">Confirm & Abandon</button>
            </div>
        </form>
    </div>
</div>