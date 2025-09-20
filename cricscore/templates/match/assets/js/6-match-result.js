/**
 * Logic for the Final Match Result screen (Step 6)
 * Redesigned with a summary header and tabbed interface.
 *
 * @package CricScore
 * @version 2.2.2
 */
(function(cricscore) {

    cricscore.steps.result = {

        elements: {},

        formatOvers: function(oversString) {
            if (!oversString || typeof oversString !== 'string') return '0.0';
            let [overs, balls] = oversString.split('.').map(Number);
            if (isNaN(overs)) overs = 0;
            if (isNaN(balls)) balls = 0;
            
            if (balls >= 6) {
                overs += Math.floor(balls / 6);
                balls = balls % 6;
            }
            return `${overs}.${balls}`;
        },

        init: function() {
            this.elements = {
                team1LogoContainer: document.getElementById('final-team1-logo-container'),
                team1ShortName: document.getElementById('final-team1-short-name'),
                team1Score: document.getElementById('final-team1-score'),
                team2LogoContainer: document.getElementById('final-team2-logo-container'),
                team2ShortName: document.getElementById('final-team2-short-name'),
                team2Score: document.getElementById('final-team2-score'),
                summaryResultText: document.getElementById('final-result-text-summary'),
                tabLinks: document.querySelectorAll('.tab-link'),
                tabContents: document.querySelectorAll('.tab-content'),
                innings1BattingTitle: document.getElementById('final-innings1-batting-title'),
                innings1BowlingTitle: document.getElementById('final-innings1-bowling-title'),
                innings1BattingTbody: document.getElementById('final-innings1-batting-tbody'),
                innings1BowlingTbody: document.getElementById('final-innings1-bowling-tbody'),
                innings2BattingTitle: document.getElementById('final-innings2-batting-title'),
                innings2BowlingTitle: document.getElementById('final-innings2-bowling-title'),
                innings2BattingTbody: document.getElementById('final-innings2-batting-tbody'),
                innings2BowlingTbody: document.getElementById('final-innings2-bowling-tbody'),
            };
            this.populateData();
            this.bindTabEvents();
        },

        bindTabEvents: function() {
            this.elements.tabLinks.forEach(link => {
                link.addEventListener('click', () => {
                    const tabId = link.getAttribute('data-tab');
                    this.elements.tabLinks.forEach(item => item.classList.remove('active'));
                    this.elements.tabContents.forEach(item => item.classList.remove('active'));
                    link.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        },

        populateData: function() {
            const match = cricscore.matchState.match;
            const firstInnings = cricscore.matchState.firstInnings;
            const secondInnings = cricscore.matchState.currentInnings;
            const result = cricscore.matchState.result;

            if (!firstInnings || !secondInnings || !result) {
                if (result && result.text) {
                    this.elements.summaryResultText.textContent = result.text;
                } else {
                    console.error("Complete match data not found in state.");
                }
                return;
            }

            this.elements.summaryResultText.textContent = result.text;

            const teamLeftId = firstInnings.batting_team_id;
            const teamRightId = (teamLeftId == match.team1_id) ? match.team2_id : match.team1_id;

            const teamLeftData = (teamLeftId == match.team1_id) ? 
                { id: match.team1_id, name: match.team1_name, short_name: match.team1_short_name, logo_url: match.team1_logo_url } : 
                { id: match.team2_id, name: match.team2_name, short_name: match.team2_short_name, logo_url: match.team2_logo_url };

            const teamRightData = (teamRightId == match.team1_id) ? 
                { id: match.team1_id, name: match.team1_name, short_name: match.team1_short_name, logo_url: match.team1_logo_url } : 
                { id: match.team2_id, name: match.team2_name, short_name: match.team2_short_name, logo_url: match.team2_logo_url };

            this.elements.team1ShortName.textContent = teamLeftData.short_name || 'T1';
            this.elements.team1Score.textContent = `${firstInnings.total_score}/${firstInnings.total_wickets} (${this.formatOvers(firstInnings.total_overs)})`;
            this.elements.team1LogoContainer.innerHTML = `<img src="${teamLeftData.logo_url || ''}" alt="${teamLeftData.name} Logo">`;

            this.elements.team2ShortName.textContent = teamRightData.short_name || 'T2';
            this.elements.team2Score.textContent = `${secondInnings.total_score}/${secondInnings.total_wickets} (${this.formatOvers(secondInnings.total_overs)})`;
            this.elements.team2LogoContainer.innerHTML = `<img src="${teamRightData.logo_url || ''}" alt="${teamRightData.name} Logo">`;

            const innings1BattingName = firstInnings.batting_team_id == match.team1_id ? match.team1_name : match.team2_name;
            const innings1BowlingName = firstInnings.batting_team_id == match.team1_id ? match.team2_name : match.team1_name;
            const innings2BattingName = secondInnings.batting_team_id == match.team1_id ? match.team1_name : match.team2_name;
            const innings2BowlingName = secondInnings.batting_team_id == match.team1_id ? match.team2_name : match.team1_name;

            this.elements.innings1BattingTitle.textContent = `${innings1BattingName} Batting`;
            this.elements.innings1BowlingTitle.textContent = `${innings1BowlingName} Bowling`;
            this._populateScorecardTables(this.elements.innings1BattingTbody, this.elements.innings1BowlingTbody, firstInnings, match);

            this.elements.innings2BattingTitle.textContent = `${innings2BattingName} Batting`;
            this.elements.innings2BowlingTitle.textContent = `${innings2BowlingName} Bowling`;
            this._populateScorecardTables(this.elements.innings2BattingTbody, this.elements.innings2BowlingTbody, secondInnings, match);
        },
        
        // --- CHANGE 1: New helper function to format dismissal text ---
        formatDismissal: function(stats, allPlayers) {
            const type = stats.dismissal_type || 'not out';
            if (type.toLowerCase() === 'not out') {
                return 'not out';
            }

            const bowler = allPlayers.find(p => p.id == stats.bowler_id);
            const fielder = allPlayers.find(p => p.id == stats.fielder_id);
            const bowlerName = bowler ? bowler.name : '';
            const fielderName = fielder ? fielder.name : '';

            switch (type) {
                case 'Caught':
                    return `c ${fielderName} b ${bowlerName}`;
                case 'Bowled':
                    return `b ${bowlerName}`;
                case 'LBW':
                    return `lbw b ${bowlerName}`;
                case 'Stumped':
                    return `st ${fielderName} b ${bowlerName}`;
                case 'Run Out':
                    return `run out (${fielderName})`;
                default:
                    return type;
            }
        },

        _populateScorecardTables: function(battingTbody, bowlingTbody, innings, match) {
            const battingTeamPlayers = innings.batting_team_id == match.team1_id ? match.team1_players : match.team2_players;
            const bowlingTeamPlayers = innings.batting_team_id == match.team1_id ? match.team2_players : match.team1_players;
            const allPlayers = [...match.team1_players, ...match.team2_players];

            let batsmenStats = innings.batsmen || [];
            let bowlersStats = innings.bowlers || [];

            battingTbody.innerHTML = '';
            const battingOrder = innings.battingOrder || [];
            const playersWhoBattedIds = [];

            battingOrder.forEach(playerId => {
                const player = battingTeamPlayers.find(p => p.id == playerId);
                if (!player) return;

                playersWhoBattedIds.push(parseInt(playerId, 10));
                const stats = batsmenStats.find(b => b.player_id == playerId);
                const placeholderImg = `https://placehold.co/36x36/E2E8F0/475569?text=${player.name.charAt(0)}`;
                const playerImg = player.profile_image_url || placeholderImg;
                
                // --- CHANGE 2: Call the new formatDismissal function ---
                const dismissal = this.formatDismissal(stats, allPlayers);
                const runs = parseInt(stats.runs_scored, 10) || 0;
                const balls = parseInt(stats.balls_faced, 10) || 0;
                const fours = parseInt(stats.fours, 10) || 0;
                const sixes = parseInt(stats.sixes, 10) || 0;
                const strikeRate = balls > 0 ? ((runs / balls) * 100).toFixed(2) : '0.00';

                const row = `
                    <tr>
                        <td class="batsman-col">
                            <div class="player-cell">
                                <img src="${playerImg}" alt="${player.name}" class="player-image">
                                <div>
                                    <span class="player-name">${player.name}</span>
                                    <span class="dismissal-info">${dismissal}</span>
                                </div>
                            </div>
                        </td>
                        <td class="runs-col">${runs}</td>
                        <td class="balls-col">${balls}</td>
                        <td class="fours-col">${fours}</td>
                        <td class="sixes-col">${sixes}</td>
                        <td class="sr-col">${strikeRate}</td>
                    </tr>`;
                battingTbody.innerHTML += row;
            });

            (battingTeamPlayers || []).forEach(player => {
                if (!playersWhoBattedIds.includes(parseInt(player.id, 10))) {
                    const placeholderImg = `https://placehold.co/36x36/E2E8F0/475569?text=${player.name.charAt(0)}`;
                    const playerImg = player.profile_image_url || placeholderImg;
                    const row = `
                        <tr>
                            <td class="batsman-col" colspan="6">
                                <div class="player-cell">
                                    <img src="${playerImg}" alt="${player.name}" class="player-image">
                                    <div>
                                        <span class="player-name">${player.name}</span>
                                        <span class="dismissal-info">Did Not Bat</span>
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                    battingTbody.innerHTML += row;
                }
            });

            bowlingTbody.innerHTML = '';
            (bowlingTeamPlayers || []).forEach(player => {
                const stats = bowlersStats.find(b => b.player_id == player.id);
                if (stats) {
                    const placeholderImg = `https://placehold.co/36x36/E2E8F0/475569?text=${player.name.charAt(0)}`;
                    const playerImg = player.profile_image_url || placeholderImg;
                    const oversFloat = parseFloat(stats.overs_bowled) || 0;
                    const runsConceded = parseInt(stats.runs_conceded, 10) || 0;
                    const econ = oversFloat > 0 ? (runsConceded / oversFloat).toFixed(2) : '0.00';

                    const row = `
                        <tr>
                            <td><div class="player-cell"><img src="${playerImg}" alt="${player.name}" class="player-image"><span class="player-name">${player.name}</span></div></td>
                            <td>${stats.overs_bowled}</td>
                            <td>${stats.maidens}</td>
                            <td>${runsConceded}</td>
                            <td>${stats.wickets_taken}</td>
                            <td>${econ}</td>
                        </tr>`;
                    bowlingTbody.innerHTML += row;
                }
            });
        }
    };

})(window.cricscore);