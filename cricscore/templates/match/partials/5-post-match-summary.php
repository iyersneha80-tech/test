<?php
/**
 * Partial for the Post-Match Summary screen.
 * Displays the final result and scorecard for the second innings.
 *
 * @package CricScore
 * @version 2.0.0
 */
?>

<div class="summary-container">
    <header class="summary-header">
        <h1>Match Finished</h1>
    </header>

    <div class="summary-body">
        <div class="target-card result-card">
            <h2 id="post-match-result-text">Tushar's Team won by 15 runs</h2>
        </div>

        <div class="scorecard">
            <h3 id="post-match-batting-scorecard-title">Zak's Team Batting</h3>
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
                <tbody id="post-match-batting-tbody">
                    </tbody>
            </table>
        </div>
        
        <div class="scorecard">
            <h3 id="post-match-bowling-scorecard-title">Tushar's Team Bowling</h3>
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
                <tbody id="post-match-bowling-tbody">
                    </tbody>
            </table>
        </div>
    </div>

    <footer class="summary-actions">
        <button id="finalize-match-btn" class="button-premium-brand">Finalize & Complete Match</button>
    </footer>
</div>