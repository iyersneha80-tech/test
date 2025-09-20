<?php
/**
 * Mobile Bottom Navigation for CricScore V2 Dashboard
 * @package CricScore
 */
if (!defined('ABSPATH')) { exit; } 
?>
<nav id="lr-bottom-nav" aria-label="Primary" class="lr-bottom-nav" data-lr-bottom-nav>
  <div class="lr-bottom-nav-inner">
    <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="lr-nav-btn" aria-label="Home"><i class="fas fa-home"></i></a>
    <a href="<?php echo esc_url( home_url( '/dashboard/my-matches/' ) ); ?>" class="lr-nav-btn" aria-label="My Matches"><i class="fas fa-trophy"></i></a>
    <a href="<?php echo esc_url( home_url( '/dashboard/create-match/' ) ); ?>" class="lr-center-fab" aria-label="Create Match"><i class="fas fa-plus"></i></a>
    <a href="<?php echo esc_url( home_url( '/dashboard/my-teams/' ) ); ?>" class="lr-nav-btn" aria-label="My Teams"><i class="fas fa-users"></i></a>
    <button class="lr-nav-btn" type="button" id="lr-more-trigger" aria-label="More" title="More"><i class="fas fa-ellipsis-h"></i></button>
  </div>
</nav>