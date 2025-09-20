<?php
/**
 * Partial for the Match End screen (Refactored with Team Editing and Robust Rematch).
 *
 * Displays a detailed match summary and provides options for a rematch or starting over.
 *
 * @package CricScore
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="min-h-screen bg-gray-900 text-white p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">

        <!-- Match Result Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-pink-500">Match Over</h1>
            <p x-text="matchSummaryData.matchResult" class="text-lg text-gray-300 mt-2"></p>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

            <!-- Left Column: Match Summary -->
            <div class="lg:col-span-3 bg-gray-800 p-6 rounded-2xl shadow-lg">
                <h2 class="text-2xl font-bold border-b border-gray-700 pb-3 mb-4">Match Summary</h2>
                <div id="match-summary-container" class="space-y-6">
                    <!-- Match summary content will be rendered here by module-match-summary.js -->
                </div>
            </div>

            <!-- Right Column: Next Match Options -->
            <div class="lg:col-span-2 bg-gray-800 p-6 rounded-2xl shadow-lg flex flex-col">
                <div class="flex-grow">
                    <h2 class="text-2xl font-bold border-b border-gray-700 pb-3 mb-4">Next Match</h2>

                    <div class="space-y-4">
                        <p class="text-gray-400 text-sm">
                           Start a new match with the same teams or go back to edit them first.
                        </p>
                        
                        <!-- THE ONLY CHANGE IS ON THE LINE BELOW -->
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <h3 class="font-bold text-lg" x-text="matchSummaryData.teams[1].name"></h3>
                                <ul class="mt-2 space-y-1 text-gray-300 text-sm">
                                    <template x-for="player in matchSummaryData.teams[1].squad" :key="player.id">
                                        <li x-text="player.name"></li>
                                    </template>
                                </ul>
                            </div>

                            <div>
                               <h3 class="font-bold text-lg" x-text="matchSummaryData.teams[2].name"></h3>
                                <ul class="mt-2 space-y-1 text-gray-300 text-sm">
                                    <template x-for="player in matchSummaryData.teams[2].squad" :key="player.id">
                                        <li x-text="player.name"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 pt-6 border-t border-gray-700 space-y-4">
                    <button @click="startRematch()" class="w-full text-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-transform duration-150 ease-in-out hover:scale-105">
                        <i class="fas fa-play mr-2"></i> Start Rematch (New Toss)
                    </button>

                    <button @click="downloadSummary()" class="w-full text-center bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition-transform duration-150 ease-in-out hover:scale-105">
                        <i class="fas fa-download mr-2"></i> Download Summary (PDF)
                    </button>
                    
                    <button @click="resetMatch()" class="w-full text-center bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-transform duration-150 ease-in-out hover:scale-105">
                        <i class="fas fa-power-off mr-2"></i> New Match From Scratch
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>