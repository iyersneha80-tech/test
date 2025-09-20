/**
 * Main JavaScript Engine for the Live Scoring Application.
 * This script acts as a controller, managing the current state of the match
 * and loading the appropriate UI and logic for each step.
 *
 * @package CricScore
 * @version 2.1.0 
 */

// --- Debounce Helper Function ---
const debounce = (func, delay) => {
    let timeout;
    return function(...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), delay);
    };
};

// --- API Call Logic ---
const performSave = async () => {
    const saveStatusEl = document.getElementById('save-status');
    if (saveStatusEl) saveStatusEl.textContent = 'Saving...';
    
    console.log("Saving state to backend...", cricscore.matchState);
    try {
        const response = await fetch(`${cricscore.api.base}/matches/${cricscore.api.matchId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cricscore.api.nonce },
            body: JSON.stringify(cricscore.matchState)
        });
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'API Error');
        }
        if (saveStatusEl) saveStatusEl.textContent = 'All changes saved';
    } catch (error) {
        console.error('Failed to save match state:', error);
        if (saveStatusEl) saveStatusEl.textContent = 'Save failed';
    }
};

const debouncedSave = debounce(performSave, 1500);

// --- Global Namespace Object ---
window.cricscore = {
    matchState: {},
    api: {
        base: '/wp-json/cricscore/v1',
        nonce: cricscore_match_data.nonce,
        matchId: cricscore_match_data.match_id
    },
    steps: {},
    showStep: function(stepKey) {},
    saveState: function() {
        debouncedSave();
    },
    saveStateImmediate: async function() {
        await performSave();
    },
    // --- CHANGE 1: Expose initializeMatch and showError globally ---
    initializeMatch: async function() {}, 
    showError: function(message) {}
};

document.addEventListener('DOMContentLoaded', function () {
    const stepContainers = {
        loading: document.getElementById('match-loading'),
        preMatch: document.getElementById('step-pre-match-summary'),
        inningsPrep: document.getElementById('step-innings-preparation'),
        scoring: document.getElementById('step-live-scoring'),
        midSummary: document.getElementById('step-mid-innings-summary'),
        postSummary: document.getElementById('step-post-match-summary'),
        result: document.getElementById('step-match-result'),
    };

    window.cricscore.showStep = function(stepKey) {
        Object.values(stepContainers).forEach(stepEl => {
            if (stepEl) stepEl.style.display = 'none';
        });
        if (stepContainers[stepKey]) {
            stepContainers[stepKey].style.display = 'block';
            if (typeof window.cricscore.steps[stepKey]?.init === 'function') {
                window.cricscore.steps[stepKey].init();
            }
        } else {
            console.error(`Error: Step "${stepKey}" not found.`);
            cricscore.showError(`Error: Could not load step: ${stepKey}`);
        }
    }

    // --- CHANGE 2: Assign the implementation to the globally exposed functions ---
    window.cricscore.showError = function(message) {
        stepContainers.loading.innerHTML = `<p style="color: red;">${message}</p>`;
        stepContainers.loading.style.display = 'block';
    }

    window.cricscore.initializeMatch = async function() {
        if (!cricscore.api.matchId) {
            cricscore.showError('Error: No Match ID found. Cannot load match.');
            return;
        }
        try {
            const response = await fetch(`${cricscore.api.base}/matches/${cricscore.api.matchId}`, {
                headers: { 'X-WP-Nonce': cricscore.api.nonce }
            });
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to fetch match data.');
            }

            const data = await response.json();
            
            if (data.match.status === 'live' && data.match.live_state) {
                window.cricscore.matchState = data.match.live_state;
                if (cricscore.matchState.currentInnings && cricscore.matchState.currentInnings.number === 2) {
                    cricscore.showStep('scoring');
                } else if (cricscore.matchState.firstInnings) {
                    cricscore.showStep('midSummary');
                } else if (cricscore.matchState.currentInnings) {
                    cricscore.showStep('scoring');
                } else {
                    cricscore.showStep('inningsPrep');
                }
            } else if (data.match.status === 'completed' || data.match.status === 'abandoned') {
                window.cricscore.matchState = { match: data.match, innings: data.innings };
                const innings = window.cricscore.matchState.innings;
                if (innings && innings.length > 0) {
                    window.cricscore.matchState.firstInnings = innings.find(i => i.innings_number == 1);
                    window.cricscore.matchState.currentInnings = innings.find(i => i.innings_number == 2) || innings.find(i => i.innings_number == 1);
                    window.cricscore.matchState.result = { text: data.match.result_summary };
                }
                cricscore.showStep('result');
            } else {
                window.cricscore.matchState = { match: data.match };
                // --- FIX: Promote the rules object to the top level of the state ---
                // This ensures the live scorer can always find the rules configuration.
                if (data.match.live_state && data.match.live_state.rules) {
                    window.cricscore.matchState.rules = data.match.live_state.rules;
                }
                cricscore.showStep('preMatch');
            }
        } catch (error) {
            cricscore.showError(`Error: ${error.message}`);
        }
    }

    // Initial load
    cricscore.initializeMatch();
});