/**
 * A simplified test function for debugging.
 */
function cricscoreApp() {
  console.log('cricscoreApp function is running!');

  return {
    message: 'If you can see this, Alpine.js is working!',
    currentStep: 'setup', // Keep this to prevent other errors
    matchConfig: {
      teamA: { name: 'Test Team A', players: [] },
      teamB: { name: 'Test Team B', players: [] },
      playerPool: []
    }
  };
}