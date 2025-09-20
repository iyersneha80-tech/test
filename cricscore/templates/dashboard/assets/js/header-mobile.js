/**
 * Script for CricScore V2 Mobile UI
 */
(function() {
    try {
        // --- Mobile Header Dropdown ---
        const trigger = document.getElementById('user-menu-trigger-mobile');
        const menu = document.getElementById('user-dropdown-menu-mobile');

        if (trigger && menu) {
            // Function to toggle the menu's visibility
            const toggleMenu = (event) => {
                event.stopPropagation();
                menu.classList.toggle('visible');
                trigger.classList.toggle('active');
            };
            trigger.addEventListener('click', toggleMenu);

            // Global click listener to close the menu if clicking outside
            document.addEventListener('click', (e) => {
                if (!menu.contains(e.target) && !trigger.contains(e.target)) {
                    if (menu.classList.contains('visible')) {
                        menu.classList.remove('visible');
                        trigger.classList.remove('active');
                    }
                }
            });
        }

        // --- Bottom Sheet ---
        var moreSheet = document.getElementById('lr-bottom-more-sheet');
        var backdrop = document.getElementById('lr-bottom-sheet-backdrop');
        var moreTrigger = document.getElementById('lr-more-trigger');
        var closeBtn = document.getElementById('lr-sheet-close-btn');

        if(moreSheet && backdrop && moreTrigger && closeBtn) {
            function openSheet() {
                moreSheet.hidden = false;
                backdrop.hidden = false;
                requestAnimationFrame(function() {
                    moreSheet.classList.add('is-open');
                });
            }

            function closeSheet() {
                moreSheet.classList.remove('is-open');
                setTimeout(function() {
                    moreSheet.hidden = true;
                    backdrop.hidden = true;
                }, 200);
            }

            moreTrigger.addEventListener('click', openSheet);
            backdrop.addEventListener('click', closeSheet);
            closeBtn.addEventListener('click', closeSheet);
            
            // Close on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeSheet();
            });
        }

    } catch (err) {
        console.error('CricScore mobile UI init error', err);
    }
})();