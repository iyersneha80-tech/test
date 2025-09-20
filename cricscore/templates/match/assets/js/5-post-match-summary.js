/**
 * Logic for the Post-Match Summary screen (Step 5)
 *
 * @package CricScore
 * @version 2.1.0
 */
(function(cricscore) {

    cricscore.steps.postSummary = {

        elements: {},

        init: function() {
            this.elements = {
                resultText: document.getElementById('post-match-result-text'),
                battingScorecardTitle: document.getElementById('post-match-batting-scorecard-title'),
                bowlingScorecardTitle: document.getElementById('post-match-bowling-scorecard-title'),
                battingTbody: document.getElementById('post-match-batting-tbody'),
                bowlingTbody: document.getElementById('post-match-bowling-tbody'),
                finalizeBtn: document.getElementById('finalize-match-btn'),
            };
            this.populateData();
            this.bindEvents();
        },

        populateData: function() {
            const match = cricscore.matchState.match;
            const firstInnings = cricscore.matchState.firstInnings;
            const secondInnings = cricscore.matchState.currentInnings;

            if (!firstInnings || !secondInnings) {
                console.error("Innings data is missing from matchState.");
                return;
            }

            const result = this.determineWinner(match, firstInnings, secondInnings);
            cricscore.matchState.result = result;
            this.elements.resultText.textContent = result.text;

            const battingTeamName = secondInnings.batting_team_id == match.team1_id ? match.team1_name : match.team2_name;
            const bowlingTeamName = secondInnings.bowling_team_id == match.team1_id ? match.team1_name : match.team2_name;

            this.elements.battingScorecardTitle.textContent = `${battingTeamName} Batting`;
            this.elements.bowlingScorecardTitle.textContent = `${bowlingTeamName} Bowling`;

            this.elements.battingTbody.innerHTML = '';
            for (const playerId in secondInnings.batsmen) {
                const batsman = secondInnings.batsmen[playerId];
                const sr = batsman.balls > 0 ? ((batsman.runs / batsman.balls) * 100).toFixed(2) : '0.00';
                const row = `
                    <tr>
                        <td>${batsman.name}</td>
                        <td>${batsman.dismissal_type || 'not out'}</td>
                        <td>${batsman.runs}</td>
                        <td>${batsman.balls}</td>
                        <td>${batsman.fours || 0}</td>
                        <td>${batsman.sixes || 0}</td>
                        <td>${sr}</td>
                    </tr>
                `;
                this.elements.battingTbody.innerHTML += row;
            }

            this.elements.bowlingTbody.innerHTML = '';
             for (const playerId in secondInnings.bowlers) {
                const bowler = secondInnings.bowlers[playerId];
                const overs = `${bowler.overs}.${bowler.balls}`;
                const econ = bowler.overs > 0 ? (bowler.runs / (bowler.overs + bowler.balls / 6)).toFixed(2) : '0.00';
                const row = `
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
        },

        determineWinner: function(match, firstInnings, secondInnings) {
            const target = firstInnings.score + 1;
            const team1Name = match.team1_name;
            const team2Name = match.team2_name;
            
            const firstInningsBattingTeamName = firstInnings.batting_team_id == match.team1_id ? team1Name : team2Name;
            const secondInningsBattingTeamName = secondInnings.batting_team_id == match.team1_id ? team1Name : team2Name;

            if (secondInnings.score >= target) {
                const wicketsInHand = 10 - secondInnings.wickets;
                return {
                    winner_id: secondInnings.batting_team_id,
                    text: `${secondInningsBattingTeamName} won by ${wicketsInHand} wicket${wicketsInHand !== 1 ? 's' : ''}`
                };
            } else if (secondInnings.score < firstInnings.score) {
                const runMargin = firstInnings.score - secondInnings.score;
                return {
                    winner_id: firstInnings.batting_team_id,
                    text: `${firstInningsBattingTeamName} won by ${runMargin} run${runMargin !== 1 ? 's' : ''}`
                };
            } else {
                return {
                    winner_id: null,
                    text: `Match Tied`
                };
            }
        },

        bindEvents: function() {
            if (this.eventsBound) return;
            this.elements.finalizeBtn.onclick = () => this.finalizeMatch();
            this.eventsBound = true;
        },

        // --- CHANGE 3: Refactor finalizeMatch to re-initialize the app ---
        finalizeMatch: async function() {
            console.log("Finalizing match...", cricscore.matchState);
            this.elements.finalizeBtn.textContent = 'Saving...';
            this.elements.finalizeBtn.disabled = true;

            try {
                const response = await fetch(`${cricscore.api.base}/matches/${cricscore.api.matchId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cricscore.api.nonce },
                    body: JSON.stringify(cricscore.matchState)
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Failed to save the final match state.');
                }
                
                console.log("Match saved successfully! Re-initializing to show final result...");
                // Instead of just showing the next step, we re-run the main loader.
                // This ensures the state is fresh from the database, fixing the bug.
                cricscore.initializeMatch();

            } catch (error) {
                alert(`Error: Could not finalize match. ${error.message}`);
                this.elements.finalizeBtn.textContent = 'Finalize & Complete Match';
                this.elements.finalizeBtn.disabled = false;
            }
        }
    };

})(window.cricscore);