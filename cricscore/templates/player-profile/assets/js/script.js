document.addEventListener('DOMContentLoaded', function () {
    const { player_id, api_base, nonce } = cricscore_player_data;

    const profileContainer = document.getElementById('profile-container');
    const loadingMessage = document.getElementById('loading-message');
    const errorMessage = document.getElementById('error-message');

    // Helper function to create a single stat item
    function createStatItem(label, value) {
        const item = document.createElement('div');
        item.className = 'stat-item';
        item.innerHTML = `
            <p class="value">${value ?? 'N/A'}</p>
            <p class="label">${label}</p>
        `;
        return item;
    }
    
    // Helper function to calculate age
    function calculateAge(dobString) {
        if (!dobString) return 'N/A';
        const dob = new Date(dobString);
        const diff_ms = Date.now() - dob.getTime();
        const age_dt = new Date(diff_ms);
        return Math.abs(age_dt.getUTCFullYear() - 1970);
    }

    async function fetchAndRenderPlayer() {
        if (!player_id) {
            loadingMessage.style.display = 'none';
            errorMessage.style.display = 'block';
            console.error('Player ID not found.');
            return;
        }

        try {
            const response = await fetch(`${api_base}/players/${player_id}`, {
                headers: { 'X-WP-Nonce': nonce }
            });

            if (!response.ok) {
                throw new Error(`API responded with status: ${response.status}`);
            }

            const data = await response.json();
            
            // --- Populate Player Info ---
            document.title = `Player Profile - ${data.info.name}`;
            document.getElementById('player-name').textContent = data.info.name || 'Unknown Player';
            document.getElementById('player-role').textContent = data.info.role || 'N/A';
            document.getElementById('player-avatar-img').src = data.info.profile_image_url || 'https://i.pravatar.cc/150';
            document.getElementById('player-age').textContent = calculateAge(data.info.dob);
            document.getElementById('player-country').textContent = data.info.country || 'N/A';
            document.getElementById('player-batting-style').textContent = data.info.batting_style || 'N/A';
            document.getElementById('player-bowling-style').textContent = data.info.bowling_style || 'N/A';

            // --- Populate Batting Stats ---
            const battingGrid = document.getElementById('batting-stats-grid');
            battingGrid.innerHTML = ''; // Clear any placeholders
            battingGrid.appendChild(createStatItem('Matches', data.stats.batting.matches));
            battingGrid.appendChild(createStatItem('Innings', data.stats.batting.innings));
            battingGrid.appendChild(createStatItem('Runs', data.stats.batting.runs));
            battingGrid.appendChild(createStatItem('High Score', data.stats.batting.high_score));
            battingGrid.appendChild(createStatItem('Average', data.stats.batting.average));
            battingGrid.appendChild(createStatItem('Strike Rate', data.stats.batting.strike_rate));
            battingGrid.appendChild(createStatItem('100s', data.stats.batting.hundreds));
            battingGrid.appendChild(createStatItem('50s', data.stats.batting.fifties));
            
            // --- Populate Bowling Stats ---
            const bowlingGrid = document.getElementById('bowling-stats-grid');
            bowlingGrid.innerHTML = ''; // Clear any placeholders
            bowlingGrid.appendChild(createStatItem('Innings', data.stats.bowling.innings));
            bowlingGrid.appendChild(createStatItem('Wickets', data.stats.bowling.wickets));
            bowlingGrid.appendChild(createStatItem('Overs', data.stats.bowling.overs_bowled));
            bowlingGrid.appendChild(createStatItem('Best Figures', data.stats.bowling.best_figures));
            bowlingGrid.appendChild(createStatItem('Average', data.stats.bowling.average));
            bowlingGrid.appendChild(createStatItem('Economy', data.stats.bowling.economy));
            bowlingGrid.appendChild(createStatItem('Strike Rate', data.stats.bowling.strike_rate));
            bowlingGrid.appendChild(createStatItem('3-fers', data.stats.bowling.three_fers));
            bowlingGrid.appendChild(createStatItem('5-fers', data.stats.bowling.five_fers));

            // Show the profile and hide the loading message
            loadingMessage.style.display = 'none';
            profileContainer.style.display = 'grid';
            // --- Populate Match Log ---
        const matchLogContainer = document.getElementById('match-log-table-container');
        if (data.match_log && data.match_log.length > 0) {
            let tableHTML = `
                <table class="match-log-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Match</th>
                            <th>Venue</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            data.match_log.forEach(match => {
                const matchDate = new Date(match.date).toLocaleDateString('en-GB', {
                    day: '2-digit', month: 'short', year: 'numeric'
                });
                const matchUrl = `/match/${match.id}-${match.share_slug}/`;
                tableHTML += `
                    <tr>
                        <td>${matchDate}</td>
                        <td class="match-teams">${match.team1 || 'N/A'} vs ${match.team2 || 'N/A'}</td>
                        <td class="match-venue">${match.venue || 'N/A'}</td>
                        <td><a href="${matchUrl}" class="view-match-btn">View</a></td>
                    </tr>
                `;
            });
            tableHTML += `</tbody></table>`;
            matchLogContainer.innerHTML = tableHTML;
        } else {
            matchLogContainer.innerHTML = '<p>No match history found for this player.</p>';
        }

        } catch (error) {
            console.error('Failed to fetch player data:', error);
            loadingMessage.style.display = 'none';
            errorMessage.style.display = 'block';
        }
    }

    fetchAndRenderPlayer();
});