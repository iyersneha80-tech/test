/**
 * Main Scoreboard Logic Module (Refactored for Multi-Innings State & Robust 'All Out' Logic)
 * @param {object} matchConfig - The initial setup data from the form.
 */
function initializeScoreboard(matchConfig) {
    const historyManager = {
        stack: [],
        limit: 30,
        save(state) {
            this.stack.push(JSON.stringify(state));
            if (this.stack.length > this.limit) this.stack.shift();
            ui.undoBtn.disabled = false;
        },
        pop() {
            if (this.stack.length === 0) return null;
            const stateString = this.stack.pop();
            ui.undoBtn.disabled = this.stack.length === 0;
            return stateString;
        }
    };

    let state = {};

    const ui = {
        scoreboardContainer: document.getElementById('scoreboard-container'),
        battingTeamName: document.getElementById('batting-team-name'),
        battingTeamShortname: document.getElementById('batting-team-shortname'),
        bowlingTeamName: document.getElementById('bowling-team-name'),
        bowlingTeamShortname: document.getElementById('bowling-team-shortname'),
        teamScore: document.getElementById('team-score'),
        teamWickets: document.getElementById('team-wickets'),
        teamOvers: document.getElementById('team-overs'),
        batsman1Panel: document.getElementById('batsman1-panel'),
        batsman1Name: document.getElementById('batsman1-name'),
        batsman1Runs: document.getElementById('batsman1-runs'),
        batsman1Balls: document.getElementById('batsman1-balls'),
        batsman1StrikerIndicator: document.getElementById('batsman1-striker-indicator'),
        batsman2Panel: document.getElementById('batsman2-panel'),
        batsman2Name: document.getElementById('batsman2-name'),
        batsman2Runs: document.getElementById('batsman2-runs'),
        batsman2Balls: document.getElementById('batsman2-balls'),
        batsman2StrikerIndicator: document.getElementById('batsman2-striker-indicator'),
        partnershipRuns: document.getElementById('partnership-runs'),
        partnershipBalls: document.getElementById('partnership-balls'),
        bowlerName: document.getElementById('bowler-name'),
        bowlerFigures: document.getElementById('bowler-figures'),
        thisOverDisplay: document.getElementById('this-over-display'),
        freeHitIndicator: document.getElementById('free-hit-indicator'),
        timelineLog: document.getElementById('timeline-log'),
        modalBackdrop: document.getElementById('modal-backdrop'),
        setupModal: document.getElementById('setup-modal'),
        setupInningsNum: document.getElementById('setup-innings-num'),
        setupStrikerSelect: document.getElementById('setup-striker-select'),
        setupNonstrikerSelect: document.getElementById('setup-nonstriker-select'),
        setupBowlerSelect: document.getElementById('setup-bowler-select'),
        startInningsBtn: document.getElementById('start-innings-btn'),
        newBowlerModal: document.getElementById('new-bowler-modal'),
        newBowlerOverNum: document.getElementById('new-bowler-over-num'),
        newBowlerButtons: document.getElementById('new-bowler-buttons'),
        confirmBowlerBtn: document.getElementById('confirm-bowler-btn'),
        extrasBtn: document.getElementById('extras-btn'),
        wicketBtn: document.getElementById('wicket-btn'),
        undoBtn: document.getElementById('cric-undo-btn'),
        extrasModal: document.getElementById('extras-modal'),
        extrasTypeBtns: document.getElementById('extras-type-btns'),
        extrasRunBtns: document.getElementById('extras-run-btns'),
        confirmExtraBtn: document.getElementById('confirm-extra-btn'),
        wicketModal: document.getElementById('wicket-modal'),
        wicketTypeButtons: document.getElementById('wicket-type-buttons'),
        wicketNextBatsmanButtons: document.getElementById('wicket-next-batsman-buttons'),
        confirmWicketBtn: document.getElementById('confirm-wicket-btn'),
        chaseInfo: document.getElementById('chase-info'),
        targetScore: document.getElementById('target-score'),
        currentRunRate: document.getElementById('current-run-rate'),
        requiredRunRate: document.getElementById('required-run-rate'),
    };

    function rebind(el) {
        if (!el) return el;
        const newEl = el.cloneNode(true);
        el.parentNode.replaceChild(newEl, el);
        return newEl;
    }

    function saveState() {
        const stateToSave = JSON.parse(JSON.stringify(state));
        delete stateToSave.modal;
        localStorage.setItem('cricscoreLive', JSON.stringify(stateToSave));
    }

    function createInningsObject(battingTeamId, bowlingTeamId) {
        return {
            battingTeamId: battingTeamId,
            bowlingTeamId: bowlingTeamId,
            score: 0,
            wickets: 0,
            overs: 0,
            balls: 0,
            thisOver: [],
            currentOverRuns: 0,
            extras: { total: 0, wd: 0, nb: 0, b: 0, lb: 0 },
            fallOfWickets: [],
            batsmen: [],
            bowlers: [],
            onStrikeBatsmanId: null,
            batsman1: {},
            batsman2: {},
            currentBowler: {},
            partnership: { runs: 0, balls: 0, batsman1Id: null, batsman2Id: null },
        };
    }

    function createNewState() {
        let initialBattingTeamId = 1;
        let initialBowlingTeamId = 2;
        const tossWinner = matchConfig.toss.winner;
        const tossDecision = matchConfig.toss.decision;
        if ((tossWinner === 'A' && tossDecision === 'Bowl') || (tossWinner === 'B' && tossDecision === 'Bat')) {
            [initialBattingTeamId, initialBowlingTeamId] = [2, 1];
        }

        return {
            matchConfig: matchConfig,
            teams: {
                1: { id: 1, name: matchConfig.teamA.name, shortName: matchConfig.teamA.name.substring(0, 3).toUpperCase(), squad: matchConfig.teamA.players.map(p => ({ ...p, out: false })) },
                2: { id: 2, name: matchConfig.teamB.name, shortName: matchConfig.teamB.name.substring(0, 3).toUpperCase(), squad: matchConfig.teamB.players.map(p => ({ ...p, out: false })) }
            },
            totalOvers: matchConfig.totalOvers || 20,
            currentInningsNum: 1,
            innings: {
                1: createInningsObject(initialBattingTeamId, initialBowlingTeamId),
                2: createInningsObject(initialBowlingTeamId, initialBattingTeamId)
            },
            isFreeHit: false,
            target: 0,
            matchOver: false,
            matchResult: '',
            modal: { extras: { type: null, runs: 0 }, wicket: { type: null, nextBatsmanId: null }, newBowler: { nextBowlerId: null } }
        };
    }

    function undo() {
        const prevStateString = historyManager.pop();
        if (prevStateString) {
            state = JSON.parse(prevStateString);
            addLogEvent(`<span class="text-yellow-400">Last action undone.</span>`);
            saveState();
            render();
        }
    }

    function render() {
        const currentInnings = state.innings[state.currentInningsNum];
        const battingTeam = state.teams[currentInnings.battingTeamId];
        const bowlingTeam = state.teams[currentInnings.bowlingTeamId];

        // Naabaad Mode: Hide the non-striker panel if the last man is batting
        if (ui.batsman2Panel) {
            const squadSize = battingTeam.squad.length;
            const isNaabaadModeActive = matchConfig.enableNaabaad && currentInnings.wickets >= squadSize - 1;
            ui.batsman2Panel.style.display = isNaabaadModeActive ? 'none' : 'flex';
            const partnershipContainer = ui.batsman2Panel.nextElementSibling;
            if (partnershipContainer) {
                partnershipContainer.style.display = isNaabaadModeActive ? 'none' : 'block';
            }
        }

        ui.battingTeamName.textContent = battingTeam.name;
        ui.battingTeamShortname.textContent = battingTeam.shortName;
        ui.bowlingTeamName.textContent = bowlingTeam.name;
        ui.bowlingTeamShortname.textContent = bowlingTeam.shortName;
        ui.teamScore.textContent = currentInnings.score;
        ui.teamWickets.textContent = currentInnings.wickets;
        ui.teamOvers.textContent = `(${currentInnings.overs}.${currentInnings.balls})`;

        const b1 = currentInnings.batsman1 || {};
        const b2 = currentInnings.batsman2 || {};
        ui.batsman1Name.textContent = b1.name || '';
        ui.batsman1Runs.textContent = b1.runs;
        ui.batsman1Balls.textContent = b1.balls;
        ui.batsman2Name.textContent = b2.name || '';
        ui.batsman2Runs.textContent = b2.runs;
        ui.batsman2Balls.textContent = b2.balls;

        ui.batsman1Panel.classList.toggle('on-strike', currentInnings.onStrikeBatsmanId === b1.id);
        ui.batsman1StrikerIndicator.classList.toggle('hidden', currentInnings.onStrikeBatsmanId !== b1.id);
        ui.batsman2Panel.classList.toggle('on-strike', currentInnings.onStrikeBatsmanId === b2.id);
        ui.batsman2StrikerIndicator.classList.toggle('hidden', currentInnings.onStrikeBatsmanId !== b2.id);

        ui.partnershipRuns.textContent = currentInnings.partnership.runs;
        ui.partnershipBalls.textContent = currentInnings.partnership.balls;

        const bowler = currentInnings.bowlers.find(b => b.id === currentInnings.currentBowler.id) || { overs: 0, maidens: 0, runs: 0, wickets: 0 };
        ui.bowlerName.textContent = currentInnings.currentBowler.name || '';
        ui.bowlerFigures.textContent = `${bowler.overs}-${bowler.maidens}-${bowler.runs}-${bowler.wickets}`;

        ui.thisOverDisplay.innerHTML = '';
        currentInnings.thisOver.forEach(ball => {
            const ballEl = document.createElement('div');
            ballEl.className = 'h-7 w-7 flex items-center justify-center rounded-full text-sm font-bold text-white';
            let bgColor = 'bg-gray-600';
            if (ball.includes('4')) bgColor = 'bg-green-500';
            if (ball.includes('6')) bgColor = 'bg-purple-600';
            if (ball.includes('W')) bgColor = 'bg-red-500';
            ballEl.classList.add(bgColor);
            ballEl.textContent = ball;
            ui.thisOverDisplay.appendChild(ballEl);
        });

        ui.freeHitIndicator.classList.toggle('hidden', !state.isFreeHit);

        if (state.currentInningsNum === 2 && !state.matchOver) {
            ui.chaseInfo.classList.remove('hidden');
            ui.targetScore.textContent = state.target;
            const totalBalls = currentInnings.overs * 6 + currentInnings.balls;
            ui.currentRunRate.textContent = totalBalls > 0 ? (currentInnings.score / (totalBalls / 6)).toFixed(2) : '0.00';
            const remainingBalls = state.totalOvers * 6 - totalBalls;
            const runsNeeded = state.target - currentInnings.score;
            ui.requiredRunRate.textContent = (remainingBalls > 0 && runsNeeded > 0) ? (runsNeeded / (remainingBalls / 6)).toFixed(2) : '0.00';
        } else {
            ui.chaseInfo.classList.add('hidden');
        }
    }

    function addLogEvent(text) {
        const logEntry = document.createElement('p');
        logEntry.className = 'font-mono text-sm text-gray-300 border-b border-gray-700/50 py-2';
        logEntry.innerHTML = text;
        ui.timelineLog.prepend(logEntry);
    }

    function showMatchResult(resultText) {
        state.matchOver = true;
        state.matchResult = resultText;
        addLogEvent(`<strong class="text-pink-400">${resultText}</strong>`);

        const matchEndedEvent = new CustomEvent('match-ended', {
            detail: { finalState: state },
            bubbles: true,
            composed: true
        });
        document.dispatchEvent(matchEndedEvent);
        saveState();
    }

    function startNewInnings() {
        if (state.currentInningsNum > 1) return;

        const firstInnings = state.innings[1];
        state.target = firstInnings.score + 1;
        addLogEvent(`<strong>End of Innings 1. Target for ${state.teams[state.innings[2].battingTeamId].name} is ${state.target}.</strong>`);

        state.currentInningsNum = 2;
        state.isFreeHit = false;

        const newBattingTeamId = state.innings[2].battingTeamId;
        state.teams[newBattingTeamId].squad.forEach(p => p.out = false);

        ui.scoreboardContainer.classList.add('hidden');
        closeAllModals();
        saveState();
        openInningsSetupModal();
    }

    function handleInningsEnd() {
        if (state.matchOver) return;

        const currentInnings = state.innings[state.currentInningsNum];
        const battingTeam = state.teams[currentInnings.battingTeamId];
        const squadSize = battingTeam.squad.length;
        if (squadSize === 0) return; // Prevent errors with empty teams

        const battingTeamWon = state.currentInningsNum === 2 && currentInnings.score >= state.target;
        
        // **REFACTORED All Out Condition**
        const allOutCondition = matchConfig.enableNaabaad
            ? currentInnings.wickets >= squadSize
            : currentInnings.wickets >= squadSize - 1;

        const inningsOverByOvers = currentInnings.overs >= state.totalOvers;

        if (battingTeamWon) {
            const wicketsInHand = squadSize - 1 - currentInnings.wickets;
            showMatchResult(`${battingTeam.name} won by ${wicketsInHand} wicket${wicketsInHand !== 1 ? 's' : ''}.`);
        } else if (allOutCondition || inningsOverByOvers) {
            if (state.currentInningsNum === 1) {
                startNewInnings();
            } else {
                if (currentInnings.score === state.target - 1) showMatchResult("Match Tied.");
                else showMatchResult(`${state.teams[currentInnings.bowlingTeamId].name} won by ${state.target - 1 - currentInnings.score} runs.`);
            }
        }
    }

    function rotateStrike() {
        const inn = state.innings[state.currentInningsNum];
        const squadSize = state.teams[inn.battingTeamId].squad.length;

        // **REFACTORED Naabaad Condition**
        if (matchConfig.enableNaabaad && inn.wickets >= squadSize - 1) {
            return; // Do not rotate strike for the last batsman
        }
        inn.onStrikeBatsmanId = (inn.onStrikeBatsmanId === inn.batsman1.id) ? inn.batsman2.id : inn.batsman1.id;
    }

    function getOnStrikeBatsman() {
        const inn = state.innings[state.currentInningsNum];
        return inn.onStrikeBatsmanId === inn.batsman1.id ? inn.batsman1 : inn.batsman2;
    }

    function getBowler(bowlerId) {
        const inn = state.innings[state.currentInningsNum];
        let bowler = inn.bowlers.find(b => b.id === bowlerId);
        if (!bowler) {
            const bowlerData = state.teams[inn.bowlingTeamId].squad.find(p => p.id === bowlerId);
            bowler = { ...bowlerData, overs: 0, maidens: 0, runs: 0, wickets: 0 };
            inn.bowlers.push(bowler);
        }
        return bowler;
    }

    function endOfOverCheck() {
        const inn = state.innings[state.currentInningsNum];
        const originalInningsNum = state.currentInningsNum;

        if (inn.balls === 6) {
            const bowler = getBowler(inn.currentBowler.id);
            const oversInt = Math.floor(bowler.overs);
            bowler.overs = oversInt + 1;

            if (inn.currentOverRuns === 0) bowler.maidens++;

            addLogEvent(`<strong>End of Over ${inn.overs + 1}:</strong> ${inn.currentOverRuns} runs. ${state.teams[inn.battingTeamId].name} ${inn.score}/${inn.wickets}.`);

            inn.overs++;
            inn.balls = 0;
            inn.thisOver = [];
            inn.currentOverRuns = 0;
            rotateStrike();

            handleInningsEnd();

            if (state.matchOver || state.currentInningsNum !== originalInningsNum) {
                return;
            }

            openNewBowlerModal();
        } else {
            handleInningsEnd();
        }
    }

    function processBall(runs) {
        historyManager.save(state);
        if (state.isFreeHit) state.isFreeHit = false;

        const inn = state.innings[state.currentInningsNum];
        inn.score += runs;
        inn.currentOverRuns += runs;
        inn.partnership.runs += runs;

        const onStrike = getOnStrikeBatsman();
        onStrike.runs += runs;
        onStrike.balls++;
        if (runs === 4) onStrike.fours = (onStrike.fours || 0) + 1;
        if (runs === 6) onStrike.sixes = (onStrike.sixes || 0) + 1;

        inn.partnership.balls++;

        const bowler = getBowler(inn.currentBowler.id);
        bowler.runs += runs;

        if (runs % 2 !== 0) rotateStrike();

        inn.balls++;
        inn.thisOver.push(runs.toString());
        addLogEvent(`${inn.overs}.${inn.balls}: ${inn.currentBowler.name} to ${onStrike.name}, ${runs} run${runs !== 1 ? 's' : ''}`);

        const oversInt = Math.floor(bowler.overs);
        bowler.overs = parseFloat((oversInt + inn.balls / 10).toFixed(1));

        saveState();
        endOfOverCheck();
        render();
    }

    function processExtra() {
        historyManager.save(state);
        const { type, runs } = state.modal.extras;
        if (!type) return alert('Please select an extra type.');

        const inn = state.innings[state.currentInningsNum];
        const penaltyRun = (type === 'wd' || type === 'nb') ? 1 : 0;
        const totalRuns = runs + penaltyRun;
        inn.score += totalRuns;
        inn.extras.total += totalRuns;
        inn.extras[type] += totalRuns;

        const onStrike = getOnStrikeBatsman();
        addLogEvent(`${inn.overs}.${inn.balls + (penaltyRun ? 0 : 1)}: ${inn.currentBowler.name} to ${onStrike.name}, <span class="text-yellow-400">${type.toUpperCase()}</span>, ${totalRuns} run${totalRuns !== 1 ? 's' : ''}`);

        const bowler = getBowler(inn.currentBowler.id);

        if (penaltyRun > 0) {
            bowler.runs += totalRuns;
            inn.currentOverRuns += totalRuns;
            inn.thisOver.push(`${runs > 0 ? runs : ''}${type}`);
            if (type === 'nb') {
                onStrike.runs += runs;
                inn.partnership.runs += runs;
                state.isFreeHit = true;
                onStrike.balls++;
                inn.partnership.balls++;
            }
        } else {
            inn.balls++;
            inn.partnership.balls++;
            inn.currentOverRuns += runs;
            inn.thisOver.push(`${runs}${type}`);
            const oversInt = Math.floor(bowler.overs);
            bowler.overs = parseFloat((oversInt + inn.balls / 10).toFixed(1));
        }

        if (runs % 2 !== 0) rotateStrike();
        saveState();
        endOfOverCheck();
        closeAllModals();
        render();
    }

    // ====================================================================================
    // --- REFACTORED processWicket FUNCTION ---
    // ====================================================================================
    function processWicket() {
        historyManager.save(state);

        // --- 1. SETUP & CALCULATE CONDITIONS ---
        const { type: dismissalType, nextBatsmanId } = state.modal.wicket;
        const inn = state.innings[state.currentInningsNum];
        const battingTeam = state.teams[inn.battingTeamId];
        const squadSize = battingTeam.squad.length;

        const isNaabaadEnabled = matchConfig.enableNaabaad;
        // The wicket that triggers Naabaad mode (e.g., the 10th wicket in an 11-player game)
        const isNaabaadStarting = isNaabaadEnabled && inn.wickets === squadSize - 2;
        // The final wicket of the innings
        const isFinalWicket = isNaabaadEnabled ? inn.wickets === squadSize - 1 : inn.wickets === squadSize - 2;

        // --- 2. VALIDATION ---
        if (!dismissalType) return alert("Please select a dismissal type.");

        // We need a 'next batsman' unless it's the final wicket of the innings.
        if (!nextBatsmanId && !isFinalWicket) {
            const availableBatsmen = battingTeam.squad.filter(p => !p.out && p.id !== inn.batsman1.id && p.id !== inn.batsman2.id);
            if (availableBatsmen.length > 0) {
                return alert("Please select the next batsman.");
            }
        }
        
        // Handle Free Hit
        if (state.isFreeHit && !['Run Out'].includes(dismissalType)) {
            addLogEvent(`Batsman is NOT OUT due to Free Hit.`);
            closeAllModals();
            return;
        }

        // --- 3. CORE STATE UPDATES ---
        state.isFreeHit = false;
        inn.balls++;
        inn.wickets++;

        const bowler = getBowler(inn.currentBowler.id);
        if (dismissalType !== 'Run Out') bowler.wickets++;
        const oversInt = Math.floor(bowler.overs);
        bowler.overs = parseFloat((oversInt + inn.balls / 10).toFixed(1));

        const dismissedBatsman = getOnStrikeBatsman();
        dismissedBatsman.balls++;
        dismissedBatsman.out = true;
        dismissedBatsman.dismissal = { type: dismissalType, bowler: (dismissalType !== 'Run Out') ? bowler.name : null };
        battingTeam.squad.find(p => p.id === dismissedBatsman.id).out = true;

        addLogEvent(`<strong class="text-red-500">WICKET!</strong> ${dismissedBatsman.name} is out (${dismissalType})`);
        inn.thisOver.push('W');
        inn.partnership = { runs: 0, balls: 0, batsman1Id: null, batsman2Id: null };

        // --- 4. REFACTORED NEXT BATSMAN LOGIC ---
        if (isNaabaadStarting) {
            // It's the wicket that triggers Naabaad mode. The non-striker becomes the lone batsman.
            const lastMan = inn.onStrikeBatsmanId === inn.batsman1.id ? inn.batsman2 : inn.batsman1;
            inn.batsman1 = lastMan;
            inn.batsman2 = {};
            inn.onStrikeBatsmanId = lastMan.id;
            addLogEvent(`<strong class="text-yellow-400">NAABAAD!</strong> ${lastMan.name} is the last batsman remaining.`);

        } else if (!isFinalWicket) {
            // It's a standard wicket. Bring in the selected next batsman.
            const newBatsmanData = battingTeam.squad.find(p => p.id === nextBatsmanId);
            const newBatsman = newBatsmanData ? { ...newBatsmanData, runs: 0, balls: 0, fours: 0, sixes: 0, out: false } : {};

            if (inn.onStrikeBatsmanId === inn.batsman1.id) inn.batsman1 = newBatsman;
            else inn.batsman2 = newBatsman;

            inn.batsmen.push(newBatsman);

            if (newBatsman.id) {
                inn.onStrikeBatsmanId = newBatsman.id;
                inn.partnership.batsman1Id = newBatsman.id;
                inn.partnership.batsman2Id = (inn.onStrikeBatsmanId === inn.batsman1.id) ? inn.batsman2.id : inn.batsman1.id;
            }
            if (newBatsman.name) addLogEvent(`<strong>${newBatsman.name}</strong> comes to the crease.`);
        }
        // If it IS the final wicket, do nothing here. The handleInningsEnd() function will take care of it.

        // --- 5. FINALIZE ---
        saveState();
        closeAllModals();
        render();
        endOfOverCheck();
    }
    // ====================================================================================
    // --- END OF REFACTORED FUNCTION ---
    // ====================================================================================

    function openModal(modal) {
        ui.modalBackdrop.classList.remove('hidden');
        modal.classList.remove('hidden');
    }

    function closeAllModals() {
        ui.modalBackdrop.classList.add('hidden');
        [ui.setupModal, ui.newBowlerModal, ui.extrasModal, ui.wicketModal].forEach(m => m.classList.add('hidden'));
        ui.modalBackdrop.classList.remove('is-locked');
    }

    function openInningsSetupModal() {
        const inn = state.innings[state.currentInningsNum];
        ui.setupInningsNum.textContent = state.currentInningsNum;
        const battingTeam = state.teams[inn.battingTeamId];
        const bowlingTeam = state.teams[inn.bowlingTeamId];
        const availableBatsmen = battingTeam.squad.filter(p => !p.out);

        ui.setupStrikerSelect.innerHTML = availableBatsmen.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
        ui.setupNonstrikerSelect.innerHTML = availableBatsmen.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
        if (availableBatsmen.length > 1) ui.setupNonstrikerSelect.value = availableBatsmen[1].id;

        ui.setupBowlerSelect.innerHTML = bowlingTeam.squad.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
        ui.modalBackdrop.classList.add('is-locked');
        openModal(ui.setupModal);
    }

    function openNewBowlerModal() {
        state.modal.newBowler = { nextBowlerId: null };
        ui.newBowlerButtons.innerHTML = '';
        const inn = state.innings[state.currentInningsNum];
        ui.newBowlerOverNum.textContent = inn.overs;
        const availableBowlers = state.teams[inn.bowlingTeamId].squad.filter(b => b.id !== inn.currentBowler.id);

        if (availableBowlers.length > 0) {
            availableBowlers.forEach(bowler => {
                const btn = document.createElement('button');
                btn.textContent = bowler.name;
                btn.className = 'score-btn bg-gray-200 rounded-lg py-2 font-bold w-full text-sm';
                btn.addEventListener('click', () => {
                    ui.newBowlerButtons.querySelectorAll('button').forEach(b => b.classList.remove('selected'));
                    btn.classList.add('selected');
                    state.modal.newBowler.nextBowlerId = parseInt(bowler.id, 10);
                });
                ui.newBowlerButtons.appendChild(btn);
            });
        } else {
            ui.newBowlerButtons.innerHTML = `<p class="text-center text-sm text-gray-500 col-span-full">No other bowlers available.</p>`;
        }
        ui.modalBackdrop.classList.add('is-locked');
        openModal(ui.newBowlerModal);
    }

    function setupGlobalListeners() {
        ui.undoBtn = rebind(ui.undoBtn);
        ui.undoBtn.addEventListener('click', undo);
        ui.modalBackdrop.addEventListener('click', (e) => {
            if (e.target === ui.modalBackdrop && !ui.modalBackdrop.classList.contains('is-locked')) {
                closeAllModals();
            }
        });
    }

    function setupScorepadListeners() {
        document.querySelectorAll('.score-btn[data-runs]').forEach(btn => btn.addEventListener('click', () => processBall(parseInt(btn.dataset.runs, 10))));
        ui.extrasBtn = rebind(ui.extrasBtn);
        ui.extrasBtn.addEventListener('click', () => {
            state.modal.extras = { type: null, runs: 0 };
            ui.extrasTypeBtns.querySelectorAll('button').forEach(b => b.classList.remove('selected'));
            ui.extrasRunBtns.querySelectorAll('button').forEach(b => b.classList.remove('selected'));
            openModal(ui.extrasModal);
        });
        ui.wicketBtn = rebind(ui.wicketBtn);
        ui.wicketBtn.addEventListener('click', () => {
            state.modal.wicket = { type: null, nextBatsmanId: null };
            ui.wicketTypeButtons.querySelectorAll('button').forEach(b => b.classList.remove('selected'));
            const inn = state.innings[state.currentInningsNum];
            const available = state.teams[inn.battingTeamId].squad.filter(p => !p.out && p.id !== inn.batsman1.id && p.id !== inn.batsman2.id);
            ui.wicketNextBatsmanButtons.innerHTML = '';
            if (available.length > 0) {
                available.forEach(player => {
                    const btn = document.createElement('button');
                    btn.textContent = player.name;
                    btn.className = 'score-btn bg-gray-200 rounded-lg py-2 font-bold w-full text-sm';
                    btn.addEventListener('click', () => {
                        ui.wicketNextBatsmanButtons.querySelectorAll('button').forEach(b => b.classList.remove('selected'));
                        btn.classList.add('selected');
                        state.modal.wicket.nextBatsmanId = parseInt(player.id, 10);
                    });
                    ui.wicketNextBatsmanButtons.appendChild(btn);
                });
            } else {
                ui.wicketNextBatsmanButtons.innerHTML = `<p class="text-center text-sm text-gray-500 col-span-full">All players are out.</p>`;
            }
            openModal(ui.wicketModal);
        });
    }

    function setupModalListeners() {
        ui.extrasTypeBtns.querySelectorAll('button').forEach(btn => btn.addEventListener('click', () => {
            ui.extrasTypeBtns.querySelectorAll('button').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            state.modal.extras.type = btn.dataset.extraType;
        }));
        ui.extrasRunBtns.querySelectorAll('button').forEach(btn => btn.addEventListener('click', () => {
            ui.extrasRunBtns.querySelectorAll('button').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            state.modal.extras.runs = parseInt(btn.dataset.extraRun, 10);
        }));
        ui.confirmExtraBtn = rebind(ui.confirmExtraBtn);
        ui.confirmExtraBtn.addEventListener('click', processExtra);

        ui.wicketTypeButtons.querySelectorAll('button').forEach(btn => btn.addEventListener('click', () => {
            ui.wicketTypeButtons.querySelectorAll('button').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            state.modal.wicket.type = btn.dataset.wicketType;
        }));
        ui.confirmWicketBtn = rebind(ui.confirmWicketBtn);
        ui.confirmWicketBtn.addEventListener('click', processWicket);

        ui.startInningsBtn = rebind(ui.startInningsBtn);
        ui.startInningsBtn.addEventListener('click', () => {
            const inn = state.innings[state.currentInningsNum];
            const strikerId = parseInt(ui.setupStrikerSelect.value, 10);
            const nonStrikerId = parseInt(ui.setupNonstrikerSelect.value, 10);
            const bowlerId = parseInt(ui.setupBowlerSelect.value, 10);
            if (strikerId === nonStrikerId) return alert("Striker and Non-Striker must be different.");

            const battingTeam = state.teams[inn.battingTeamId];

            const b1Data = { ...battingTeam.squad.find(p => p.id === strikerId), runs: 0, balls: 0, fours: 0, sixes: 0, out: false };
            const b2Data = { ...battingTeam.squad.find(p => p.id === nonStrikerId), runs: 0, balls: 0, fours: 0, sixes: 0, out: false };

            inn.batsman1 = b1Data;
            inn.batsman2 = b2Data;
            inn.batsmen.push(b1Data, b2Data);

            inn.onStrikeBatsmanId = strikerId;
            inn.currentBowler = { ...state.teams[inn.bowlingTeamId].squad.find(p => p.id === bowlerId) };
            getBowler(bowlerId);

            inn.partnership = { runs: 0, balls: 0, batsman1Id: strikerId, batsman2Id: nonStrikerId };

            addLogEvent(`<strong>${inn.batsman1.name}</strong> and <strong>${inn.batsman2.name}</strong> are opening the innings.`);
            addLogEvent(`<strong>${inn.currentBowler.name}</strong> will open the attack.`);

            closeAllModals();
            ui.scoreboardContainer.classList.remove('hidden');
            saveState();
            render();
        });

        ui.confirmBowlerBtn = rebind(ui.confirmBowlerBtn);
        ui.confirmBowlerBtn.addEventListener('click', () => {
            const { nextBowlerId } = state.modal.newBowler;
            if (!nextBowlerId) return alert("Please select the next bowler.");
            const inn = state.innings[state.currentInningsNum];
            inn.currentBowler = { ...state.teams[inn.bowlingTeamId].squad.find(p => p.id === nextBowlerId) };
            getBowler(nextBowlerId);
            addLogEvent(`<strong>${inn.currentBowler.name}</strong> comes into the attack.`);
            closeAllModals();
            saveState();
            render();
        });
    }

    function init() {
        const savedStateString = localStorage.getItem('cricscoreLive');

        if (savedStateString) {
            state = JSON.parse(savedStateString);
            // For backward compatibility with old saved matches
if (!state.matchConfig) {
    state.matchConfig = matchConfig;
}
            state.modal = { extras: { type: null, runs: 0 }, wicket: { type: null, nextBatsmanId: null }, newBowler: { nextBowlerId: null } };
        } else {
            if (ui.timelineLog) ui.timelineLog.innerHTML = '';
            state = createNewState();
        }

        setupGlobalListeners();
        setupScorepadListeners();
        setupModalListeners();

        ui.scoreboardContainer.classList.remove('hidden');

        if (savedStateString && !state.matchOver) {
            render();
        } else if (!state.matchOver) {
            openInningsSetupModal();
        }
    }

    init();
}