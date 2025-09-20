<?php
/**
 * The view file for the public player profile.
 *
 * @package CricScore
 * @version 1.0.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profile</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'cricscore-player-profile' ); ?>>

    <main class="profile-container" id="profile-container" style="display: none;">
        
        <aside class="player-info-card">
            <div class="player-avatar">
                <img src="" alt="Player Avatar" id="player-avatar-img">
            </div>
            <h1 class="player-name" id="player-name"></h1>
            <p class="player-role" id="player-role"></p>
            <ul class="player-details">
                <li>
                    <span class="label">Age</span>
                    <span class="value" id="player-age"></span>
                </li>
                <li>
                    <span class="label">Country</span>
                    <span class="value" id="player-country"></span>
                </li>
                <li>
                    <span class="label">Batting Style</span>
                    <span class="value" id="player-batting-style"></span>
                </li>
                <li>
                    <span class="label">Bowling Style</span>
                    <span class="value" id="player-bowling-style"></span>
                </li>
            </ul>
        </aside>

        <section class="stats-container">
            
            <div class="stats-card">
                <h2 class="stats-header"><i class="fa-solid fa-person-running"></i>Batting Career</h2>
                <div class="stats-grid" id="batting-stats-grid">
                    </div>
            </div>

            <div class="stats-card">
                <h2 class="stats-header"><i class="fa-solid fa-baseball"></i>Bowling Career</h2>
                <div class="stats-grid" id="bowling-stats-grid">
                    </div>
            </div>

        </section>
        <section class="match-log-container stats-card">
        <h2 class="stats-header"><i class="fa-solid fa-history"></i> Recent Matches</h2>
        <div id="match-log-table-container">
            </div>
    </section>

    </main>
    
    <div id="loading-message" style="text-align: center; padding: 4rem; font-family: sans-serif; color: #6b7280;">
        <p>Loading Player Profile...</p>
    </div>

    <div id="error-message" style="display: none; text-align: center; padding: 4rem; font-family: sans-serif; color: #ef4444;">
        <h2>Oops! Player Not Found</h2>
        <p>The player profile you are looking for does not exist or could not be loaded.</p>
    </div>

    <?php wp_footer(); ?>
</body>
</html>