/**
 * Handles all functionality for the "Create New Match" page (Redesigned "Visualizer" Concept).
 *
 * @package CricScore
 * @version 2.2.3
 */
document.addEventListener('DOMContentLoaded', function () {
    const apiBase = '/wp-json/cricscore/v1';
    const nonce = cricscore_dashboard.nonce;

    // --- Element Selectors ---
    const createMatchForm = document.getElementById('create-match-form');
    const formMessage = document.getElementById('form-message');
    const steps = document.querySelectorAll('.step');
    const stepContents = document.querySelectorAll('.step-content');
    const nextStepBtn = document.getElementById('next-step-btn');
    const prevStepBtn = document.getElementById('prev-step-btn');
    const createMatchBtn = document.getElementById('create-match-btn');
    const step2Tooltip = document.getElementById('step-2-tooltip');
    const step1Tooltip = document.getElementById('step-1-tooltip');

    // --- Step 1 Elements ---
    const formatCards = document.querySelectorAll('.format-card');
    const oversGroup = document.getElementById('overs-group');
    const oversInput = document.getElementById('match-overs');
    const venueSelect = document.getElementById('match-venue');

    // --- Step 2 (Live Feed) Elements ---
    const team1Select = document.getElementById('team1-select');
    const team2Select = document.getElementById('team2-select');
    const teamSelectionCard = document.getElementById('team-selection-card');
    const squadAName = document.getElementById('squad-a-name');
    const squadBName = document.getElementById('squad-b-name');
    const poolList = document.getElementById('player-pool-list');
    const teamAList = document.getElementById('team-a-list');
    const teamBList = document.getElementById('team-b-list');
    const teamACount = document.getElementById('team-a-count');
    const teamBCount = document.getElementById('team-b-count');
    const emptyPoolMsg = document.getElementById('empty-pool-message');
    const emptySquadAMsg = document.getElementById('empty-squad-a');
    const emptySquadBMsg = document.getElementById('empty-squad-b');
    const searchBar = document.getElementById('search-bar');
    
    // --- Step 3 Elements ---
    const tossTeamCards = document.querySelectorAll('.toss-team-card');
    const decisionButtonsContainer = document.querySelector('.toss-decision-buttons');
    const decisionButtons = document.querySelectorAll('.decision-btn');
    const openTossModalBtn = document.getElementById('openTossModalBtn'); // NEW
        const lastManRuleToggle = document.getElementById('last-man-rule-toggle');

    // --- State Management ---
    let currentStep = 1;
    const totalSteps = 4; // Hardcoded to 4 to include our new step
    let userTeams = [];
    let userPlayers = [];
    let playerPool = [];
    let teamA = [];
    let teamB = [];
    let step1Attempted = false;
    let step2Attempted = false;

    const matchData = {
        match_format: null, 
        overs_per_innings: null, 
        venue_id: null,
        team1_id: null, 
        team2_id: null,
        team1_players: [], 
        team2_players: [],
        toss_data: { winner: null, decision: null },
        rules: {
            last_man_stands: false // Default to off
        }
    };
    // --- Main Functions ---
    async function fetchSetupData() {
        try {
            const [teamsRes, playersRes, venuesRes] = await Promise.all([
                fetch(`${apiBase}/teams`, { headers: { 'X-WP-Nonce': nonce } }),
                fetch(`${apiBase}/players`, { headers: { 'X-WP-Nonce': nonce } }),
                fetch(`${apiBase}/venues`, { headers: { 'X-WP-Nonce': nonce } })
            ]);
            if (!teamsRes.ok || !playersRes.ok || !venuesRes.ok) throw new Error('Failed to load initial match data.');

            userTeams = await teamsRes.json();
            userPlayers = await playersRes.json();
            const userVenues = await venuesRes.json();

            populateSelect(team1Select, userTeams, 'Select Team 1');
            populateSelect(team2Select, userTeams, 'Select Team 2');
            populateSelect(venueSelect, userVenues, 'Select a Venue');
        } catch (error) {
            formMessage.textContent = `Error: Could not load data for setup. ${error.message}`;
            formMessage.className = 'form-message notice notice-error';
            formMessage.style.display = 'block';
        }
    }
    
    // --- Stepper Navigation ---
    function updateView() {
        steps.forEach((step, index) => {
            const stepNum = index + 1;
            step.classList.toggle('active', stepNum === currentStep);
            step.classList.toggle('completed', stepNum < currentStep);
        });
        stepContents.forEach((content, index) => content.classList.toggle('active', (index + 1) === currentStep));
        
        prevStepBtn.style.visibility = currentStep > 1 ? 'visible' : 'hidden';
        nextStepBtn.style.display = currentStep < totalSteps ? 'inline-block' : 'none';
        
        // --- Validation Logic for the Next Button ---
if (currentStep === 1) {
    const formatSelected = matchData.match_format !== null;
    const venueSelected = venueSelect.value !== '';
    const isStep1Valid = formatSelected && venueSelected;

    // Use a class to visually disable the button, but keep it clickable
    nextStepBtn.classList.toggle('disabled', !isStep1Valid);
    step1Tooltip.classList.toggle('visible', !isStep1Valid && step1Attempted);
    step2Tooltip.classList.remove('visible'); // Always hide the other tooltip
} else if (currentStep === 2) {
    const team1Selected = team1Select.value !== '';
    const team2Selected = team2Select.value !== '';
    const bothTeamsSelected = team1Selected && team2Selected;

    // Use a class to visually disable the button, but keep it clickable
    nextStepBtn.classList.toggle('disabled', !bothTeamsSelected);
    step2Tooltip.classList.toggle('visible', !bothTeamsSelected && step2Attempted);
    step1Tooltip.classList.remove('visible'); // Always hide the other tooltip
} else {
    // For any other step (like step 3) or future steps
    nextStepBtn.classList.remove('disabled');
    step1Tooltip.classList.remove('visible');
    step2Tooltip.classList.remove('visible');
}
        
        createMatchBtn.style.display = currentStep === totalSteps ? 'inline-block' : 'none';
        if (currentStep === totalSteps) validateTossCompletion();

        // Show the toss button only on Step 3
        if (openTossModalBtn) {
            openTossModalBtn.style.display = currentStep === 3 ? 'inline-flex' : 'none';
        }
    }


    nextStepBtn.addEventListener('click', () => {
    // First, check if the button is visually disabled. If so, set the attempt flag.
    if (nextStepBtn.classList.contains('disabled')) {
        if (currentStep === 1) step1Attempted = true;
        if (currentStep === 2) step2Attempted = true;
        updateView(); // Re-run to show the tooltip
        return;
    }

        if (currentStep < totalSteps) {
            currentStep++;
            // Reset flags since we are moving to the next step
            step1Attempted = false;
            step2Attempted = false;
            if (currentStep === 2) preparePlayerSelectionStep();
            if (currentStep === 3) prepareTossStep();
            updateView();
        }
    });

    prevStepBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            // Reset flags when moving backward as well
            step1Attempted = false;
            step2Attempted = false;
            updateView();
        }
    });

    // --- Step 1 Logic ---
    formatCards.forEach(card => {
        card.addEventListener('click', () => {
            formatCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            matchData.match_format = card.dataset.format;
            if (matchData.match_format === 'T20') matchData.overs_per_innings = 20;
            if (matchData.match_format === 'ODI') matchData.overs_per_innings = 50;
            if (matchData.match_format === 'Custom') matchData.overs_per_innings = oversInput.value;
            oversGroup.style.display = matchData.match_format === 'Custom' ? 'block' : 'none';
            updateView(); // Re-validate the button state
        });
    });
    oversInput.addEventListener('change', e => matchData.overs_per_innings = e.target.value);
    venueSelect.addEventListener('change', e => {
        matchData.venue_id = e.target.value;
        updateView(); // Re-validate the button state
    });

    // --- Step 2 Logic (Live Feed) ---
    function populateSelect(select, items, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        items.forEach(item => select.innerHTML += `<option value="${item.id}">${item.name}</option>`);
    }

function preparePlayerSelectionStep() {
    // Reconcile the player pool instead of resetting everything.
    // This preserves selections in teamA and teamB when navigating back and forth.
    const selectedPlayerIds = new Set([...teamA.map(p => p.id), ...teamB.map(p => p.id)]);
    playerPool = userPlayers.filter(p => !selectedPlayerIds.has(p.id));
    renderAllSquads();
}
    function renderPlayerItem(player) {
    const initials = player.name.match(/\b(\w)/g)?.join('') || '?';
    // *** NEW LOGIC STARTS HERE ***
    // Check if the player has a profile_image_url and it's not empty.
    const avatarContent = player.profile_image_url
        ? `<img src="${player.profile_image_url}" alt="${player.name}">` // If yes, use the image
        : initials; // If no, fall back to initials
    // *** NEW LOGIC ENDS HERE ***

    return `
        <div class="player-avatar">${avatarContent}</div>
        <div class="player-info">
            <div class="name">${player.name}</div>
            <div class="role">${player.role || 'Player'}</div>
        </div>
    `;
}

    function renderAllSquads() {
        poolList.innerHTML = playerPool.map(player => `
            <li data-id="${player.id}">
                ${renderPlayerItem(player)}
                <div class="assignment-buttons">
                    <button type="button" class="assign-btn team-a" data-team="a">A</button>
                    <button type="button" class="assign-btn team-b" data-team="b">B</button>
                </div>
            </li>
        `).join('');

        teamAList.innerHTML = teamA.map(player => `
            <li data-id="${player.id}">
                ${renderPlayerItem(player)}
                 <button type="button" class="remove-btn" data-team="a">&times;</button>
            </li>
        `).join('');

        teamBList.innerHTML = teamB.map(player => `
            <li data-id="${player.id}">
                ${renderPlayerItem(player)}
                 <button type="button" class="remove-btn" data-team="b">&times;</button>
            </li>
        `).join('');
        
        updateCountsAndMessages();
    }

    function updateCountsAndMessages() {
        teamACount.textContent = `${teamA.length}/11`;
        teamBCount.textContent = `${teamB.length}/11`;
        emptyPoolMsg.style.display = playerPool.length === 0 ? 'block' : 'none';
        emptySquadAMsg.style.display = teamA.length === 0 ? 'block' : 'none';
        emptySquadBMsg.style.display = teamB.length === 0 ? 'block' : 'none';
    }

    function assignPlayer(playerId, team) {
        const playerIndex = playerPool.findIndex(p => p.id == playerId);
        if (playerIndex === -1) return;
        const [player] = playerPool.splice(playerIndex, 1);
        (team === 'a' ? teamA : teamB).push(player);
        
        const poolItem = poolList.querySelector(`li[data-id="${playerId}"]`);
        if (poolItem) {
            poolItem.classList.add('removing');
            setTimeout(renderAllSquads, 300);
        } else {
            renderAllSquads();
        }
    }

    function unassignPlayer(playerId, team) {
        const teamArray = (team === 'a' ? teamA : teamB);
        const playerIndex = teamArray.findIndex(p => p.id == playerId);
        if (playerIndex === -1) return;
        const [player] = teamArray.splice(playerIndex, 1);
        playerPool.push(player);
        playerPool.sort((a, b) => a.id - b.id);
        renderAllSquads();
    }
    
    function handleTeamChange(team, event) {
        const teamId = event.target.value;
        const teamName = event.target.options[event.target.selectedIndex].text;
        
        if (team === 'a') {
            matchData.team1_id = teamId;
            squadAName.textContent = teamName;
        } else {
            matchData.team2_id = teamId;
            squadBName.textContent = teamName;
        }
        
        matchData.toss_data = { winner: null, decision: null };
        updateView(); // Re-validate the button state
    }
    
    team1Select.addEventListener('change', e => handleTeamChange('a', e));
    team2Select.addEventListener('change', e => handleTeamChange('b', e));

    poolList.addEventListener('click', e => {
        if (e.target.matches('.assign-btn')) assignPlayer(e.target.closest('li').dataset.id, e.target.dataset.team);
    });
    teamAList.addEventListener('click', e => {
        if (e.target.matches('.remove-btn')) unassignPlayer(e.target.closest('li').dataset.id, 'a');
    });
    teamBList.addEventListener('click', e => {
        if (e.target.matches('.remove-btn')) unassignPlayer(e.target.closest('li').dataset.id, 'b');
    });
    
    searchBar.addEventListener('input', e => {
        const searchTerm = e.target.value.toLowerCase();
        poolList.querySelectorAll('li').forEach(li => {
            const name = li.querySelector('.name').textContent.toLowerCase();
            li.classList.toggle('hidden', !name.includes(searchTerm));
        });
    });

    function resetTossSelection() {
        tossTeamCards.forEach(c => {
            c.classList.remove('winner', 'loser');
            c.style.pointerEvents = 'auto';
        });
        decisionButtonsContainer.classList.remove('visible');
        decisionButtons.forEach(b => b.classList.remove('selected'));
        
        matchData.toss_data = { winner: null, decision: null };
        validateTossCompletion();
    }
    
    // --- Step 3 Logic ---
    function prepareTossStep() {
        matchData.toss_data = { winner: null, decision: null };
        tossTeamCards.forEach(c => {
            c.classList.remove('winner', 'loser');
            c.style.pointerEvents = 'auto';
        });
        decisionButtonsContainer.classList.remove('visible');
        decisionButtons.forEach(b => b.classList.remove('selected'));
        
        const team1 = userTeams.find(t => t.id == matchData.team1_id);
        const team2 = userTeams.find(t => t.id == matchData.team2_id);
        const card1 = document.querySelector('.toss-team-card:first-child');
        const card2 = document.querySelector('.toss-team-card:last-child');

        [team1, team2].forEach((team, index) => {
            const card = index === 0 ? card1 : card2;
            if (team) {
                card.dataset.teamId = team.id;
                card.querySelector('h4').textContent = team.name;
                const placeholder = card.querySelector('.team-logo-placeholder');
                placeholder.innerHTML = team.logo_url ? `<img src="${team.logo_url}" alt="${team.name}">` : (team.short_name || team.name.substring(0, 2).toUpperCase());
            }
        });
    }

    function validateTossCompletion() {
        createMatchBtn.disabled = !(matchData.toss_data.winner && matchData.toss_data.decision);
    }

    tossTeamCards.forEach(card => {
        card.addEventListener('click', () => {
            // If the clicked card is already the winner, reset everything
            if (card.classList.contains('winner')) {
                resetTossSelection();
                return; // Stop further execution
            }

            // Otherwise, proceed with selecting a winner
            const selectedTeamId = card.dataset.teamId;
            matchData.toss_data.winner = selectedTeamId;
            matchData.toss_data.decision = null; // Clear previous decision

            // Update UI
            decisionButtons.forEach(b => b.classList.remove('selected'));
            tossTeamCards.forEach(c => {
                const isWinner = c.dataset.teamId === selectedTeamId;
                c.classList.toggle('winner', isWinner);
                c.classList.toggle('loser', !isWinner);
                c.style.pointerEvents = isWinner ? 'auto' : 'none';
            });
            
            decisionButtonsContainer.classList.add('visible');
            validateTossCompletion(); // Re-validate, create button will be disabled
        });
    });

    decisionButtons.forEach(button => {
        button.addEventListener('click', () => {
            decisionButtons.forEach(b => b.classList.remove('selected'));
            button.classList.add('selected');
            matchData.toss_data.decision = button.dataset.decision;
            validateTossCompletion();
        });
    });

    // --- Final Form Submission ---
    createMatchForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        createMatchBtn.disabled = true;
        formMessage.textContent = 'Creating match...';
        formMessage.className = 'form-message notice notice-info';
        formMessage.style.display = 'block';

        matchData.team1_players = teamA.map(p => p.id);
        matchData.team2_players = teamB.map(p => p.id);

        try {
            const response = await fetch(`${apiBase}/matches`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify(matchData)
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Failed to create match.');

            formMessage.textContent = 'Match created successfully! Redirecting...';
            formMessage.className = 'form-message notice notice-success';
            
            setTimeout(() => { window.location.href = '/dashboard/my-matches/'; }, 1500);
        } catch (error) {
            formMessage.textContent = `Error: ${error.message}`;
            formMessage.className = 'form-message notice notice-error';
            createMatchBtn.disabled = false;
        }
    });

    // --- Initial Load ---
    function initializePage() {
        fetchSetupData();
        updateView();
    }
        // --- Step 4 Logic ---
    lastManRuleToggle.addEventListener('change', function() {
        matchData.rules.last_man_stands = this.checked;
    });

    initializePage();

    // --- Tooltip Click-to-Scroll ---
    step2Tooltip.addEventListener('click', () => {
        if (teamSelectionCard) {
            teamSelectionCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    step1Tooltip.addEventListener('click', () => {
        const step1Content = document.getElementById('step-1-content');
        if (step1Content) {
            step1Content.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});