<?php
/**
 * Partial for the Mid-Innings Summary screen.
 * Displays the scorecard for the first innings and the target.
 *
 * @package CricScore
 * @version 2.0.0
 */
?>

<div class="summary-container">
    <header class="summary-header">
        <h1>Innings Break</h1>
    </header>

    <div class="summary-body">
        <div class="target-card">
            <h3 id="mid-summary-batting-team">Team A scored:</h3>
            <p class="final-score" id="mid-summary-final-score">154 / 8 (20.0 overs)</p>
            <hr>
            <h3 id="mid-summary-chasing-team">Team B need:</h3>
            <p class="target-score" id="mid-summary-target">155 to win</p>
        </div>

        <div class="scorecard">
            <h3 id="mid-summary-batting-scorecard-title">Team A Batting</h3>
            <table class="scorecard-table">
                <thead>
                    <tr>
                        <th>Batsman</th>
                        <th>Dismissal</th>
                        <th>R</th>
                        <th>B</th>
                        <th>4s</th>
                        <th>6s</th>
                        <th>SR</th>
                    </tr>
                </thead>
                <tbody id="mid-summary-batting-tbody">
                    </tbody>
            </table>
        </div>
        
        <div class="scorecard">
            <h3 id="mid-summary-bowling-scorecard-title">Team B Bowling</h3>
            <table class="scorecard-table">
                <thead>
                    <tr>
                        <th>Bowler</th>
                        <th>O</th>
                        <th>M</th>
                        <th>R</th>
                        <th>W</th>
                        <th>Econ</th>
                    </tr>
                </thead>
                <tbody id="mid-summary-bowling-tbody">
                    </tbody>
            </table>
        </div>
    </div>

    <footer class="summary-actions">
        <button id="start-second-innings-btn" class="button-premium-brand">Start Second Innings</button>
    </footer>
</div>