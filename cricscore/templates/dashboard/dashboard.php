<?php
/**
 * Main template for the CricScore user dashboard.
 * Acts as a PHP-based router to display the correct view.
 *
 * @package CricScore
 * @version 2.0.0
 */

// Security Check: Redirect to login page if user is not logged in.
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url( home_url( '/dashboard/' ) ) );
    exit;
}

// Get the current view from the URL (e.g., 'my-teams', 'my-players')
$view = get_query_var( 'dashboard_view', 'main' );
$view_file = '';
$page_title = 'Dashboard';

// Determine the page title and which view file to load (Original functionality preserved)
switch ( $view ) {
    case 'my-matches':
        $page_title = 'My Matches';
        $view_file = __DIR__ . '/my-matches/my-matches-loader.php';
        break;
    case 'create-match':
        $page_title = 'Create New Match';
        $view_file = __DIR__ . '/create-match/create-match-loader.php';
    break;
    case 'my-teams':
        $page_title = 'My Teams';
        $view_file = __DIR__ . '/my-teams/my-teams-loader.php';
        break;
    case 'my-players':
        $page_title = 'My Players';
        $view_file = __DIR__ . '/my-players/my-players-loader.php';
        break;
    case 'my-venues':
        $page_title = 'My Venues';
        $view_file = __DIR__ . '/my-venues/my-venues-loader.php';
        break;
    case 'my-tournaments':
        $page_title = 'My Tournaments';
        $view_file = __DIR__ . '/my-tournaments/my-tournaments-loader.php';
        break;
}

// NEW: Conditionally load the correct header and opening tags
// On mobile, this will load the mobile-only top bar.
// On desktop, this loads the full header with the sidebar.
if ( wp_is_mobile() ) {
    include_once __DIR__ . '/partials/header-mobile.php';
    // On mobile, we still need the main layout structure that header.php provides.
    // So we include a lightweight version of the HTML structure here.
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
        <div class="content-wrapper">
             <main class="main-content">
    <?php
} else {
    // On desktop, load the full header which includes the sidebar.
    include_once __DIR__ . '/partials/header.php';
}


// Load the correct content based on the view (Original functionality preserved)
if ( ! empty( $view_file ) && file_exists( $view_file ) ) {
    include_once $view_file;
} else {
    // This is the default content for the main /dashboard/ page
    echo '<div class="cricscore-card"><div class="cricscore-card-content"><p>Welcome to your Dashboard! Select an option from the menu.</p></div></div>';
}

// NEW: Load the standard footer which closes the main content area.
include_once __DIR__ . '/partials/footer.php';

// NEW: Load mobile-only navigation components at the very end of the body.
if ( wp_is_mobile() ) {
    if ( file_exists( __DIR__ . '/partials/bottom-nav.php' ) ) {
        include_once __DIR__ . '/partials/bottom-nav.php';
    }
    if ( file_exists( __DIR__ . '/partials/bottom-sheet.php' ) ) {
        include_once __DIR__ . '/partials/bottom-sheet.php';
    }
}