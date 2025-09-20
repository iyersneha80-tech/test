/**
 * Logic for the Live Scoring screen (Step 3)
 *
 * @package CricScore
 * @version 2.2.4
 */
(function(cricscore) {

    cricscore.steps.scoring = {

        elements: {},
        extraState: { type: null, runs: 0 },
        historyStack: [],
        wicketState: {
            type: null,
            fielderId: null,
            nextBatsmanId: null
        },
        newBowlerState: {
            bowlerId: null
        },
        isWaitingForNewBowler: false,

        init: function() {
            this.elements = {
                battingTeam: document.getElementById('live-batting-team'),
                runs: document.getElementById('live-score-runs'),
                wickets: document.getElementById('live-score-wickets'),
                overs: document.getElementById('live-overs-completed'),
                balls: document.getElementById('live-balls-completed'),
                strikerDetails: document.getElementById('striker-details'),
                nonStrikerDetails: document.getElementById('non-striker-details'),
                strikerName: document.getElementById('striker-name'),
                strikerRuns: document.getElementById('striker-runs'),
                strikerBalls: document.getElementById('striker-balls'),
                nonStrikerName: document.getElementById('non-striker-name'),
                nonStrikerRuns: document.getElementById('non-striker-runs'),
                nonStrikerBalls: document.getElementById('non-striker-balls'),
                bowlerName: document.getElementById('current-bowler-name'),
                bowlerFigures: document.getElementById('current-bowler-figures'),
                thisOverSummary: document.getElementById('this-over-summary'),
                runBtns: document.querySelectorAll('.score-btn.runs'),
                extraBtn: document.getElementById('extra-btn'),
                wicketBtn: document.getElementById('wicket-btn'),
                wicketModal: document.getElementById('wicket-modal'),
                wicketModalClose: document.getElementById('wicket-modal-close'),
                dismissalTypeGrid: document.getElementById('dismissal-type-grid'),
                fielderSelectionContainer: document.getElementById('fielder-selection-container'),
                fielderRosterGrid: document.getElementById('fielder-roster-grid'),
                nextBatsmanSelectionContainer: document.getElementById('next-batsman-selection-container'),
                nextBatsmanRosterGrid: document.getElementById('next-batsman-roster-grid'),
                confirmWicketBtn: document.getElementById('confirm-wicket-btn'),
                wicketError: document.getElementById('wicket-error'),
                newBowlerModal: document.getElementById('new-bowler-modal'),
                newBowlerModalClose: document.getElementById('new-bowler-modal-close'),
                newBowlerRosterGrid: document.getElementById('new-bowler-roster-grid'),
                confirmNewBowlerBtn: document.getElementById('confirm-new-bowler-btn'),
                extrasModal: document.getElementById('extras-modal'),
                extrasModalClose: document.getElementById('extras-modal-close'),
                extrasTypeBtns: document.querySelectorAll('.extra-type-btn'),
                extrasRunsBtns: document.querySelectorAll('.extra-run-btn'),
                confirmExtraBtn: document.getElementById('confirm-extra-btn'),
                extrasError: document.getElementById('extras-error'),
                undoBtn: document.getElementById('undo-btn'),
                confirmInningsContainer: document.getElementById('confirm-innings-container'),
                confirmInningsBtn: document.getElementById('confirm-innings-btn'),
                chaseInfo: document.getElementById('chase-info'),
                targetScore: document.getElementById('target-score'),
                runsNeededSummary: document.getElementById('runs-needed-summary'),
            };
            
            if (!cricscore.matchState.currentInnings.initialized) {
                this.prepareInningsData();
                cricscore.matchState.currentInnings.initialized = true;
            }
            
            this.isWaitingForNewBowler = false;

            // --- NEW: Determine max wickets based on Last Man Rule ---
            const match = cricscore.matchState.match;
            this.maxWickets = (match.team1_players.length || 11) - 1; // Default to 10
            if (cricscore.matchState.rules && cricscore.matchState.rules.last_man_stands === true) {
                this.maxWickets += 1; // Increase to 11 if rule is active
            }

            this.render();
            this.bindEvents();
        },

        prepareInningsData: function() {
            const innings = cricscore.matchState.currentInnings;
            const match = cricscore.matchState.match;
            const battingTeamPlayers = innings.batting_team_id == match.team1_id ? match.team1_players : match.team2_players;
            const bowlingTeamPlayers = innings.bowling_team_id == match.team1_id ? match.team1_players : match.team2_players;
            const strikerData = battingTeamPlayers.find(p => p.id == innings.striker_id);
            const nonStrikerData = battingTeamPlayers.find(p => p.id == innings.non_striker_id);
            const bowlerData = bowlingTeamPlayers.find(p => p.id == innings.current_bowler_id);
            
            innings.batsmen[innings.striker_id] = { name: strikerData.name, runs: 0, balls: 0, fours: 0, sixes: 0, status: 'not out' };
            innings.batsmen[innings.non_striker_id] = { name: nonStrikerData.name, runs: 0, balls: 0, fours: 0, sixes: 0, status: 'not out' };
            
            innings.battingOrder = [innings.striker_id, innings.non_striker_id];

            innings.bowlers[innings.current_bowler_id] = { name: bowlerData.name, overs: 0, balls: 0, maidens: 0, runs: 0, wickets: 0 };
        },

        bindEvents: function() {
            if (this.eventsBound) return;
            this.elements.runBtns.forEach(btn => btn.onclick = () => this.processBall(parseInt(btn.dataset.run, 10), { isExtra: false }));
            this.elements.wicketBtn.onclick = () => this.setupWicketModal();
            this.elements.wicketModalClose.onclick = () => this.elements.wicketModal.style.display = 'none';
            
            this.elements.dismissalTypeGrid.onclick = (e) => this.handleDismissalSelection(e);
            this.elements.fielderRosterGrid.onclick = (e) => this.handleFielderSelection(e);
            this.elements.nextBatsmanRosterGrid.onclick = (e) => this.handleNextBatsmanSelection(e);

            this.elements.confirmWicketBtn.onclick = () => this.processWicket();

            this.elements.newBowlerModalClose.onclick = () => {
                this.elements.newBowlerModal.style.display = 'none';
            };
            this.elements.newBowlerRosterGrid.onclick = (e) => this.handleNewBowlerSelection(e);
            this.elements.confirmNewBowlerBtn.onclick = () => this.handleNewBowler();

            this.elements.extraBtn.onclick = () => {
                if (this.isWaitingForNewBowler) {
                    this.elements.newBowlerModal.style.display = 'block';
                    return;
                }
                this.resetExtrasModal(); 
                this.elements.extrasModal.style.display = 'block';
            };
            this.elements.extrasModalClose.onclick = () => this.elements.extrasModal.style.display = 'none';
            this.elements.extrasTypeBtns.forEach(btn => btn.onclick = (e) => this.selectExtraType(e));
            this.elements.extrasRunsBtns.forEach(btn => btn.onclick = (e) => this.selectExtraRuns(e));
            this.elements.confirmExtraBtn.onclick = () => this.processExtra();
            this.elements.undoBtn.onclick = () => this.undoLastAction();
            this.elements.confirmInningsBtn.onclick = async () => await this.finalizeInnings();
            this.eventsBound = true;
        },
        
        processBall: function(runs, options = {}) {
            if (this.isWaitingForNewBowler) {
                this.elements.newBowlerModal.style.display = 'block';
                return;
            }

            this.saveStateForUndo();
            const innings = cricscore.matchState.currentInnings;
            const striker = innings.batsmen[innings.on_strike_id];
            const bowler = innings.bowlers[innings.current_bowler_id];

            striker.runs += runs;
            innings.score += runs;
            bowler.runs += runs;

            if (!options.isExtra) {
                if (runs === 4) striker.fours = (striker.fours || 0) + 1;
                if (runs === 6) striker.sixes = (striker.sixes || 0) + 1;
            }
            
            if (!options.isWicket && !options.logProcessed) {
                innings.log.push(runs);
            }

            const isLegalDelivery = !options.isExtra;
            if (isLegalDelivery) {
                striker.balls++;
                bowler.balls++;
            }

            if (runs % 2 !== 0) {
                this.rotateStrike();
            }
            
            if (isLegalDelivery && bowler.balls === 6) {
                this.handleEndOfOver();
            }
            
            this.checkInningsCompletion();
            this.render();
            this.saveState();
        },

        setupWicketModal: function() {
            if (this.isWaitingForNewBowler) {
                this.elements.newBowlerModal.style.display = 'block';
                return;
            }
            this.wicketState = { type: null, fielderId: null, nextBatsmanId: null };
            this.populateWicketModal();
            this.updateWicketModalUI();
            this.hideWicketError();
            this.elements.wicketModal.style.display = 'block';
        },

        populateWicketModal: function() {
            const innings = cricscore.matchState.currentInnings;
            const match = cricscore.matchState.match;

            const dismissalTypes = ['Caught', 'Bowled', 'LBW', 'Stumped', 'Run Out'];
            this.elements.dismissalTypeGrid.innerHTML = '';
            dismissalTypes.forEach(type => {
                const btn = document.createElement('button');
                btn.className = 'dismissal-btn';
                btn.dataset.type = type;
                btn.textContent = type;
                this.elements.dismissalTypeGrid.appendChild(btn);
            });

            const bowlingTeamPlayers = innings.bowling_team_id == match.team1_id ? match.team1_players : match.team2_players;
            this.elements.fielderRosterGrid.innerHTML = '';
            bowlingTeamPlayers.forEach(player => {
                this.elements.fielderRosterGrid.appendChild(this.createPlayerCard(player, 'fielder'));
            });

            const battingTeamPlayers = innings.batting_team_id == match.team1_id ? match.team1_players : match.team2_players;
            const availableBatsmen = battingTeamPlayers.filter(p => !innings.batsmen[p.id] || innings.batsmen[p.id].status === 'not out').filter(p => p.id != innings.striker_id && p.id != innings.non_striker_id);
            this.elements.nextBatsmanRosterGrid.innerHTML = '';
            if (availableBatsmen.length > 0) {
                this.elements.nextBatsmanSelectionContainer.style.display = 'block';
                availableBatsmen.forEach(player => {
                    this.elements.nextBatsmanRosterGrid.appendChild(this.createPlayerCard(player, 'next-batsman'));
                });
            } else {
                this.elements.nextBatsmanSelectionContainer.style.display = 'none';
            }
        },

        createPlayerCard: function(player, type) {
            const card = document.createElement('div');
            card.className = `player-card ${type}`;
            card.dataset.playerId = player.id;
            const img = document.createElement('img');
            img.className = 'player-card-img';
            const placeholderImg = `https://placehold.co/40x40/E2E8F0/475569?text=${player.name.charAt(0)}`;
            img.src = player.profile_image_url || placeholderImg;
            const name = document.createElement('span');
            name.className = 'player-card-name';
            name.textContent = player.name;
            card.appendChild(img);
            card.appendChild(name);
            return card;
        },

        handleDismissalSelection: function(e) {
            const btn = e.target.closest('.dismissal-btn');
            if (!btn) return;
            this.wicketState.type = btn.dataset.type;
            this.updateWicketModalUI();
        },

        handleFielderSelection: function(e) {
            const card = e.target.closest('.player-card');
            if (!card) return;
            const playerId = parseInt(card.dataset.playerId, 10);
            this.wicketState.fielderId = this.wicketState.fielderId === playerId ? null : playerId;
            this.updateWicketModalUI();
        },

        handleNextBatsmanSelection: function(e) {
            const card = e.target.closest('.player-card');
            if (!card) return;
            const playerId = parseInt(card.dataset.playerId, 10);
            this.wicketState.nextBatsmanId = this.wicketState.nextBatsmanId === playerId ? null : playerId;
            this.updateWicketModalUI();
        },

        updateWicketModalUI: function() {
            this.elements.dismissalTypeGrid.querySelectorAll('.dismissal-btn').forEach(btn => {
                btn.classList.toggle('selected', this.wicketState.type === btn.dataset.type);
            });

            const fielderNeeded = ['Caught', 'Stumped', 'Run Out'];
            this.elements.fielderSelectionContainer.style.display = fielderNeeded.includes(this.wicketState.type) ? 'block' : 'none';

            this.elements.fielderRosterGrid.querySelectorAll('.player-card').forEach(card => {
                const playerId = parseInt(card.dataset.playerId, 10);
                card.classList.toggle('selected', this.wicketState.fielderId === playerId);
            });

            this.elements.nextBatsmanRosterGrid.querySelectorAll('.player-card').forEach(card => {
                const playerId = parseInt(card.dataset.playerId, 10);
                card.classList.toggle('selected', this.wicketState.nextBatsmanId === playerId);
            });
        },

        processWicket: function() {
            this.saveStateForUndo();
            const innings = cricscore.matchState.currentInnings;
            if (innings.wickets >= 10) return;

            const { type, fielderId, nextBatsmanId } = this.wicketState;

            if (!type) {
                this.showWicketError('Please select a dismissal type.');
                return;
            }
            const fielderNeeded = ['Caught', 'Stumped', 'Run Out'];
            if (fielderNeeded.includes(type) && !fielderId) {
                this.showWicketError('Please select the fielder involved.');
                return;
            }
            if (this.elements.nextBatsmanSelectionContainer.style.display !== 'none' && !nextBatsmanId) {
                this.showWicketError('Please select the next batsman.');
                return;
            }
            this.hideWicketError();

            const dismissedBatsmanId = innings.on_strike_id;
            const dismissedBatsman = innings.batsmen[dismissedBatsmanId];
            dismissedBatsman.status = 'out';
            
            dismissedBatsman.dismissal_info = {
                type: type,
                bowler_id: innings.current_bowler_id,
                fielder_id: fielderId
            };
            dismissedBatsman.dismissal_type = type;

            innings.wickets++;
            innings.log.push('W');

            const bowler = innings.bowlers[innings.current_bowler_id];
            if (type !== 'Run Out') {
                bowler.wickets++;
            }

            this.processBall(0, { isWicket: true, isExtra: false });

            if (innings.wickets < 10 && nextBatsmanId) {
                const newBatsmanId = nextBatsmanId;
                const match = cricscore.matchState.match;
                const battingTeamPlayers = innings.batting_team_id == match.team1_id ? match.team1_players : match.team2_players;
                const newBatsmanData = battingTeamPlayers.find(p => p.id == newBatsmanId);
                
                innings.batsmen[newBatsmanId] = { name: newBatsmanData.name, runs: 0, balls: 0, fours: 0, sixes: 0, status: 'not out' };
                
                if (innings.battingOrder && !innings.battingOrder.includes(newBatsmanId)) {
                    innings.battingOrder.push(newBatsmanId);
                }

                if (innings.striker_id === dismissedBatsmanId) {
                    innings.striker_id = newBatsmanId;
                } else {
                    innings.non_striker_id = newBatsmanId;
                }
                
                innings.on_strike_id = newBatsmanId;
            }
            
                        // --- NEW: Handle the "Last Man Standing" state ---
            // This runs when the second-to-last wicket has fallen in a Last Man Rule match.
            if (innings.wickets === (this.maxWickets - 1)) {
                // Find the ID of the one batsman who is NOT out.
                const lastManId = (innings.striker_id === dismissedBatsmanId) 
                                ? innings.non_striker_id 
                                : innings.striker_id;

                // Set BOTH striker and non-striker IDs to the last man.
                // This makes them bat "alone".
                innings.striker_id = lastManId;
                innings.non_striker_id = lastManId;
                innings.on_strike_id = lastManId;
            }

            this.elements.wicketModal.style.display = 'none';
            this.render();
        },

        processExtra: function() {
            this.saveStateForUndo();
            if (!this.extraState.type) {
                this.elements.extrasError.textContent = 'Please select the type of extra.';
                this.elements.extrasError.style.display = 'block';
                return;
            }
            this.elements.extrasError.style.display = 'none';

            const innings = cricscore.matchState.currentInnings;
            const type = this.extraState.type;
            const runs = this.extraState.runs;
            
            const isIllegal = (type === 'wd' || type === 'nb');
            const penalty = isIllegal ? 1 : 0;
            const totalRuns = runs + penalty;

            innings.log.push(`${runs > 0 ? runs : ''}${type}`);
            if (type === 'nb') {
                innings.batsmen[innings.on_strike_id].runs += runs;
            }
            if (runs % 2 !== 0) {
                this.rotateStrike();
            }
            
            this.processBall(totalRuns, { isExtra: true, logProcessed: true });

            this.elements.extrasModal.style.display = 'none';
        },

        handleEndOfOver: function() {
            const innings = cricscore.matchState.currentInnings;
            const bowler = innings.bowlers[innings.current_bowler_id];
            innings.overs_completed++;
            bowler.overs++;
            bowler.balls = 0;
            this.rotateStrike();

            if (!this.checkInningsCompletion()) {
                this.isWaitingForNewBowler = true;
                this.setupNewBowlerModal();
                this.render();
            }
        },
        
        setupNewBowlerModal: function() {
            this.newBowlerState.bowlerId = null;
            this.populateNewBowlerModal();
            this.updateNewBowlerModalUI();
            this.elements.newBowlerModal.style.display = 'block';
        },

        populateNewBowlerModal: function() {
            const innings = cricscore.matchState.currentInnings;
            const match = cricscore.matchState.match;
            const bowlingTeamPlayers = innings.bowling_team_id == match.team1_id ? match.team1_players : match.team2_players;
            
            const availableBowlers = bowlingTeamPlayers.filter(p => p.id != innings.current_bowler_id);
            
            this.elements.newBowlerRosterGrid.innerHTML = '';
            availableBowlers.forEach(player => {
                this.elements.newBowlerRosterGrid.appendChild(this.createPlayerCard(player, 'new-bowler'));
            });
        },

        handleNewBowlerSelection: function(e) {
            const card = e.target.closest('.player-card');
            if (!card) return;
            const playerId = parseInt(card.dataset.playerId, 10);
            this.newBowlerState.bowlerId = this.newBowlerState.bowlerId === playerId ? null : playerId;
            this.updateNewBowlerModalUI();
        },

        updateNewBowlerModalUI: function() {
            this.elements.newBowlerRosterGrid.querySelectorAll('.player-card').forEach(card => {
                const playerId = parseInt(card.dataset.playerId, 10);
                card.classList.toggle('selected', this.newBowlerState.bowlerId === playerId);
            });
        },

        handleNewBowler: function() {
            const newBowlerId = this.newBowlerState.bowlerId;
            if (!newBowlerId) {
                alert("Please select a bowler.");
                return;
            }
            const innings = cricscore.matchState.currentInnings;
            innings.current_bowler_id = newBowlerId;
            
            if (!innings.bowlers[newBowlerId]) {
                const match = cricscore.matchState.match;
                const bowlingTeamPlayers = innings.bowling_team_id == match.team1_id ? match.team1_players : match.team2_players;
                const bowlerData = bowlingTeamPlayers.find(p => p.id == newBowlerId);
                innings.bowlers[newBowlerId] = { name: bowlerData.name, overs: 0, balls: 0, maidens: 0, runs: 0, wickets: 0 };
            }
            
            this.isWaitingForNewBowler = false;

            this.elements.newBowlerModal.style.display = 'none';
            innings.log = [];
            this.render();
            this.saveState();
        },
        
        checkInningsCompletion: function() {
            const innings = cricscore.matchState.currentInnings;
            const match = cricscore.matchState.match;
            const maxOvers = parseInt(match.overs_per_innings, 10);
            const maxWickets = this.maxWickets;
            let isComplete = false;

            if (innings.number === 1) {
                isComplete = innings.wickets >= maxWickets || innings.overs_completed >= maxOvers;
            } else {
                const target = cricscore.matchState.firstInnings.score + 1;
                isComplete = innings.score >= target || innings.wickets >= maxWickets || innings.overs_completed >= maxOvers;
            }
            
            if (isComplete) {
                innings.isComplete = true;
            }

            return isComplete;
        },

        rotateStrike: function() {
            const innings = cricscore.matchState.currentInnings;
            const onStrike = innings.on_strike_id;
            innings.on_strike_id = (onStrike === innings.striker_id) ? innings.non_striker_id : innings.striker_id;
        },

        render: function() {
            const innings = cricscore.matchState.currentInnings;
            if (!innings) return;
            const match = cricscore.matchState.match;
            const battingTeam = innings.batting_team_id == match.team1_id ? match.team1_name : match.team2_name;
            const bowler = innings.bowlers[innings.current_bowler_id];
            this.elements.battingTeam.textContent = battingTeam;
            this.elements.runs.textContent = innings.score;
            this.elements.wickets.textContent = innings.wickets;
            this.elements.overs.textContent = innings.overs_completed;
            this.elements.balls.textContent = bowler.balls;
            const striker = innings.batsmen[innings.striker_id];
            const nonStriker = innings.batsmen[innings.non_striker_id];
            this.elements.strikerName.textContent = striker.name;
            this.elements.strikerRuns.textContent = striker.runs;
            this.elements.strikerBalls.textContent = striker.balls;

            // --- FIX: Conditionally show or hide the non-striker UI ---
            if (innings.striker_id === innings.non_striker_id) {
                // If it's the last man, hide the non-striker row.
                this.elements.nonStrikerDetails.style.display = 'none';
            } else {
                // Otherwise, ensure the non-striker row is visible and populated.
                this.elements.nonStrikerDetails.style.display = 'flex';
                this.elements.nonStrikerName.textContent = nonStriker.name;
                this.elements.nonStrikerRuns.textContent = nonStriker.runs;
                this.elements.nonStrikerBalls.textContent = nonStriker.balls;
            }
            // Always set the name without the asterisk first
this.elements.strikerName.textContent = striker.name;
this.elements.nonStrikerName.textContent = nonStriker.name;

// Now, toggle the .on-strike class on the parent element for the CSS to handle the asterisk
if (innings.on_strike_id === innings.striker_id) {
    this.elements.strikerDetails.classList.add('on-strike');
    this.elements.nonStrikerDetails.classList.remove('on-strike');
} else {
    this.elements.strikerDetails.classList.remove('on-strike');
    this.elements.nonStrikerDetails.classList.add('on-strike');
}
            this.elements.bowlerName.textContent = bowler.name;
            this.elements.bowlerFigures.textContent = `${bowler.overs}.${bowler.balls}-${bowler.maidens}-${bowler.runs}-${bowler.wickets}`;
            
            const isComplete = innings.isComplete || false;
            const scoringDisabled = isComplete;

            this.elements.runBtns.forEach(btn => btn.disabled = scoringDisabled);
            this.elements.extraBtn.disabled = scoringDisabled;
            this.elements.wicketBtn.disabled = scoringDisabled;
            
            if (isComplete) {
    this.elements.confirmInningsContainer.style.display = 'block';
} else {
    this.elements.confirmInningsContainer.style.display = 'none';
}
            
            this.elements.thisOverSummary.innerHTML = innings.log.map(ball => {
    let className = 'ball-log';
    // Check the ball value and add the appropriate class for styling
    if (ball === 4 || ball === '4') {
        className += ' four';
    } else if (ball === 6 || ball === '6') {
        className += ' six';
    } else if (String(ball).toUpperCase() === 'W') {
        className += ' wicket';
    }
    return `<span class="${className}">${ball}</span>`;
}).join('');
            const firstInnings = cricscore.matchState.firstInnings;
            if (innings.number === 2 && firstInnings) {
                this.elements.chaseInfo.style.display = 'block';
                
                const target = firstInnings.score + 1;
                this.elements.targetScore.textContent = target;

                const runsNeeded = target - innings.score;
                const maxOvers = parseInt(match.overs_per_innings, 10);
                const bowler = innings.bowlers[innings.current_bowler_id];
                const ballsBowled = (innings.overs_completed * 6) + (bowler ? bowler.balls : 0);
                const ballsRemaining = (maxOvers * 6) - ballsBowled;

                if (runsNeeded <= 0) {
                    this.elements.runsNeededSummary.textContent = ' - Match Won!';
                } else if (ballsRemaining <= 0 && runsNeeded > 0) {
                    this.elements.runsNeededSummary.textContent = ' - Match Lost.';
                } else {
                    const rrr = ballsRemaining > 0 ? ((runsNeeded / ballsRemaining) * 6).toFixed(2) : ' - ';
                    this.elements.runsNeededSummary.textContent = ` - Need ${runsNeeded} from ${ballsRemaining} balls (RRR: ${rrr})`;
                }
            } else {
                this.elements.chaseInfo.style.display = 'none';
            }
        },
        
        resetExtrasModal: function() {
            this.extraState = { type: null, runs: 0 };
            this.elements.extrasTypeBtns.forEach(btn => btn.classList.remove('selected'));
            this.elements.extrasRunsBtns.forEach(btn => btn.classList.remove('selected'));
            this.elements.extrasError.style.display = 'none';
        },

        selectExtraType: function(e) {
            this.elements.extrasTypeBtns.forEach(btn => btn.classList.remove('selected'));
            e.target.classList.add('selected');
            this.extraState.type = e.target.dataset.type;
        },

        selectExtraRuns: function(e) {
            this.elements.extrasRunsBtns.forEach(btn => btn.classList.remove('selected'));
            e.target.classList.add('selected');
            this.extraState.runs = parseInt(e.target.dataset.runs, 10);
        },

        saveState: function() {
            cricscore.saveState();
        },

        saveStateForUndo: function() {
            const stateCopy = JSON.parse(JSON.stringify(cricscore.matchState));
            this.historyStack.push(stateCopy);
            if (this.historyStack.length > 15) {
                this.historyStack.shift();
            }
            if (this.elements.undoBtn) {
                this.elements.undoBtn.disabled = false;
            }
        },
    
        undoLastAction: function() {
            if (this.historyStack.length === 0) return;
            const lastState = this.historyStack.pop();
            cricscore.matchState = lastState;
            this.isWaitingForNewBowler = false; 
            this.render();
            this.saveState();
            if (this.elements.undoBtn) {
                this.elements.undoBtn.disabled = this.historyStack.length === 0;
            }
        },

        finalizeInnings: async function() {
            const innings = cricscore.matchState.currentInnings;
            if (innings.number === 1) {
                cricscore.matchState.firstInnings = JSON.parse(JSON.stringify(innings));
                await cricscore.saveStateImmediate(); 
                cricscore.showStep('midSummary');
            } else {
                cricscore.showStep('postSummary');
            }
        },

        showWicketError: function(message) {
            this.elements.wicketError.textContent = message;
            this.elements.wicketError.style.display = 'block';
        },

        hideWicketError: function() {
            this.elements.wicketError.style.display = 'none';
        }
    };

})(window.cricscore);