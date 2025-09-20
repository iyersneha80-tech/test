<?php
/**
 * The footer for the user dashboard.
 *
 * @package CricScore
 * @version 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
        </main>
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-copyright">
                    &copy; <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); ?>. All Rights Reserved.
                </div>
                <div class="footer-links">
                    <a href="#">Terms of Use</a>
                    <a href="#">Privacy Policy</a>
                </div>
            </div>
        </footer>
    </div> 
</div> 
<?php wp_footer(); // For loading scripts ?>
</body>
</html>