/**
 * Script for CricScore V2 Desktop UI Interactivity
 */
document.addEventListener('DOMContentLoaded', function() {
    try {
        const userMenuTrigger = document.getElementById('user-menu-trigger');
        const userDropdownMenu = document.getElementById('user-dropdown-menu');

        if (userMenuTrigger && userDropdownMenu) {
            const userMenuWrapper = userMenuTrigger.closest('.user-menu-wrapper');

            if (userMenuWrapper) {
                // Show dropdown on hover
                userMenuWrapper.addEventListener('mouseenter', () => {
                    userDropdownMenu.classList.add('visible');
                });

                // Hide dropdown on mouse leave, unless it's "sticky"
                userMenuWrapper.addEventListener('mouseleave', () => {
                    if (!userDropdownMenu.classList.contains('is-sticky')) {
                        userDropdownMenu.classList.remove('visible');
                    }
                });
            }

            // Toggle the "sticky" state on click
            userMenuTrigger.addEventListener('click', (event) => {
                event.stopPropagation();
                userDropdownMenu.classList.toggle('is-sticky');
                // If we are making it sticky, ensure it's visible
                if (userDropdownMenu.classList.contains('is-sticky')) {
                    userDropdownMenu.classList.add('visible');
                }
            });

            // Close dropdown if clicked outside (and remove sticky state)
            document.addEventListener('click', (event) => {
                if (userDropdownMenu.classList.contains('is-sticky')) {
                    if (!userMenuTrigger.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                        userDropdownMenu.classList.remove('visible');
                        userDropdownMenu.classList.remove('is-sticky');
                    }
                }
            });
        }
    } catch (err) {
        console.error('CricScore desktop UI init error', err);
    }
});