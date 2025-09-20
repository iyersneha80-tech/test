/**
 * Handles all functionality for the Virtual Coin Toss Modal.
 *
 * @package CricScore
 * @version 1.2.0
 */
document.addEventListener('DOMContentLoaded', function () {
    // --- DYNAMICALLY Preload all coin images for instant display ---
    (function preloadCoinImages() {
        // Check if the data object and the image_urls array from PHP exist
        if (typeof cricscore_coin_toss_data !== 'undefined' && Array.isArray(cricscore_coin_toss_data.image_urls)) {
            // Loop through each URL provided by PHP
            cricscore_coin_toss_data.image_urls.forEach(url => {
                // Create a new in-memory image for each URL
                const img = new Image();
                // Setting the src triggers the browser to download and cache it
                img.src = url;
            });
        }
    })();

    // --- Element Selectors for Coin Toss Modal ---
    const openModalBtn = document.getElementById('openTossModalBtn');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const coinTossModal = document.getElementById('coinTossModal');
    const spinBtn = document.getElementById('spinBtn');
    const coin = document.getElementById('coin');
    const resultText = document.getElementById('resultText');

    // Check if all required elements exist on the page before proceeding.
    // This prevents errors if the HTML isn't present.
    if (!openModalBtn || !modalBackdrop || !coinTossModal || !spinBtn || !coin || !resultText) {
        return; // Exit if any modal element is missing
    }

    let isFlipping = false;

    const openModal = () => {
        modalBackdrop.classList.add('visible');
    };

    const closeModal = () => {
        modalBackdrop.classList.remove('visible');
        // Reset the modal's state after the closing animation finishes
        setTimeout(() => {
            coin.style.transition = 'none'; // Disable transition for an instant reset
            coin.classList.remove('flipping');
            coin.style.transform = 'rotateY(0deg)';
            resultText.classList.remove('visible', 'heads', 'tails');
            resultText.textContent = '';
            coinTossModal.classList.remove('flash-heads', 'flash-tails');
            spinBtn.disabled = false;
            isFlipping = false;
        }, 300); // This duration should match the transition time in the CSS
    };

    const tossCoin = () => {
        if (isFlipping) {
            return; // Don't do anything if the coin is already flipping
        }

        isFlipping = true;
        spinBtn.disabled = true;
        resultText.classList.remove('visible', 'heads', 'tails');
        coinTossModal.classList.remove('flash-heads', 'flash-tails');
        
        // Reset animation state before starting a new flip
        coin.style.transition = 'none';
        coin.classList.remove('flipping');
        coin.style.transform = 'rotateY(0deg)';

        // This is a trick to force the browser to restart the CSS animation
        void coin.offsetWidth; 

        const isHeads = Math.random() < 0.5;
        const finalRotation = isHeads ? 0 : 180;
        
        // Use a short timeout to ensure the styles are applied correctly before animating
        setTimeout(() => {
            coin.style.transition = 'transform 2.5s cubic-bezier(0.25, 1, 0.5, 1)';
            // The keyframe animation handles the spinning, this sets the final landing side
            coin.style.transform = `rotateY(${2880 + finalRotation}deg)`;
        }, 10);

        // Wait for the animation to complete before showing the result
        setTimeout(() => {
            resultText.textContent = isHeads ? 'Heads' : 'Tails';
            resultText.classList.add('visible', isHeads ? 'heads' : 'tails');
            coinTossModal.classList.add(isHeads ? 'flash-heads' : 'flash-tails');
            
            spinBtn.disabled = false;
            isFlipping = false;
        }, 2500); // This duration must match the animation duration in the CSS
    };

    // --- Event Listeners ---
    openModalBtn.addEventListener('click', openModal);
    
    modalBackdrop.addEventListener('click', (event) => {
        // Close the modal only if the dark backdrop area itself is clicked
        if (event.target === modalBackdrop) {
            closeModal();
        }
    });
    
    spinBtn.addEventListener('click', tossCoin);
});