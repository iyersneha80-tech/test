<div class="p-2 sm:p-4 cricscore-dark-bg min-h-screen">
    <header class="md:hidden flex justify-between items-center p-2 mb-4">
        <h1 class="text-lg font-bold text-white">Live Scorecard</h1>
        <button @click="resetMatch()" title="Reset and abandon match" class="w-10 h-10 flex items-center justify-center bg-red-600 hover:bg-red-700 text-white rounded-full transition-transform duration-150 ease-in-out hover:scale-110 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-75">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
        </button>
    </header>

    <div id="scoreboard-container" class="max-w-7xl mx-auto hidden">

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

            <div class="md:col-span-3 space-y-4">

                <div class="bg-gray-800 p-4 rounded-xl shadow-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div id="batting-team-shortname" class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center font-bold text-white"></div>
                            <div>
                                <h2 id="batting-team-name" class="text-xl sm:text-2xl font-bold text-white"></h2>
                                <p class="font-mono text-2xl sm:text-3xl font-bold tracking-tighter text-white">
                                    <span id="team-score">0</span>-<span id="team-wickets">0</span>
                                    <span id="team-overs" class="text-lg text-gray-400">(0.0)</span>
                                </p>
                                <div id="chase-info" class="hidden mt-1 text-sm text-gray-300 space-y-1">
                                    <p>Target: <span id="target-score" class="font-bold text-pink-400"></span></p>
                                    <div class="flex gap-4">
                                        <p>CRR: <span id="current-run-rate" class="font-mono"></span></p>
                                        <p>RRR: <span id="required-run-rate" class="font-mono"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-right">
                            <div>
                                <h2 id="bowling-team-name" class="text-xl sm:text-2xl font-bold text-white"></h2>
                            </div>
                            <div id="bowling-team-shortname" class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center font-bold text-white"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-800 p-4 rounded-xl shadow-2xl text-white">
                    <div class="grid grid-cols-2 gap-4">
                        <div id="batsman1-panel" class="relative flex justify-between items-center p-2 rounded-lg"><p id="batsman1-name" class="font-bold"></p><p><span id="batsman1-runs" class="font-bold"></span> (<span id="batsman1-balls"></span>)</p><div id="batsman1-striker-indicator" class="striker-indicator hidden"></div></div>
                        <div id="batsman2-panel" class="relative flex justify-between items-center p-2 rounded-lg"><p id="batsman2-name" class="font-bold"></p><p><span id="batsman2-runs" class="font-bold"></span> (<span id="batsman2-balls"></span>)</p><div id="batsman2-striker-indicator" class="striker-indicator hidden"></div></div>
                    </div>
                    <div class="text-center text-xs text-gray-400 mt-2">Partnership: <span id="partnership-runs"></span> (<span id="partnership-balls"></span>)</div>
                    <div class="mt-2 pt-2 border-t border-gray-700 flex justify-between items-center"><p id="bowler-name" class="font-bold"></p><p id="bowler-figures" class="font-mono text-sm"></p></div>
                </div>

                <div class="bg-gray-800 p-4 rounded-xl shadow-2xl">
                    <div class="flex justify-between items-center mb-3">
                        <div class="flex items-center gap-4">
                            <h4 class="font-bold text-white">This Over</h4>
                            <button id="cric-undo-btn" class="text-sm bg-gray-700 hover:bg-gray-600 text-white font-bold py-1 px-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>Undo</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <div id="free-hit-indicator" class="free-hit h-7 px-2 flex items-center justify-center rounded-md text-sm font-bold text-white hidden">FREE HIT</div>
                            <div id="this-over-display" class="flex items-center gap-1.5 flex-wrap justify-end"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-2 text-center text-lg">
                        <button data-runs="0" class="score-btn bg-gray-600 hover:bg-gray-500 text-white rounded-lg h-14 font-bold">0</button>
                        <button data-runs="1" class="score-btn bg-blue-600 hover:bg-blue-500 text-white rounded-lg h-14 font-bold">1</button>
                        <button data-runs="2" class="score-btn bg-blue-600 hover:bg-blue-500 text-white rounded-lg h-14 font-bold">2</button>
                        <button data-runs="3" class="score-btn bg-blue-600 hover:bg-blue-500 text-white rounded-lg h-14 font-bold">3</button>
                        <button data-runs="4" class="score-btn bg-green-600 hover:bg-green-500 text-white rounded-lg h-14 font-bold">4</button>
                        <button data-runs="6" class="score-btn bg-purple-600 hover:bg-purple-500 text-white rounded-lg h-14 font-bold">6</button>
                        <button id="extras-btn" class="score-btn bg-yellow-600 hover:bg-yellow-500 text-white rounded-lg h-14 font-bold">Extras</button>
                        <button id="wicket-btn" class="score-btn bg-red-600 hover:bg-red-500 text-white rounded-lg h-14 font-bold">Wicket</button>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 bg-gray-800 p-4 rounded-xl shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-white">Timeline</h3>
                    <button @click="resetMatch()" title="Reset and abandon match" class="hidden md:flex w-10 h-10 items-center justify-center bg-red-600 hover:bg-red-700 text-white rounded-full transition-transform duration-150 ease-in-out hover:scale-110 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-75">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </div>
                <div id="timeline-log" class="space-y-2 max-h-[65vh] overflow-y-auto pr-2">
                    </div>
            </div>
        </div>

        <div id="match-result-display" class="hidden absolute inset-0 bg-gray-900/90 flex items-center justify-center z-20">
            <div class="text-center p-8 bg-gray-800 rounded-xl shadow-2xl">
                <h2 id="match-result-text" class="text-3xl sm:text-4xl font-bold text-white"></h2>
            </div>
        </div>
    </div>

    <div id="modal-backdrop" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center p-4 z-50 hidden">

        <div id="setup-modal" class="bg-white text-gray-800 rounded-xl shadow-2xl w-full max-w-md hidden">
            <div class="p-6 border-b"><h2 class="text-2xl font-bold">Prepare Innings <span id="setup-innings-num"></span></h2></div>
            <div class="p-6 space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Striker</label><select id="setup-striker-select" class="w-full mt-1 p-2 border rounded-md"></select></div>
                <div><label class="block text-sm font-medium text-gray-700">Non-Striker</label><select id="setup-nonstriker-select" class="w-full mt-1 p-2 border rounded-md"></select></div>
                <div><label class="block text-sm font-medium text-gray-700">Opening Bowler</label><select id="setup-bowler-select" class="w-full mt-1 p-2 border rounded-md"></select></div>
            </div>
            <div class="p-4 bg-gray-50 flex justify-end"><button id="start-innings-btn" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg">Start Innings</button></div>
        </div>

        <div id="new-bowler-modal" class="bg-white text-gray-800 rounded-xl shadow-2xl w-full max-w-md hidden">
             <div class="p-6 border-b"><h2 class="text-2xl font-bold">End of Over <span id="new-bowler-over-num"></span></h2></div>
             <div class="p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Next Bowler</label>
                <div id="new-bowler-buttons" class="grid grid-cols-2 sm:grid-cols-3 gap-2 btn-group h-48 overflow-y-auto p-1 bg-gray-50 rounded-lg">
                </div>
             </div>
             <div class="p-4 bg-gray-50 flex justify-end"><button id="confirm-bowler-btn" class="bg-pink-600 text-white font-bold py-2 px-6 rounded-lg">Confirm</button></div>
        </div>

        <div id="extras-modal" class="bg-white text-gray-800 rounded-xl shadow-2xl w-full max-w-md hidden">
             <div class="p-6 border-b"><h2 class="text-2xl font-bold">Extras</h2></div>
             <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <div id="extras-type-btns" class="grid grid-cols-4 gap-2 btn-group">
                        <button data-extra-type="wd" class="score-btn bg-gray-200 rounded-lg py-2 font-bold">Wide</button>
                        <button data-extra-type="nb" class="score-btn bg-gray-200 rounded-lg py-2 font-bold">No Ball</button>
                        <button data-extra-type="b" class="score-btn bg-gray-200 rounded-lg py-2 font-bold">Bye</button>
                        <button data-extra-type="lb" class="score-btn bg-gray-200 rounded-lg py-2 font-bold">Leg Bye</button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Runs Batsmen Took</label>
                    <div id="extras-run-btns" class="grid grid-cols-6 gap-2 btn-group">
                        <button data-extra-run="1" class="score-btn bg-gray-200 rounded-lg h-12 font-bold">1</button>
                        <button data-extra-run="2" class="score-btn bg-gray-200 rounded-lg h-12 font-bold">2</button>
                        <button data-extra-run="3" class="score-btn bg-gray-200 rounded-lg h-12 font-bold">3</button>
                        <button data-extra-run="4" class="score-btn bg-gray-200 rounded-lg h-12 font-bold">4</button>
                        <button data-extra-run="5" class="score-btn bg-gray-200 rounded-lg h-12 font-bold">5</button>
                        <button data-extra-run="6" class="score-btn bg-gray-200 rounded-lg h-12 font-bold">6</button>
                    </div>
                </div>
             </div>
             <div class="p-4 bg-gray-50 flex justify-end"><button id="confirm-extra-btn" class="bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg">Add Extra</button></div>
        </div>

        <div id="wicket-modal" class="bg-white text-gray-800 rounded-xl shadow-2xl w-full max-w-md hidden">
            <div class="p-6 border-b"><h2 class="text-2xl font-bold">Wicket!</h2></div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dismissal Type</label>
                    <div id="wicket-type-buttons" class="grid grid-cols-2 sm:grid-cols-4 gap-2 btn-group">
                        <button data-wicket-type="Bowled" class="score-btn bg-gray-200 rounded-lg py-3 font-bold">Bowled</button>
                        <button data-wicket-type="Caught" class="score-btn bg-gray-200 rounded-lg py-3 font-bold">Caught</button>
                        <button data-wicket-type="LBW" class="score-btn bg-gray-200 rounded-lg py-3 font-bold">LBW</button>
                        <button data-wicket-type="Run Out" class="score-btn bg-gray-200 rounded-lg py-3 font-bold">Run Out</button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Next Batsman</label>
                    <div id="wicket-next-batsman-buttons" class="grid grid-cols-2 sm:grid-cols-3 gap-2 btn-group h-48 overflow-y-auto p-1 bg-gray-50 rounded-lg">
                    </div>
                </div>
            </div>
            <div class="p-4 bg-gray-50 flex justify-end"><button id="confirm-wicket-btn" class="bg-red-600 text-white font-bold py-2 px-6 rounded-lg">Confirm Wicket</button></div>
        </div>
    </div>

    <style>
        .cricscore-dark-bg { background-color: #030712; }
        .score-btn { transition: all 0.15s ease-in-out; user-select: none; }
        .score-btn:active { transform: scale(0.95); box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); }
        .striker-indicator {
            position: absolute; top: 5px; right: 5px; width: 8px; height: 8px;
            background-color: #ec4899; border-radius: 50%; border: 2px solid #1f2937;
        }
        #batsman1-panel.on-strike, #batsman2-panel.on-strike { background-color: rgba(236, 72, 153, 0.2); }
        .btn-group > button.selected { background-color: #db2777; color: white; }
        .free-hit {
            animation: pulse-free-hit 1.5s infinite;
            background-color: #ef4444;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        }
        @keyframes pulse-free-hit {
            50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
        }
        /* Custom scrollbar for timeline */
        #timeline-log::-webkit-scrollbar { width: 6px; }
        #timeline-log::-webkit-scrollbar-track { background: #1f2937; }
        #timeline-log::-webkit-scrollbar-thumb { background-color: #4b5563; border-radius: 6px; }
        #timeline-log::-webkit-scrollbar-thumb:hover { background-color: #6b7280; }
    </style>
</div>