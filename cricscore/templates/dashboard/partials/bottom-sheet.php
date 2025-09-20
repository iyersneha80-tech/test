<?php
/**
 * Mobile Bottom Sheet for CricScore V2 Dashboard
 * @package CricScore
 */
if (!defined('ABSPATH')) { exit; } 
?>
<div id="lr-bottom-sheet-backdrop" class="lr-bottom-sheet-backdrop" hidden></div>
<section id="lr-bottom-more-sheet" class="lr-bottom-more-sheet" role="dialog" aria-modal="true" aria-labelledby="lr-more-title" hidden>
  <div class="lr-sheet-handle" aria-hidden="true"></div>
  <header class="lr-sheet-header">
    <h3 id="lr-more-title">More Navigation</h3>
    <button type="button" class="lr-sheet-close" id="lr-sheet-close-btn" aria-label="Close"><i class="fas fa-times"></i></button>
  </header>
  <div class="lr-sheet-grid">
    <a href="<?php echo esc_url( home_url( '/dashboard/my-players/' ) ); ?>" class="lr-sheet-item" ><i class="fas fa-user"></i><span>My Players</span></a>
    <a href="<?php echo esc_url( home_url( '/dashboard/my-venues/' ) ); ?>" class="lr-sheet-item"><i class="fas fa-map-marker-alt"></i><span>My Venues</span></a>
    <a href="<?php echo esc_url( home_url( '/dashboard/my-tournaments/' ) ); ?>" class="lr-sheet-item"><i class="fas fa-sitemap"></i><span>My Tournaments</span></a>
  </div>
</section>