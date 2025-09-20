/**
 * Module for rendering the final match summary on the 'matchEnd' screen.
 * @param {object} finalState - The complete state object from the ended match.
 */
function renderMatchSummary(finalState) {
    // Safety check for invalid data
    if (!finalState || !finalState.teams || !finalState.innings) {
        const container = document.getElementById('match-summary-container');
        if (container) {
            container.innerHTML = '<p class="text-gray-400">Error: Could not load match summary data.</p>';
        }
        console.error('Render Match Summary failed due to invalid finalState object.');
        return;
    }

    const container = document.getElementById('match-summary-container');
    if (!container) {
        console.error('Match summary container not found!');
        return;
    }

    // Create a prominent display for the final match result to be used as a footer.
    const resultHtml = `
    <div class="text-center border-t border-gray-600 pt-4 mt-6">
        <h3 class="text-xl font-bold text-green-400">${finalState.matchResult}</h3>
    </div>
`;

    let inningsHtml = '';

    // Loop through each innings in the final state
    for (const inningsNum in finalState.innings) {
        const innings = finalState.innings[inningsNum];

        // Skip innings that were not played
        if (innings.overs === 0 && innings.score === 0 && innings.wickets === 0) {
            continue;
        }

        const battingTeam = finalState.teams[innings.battingTeamId];
        const squadSize = battingTeam.squad.length;

        // --- SAFER LOGIC ---
        // Provide default fallbacks in case matchConfig or totalOvers is missing from old data
        const isNaabaadEnabled = finalState.matchConfig ? finalState.matchConfig.enableNaabaad : false;
        const totalOvers = finalState.totalOvers || 999; // Default to a high number if not found

        const isAllOut = squadSize > 0 && (isNaabaadEnabled ? innings.wickets >= squadSize : innings.wickets >= squadSize - 1);

        // Correctly format the overs display string
        const oversDisplay = (innings.balls > 0 && innings.overs < totalOvers)
            ? `${innings.overs}.${innings.balls}`
            : `${innings.overs}`;

        // Build the complete summary text, adding "All Out" if applicable
        let summaryText = `${innings.score}/${innings.wickets} (${oversDisplay} Overs)`;
        if (isAllOut && innings.overs < totalOvers) {
            summaryText += ` <span class="font-semibold text-gray-300">All Out</span>`;
        }

        // Start of an innings card
        inningsHtml += `<div class="mb-6">`;

        // Innings Title
        inningsHtml += `
            <h4 class="text-xl font-bold text-pink-500">${battingTeam.name} - ${inningsNum}${inningsNum == 1 ? 'st' : 'nd'} Innings</h4>
            <p class="text-gray-400 mb-3">${summaryText}</p>
        `;

        // Batting Table
        inningsHtml += `
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-700/50">
                        <th class="py-2 font-normal">Batsman</th>
                        <th class="py-2 font-normal">Dismissal</th>
                        <th class="py-2 font-normal text-center">R</th>
                        <th class="py-2 font-normal text-center">B</th>
                        <th class="py-2 font-normal text-center">4s</th>
                        <th class="py-2 font-normal text-center">6s</th>
                        <th class="py-2 font-normal text-right">SR</th>
                    </tr>
                </thead>
                <tbody>
        `;

        innings.batsmen.forEach(batsman => {
            if (!batsman.name) return;
            // Format the dismissal text to include the bowler's name
            let dismissalText = 'not out';
            if (batsman.dismissal && batsman.dismissal.type) { // Check for the new object format
                const type = batsman.dismissal.type;
                const bowler = batsman.dismissal.bowler;

                if (type === 'Run Out') {
                    dismissalText = 'Run Out';
                } else if (bowler) {
                    if (type === 'Bowled') {
                        dismissalText = `b. ${bowler}`;
                    } else if (type === 'Caught') {
                        dismissalText = `c. & b. ${bowler}`; // Simplified as fielder isn't tracked
                    } else if (type === 'LBW') {
                        dismissalText = `lbw b. ${bowler}`;
                    } else {
                        // Fallback for any other dismissal types (like Stumped, etc.)
                        dismissalText = `${type} b. ${bowler}`;
                    }
                } else {
                    dismissalText = type; // Should not happen unless it's a Run Out
                }
            } else if (typeof batsman.dismissal === 'string') {
                // This provides backward compatibility for any old matches stored in localStorage
                dismissalText = batsman.dismissal;
            }
            const sr = batsman.balls > 0 ? ((batsman.runs / batsman.balls) * 100).toFixed(2) : '0.00';
            inningsHtml += `
                <tr class="border-b border-gray-800">
                    <td class="py-2 font-semibold">${batsman.name}</td>
                    <td class="py-2 text-gray-400">${dismissalText}</td>
                    <td class="py-2 font-semibold text-center">${batsman.runs}</td>
                    <td class="py-2 text-center">${batsman.balls}</td>
                    <td class="py-2 text-center">${batsman.fours || 0}</td>
                    <td class="py-2 text-center">${batsman.sixes || 0}</td>
                    <td class="py-2 font-semibold text-right">${sr}</td>
                </tr>
            `;
        });

        inningsHtml += `</tbody></table>`;
        
        // --- NEW CODE TO DISPLAY EXTRAS ---
        const extras = innings.extras;
        if (extras && extras.total > 0) {
            const extrasBreakdown = [];
            if (extras.wd > 0) extrasBreakdown.push(`wd ${extras.wd}`);
            if (extras.nb > 0) extrasBreakdown.push(`nb ${extras.nb}`);
            if (extras.b > 0) extrasBreakdown.push(`b ${extras.b}`);
            if (extras.lb > 0) extrasBreakdown.push(`lb ${extras.lb}`);

            inningsHtml += `
                <div class="py-2 text-sm text-gray-400">
                    <span class="font-semibold text-gray-300">Extras:</span>
                    <span class="font-bold ml-2">${extras.total}</span>
                    <span class="ml-1">(${extrasBreakdown.join(', ')})</span>
                </div>
            `;
        }
        // --- END OF NEW CODE ---

        // Bowling Table
        inningsHtml += `<div class="mt-4">`;
        inningsHtml += `
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-700/50">
                        <th class="py-2 font-normal">Bowler</th>
                        <th class="py-2 font-normal text-center">O</th>
                        <th class="py-2 font-normal text-center">M</th>
                        <th class="py-2 font-normal text-center">R</th>
                        <th class="py-2 font-normal text-center">W</th>
                        <th class="py-2 font-normal text-right">Econ</th>
                    </tr>
                </thead>
                <tbody>
        `;

        innings.bowlers.forEach(bowler => {
            if (!bowler.name) return;
            const totalBalls = Math.floor(bowler.overs) * 6 + (bowler.overs * 10) % 10;
            const econ = totalBalls > 0 ? (bowler.runs / (totalBalls / 6)).toFixed(2) : '0.00';
            inningsHtml += `
                <tr class="border-b border-gray-800">
                    <td class="py-2 font-semibold">${bowler.name}</td>
                    <td class="py-2 text-center">${bowler.overs}</td>
                    <td class="py-2 text-center">${bowler.maidens}</td>
                    <td class="py-2 text-center">${bowler.runs}</td>
                    <td class="py-2 font-semibold text-center">${bowler.wickets}</td>
                    <td class="py-2 font-semibold text-right">${econ}</td>
                </tr>
            `;
        });

        inningsHtml += `</tbody></table></div>`;

        // End of an innings card
        inningsHtml += `</div>`;
    }

    // Prepend the result to the innings details.
    container.innerHTML = inningsHtml + resultHtml;
}