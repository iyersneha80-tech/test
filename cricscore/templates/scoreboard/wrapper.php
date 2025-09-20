<?php
/**
 * The template for displaying the scoreboard wrapper.
 *
 * This file is the main entry point for the single-page application,
 * controlled by Alpine.js.
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CricScore Live</title>
    <?php wp_head(); ?>
    <style>
        /* This prevents a flash of unstyled content while Alpine.js initializes */
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100">

    <div id="cricscore-app" class="min-h-screen" x-data="cricscoreApp" @match-ended.window="handleMatchEnd($event)" x-cloak>

        <div x-show="currentStep === 'start'" x-transition.opacity.duration.500ms>
            <?php include_once CRICSCORE_PATH . 'templates/scoreboard/partials/start-match.php'; ?>
        </div>

        <div x-show="currentStep === 'setup'" x-transition.opacity.duration.500ms>
            <?php include_once CRICSCORE_PATH . 'templates/scoreboard/partials/match-setup.php'; ?>
        </div>

        <div x-show="currentStep === 'scoreboard'" x-transition.opacity.duration.500ms>
            <?php include_once CRICSCORE_PATH . 'templates/scoreboard/partials/live-scoreboard.php'; ?>
        </div>

        <div x-show="currentStep === 'matchEnd'" x-transition.opacity.duration.500ms>
            <?php include_once CRICSCORE_PATH . 'templates/scoreboard/partials/match-end.php'; ?>
        </div>
        
    </div>
    <script>
    document.addEventListener('alpine:initializing', () => {
        Alpine.data('cricscoreApp', () => ({
            // ---- STATE PROPERTIES ---- //
            currentStep: localStorage.getItem('cricscoreStep') || 'start',
            newPlayerName: '',
            matchConfig: JSON.parse(localStorage.getItem('cricscoreMatch')) || {
                format: null, totalOvers: null, customOvers: '', maxOversPerBowler: '', testDays: '', oversPerDay: '',
                teamA: { name: 'Team A', players: [] }, teamB: { name: 'Team B', players: [] },
enableNaabaad: false,
                playerPool: [], nextPlayerId: 1, toss: { winner: null, decision: null },
            },
            matchSummaryData: null,
            
            // ---- LIFECYCLE & PERSISTENCE ---- //
            init() {
                this.$watch('matchConfig', (newValue) => {
                    localStorage.setItem('cricscoreMatch', JSON.stringify(newValue));
                });
                this.$watch('currentStep', (newValue) => {
                    localStorage.setItem('cricscoreStep', newValue);
                });
                if (this.currentStep === 'scoreboard') {
                    setTimeout(() => {
                        if (typeof initializeScoreboard === 'function') {
                            initializeScoreboard(this.matchConfig);
                        }
                    }, 100);
                }
                if (this.currentStep === 'matchEnd') {
                    this.matchSummaryData = JSON.parse(localStorage.getItem('cricscoreLive'));
                    setTimeout(() => {
                        if (typeof renderMatchSummary === 'function') {
                            renderMatchSummary(this.matchSummaryData);
                        }
                    }, 100);
                }
            },
            
// ---- MATCH FLOW & REMATCH LOGIC ---- //

/**
 * Handles the 'match-ended' event from module-scoreboard.js.
 */
handleMatchEnd(event) {
    this.matchSummaryData = event.detail.finalState;
    this.currentStep = 'matchEnd';
    setTimeout(() => {
        if (typeof renderMatchSummary === 'function') {
            renderMatchSummary(this.matchSummaryData);
        }
    }, 100);
},

/**
 * The robust rematch function. Keeps teams but resets the toss.
 */
startRematch() {
    if (confirm('Start a new match with the same teams? You will need to do a new toss.')) {
        localStorage.removeItem('cricscoreLive');
        // Reset only the toss data, keeping players and team names
        this.matchConfig.toss = { winner: null, decision: null };
        this.currentStep = 'setup';
    }
},


            /**
             * Resets everything and returns to the home screen.
             */
            resetMatch() {
                if (confirm('Are you sure you want to start over? All teams and match data will be deleted.')) {
                    localStorage.removeItem('cricscoreMatch');
                    localStorage.removeItem('cricscoreStep');
                    localStorage.removeItem('cricscoreLive');
                    this.matchConfig = {
                        format: null, totalOvers: null, customOvers: '', maxOversPerBowler: '', testDays: '', oversPerDay: '',
                        teamA: { name: 'Team A', players: [] }, teamB: { name: 'Team B', players: [] },
enableNaabaad: false,
                        playerPool: [], nextPlayerId: 1, toss: { winner: null, decision: null },
                    };
                    this.currentStep = 'start';
                }
            },
            /**
 * FINAL & PROFESSIONAL: Generates a beautiful, multi-page PDF summary
 * with advanced styling, headers, footers, and zebra-striped tables.
 */
downloadSummary() {
    const { jsPDF } = window.jspdf;
    const summary = this.matchSummaryData;
    if (!summary) return alert('Match data not found!');

    const downloadBtn = event.currentTarget;
    downloadBtn.disabled = true;
    downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating PDF...';

    try {
        const pdf = new jsPDF('p', 'mm', 'a4');
        const FONT_SIZES = { H1: 18, H2: 14, BODY: 9, HEADER: 8, FOOTER: 8 };
        const COLORS = { PRIMARY: '#D946EF', TEXT: '#111827', GRAY: '#6B7281', BORDER: '#E5E7EB', ZEBRA: '#F9FAFB' };
        const PAGE_WIDTH = pdf.internal.pageSize.getWidth();
        const PAGE_HEIGHT = pdf.internal.pageSize.getHeight();
        const MARGIN = 15;
        let yPos = 0; // We will control this precisely.
        let pageNumber = 1;
        const matchTitle = `${summary.teams[1].name} vs ${summary.teams[2].name}`;

        // --- HELPER: Draw Page Header ---
        const drawHeader = () => {
            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(FONT_SIZES.HEADER);
            pdf.setTextColor(COLORS.GRAY);
            pdf.text(matchTitle, MARGIN, MARGIN - 5);
            pdf.text('Match Summary', PAGE_WIDTH - MARGIN, MARGIN - 5, { align: 'right' });
            pdf.setDrawColor(COLORS.BORDER);
            pdf.line(MARGIN, MARGIN - 2, PAGE_WIDTH - MARGIN, MARGIN - 2);
            yPos = MARGIN + 5;
        };

        // --- HELPER: Draw Page Footer ---
        const drawFooter = (page) => {
            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(FONT_SIZES.FOOTER);
            pdf.setTextColor(COLORS.GRAY);
            const footerText = `Page ${page}`;
            pdf.text(footerText, PAGE_WIDTH - MARGIN, PAGE_HEIGHT - 10, { align: 'right' });
        };

        // --- HELPER: Check for Page Breaks ---
        const checkPageBreak = (requiredSpace = 20) => {
            if (yPos + requiredSpace > PAGE_HEIGHT - MARGIN) {
                drawFooter(pageNumber);
                pdf.addPage();
                pageNumber++;
                drawHeader();
            }
        };

        // --- START PDF GENERATION ---
        drawHeader();

        // Main Title
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(FONT_SIZES.H1);
        pdf.setTextColor(COLORS.PRIMARY);
        pdf.text('Match Result', PAGE_WIDTH / 2, yPos, { align: 'center' });
        yPos += 8;

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(FONT_SIZES.H2 - 2);
        pdf.setTextColor(COLORS.TEXT);
        pdf.text(summary.matchResult, PAGE_WIDTH / 2, yPos, { align: 'center' });
        yPos += 15;

        // --- LOOP THROUGH INNINGS ---
        for (const inningsNum in summary.innings) {
            const innings = summary.innings[inningsNum];
            if (innings.overs === 0 && innings.score === 0) continue;

            checkPageBreak(45);
            const battingTeam = summary.teams[innings.battingTeamId];

            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(FONT_SIZES.H2);
            pdf.setTextColor(COLORS.PRIMARY);
            pdf.text(`${battingTeam.name} - ${inningsNum}${inningsNum == 1 ? 'st' : 'nd'} Innings`, MARGIN, yPos);
            yPos += 6;

            // --- NEW SAFER LOGIC FOR PDF ---
            const squadSize = battingTeam.squad.length;
            const isNaabaadEnabled = summary.matchConfig ? summary.matchConfig.enableNaabaad : false;
            const totalOvers = summary.totalOvers || 999; // Default to a high number if not found

            const isAllOut = squadSize > 0 && (isNaabaadEnabled ? innings.wickets >= squadSize : innings.wickets >= squadSize - 1);

            const oversDisplay = (innings.balls > 0 && innings.overs < totalOvers)
                ? `${innings.overs}.${innings.balls}`
                : `${innings.overs}`;

            let summaryText = `${innings.score}/${innings.wickets} (${oversDisplay} Overs)`;
            if (isAllOut && innings.overs < totalOvers) {
                summaryText += ` - All Out`; // Add "All Out" text for the PDF
            }

            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(FONT_SIZES.BODY);
            pdf.setTextColor(COLORS.GRAY);
            pdf.text(summaryText, MARGIN, yPos);
            yPos += 8;


            // --- BATTING TABLE ---
            const battingHeaders = ['Batsman', 'Dismissal', 'R', 'B', '4s', '6s', 'SR'];
            const battingColWidths = [50, 50, 15, 15, 15, 15, 20];
            let xPos = MARGIN;

            // Draw table header with background
            pdf.setFillColor(COLORS.ZEBRA);
            pdf.rect(MARGIN, yPos, PAGE_WIDTH - (MARGIN * 2), 7, 'F');
            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(FONT_SIZES.HEADER);
            pdf.setTextColor(COLORS.TEXT);
            battingHeaders.forEach((header, i) => {
                const align = i > 1 ? 'right' : 'left';
                pdf.text(header, align === 'right' ? xPos + battingColWidths[i] : xPos + 2, yPos + 5, { align: align });
                xPos += battingColWidths[i];
            });
            yPos += 7;

            // Draw table rows
            let isZebra = false;
            innings.batsmen.forEach(batsman => {
                if (!batsman.name) return;
                checkPageBreak(10);
                xPos = MARGIN;
                
                if (isZebra) {
                    pdf.setFillColor(COLORS.ZEBRA);
                    pdf.rect(MARGIN, yPos, PAGE_WIDTH - (MARGIN * 2), 7, 'F');
                }
                isZebra = !isZebra;

                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(FONT_SIZES.BODY);
                pdf.setTextColor(COLORS.TEXT);

                let dismissalText = 'not out';
                if (batsman.dismissal && batsman.dismissal.type) {
                    const type = batsman.dismissal.type;
                    const bowler = batsman.dismissal.bowler;
                    if (type === 'Run Out') dismissalText = 'Run Out';
                    else if (bowler) {
                        if (type === 'Bowled') dismissalText = `b. ${bowler}`;
                        else if (type === 'Caught') dismissalText = `c. & b. ${bowler}`;
                        else if (type === 'LBW') dismissalText = `lbw b. ${bowler}`;
                        else dismissalText = `${type} b. ${bowler}`;
                    } else dismissalText = type;
                } else if (typeof batsman.dismissal === 'string') dismissalText = batsman.dismissal;

                const sr = batsman.balls > 0 ? ((batsman.runs / batsman.balls) * 100).toFixed(2) : '0.00';
                const batsmanData = [batsman.name, dismissalText, String(batsman.runs), String(batsman.balls), String(batsman.fours || 0), String(batsman.sixes || 0), sr];
                
                batsmanData.forEach((data, i) => {
                    const align = i > 1 ? 'right' : 'left';
                    pdf.text(data, align === 'right' ? xPos + battingColWidths[i] : xPos + 2, yPos + 5, { align: align });
                    xPos += battingColWidths[i];
                });
                yPos += 7;
            });
            yPos += 7;

            // --- BOWLING TABLE ---
            checkPageBreak(25);
            const bowlingHeaders = ['Bowler', 'O', 'M', 'R', 'W', 'Econ'];
            const bowlingColWidths = [70, 20, 20, 20, 20, 30];
            xPos = MARGIN;

            pdf.setFillColor(COLORS.ZEBRA);
            pdf.rect(MARGIN, yPos, PAGE_WIDTH - (MARGIN * 2), 7, 'F');
            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(FONT_SIZES.HEADER);
            pdf.setTextColor(COLORS.TEXT);
            bowlingHeaders.forEach((header, i) => {
                const align = i > 0 ? 'right' : 'left';
                pdf.text(header, align === 'right' ? xPos + bowlingColWidths[i] : xPos + 2, yPos + 5, { align: align });
                xPos += bowlingColWidths[i];
            });
            yPos += 7;

            isZebra = false;
            innings.bowlers.forEach(bowler => {
                if (!bowler.name) return;
                checkPageBreak(10);
                xPos = MARGIN;

                if (isZebra) {
                    pdf.setFillColor(COLORS.ZEBRA);
                    pdf.rect(MARGIN, yPos, PAGE_WIDTH - (MARGIN * 2), 7, 'F');
                }
                isZebra = !isZebra;

                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(FONT_SIZES.BODY);
                pdf.setTextColor(COLORS.TEXT);

                const totalBalls = Math.floor(bowler.overs) * 6 + (bowler.overs * 10) % 10;
                const econ = totalBalls > 0 ? (bowler.runs / (totalBalls / 6)).toFixed(2) : '0.00';
                const bowlerData = [bowler.name, String(bowler.overs), String(bowler.maidens), String(bowler.runs), String(bowler.wickets), econ];
                
                bowlerData.forEach((data, i) => {
                    const align = i > 0 ? 'right' : 'left';
                    pdf.text(data, align === 'right' ? xPos + bowlingColWidths[i] : xPos + 2, yPos + 5, { align: align });
                    xPos += bowlingColWidths[i];
                });
                yPos += 7;
            });
            yPos += 15;
        }

        // --- FINISH AND SAVE ---
        drawFooter(pageNumber);
        const fileName = `${matchTitle.replace(/\s/g, '-')}-Summary.pdf`;
        pdf.save(fileName);

    } catch (err) {
        console.error("Error generating custom PDF:", err);
        alert("Sorry, there was an error creating the PDF. Please check the console for details.");
    } finally {
        downloadBtn.disabled = false;
        downloadBtn.innerHTML = '<i class="fas fa-download mr-2"></i> Download Summary (PDF)';
    }
},


            // ---- SETUP SCREEN METHODS ---- //

            setOvers(o) {
                this.matchConfig.totalOvers = o;
                this.matchConfig.customOvers = '';
            },

            isStepOneValid() {
                if (this.matchConfig.format === 'limited') {
                    const totalOvers = this.matchConfig.totalOvers || parseInt(this.matchConfig.customOvers);
                    return totalOvers > 0;
                }
                if (this.matchConfig.format === 'test') {
                    return parseInt(this.matchConfig.testDays) > 0 && parseInt(this.matchConfig.oversPerDay) > 0;
                }
                return false;
            },

            proceedToSetup() {
                if (!this.isStepOneValid()) return;
                if (this.matchConfig.format === 'limited' && this.matchConfig.customOvers) {
                    this.matchConfig.totalOvers = parseInt(this.matchConfig.customOvers);
                }
                this.currentStep = 'setup';
            },

            addPlayer() {
                if (!this.newPlayerName.trim()) return;
                this.matchConfig.playerPool.push({
                    id: this.matchConfig.nextPlayerId++,
                    name: this.newPlayerName.trim()
                });
                this.newPlayerName = '';
            },

            assignPlayer(playerId, team) {
                const playerIndex = this.matchConfig.playerPool.findIndex(p => p.id === playerId);
                if (playerIndex === -1) return;
                const [player] = this.matchConfig.playerPool.splice(playerIndex, 1);
                if (team === 'A') {
                    this.matchConfig.teamA.players.push(player);
                } else {
                    this.matchConfig.teamB.players.push(player);
                }
            },

            unassignPlayer(playerId, team) {
                const targetTeam = team === 'A' ? this.matchConfig.teamA : this.matchConfig.teamB;
                const playerIndex = targetTeam.players.findIndex(p => p.id === playerId);
                if (playerIndex === -1) return;
                const [player] = targetTeam.players.splice(playerIndex, 1);
                this.matchConfig.playerPool.push(player);
                this.matchConfig.playerPool.sort((a, b) => a.id - b.id);
            },

            tossUnlocked() {
                return this.matchConfig.teamA.players.length > 0 && this.matchConfig.teamB.players.length > 0;
            },

            isStepTwoValid() {
                if (!this.matchConfig.teamA.name.trim() || !this.matchConfig.teamB.name.trim()) return false;
                if (!this.tossUnlocked()) return false;
                if (!this.matchConfig.toss.winner || !this.matchConfig.toss.decision) return false;

                // Check for minimum players (e.g., at least 2 for a match)
                if (this.matchConfig.teamA.players.length < 2 || this.matchConfig.teamB.players.length < 2) return false;

                return true;
            },

            startMatch() {
                if (!this.isStepTwoValid()) return;
                localStorage.removeItem('cricscoreLive');
                this.currentStep = 'scoreboard';
                setTimeout(() => {
                    if (typeof initializeScoreboard === 'function') {
                        initializeScoreboard(this.matchConfig);
                    }
                }, 100);
            }
        }));
    });
</script>

    <?php wp_footer(); ?>
</body>
</html>