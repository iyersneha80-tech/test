/**
 * Logic for the Mid-Innings Summary screen (Step 4)
 *
 * @package CricScore
 * @version 2.0.1
 */
(function(cricscore) {

    cricscore.steps.midSummary = {

        elements: {},

        /**
         * Initialize the step.
         */
        init: function() {
            this.elements = {
                // Summary Card
                battingTeam: document.getElementById('mid-summary-batting-team'),
                finalScore: document.getElementById('mid-summary-final-score'),
                chasingTeam: document.getElementById('mid-summary-chasing-team'),
                target: document.getElementById('mid-summary-target'),
                // Scorecard Titles
                battingScorecardTitle: document.getElementById('mid-summary-batting-scorecard-title'),
                bowlingScorecardTitle: document.getElementById('mid-summary-bowling-scorecard-title'),
                // Table Bodies
                battingTbody: document.getElementById('mid-summary-batting-tbody'),
                bowlingTbody: document.getElementById('mid-summary-bowling-tbody'),
                // Button
                startSecondInningsBtn: document.getElementById('start-second-innings-btn'),
            };

            this.populateData();
            this.bindEvents();
        },

        /**
         * Populate the HTML elements with data from the completed first innings.
         */
        populateData: function() {
            const firstInnings = cricscore.matchState.firstInnings;
            const match = cricscore.matchState.match;

            if (!firstInnings) {
                console.error("First innings data not found in matchState.");
                return;
            }

            // --- Determine Team Names & Players ---
            const battingTeamId = firstInnings.batting_team_id;
            const bowlingTeamId = firstInnings.bowling_team_id;
            const battingTeam = {
                name: battingTeamId == match.team1_id ? match.team1_name : match.team2_name,
                players: battingTeamId == match.team1_id ? match.team1_players : match.team2_players
            };
            const bowlingTeam = {
                name: bowlingTeamId == match.team1_id ? match.team1_name : match.team2_name,
                players: bowlingTeamId == match.team1_id ? match.team1_players : match.team2_players
            };
            
            // --- Corrected Overs Display Logic ---
            const oversDisplay = `${firstInnings.overs_completed}.${firstInnings.log.length % 6}`;

            // --- Populate Target Card ---
            this.elements.battingTeam.textContent = `${battingTeam.name} scored:`;
            this.elements.finalScore.textContent = `${firstInnings.score} / ${firstInnings.wickets} (${oversDisplay} overs)`;
            this.elements.chasingTeam.textContent = `${bowlingTeam.name} need:`;
            this.elements.target.textContent = `${firstInnings.score + 1} to win`;

            // --- Populate Scorecard Titles ---
            this.elements.battingScorecardTitle.textContent = `${battingTeam.name} Batting`;
            this.elements.bowlingScorecardTitle.textContent = `${bowlingTeam.name} Bowling`;

            // --- Populate Batting Table ---
            this.elements.battingTbody.innerHTML = '';
            // Loop through all players of the batting team to include those who didn't bat.
            battingTeam.players.forEach(player => {
                const batsman = firstInnings.batsmen[player.id];
                let row;
                if (batsman) {
                    const sr = batsman.balls > 0 ? ((batsman.runs / batsman.balls) * 100).toFixed(2) : '0.00';
                    row = `
                        <tr>
                            <td>${batsman.name}</td>
                            <td>${batsman.dismissal_type || 'not out'}</td>
                            <td>${batsman.runs}</td>
                            <td>${batsman.balls}</td>
                            <td>0</td>
                            <td>0</td>
                            <td>${sr}</td>
                        </tr>
                    `;
                } else {
                     row = `<tr><td>${player.name}</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>`;
                }
                this.elements.battingTbody.innerHTML += row;
            });


            // --- ROBUST Bowling Table Population ---
            this.elements.bowlingTbody.innerHTML = '';
            // Loop through all players of the bowling team.
            bowlingTeam.players.forEach(player => {
                const bowler = firstInnings.bowlers[player.id];
                let row;
                // Only create a row if the player actually bowled.
                if (bowler) {
                    const overs = `${bowler.overs}.${bowler.balls}`;
                    const econ = (bowler.overs > 0 || bowler.balls > 0) ? (bowler.runs / (bowler.overs + bowler.balls / 6)).toFixed(2) : '0.00';
                    row = `
                        <tr>
                            <td>${bowler.name}</td>
                            <td>${overs}</td>
                            <td>${bowler.maidens}</td>
                            <td>${bowler.runs}</td>
                            <td>${bowler.wickets}</td>
                            <td>${econ}</td>
                        </tr>
                    `;
                    this.elements.bowlingTbody.innerHTML += row;
                }
            });
        },

        /**
         * Bind the click event to the "Start Second Innings" button.
         */
        bindEvents: function() {
            if (this.eventsBound) return;
            this.elements.startSecondInningsBtn.onclick = () => {
                cricscore.showStep('inningsPrep');
            };
            this.eventsBound = true;
        }
    };

})(window.cricscore);