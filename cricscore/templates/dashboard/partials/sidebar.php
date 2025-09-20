<?php
/**
 * The main sidebar for the user dashboard.
 * @package CricScore
 * @version 2.0.0
 */
$current_view = get_query_var( 'dashboard_view', 'main' );
?>
<aside class="sidebar">
    <div class="sidebar-header">
         <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="sidebar-logo-link">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin: 0;">CricScore</h2>
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item <?php echo 'main' === $current_view ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>"><i class="fas fa-home"></i><span>Dashboard</span></a>
            </li>
            <li class="nav-item <?php echo 'my-matches' === $current_view ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( home_url( '/dashboard/my-matches/' ) ); ?>"><i class="fas fa-trophy"></i><span>My Matches</span></a>
            </li>
             <li class="nav-item <?php echo 'create-match' === $current_view ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( home_url( '/dashboard/create-match/' ) ); ?>"><i class="fas fa-plus"></i><span>Create Match</span></a>
            </li>

            <li class="nav-divider"><hr></li>

            <li class="nav-item <?php echo 'my-teams' === $current_view ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( home_url( '/dashboard/my-teams/' ) ); ?>"><i class="fas fa-users"></i><span>My Teams</span></a>
            </li>
            <li class="nav-item <?php echo 'my-players' === $current_view ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( home_url( '/dashboard/my-players/' ) ); ?>"><i class="fas fa-user"></i><span>My Players</span></a>
            </li>
            <li class="nav-item <?php echo 'my-venues' === $current_view ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( home_url( '/dashboard/my-venues/' ) ); ?>"><i class="fas fa-map-marker-alt"></i><span>My Venues</span></a>
            </li>
            <li class="nav-item <?php echo 'my-tournaments' === $current_view ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( home_url( '/dashboard/my-tournaments/' ) ); ?>"><i class="fas fa-sitemap"></i><span>My Tournaments</span></a>
            </li>
        </ul>
    </nav>
</aside>