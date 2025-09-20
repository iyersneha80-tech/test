/**
 * Logic for the Pre-Match Summary screen (Step 1)
 *
 * @package CricScore
 * @version 2.0.0
 */
(function(cricscore) {

    // Define the 'preMatch' step module
    cricscore.steps.preMatch = {
        
        /**
         * Initialize the step.
         * This function is called by the main controller when the step is shown.
         */
        init: function() {
            this.populateData();
            this.bindEvents();
        },

        /**
         * Populate the HTML elements with data from the global matchState.
         */
        populateData: function() {
            const match = cricscore.matchState.match;
            if (!match) {
                console.error("Match data is missing from state.");
                return;
            }

            // --- Populate Header ---
            document.getElementById('pre-match-title').textContent = `${match.team1_name || 'Team A'} vs ${match.team2_name || 'Team B'}`;

            // --- Populate Toss Information ---
            const tossWinnerId = match.toss_data?.winner;
            const tossDecision = match.toss_data?.decision;
            const tossWinnerName = tossWinnerId == match.team1_id ? match.team1_name : match.team2_name;
            if (tossWinnerName && tossDecision) {
                document.getElementById('pre-match-toss-result').innerHTML = `<strong>${tossWinnerName}</strong> won the toss and elected to <strong>${tossDecision}</strong>.`;
            } else {
                document.getElementById('pre-match-toss-result').textContent = 'Toss information not available.';
            }

            // --- Populate Match Info Card ---
            document.getElementById('pre-match-tournament').textContent = match.tournament_name || 'N/A';
            document.getElementById('pre-match-format').textContent = match.match_format || 'N/A';
            document.getElementById('pre-match-overs').textContent = match.overs_per_innings || 'N/A';
            document.getElementById('pre-match-venue').textContent = match.venue_name || 'N/A';
            const matchDate = new Date(match.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('pre-match-date').textContent = matchDate;

            // --- Populate Team Names in Squad Cards ---
            document.getElementById('pre-match-team1-name').textContent = match.team1_name || 'Team A';
            document.getElementById('pre-match-team2-name').textContent = match.team2_name || 'Team B';
            
            // --- NEW: Populate Player Squads ---
            const team1SquadEl = document.getElementById('pre-match-team1-squad');
            const team2SquadEl = document.getElementById('pre-match-team2-squad');

            team1SquadEl.innerHTML = ''; // Clear existing
            if (match.team1_players && match.team1_players.length > 0) {
                match.team1_players.forEach(player => {
                    const li = document.createElement('li');
                    li.textContent = player.name;
                    team1SquadEl.appendChild(li);
                });
            } else {
                team1SquadEl.innerHTML = '<li>Squad not available.</li>';
            }

            team2SquadEl.innerHTML = ''; // Clear existing
            if (match.team2_players && match.team2_players.length > 0) {
                match.team2_players.forEach(player => {
                    const li = document.createElement('li');
                    li.textContent = player.name;
                    team2SquadEl.appendChild(li);
                });
            } else {
                team2SquadEl.innerHTML = '<li>Squad not available.</li>';
            }
        },
        
        /**
         * Bind click events to the action buttons.
         */
        bindEvents: function() {
            // Ensure events are only bound once
            if (this.eventsBound) return;

            const startMatchBtn = document.getElementById('start-match-btn');
            const abandonMatchBtn = document.getElementById('abandon-match-btn');
            const abandonModal = document.getElementById('abandon-confirm-modal');
            const abandonModalClose = document.getElementById('abandon-modal-close');
            const abandonForm = document.getElementById('abandon-form');

// --- Start Match ---
            startMatchBtn.onclick = () => {
                console.log("Match Started!");
                cricscore.saveState(); // Save state to mark match as 'live'
                cricscore.showStep('inningsPrep'); // Transition to the next step
            };
            // --- Abandon Match ---
            abandonMatchBtn.onclick = () => {
                abandonModal.style.display = 'block';
            };

            abandonModalClose.onclick = () => {
                abandonModal.style.display = 'none';
            };
            
            window.onclick = (e) => { 
                if (e.target == abandonModal) {
                    abandonModal.style.display = 'none'; 
                }
            };

            abandonForm.onsubmit = (e) => {
                e.preventDefault();
                const reason = document.getElementById('abandon-reason').value;
                alert(`Match has been marked as abandoned. Reason: ${reason}. (API functionality to be added)`);
                // TODO: API call to update match status to 'abandoned' with the reason.
                abandonModal.style.display = 'none';
                cricscore.showStep('result'); // Transition to the final result screen
            };

            this.eventsBound = true;
        }
    };

})(window.cricscore);