<div class="flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800">Start a New Match</h1>
            <p class="text-gray-500 mt-2">Create a live scorecard for any cricket match.</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-1">1. Select Match Type</h3>
            <p class="text-sm text-gray-500 mb-4">Choose the core format of your game.</p>
            <div class="grid grid-cols-2 gap-4">
                <label @click="matchConfig.format = 'limited'" class="format-label cursor-pointer p-4 border-2 rounded-lg text-center" :class="{ 'selected': matchConfig.format === 'limited' }">
                    <i class="fas fa-stopwatch fa-2x mb-2 text-pink-500"></i>
                    <span class="font-bold block text-sm text-gray-800">Limited Overs</span>
                </label>
                <label @click="matchConfig.format = 'test'" class="format-label cursor-pointer p-4 border-2 rounded-lg text-center text-gray-700" :class="{ 'selected': matchConfig.format === 'test' }">
                    <i class="fas fa-calendar-day fa-2x mb-2 text-blue-500"></i>
                    <span class="font-bold block text-sm">Test Match</span>
                </label>
            </div>
        </div>

        <div x-show="matchConfig.format" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-1">2. Set Match Rules</h3>
            <p class="text-sm text-gray-500 mb-4">Customize the game to your exact needs.</p>

            <div class="space-y-4" x-show="matchConfig.format === 'limited'" x-transition>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Overs</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="o in [10, 20, 50]">
                            <button @click="setOvers(o)" :class="{'selected': matchConfig.totalOvers === o}" class="overs-btn flex-1 h-12 rounded-lg font-bold bg-gray-100 hover:bg-pink-100 transition text-gray-700">T<span x-text="o"></span></button>
                        </template>
                        <input x-model="matchConfig.customOvers" @input="matchConfig.totalOvers = null" type="number" class="flex-1 h-12 w-full mt-2 md:mt-0 p-3 bg-white border border-gray-300 rounded-lg shadow-sm text-center" placeholder="Custom">
                    </div>
                </div>
                <div>
                    <label for="max-overs" class="block text-sm font-medium text-gray-700 mb-2">Max Overs per Bowler <span class="text-gray-400">(Optional)</span></label>
                    <input type="number" id="max-overs" x-model="matchConfig.maxOversPerBowler" class="w-full p-3 bg-white border border-gray-300 rounded-lg shadow-sm text-gray-800" placeholder="e.g., 4 for a T20">
                </div>
            </div>

            <div class="space-y-4" x-show="matchConfig.format === 'test'" x-transition>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Innings</label>
                    <p class="font-semibold text-md p-3 bg-gray-100 rounded-lg text-gray-800">2 Innings per side (Standard)</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="test-days" class="block text-sm font-medium text-gray-700 mb-2">Days</label>
                        <input x-model="matchConfig.testDays" type="number" id="test-days" class="w-full p-3 bg-white border border-gray-300 rounded-lg shadow-sm text-gray-800" placeholder="e.g., 5">
                    </div>
                    <div>
                        <label for="overs-per-day" class="block text-sm font-medium text-gray-700 mb-2">Overs/Day</label>
                        <input x-model="matchConfig.oversPerDay" type="number" id="overs-per-day" class="w-full p-3 bg-white border border-gray-300 rounded-lg shadow-sm text-gray-800" placeholder="e.g., 90">
                    </div>
                </div>
            </div>
        </div>

        <button @click="proceedToSetup()" :disabled="!isStepOneValid()" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold text-lg py-4 px-4 rounded-xl transition-all shadow-lg hover:shadow-xl disabled:bg-gray-400 disabled:cursor-not-allowed disabled:shadow-md flex items-center justify-center h-16">
            <span>Next: Team Setup <i class="fas fa-arrow-right ml-2"></i></span>
        </button>

    </div>
    
    <style>
        .format-label { transition: all 0.2s ease-in-out; }
        .format-label.selected { 
            border-color: #db2777; 
            background-color: #fdf2f8; 
            color: #9d174d;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(219, 39, 119, 0.1);
        }
        .overs-btn.selected { 
            background-color: #db2777; 
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</div>