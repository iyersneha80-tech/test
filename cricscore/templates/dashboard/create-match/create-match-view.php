<?php
/**
 * The view file for the "Create New Match" page (Redesigned).
 *
 * @package CricScore
 * @version 2.0.0
 */
?>

<div class="create-match-container">
    <header class="header">
        <h1>Create New Match</h1>
    </header>

    <ol class="cricscore-stepper">
        <li class="step active" data-step="1">
            <div class="step-number">1</div>
            <div class="step-name">Match Details</div>
        </li>
        <li class="step" data-step="2">
            <div class="step-number">2</div>
            <div class="step-name">Teams & Players</div>
        </li>
        <li class="step" data-step="3">
            <div class="step-number">3</div>
            <div class="step-name">The Toss</div>
        </li>
        <li class="step" data-step="4">
            <div class="step-number">4</div>
            <div class="step-name">Rules</div>
        </li>
    </ol>

    <form id="create-match-form" class="cricscore-form">
        <div id="step-1-content" class="step-content active">
            <div class="form-group">
                <label>Match Format</label>
                <div class="format-grid">
                    <div class="format-card" data-format="T20">
                        <div class="format-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                        </div>
                        <div class="format-info">
                            <h3>T20</h3>
                            <p>Fast-paced 20-over action.</p>
                        </div>
                    </div>
                    <div class="format-card" data-format="ODI">
                        <div class="format-icon">
                           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="format-info">
                            <h3>ODI</h3>
                            <p>Classic 50-over international format.</p>
                        </div>
                    </div>
                     <div class="format-card" data-format="Test">
                        <div class="format-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>
                        </div>
                        <div class="format-info">
                            <h3>Test Match</h3>
                            <p>The traditional multi-day format.</p>
                        </div>
                    </div>
                     <div class="format-card" data-format="Custom">
                        <div class="format-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-1.007 1.11-1.226l2.552-1.021a1.125 1.125 0 011.32.748l1.021 2.552a1.125 1.125 0 01-.748 1.32l-2.552 1.021a1.125 1.125 0 01-1.226-1.11zM10.343 3.94l-2.552-1.021a1.125 1.125 0 00-1.32.748l-1.021 2.552a1.125 1.125 0 00.748 1.32l2.552 1.021M10.343 3.94a3.75 3.75 0 014.257-4.257l.08.04a3.75 3.75 0 014.257 4.257l-.04.08a3.75 3.75 0 01-4.257 4.257l-.08-.04a3.75 3.75 0 01-4.257-4.257l.04-.08zM14.25 12l-2.552-1.021a1.125 1.125 0 00-1.32.748l-1.021 2.552a1.125 1.125 0 00.748 1.32l2.552 1.021a1.125 1.125 0 001.226-1.11z" /></svg>
                        </div>
                        <div class="format-info">
                            <h3>Custom</h3>
                            <p>Set a custom number of overs.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group" id="overs-group" style="display: none;">
                <label for="match-overs">Overs Per Innings</label>
                <input type="number" id="match-overs" value="10" min="1">
            </div>
            <div class="form-group">
                <label for="match-venue">Venue</label>
                <select id="match-venue">
                    <option value="">Loading venues...</option>
                </select>
            </div>
        </div>

        <div id="step-2-content" class="step-content">
        <div id="team-selection-card" class="cricscore-card">
            <div class="card-content">
    <div class="form-group">
        <label for="team1-select">Team 1</label>
        <select id="team1-select">
            <option value="">Loading teams...</option>
        </select>
    </div>
    <div class="form-group">
        <label for="team2-select">Team 2</label>
        <select id="team2-select">
            <option value="">Loading teams...</option>
        </select>
    </div>
            </div>
        </div>

        <div class="cricscore-card">
             <div class="card-header">
                <h2>Player Pool</h2>
            </div>
            <div class="card-content">

    <section class="player-pool">
        <div class="player-pool-controls">
            <input type="text" class="search-bar" id="search-bar" placeholder="Search for a player...">
        </div>
        <ul class="player-list" id="player-pool-list">
            </ul>
        <div class="empty-pool-message" id="empty-pool-message" style="display: none;">
            <p>All players have been assigned!</p>
        </div>
    </section>
            </div>
        </div>

        <div class="cricscore-card">
            <div class="card-content">

    <div class="squad-display-grid">
    <section class="squad-display team-a">
        <div class="squad-header team-a">
            <h3 id="squad-a-name">Team A</h3>
            <span class="player-count" id="team-a-count">0/11</span>
        </div>
        <ul class="player-list" id="team-a-list">
            </ul>
        <div class="empty-squad-message" id="empty-squad-a">
            <p>Assign players from the pool above.</p>
        </div>
    </section>

    <section class="squad-display team-b">
        <div class="squad-header team-b">
            <h3 id="squad-b-name">Team B</h3>
            <span class="player-count" id="team-b-count">0/11</span>
        </div>
        <ul class="player-list" id="team-b-list">
            </ul>
        <div class="empty-squad-message" id="empty-squad-b">
            <p>Assign players from the pool above.</p>
        </div>
    </section>
    </div>
            </div>
        </div>
</div>

        <div id="step-3-content" class="step-content">
             <div class="toss-container">
                <h2>Who won the toss?</h2>
                <p class="toss-instructions" style="text-align: center; color: var(--text-secondary); margin-top: -16px; margin-bottom: 24px; font-size: 0.9rem;">Click the winning team. Click again to clear.</p>
                <div class="toss-teams">
                    <div class="toss-team-card" data-team-id="">
                        <div class="team-logo-placeholder">T1</div>
                        <h4 id="toss-team1-name">Team 1</h4>
                    </div>
                    <div class="vs-separator">VS</div>
                    <div class="toss-team-card" data-team-id="">
                        <div class="team-logo-placeholder">T2</div>
                        <h4 id="toss-team2-name">Team 2</h4>
                    </div>
                </div>
                <div class="toss-decision-container">
                    <div class="toss-decision-buttons">
                        <button type="button" class="decision-btn" data-decision="bat">Bat</button>
                        <button type="button" class="decision-btn" data-decision="bowl">Bowl</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="step-4-content" class="step-content">
            <div class="rules-card">
                <div class="rule-item">
                    <div class="rule-info">
                        <h4>Last Man Rule (Naa Baad)</h4>
                        <p>If enabled, the last remaining batsman can continue to bat alone after the 10th wicket falls. The innings ends when they are dismissed.</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="last-man-rule-toggle">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="stepper-navigation">
            <button type="button" id="prev-step-btn" class="button button-secondary" style="visibility: hidden;">Previous</button>
                                <!-- NEW: Coin Toss Trigger Button -->
        <button type="button" id="openTossModalBtn" class="toss-trigger-btn" title="Virtual Coin Toss">
            <img src="<?php echo CRICSCORE_URL . 'templates/dashboard/create-match/assets/images/coin-icon.png'; ?>" alt="Toss Coin">
        </button>
            <button type="button" id="next-step-btn" class="button button-primary">Next</button>
            <div id="step-2-tooltip" class="step-tooltip">Please select both teams to continue.</div>
            <div id="step-1-tooltip" class="step-tooltip">Please select a format and venue to continue.</div>
            <button type="submit" id="create-match-btn" class="button button-primary" style="display: none;">Create Match</button>
        </div>

        <div id="form-message" class="form-message" style="display: none;"></div>
    </form>
</div>
<!--
---
NEW: VIRTUAL COIN TOSS MODAL HTML
---
-->
<div class="modal-backdrop" id="modalBackdrop">
    <div class="coin-toss-modal" id="coinTossModal" role="dialog" aria-modal="true">
        <div class="coin-container">
            <div class="coin" id="coin">
    <div class="coin-face heads">
        <img src="<?php echo CRICSCORE_URL . 'templates/dashboard/create-match/assets/images/coin-back.png'; ?>" alt="Coin Back (Heads)">
    </div>
    <div class="coin-face tails">
        <img src="<?php echo CRICSCORE_URL . 'templates/dashboard/create-match/assets/images/coin-front.png'; ?>" alt="Coin Front (Tails)">
    </div>
</div>
        </div>

        <div class="result-text" id="resultText"></div>
        <button class="spin-button" id="spinBtn">Spin</button>
    </div>
</div>