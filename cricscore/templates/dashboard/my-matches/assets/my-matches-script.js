/**
 * Handles all functionality for the "My Matches" page.
 *
 * @package CricScore
 * @version 2.1.0
 */
document.addEventListener('DOMContentLoaded', function () {
    const apiBase = '/wp-json/cricscore/v1';
    const nonce = cricscore_dashboard.nonce;

    const matchList = document.getElementById('my-matches-list');

    // --- Helper function to create team logo HTML ---
    function getTeamLogoHTML(logoUrl, teamName) {
        if (logoUrl) {
            return `<img src="${logoUrl}" alt="${teamName}" class="team-logo">`;
        }
        // Fallback to initials if no logo
        const initials = teamName.match(/\b(\w)/g)?.join('').substring(0, 2).toUpperCase() || '?';
        return `<div class="team-logo-initials">${initials}</div>`;
    }

    // --- Main Function to Fetch and Display Matches ---
    async function fetchMatches() {
        if (!matchList) return;
        // The loading message is now part of the view.php file, so we don't need to set it here.

        try {
            const response = await fetch(`${apiBase}/matches`, { headers: { 'X-WP-Nonce': nonce } });
            if (!response.ok) {
                const errorResult = await response.json();
                throw new Error(errorResult.message || 'Failed to fetch matches.');
            }
            const matches = await response.json();

            matchList.innerHTML = ''; // Clear the loading message

            if (matches.length > 0) {
                matches.forEach(match => {
                    const matchCard = document.createElement('div');
                    matchCard.className = 'match-card';

                    const statusClass = `status-${match.status.toLowerCase()}`;
                    let actionText = 'View Match';
                    if (match.status.toLowerCase() === 'live') {
                        actionText = 'Score Now';
                    } else if (match.status.toLowerCase() === 'completed') {
                        actionText = 'View Scorecard';
                    }

                    const team1Logo = getTeamLogoHTML(match.team1_logo_url, match.team1_name);
                    const team2Logo = getTeamLogoHTML(match.team2_logo_url, match.team2_name);
                    
                    // Format the creation date
                    const createdDate = new Date(match.created_at).toLocaleDateString('en-US', {
                        month: 'short', day: 'numeric', year: 'numeric'
                    });

                    matchCard.innerHTML = `
                        <div class="match-teams">
                            ${team1Logo}
                            <div class="team-names">
                                <span>${match.team1_name}</span>
                                <span class="vs-separator">vs</span>
                                <span>${match.team2_name}</span>
                            </div>
                            ${team2Logo}
                        </div>
                        <div class="match-status">
                            <div class="status-badge ${statusClass}">${match.status}</div>
                        </div>
                        <div class="match-details">
                            <span class="detail-item"><i class="fas fa-calendar-alt"></i> Created on ${createdDate}</span>
                            <span class="detail-item"><i class="fas fa-bolt"></i> ${match.match_format}</span>
                        </div>
                        <div class="match-action">
                            <a href="/match/${match.id}-${match.share_slug}/" class="action-link">${actionText} &rarr;</a>
                        </div>
                    `;
                    matchList.appendChild(matchCard);
                });
            } else {
                matchList.innerHTML = '<p class="empty-message">You have not created any matches yet.</p>';
            }
        } catch (error) {
            matchList.innerHTML = `<p class="error-message">Error: ${error.message}</p>`;
        }
    }

    // --- Initial Load ---
    fetchMatches();
});