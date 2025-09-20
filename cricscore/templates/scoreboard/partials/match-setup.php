<div class="p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800">Match Setup</h1>
            <p class="text-gray-500 mt-2">Get your teams and toss ready for the game.</p>
        </div>

        <div class="step-card-container">
            <div class="step-card bg-white rounded-xl shadow-lg p-6 lg:col-span-1">
                <h3 class="text-lg font-bold text-gray-800 flex items-center"><span class="bg-pink-500 text-white rounded-full h-6 w-6 text-sm flex items-center justify-center mr-3">1</span> Name Your Teams</h3>
                <div class="space-y-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Team A</label>
                        <input type="text" x-model="matchConfig.teamA.name" class="w-full p-3 bg-gray-50 border-2 border-transparent focus:border-green-500 focus:bg-white rounded-lg font-bold text-lg text-gray-800 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Team B</label>
                        <input type="text" x-model="matchConfig.teamB.name" class="w-full p-3 bg-gray-50 border-2 border-transparent focus:border-yellow-500 focus:bg-white rounded-lg font-bold text-lg text-gray-800 transition">
                    </div>
                </div>
            </div>

            <div class="step-card lg:col-span-2 lg:row-span-2 bg-white rounded-xl shadow-lg p-6 mt-8 lg:mt-0">
                <h3 class="text-lg font-bold text-gray-800 flex items-center mb-4"><span class="bg-pink-500 text-white rounded-full h-6 w-6 text-sm flex items-center justify-center mr-3">2</span> Build Your Squads</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-bold text-center mb-2">Player Pool</h4>
                        <div class="p-4 bg-gray-50 rounded-lg min-h-[200px]">
                            <div class="flex gap-2 mb-4">
                                <input type="text" x-model="newPlayerName" @keydown.enter="addPlayer" placeholder="Enter player name..." class="w-full p-2 border rounded-md text-gray-800">
                                <button @click="addPlayer" class="bg-blue-600 text-white px-4 rounded-md hover:bg-blue-700 font-bold transition"><i class="fas fa-plus"></i></button>
                            </div>
                            <div class="space-y-2 h-64 overflow-y-auto pr-2">
                                <template x-if="matchConfig.playerPool.length === 0"><p class="text-center text-sm text-gray-400 pt-8">Add players to get started.</p></template>
                                <template x-for="player in matchConfig.playerPool" :key="player.id">
                                    <div class="flex justify-between items-center p-2 bg-white rounded-md shadow-sm" x-transition><span class="font-semibold text-gray-800" x-text="player.name"></span><div class="flex gap-1"><button @click="assignPlayer(player.id, 'A')" class="h-8 w-8 bg-green-500 text-white rounded text-xs font-bold hover:bg-green-600 transition">A</button><button @click="assignPlayer(player.id, 'B')" class="h-8 w-8 bg-yellow-500 text-white rounded text-xs font-bold hover:bg-yellow-600 transition">B</button></div></div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-bold text-center mb-2 text-gray-800" x-text="matchConfig.teamA.name || 'Team A'"></h4>
                            <div class="p-4 bg-green-50 rounded-lg min-h-[150px] border-2 border-green-200 h-40 overflow-y-auto pr-2">
                                <div class="space-y-2">
                                    <template x-if="matchConfig.teamA.players.length === 0"><p class="text-center text-sm text-green-800 opacity-70 pt-8">Assign players.</p></template>
                                    <template x-for="player in matchConfig.teamA.players" :key="player.id">
                                        <div class="flex justify-between items-center p-2 bg-white rounded-md shadow-sm" x-transition><span class="font-semibold text-gray-800" x-text="player.name"></span><button @click="unassignPlayer(player.id, 'A')" class="text-red-500 hover:text-red-700 transition"><i class="fas fa-times-circle"></i></button></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-bold text-center mb-2 text-gray-800" x-text="matchConfig.teamB.name || 'Team B'"></h4>
                            <div class="p-4 bg-yellow-50 rounded-lg min-h-[150px] border-2 border-yellow-200 h-40 overflow-y-auto pr-2">
                                <div class="space-y-2">
                                    <template x-if="matchConfig.teamB.players.length === 0"><p class="text-center text-sm text-yellow-800 opacity-70 pt-8">Assign players.</p></template>
                                    <template x-for="player in matchConfig.teamB.players" :key="player.id">
                                        <div class="flex justify-between items-center p-2 bg-white rounded-md shadow-sm" x-transition><span class="font-semibold text-gray-800" x-text="player.name"></span><button @click="unassignPlayer(player.id, 'B')" class="text-red-500 hover:text-red-700 transition"><i class="fas fa-times-circle"></i></button></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="step-card bg-white rounded-xl shadow-lg p-6 lg:col-span-1 mt-8 lg:mt-0" :class="{'opacity-50': !tossUnlocked()}">
                 <h3 class="text-lg font-bold text-gray-800 flex items-center"><span class="bg-pink-500 text-white rounded-full h-6 w-6 text-sm flex items-center justify-center mr-3">3</span> Finalize with Toss</h3>
                <div x-show="!tossUnlocked()" class="text-center text-sm text-gray-500 mt-4 p-4 bg-gray-50 rounded-lg">
                    <i class="fas fa-lock mr-2"></i> Add at least one player to each team to unlock.
                </div>
                <div x-show="tossUnlocked()" x-transition class="space-y-4 mt-4">
                    <div class="pt-4 border-t">
                        <label for="naabaad-rule" class="flex items-center space-x-3 cursor-pointer">
                            <input id="naabaad-rule" type="checkbox" x-model="matchConfig.enableNaabaad" class="h-5 w-5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                            <span class="text-sm font-medium text-gray-700">Enable 'Naabaad' Rule (Last batsman can bat alone)</span>
                        </label>
                    </div>
                    <p class="text-sm text-gray-500">Who won the toss?</p>
                    <div class="grid grid-cols-2 gap-4">
                        <button @click="matchConfig.toss.winner = 'A'" class="toss-btn p-4 rounded-lg font-bold transition text-gray-700" :class="{'selected bg-green-100 text-green-800': matchConfig.toss.winner === 'A', 'bg-gray-100': matchConfig.toss.winner !== 'A'}"><span x-text="matchConfig.teamA.name || 'Team A'"></span></button>
                        <button @click="matchConfig.toss.winner = 'B'" class="toss-btn p-4 rounded-lg font-bold transition text-gray-700" :class="{'selected bg-yellow-100 text-yellow-800': matchConfig.toss.winner === 'B', 'bg-gray-100': matchConfig.toss.winner !== 'B'}"><span x-text="matchConfig.teamB.name || 'Team B'"></span></button>
                    </div>
                    <div x-show="matchConfig.toss.winner" x-transition class="pt-4 border-t">
                        <p class="text-sm text-gray-800 mb-2"><strong x-text="matchConfig.toss.winner === 'A' ? matchConfig.teamA.name : matchConfig.teamB.name"></strong> elected to...</p>
                         <div class="grid grid-cols-2 gap-4">
                            <button @click="matchConfig.toss.decision = 'Bat'" class="p-4 rounded-lg font-bold transition text-gray-800" :class="{'selected bg-pink-500 text-white': matchConfig.toss.decision === 'Bat', 'bg-gray-100': matchConfig.toss.decision !== 'Bat'}">Bat</button>
                            <button @click="matchConfig.toss.decision = 'Bowl'" class="p-4 rounded-lg font-bold transition text-gray-800" :class="{'selected bg-blue-500 text-white': matchConfig.toss.decision === 'Bowl', 'bg-gray-100': matchConfig.toss.decision !== 'Bowl'}">Bowl</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-between items-center">
             <button @click="currentStep = 'start'" class="bg-gray-200 text-gray-800 font-bold py-4 px-8 rounded-xl hover:bg-gray-300 transition">Back</button>

            <button @click="startMatch()" :disabled="!isStepTwoValid()" class="bg-pink-600 hover:bg-pink-700 text-white font-bold text-lg py-4 px-8 rounded-xl transition-all shadow-lg hover:shadow-xl disabled:bg-gray-400 disabled:cursor-not-allowed disabled:shadow-md">
                <span>Start Match & Go to Scoreboard <i class="fas fa-arrow-right ml-2"></i></span>
            </button>
        </div>
    </div>
    <style>
        .step-card {
            width: 100%;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .toss-btn.selected {
            transform: scale(1.05);
            box-shadow: 0 0 0 3px #db2777;
        }
        @media (min-width: 1024px) {
            .step-card-container {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 2rem;
                align-items: start;
            }
            .step-card.lg\:col-span-2 {
                margin-top: 0;
            }
        }
    </style>
</div>