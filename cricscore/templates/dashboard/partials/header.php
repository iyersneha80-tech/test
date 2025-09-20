<?php
/**
 * The header for the user dashboard.
 * @package CricScore
 * @version 2.0.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CricScore Dashboard</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'cricscore-dashboard' ); ?>>

<div class="dashboard-app">
    <?php include_once __DIR__ . '/sidebar.php'; ?>
    <div class="content-wrapper">
        <header class="header">
            <div class="header-left">
                 <h1 id="dashboard-page-title" style="margin: 0; font-size: 1.5rem; color: var(--text-primary);">Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="user-menu-wrapper">
                    <div class="user-menu" id="user-menu-trigger">
                        <?php echo get_avatar( get_current_user_id(), 36, '', 'User Avatar', ['class' => 'user-avatar'] ); ?>
                        <span style="color: var(--text-primary); font-weight: 500;"><?php echo esc_html( wp_get_current_user()->display_name ); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
                    </div>
                    <div class="user-dropdown" id="user-dropdown-menu">
                        <ul class="user-dropdown-nav">
                             <div class="dropdown-group">
                                <li><a href="<?php echo wp_logout_url( home_url() ); ?>"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
                            </div>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
        <main class="main-content">