/**
 * Logic for the Innings Preparation screen (Step 2)
 * Now handles both First and Second Innings with an interactive UI.
 *
 * @package CricScore
 * @version 2.1.0
 */
(function(cricscore) {

    cricscore.steps.inningsPrep = {

        elements: {},
        // --- CHANGE 1: Updated state to track selections ---
        state: { 
            battingTeam: null, 
            bowlingTeam: null, 
            inningsNumber: 1,
            strikerId: null,
            nonStrikerId: null,
            bowlerId: null
        },

        init: function() {
            // --- CHANGE 2: Updated elements to reference new roster containers ---
            this.elements = {
                title: document.getElementById('innings-prep-title'),
                error: document.getElementById('innings-prep-error'),
                battingTeamName: document.getElementById('prep-batting-team-name'),
                bowlingTeamName: document.getElementById('prep-bowling-team-name'),
                batsmanRoster: document.getElementById('prep-batsman-roster'),
                bowlerRoster: document.getElementById('prep-bowler-roster'),
                startInningsBtn: document.getElementById('start-innings-btn'),
            };

            // Reset state on init
            this.state.strikerId = null;
            this.state.nonStrikerId = null;
            this.state.bowlerId = null;

            this.determineTeamRoles();
            this.populateRosters();
            this.bindEvents();
            this.updateRosterUI(); // Initial UI update
        },

        determineTeamRoles: function() {
            // This function remains unchanged
            const match = cricscore.matchState.match;
            const firstInnings = cricscore.matchState.firstInnings;
            const team1Id = parseInt(match.team1_id, 10);
            const team2Id = parseInt(match.team2_id, 10);
            let battingTeamId, bowlingTeamId;

            if (firstInnings) {
                this.state.inningsNumber = 2;
                battingTeamId = firstInnings.bowling_team_id;
                bowlingTeamId = firstInnings.batting_team_id;
                this.elements.title.textContent = 'Prepare Second Innings';
            } else {
                this.state.inningsNumber = 1;
                const tossWinnerId = parseInt(match.toss_data.winner, 10);
                const tossDecision = match.toss_data.decision;
                if ((tossWinnerId === team1Id && tossDecision === 'bat') || (tossWinnerId === team2Id && tossDecision === 'bowl')) {
                    battingTeamId = team1Id;
                    bowlingTeamId = team2Id;
                } else {
                    battingTeamId = team2Id;
                    bowlingTeamId = team1Id;
                }
                this.elements.title.textContent = 'Prepare First Innings';
            }

            this.state.battingTeam = {
                id: battingTeamId,
                name: battingTeamId === team1Id ? match.team1_name : match.team2_name,
                players: battingTeamId === team1Id ? match.team1_players : match.team2_players
            };
            this.state.bowlingTeam = {
                id: bowlingTeamId,
                name: bowlingTeamId === team1Id ? match.team1_name : match.team2_name,
                players: bowlingTeamId === team1Id ? match.team1_players : match.team2_players
            };
        },

        // --- CHANGE 3: Replaced populateDropdowns with populateRosters ---
        populateRosters: function() {
            this.elements.battingTeamName.textContent = this.state.battingTeam.name;
            this.elements.bowlingTeamName.textContent = this.state.bowlingTeam.name;
            
            this.elements.batsmanRoster.innerHTML = '';
            this.state.battingTeam.players.forEach(player => {
                this.elements.batsmanRoster.appendChild(this.createPlayerCard(player));
            });

            this.elements.bowlerRoster.innerHTML = '';
            this.state.bowlingTeam.players.forEach(player => {
                this.elements.bowlerRoster.appendChild(this.createPlayerCard(player));
            });
        },

        // --- CHANGE 4: New helper to create a player card element ---
        createPlayerCard: function(player) {
            const card = document.createElement('div');
            card.className = 'player-card';
            card.dataset.playerId = player.id;

            const img = document.createElement('img');
            img.className = 'player-card-img';
            const placeholderImg = `https://placehold.co/40x40/E2E8F0/475569?text=${player.name.charAt(0)}`;
            img.src = player.profile_image_url || placeholderImg;
            
            const name = document.createElement('span');
            name.className = 'player-card-name';
            name.textContent = player.name;

            const badge = document.createElement('div');
            badge.className = 'player-role-badge';

            card.appendChild(img);
            card.appendChild(name);
            card.appendChild(badge);
            return card;
        },

        bindEvents: function() {
            if (this.eventsBound) return;
            this.elements.startInningsBtn.onclick = async () => await this.handleStartInnings();
            
            // --- CHANGE 5: New event listeners for player selection ---
            this.elements.batsmanRoster.onclick = (e) => this.handleBatsmanSelection(e);
            this.elements.bowlerRoster.onclick = (e) => this.handleBowlerSelection(e);

            this.eventsBound = true;
        },

        // --- CHANGE 6: New logic to handle batsman selection ---
        handleBatsmanSelection: function(e) {
            const card = e.target.closest('.player-card');
            if (!card) return;
            const playerId = parseInt(card.dataset.playerId, 10);

            if (this.state.strikerId === playerId) { // Deselect striker
                this.state.strikerId = null;
            } else if (this.state.nonStrikerId === playerId) { // Deselect non-striker
                this.state.nonStrikerId = null;
            } else if (!this.state.strikerId) { // Select as striker
                this.state.strikerId = playerId;
            } else if (!this.state.nonStrikerId) { // Select as non-striker
                this.state.nonStrikerId = playerId;
            }
            this.updateRosterUI();
        },

        // --- CHANGE 7: New logic to handle bowler selection ---
        handleBowlerSelection: function(e) {
            const card = e.target.closest('.player-card');
            if (!card) return;
            const playerId = parseInt(card.dataset.playerId, 10);

            if (this.state.bowlerId === playerId) { // Deselect bowler
                this.state.bowlerId = null;
            } else { // Select as bowler
                this.state.bowlerId = playerId;
            }
            this.updateRosterUI();
        },

        // --- CHANGE 8: New function to update UI based on selections ---
        updateRosterUI: function() {
            // Update Batsmen
            this.elements.batsmanRoster.querySelectorAll('.player-card').forEach(card => {
                const playerId = parseInt(card.dataset.playerId, 10);
                const badge = card.querySelector('.player-role-badge');
                card.classList.remove('selected-striker', 'selected-nonstriker');
                badge.textContent = '';
                badge.style.display = 'none';

                if (this.state.strikerId === playerId) {
                    card.classList.add('selected-striker');
                    badge.textContent = 'Striker';
                    badge.style.display = 'block';
                } else if (this.state.nonStrikerId === playerId) {
                    card.classList.add('selected-nonstriker');
                    badge.textContent = 'Non-Striker';
                    badge.style.display = 'block';
                }
            });

            // Update Bowlers
            this.elements.bowlerRoster.querySelectorAll('.player-card').forEach(card => {
                const playerId = parseInt(card.dataset.playerId, 10);
                const badge = card.querySelector('.player-role-badge');
                card.classList.remove('selected-bowler');
                badge.textContent = '';
                badge.style.display = 'none';

                if (this.state.bowlerId === playerId) {
                    card.classList.add('selected-bowler');
                    badge.textContent = 'Bowler';
                    badge.style.display = 'block';
                }
            });
        },

        // --- CHANGE 9: Updated validation logic ---
        handleStartInnings: async function() {
            const { strikerId, nonStrikerId, bowlerId } = this.state;

            if (!strikerId || !nonStrikerId || !bowlerId) {
                this.showError("Please select a striker, non-striker, and opening bowler.");
                return;
            }
            // Striker and Non-Striker check is handled by the selection logic
            this.hideError();

            cricscore.matchState.currentInnings = {
                number: this.state.inningsNumber,
                batting_team_id: this.state.battingTeam.id,
                bowling_team_id: this.state.bowlingTeam.id,
                score: 0,
                wickets: 0,
                overs_completed: 0,
                log: [],
                batsmen: {},
                bowlers: {},
                striker_id: strikerId,
                non_striker_id: nonStrikerId,
                on_strike_id: strikerId,
                current_bowler_id: bowlerId,
                isComplete: false
            };
            
            await cricscore.saveStateImmediate();
            cricscore.showStep('scoring');
        },

        showError: function(message) {
            this.elements.error.textContent = message;
            this.elements.error.style.display = 'block';
        },

        hideError: function() {
            this.elements.error.style.display = 'none';
        }
    };

})(window.cricscore);