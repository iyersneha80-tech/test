<?php
/**
 * Mobile Header Partial for CricScore V2 Dashboard
 *
 * @package CricScore
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$current_user = wp_get_current_user();
?>

<header class="header-mobile" role="banner" aria-label="Mobile Header">
  <div class="hm-left">
    <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" style="text-decoration:none;">
        <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin: 0;">CricScore</h2>
    </a>
  </div>
  <div class="hm-right">
    <div class="user-menu-wrapper">
      <div class="user-menu-trigger-panel" id="user-menu-trigger-mobile">
        <?php echo get_avatar( $current_user->ID, 36, '', 'User Avatar', ['class' => 'user-avatar'] ); ?>
      </div>
      <div class="user-dropdown-menu-panel" id="user-dropdown-menu-mobile">
        <div class="dropdown-user-info">
            <p class="user-name"><?php echo esc_html($current_user->display_name); ?></p>
            <p class="user-email"><?php echo esc_html($current_user->user_email); ?></p>
        </div>
        <ul class="user-dropdown-nav">
            <li><a href="<?php echo wp_logout_url( home_url( '/' ) ); ?>" class="user-dropdown-link logout-link"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
      </div>
    </div>
  </div>
</header>