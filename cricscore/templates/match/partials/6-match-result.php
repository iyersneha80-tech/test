<?php
/**
 * Partial for the Final Match Result screen (Step 6).
 * Redesigned with a summary header and tabbed interface.
 *
 * @package CricScore
 * @version 2.2.0
 */
?>

<div class="cricscore-result-wrapper-redesigned">

    <!-- 1. Match Summary Header -->
    <div class="match-summary-header">
        <div class="summary-scores">
            <div class="team-summary">
                <div class="team-logo" id="final-team1-logo-container"></div>
                <div class="team-info">
                    <p class="short-name" id="final-team1-short-name">T1</p>
                    <p class="score" id="final-team1-score">0/0 (0.0)</p>
                </div>
            </div>
            <div class="vs-separator">VS</div>
            <div class="team-summary align-right">
                <div class="team-logo" id="final-team2-logo-container"></div>
                <div class="team-info">
                    <p class="short-name" id="final-team2-short-name">T2</p>
                    <p class="score" id="final-team2-score">0/0 (0.0)</p>
                </div>
            </div>
        </div>
        <div class="summary-result-text" id="final-result-text-summary">
            Match Result
        </div>
    </div>

    <!-- 2. Scorecard Tabs -->
    <div class="scorecard-tabs">
        <div class="tabs-nav">
            <button class="tab-link active" data-tab="innings1-tab">1st Innings</button>
            <button class="tab-link" data-tab="innings2-tab">2nd Innings</button>
        </div>

        <!-- Innings 1 Content -->
        <div id="innings1-tab" class="tab-content active">
            <div class="scorecard-section">
                <h3 id="final-innings1-batting-title">First Innings Batting</h3>
                <table class="scorecard-table batting-table">
                    <thead>
                        <tr>
                            <th class="batsman-col">Batsman</th>
                            <th class="runs-col">R</th>
                            <th class="balls-col">B</th>
                            <th class="fours-col">4s</th>
                            <th class="sixes-col">6s</th>
                            <th class="sr-col">SR</th>
                        </tr>
                    </thead>
                    <tbody id="final-innings1-batting-tbody"></tbody>
                </table>
            </div>
            <div class="scorecard-section">
                <h3 id="final-innings1-bowling-title">First Innings Bowling</h3>
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
                    <tbody id="final-innings1-bowling-tbody"></tbody>
                </table>
            </div>
        </div>

        <!-- Innings 2 Content -->
        <div id="innings2-tab" class="tab-content">
            <div class="scorecard-section">
                <h3 id="final-innings2-batting-title">Second Innings Batting</h3>
                <table class="scorecard-table batting-table">
                    <thead>
                        <tr>
                            <th class="batsman-col">Batsman</th>
                            <th class="runs-col">R</th>
                            <th class="balls-col">B</th>
                            <th class="fours-col">4s</th>
                            <th class="sixes-col">6s</th>
                            <th class="sr-col">SR</th>
                        </tr>
                    </thead>
                    <tbody id="final-innings2-batting-tbody"></tbody>
                </table>
            </div>
            <div class="scorecard-section">
                <h3 id="final-innings2-bowling-title">Second Innings Bowling</h3>
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
                    <tbody id="final-innings2-bowling-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>
    
    <footer class="summary-actions">
    <a href="/dashboard/my-matches/" class="summary-action-btn">Back to My Matches</a>
</footer>
</div>