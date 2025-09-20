/**
 * Handles all functionality for the advanced "My Players" page.
 *
 * @package CricScore
 * @version 1.3.0
 */
document.addEventListener('DOMContentLoaded', function () {
    const apiBase = '/wp-json/cricscore/v1';
    const nonce = cricscore_dashboard.nonce;

    // --- Element Selectors ---
    const playersList = document.getElementById('my-players-list');
    
    // Add Form Elements
    const addPlayerForm = document.getElementById('add-player-form');
    const addFormMessage = document.getElementById('add-form-message');
    const playerRoleSelect = document.getElementById('player-role');
    const battingStyleGroup = document.getElementById('batting-style-group');
    const bowlingStyleGroup = document.getElementById('bowling-style-group');
    const profileImageInput = document.getElementById('profile-image');
    const imagePreview = document.getElementById('image-preview');
    const profileImageUrlInput = document.getElementById('profile-image-url');

    // Edit Modal Elements
    const editModal = document.getElementById('edit-player-modal');
    const editPlayerForm = document.getElementById('edit-player-form');
    const editFormMessage = document.getElementById('edit-form-message');
    const closeModal = editModal.querySelector('.modal-close');
    const editProfileImageInput = document.getElementById('edit-profile-image');
    const editImagePreview = document.getElementById('edit-image-preview');
    const editProfileImageUrlInput = document.getElementById('edit-profile-image-url');

    // --- Main Function to Fetch and Display Players ---
    async function fetchPlayers() {
        try {
            const response = await fetch(`${apiBase}/players`, { headers: { 'X-WP-Nonce': nonce } });
            if (!response.ok) throw new Error('Failed to fetch players.');
            const players = await response.json();

            playersList.innerHTML = ''; // Clear list

            if (players.length > 0) {
                players.forEach(player => {
                    const row = document.createElement('tr');
                    const age = player.dob ? calculateAge(player.dob) : 'N/A';
                    const profileImg = player.profile_image_url ? `<img src="${player.profile_image_url}" class="player-avatar" alt="${player.name}" />` : '';

                    row.innerHTML = `
                        <td><div class="player-info">${profileImg} ${player.name}</div></td>
                        <td>${player.role || 'N/A'}</td>
                        <td>${age}</td>
                        <td>
                            <button class="button-secondary button-small edit-btn" data-player='${JSON.stringify(player)}'>Edit</button>
                            <button class="button-danger button-small delete-btn" data-id="${player.id}">Delete</button>
                        </td>
                        <td>
                            <button class="button-secondary button-small copy-link-btn" data-id="${player.id}" data-slug="${player.share_slug}">Copy Link</button>
                        </td>
                    `;
                    playersList.appendChild(row);
                });
            } else {
                playersList.innerHTML = '<tr><td colspan="5">You have not created any players yet.</td></tr>';
            }
        } catch (error) {
            playersList.innerHTML = `<tr><td colspan="5">Error: ${error.message}</td></tr>`;
        }
    }

    // --- Form Logic & Event Handlers ---
    addPlayerForm.addEventListener('submit', handleAddPlayerSubmit);
    editPlayerForm.addEventListener('submit', handleEditPlayerSubmit);

    playerRoleSelect.addEventListener('change', () => handleRoleChange(playerRoleSelect.value, battingStyleGroup, bowlingStyleGroup));
    document.getElementById('edit-player-role').addEventListener('change', (e) => handleRoleChange(e.target.value, document.getElementById('edit-batting-style-group'), document.getElementById('edit-bowling-style-group')));

    function handleRoleChange(role, battingGroup, bowlingGroup) {
        const canBat = ['Batsman', 'All-Rounder', 'Wicket-Keeper'].includes(role);
        const canBowl = ['Bowler', 'All-Rounder'].includes(role);
        battingGroup.style.display = canBat ? 'block' : 'none';
        bowlingGroup.style.display = canBowl ? 'block' : 'none';
    }

    profileImageInput.addEventListener('change', function() {
        handleImageUpload(this, addFormMessage, imagePreview, profileImageUrlInput);
    });
    editProfileImageInput.addEventListener('change', function() {
        handleImageUpload(this, editFormMessage, editImagePreview, editProfileImageUrlInput);
    });

    async function handleImageUpload(inputElement, messageElement, previewElement, urlInputElement) {
        if (inputElement.files.length === 0) return;
        const file = inputElement.files[0];
        const formData = new FormData();
        formData.append('file', file);
        messageElement.textContent = 'Uploading image...';
        messageElement.className = 'form-message notice notice-info';
        try {
            const response = await fetch(`${apiBase}/media/upload`, { method: 'POST', headers: { 'X-WP-Nonce': nonce }, body: formData });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Image upload failed.');
            urlInputElement.value = result.url;
            previewElement.innerHTML = `<img src="${result.url}" alt="Preview"/>`;
            messageElement.textContent = 'Image handled successfully.';
            messageElement.className = 'form-message notice notice-success';
        } catch (error) {
            messageElement.textContent = `Error: ${error.message}`;
            messageElement.className = 'form-message notice notice-error';
            inputElement.value = '';
        }
    }

    async function handleAddPlayerSubmit(event) {
        event.preventDefault();
        addFormMessage.textContent = 'Saving player...';
        addFormMessage.className = 'form-message notice notice-info';
        const playerData = {
            name: document.getElementById('player-name').value,
            dob: document.getElementById('player-dob').value,
            role: document.getElementById('player-role').value,
            country: document.getElementById('player-country').value,
            profile_image_url: profileImageUrlInput.value,
        };
        if (battingStyleGroup.style.display === 'block') playerData.batting_style = document.getElementById('batting-style').value;
        if (bowlingStyleGroup.style.display === 'block') playerData.bowling_style = document.getElementById('bowling-style').value;
        try {
            const response = await fetch(`${apiBase}/players`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce }, body: JSON.stringify(playerData) });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'An error occurred.');
            addFormMessage.textContent = 'Player saved successfully!';
            addFormMessage.className = 'form-message notice notice-success';
            addPlayerForm.reset();
            imagePreview.innerHTML = '';
            profileImageUrlInput.value = '';
            battingStyleGroup.style.display = 'none';
            bowlingStyleGroup.style.display = 'none';
            fetchPlayers();
        } catch (error) {
            addFormMessage.textContent = `Error: ${error.message}`;
            addFormMessage.className = 'form-message notice notice-error';
        }
    }
    
    playersList.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-btn')) {
            if (confirm('Are you sure you want to delete this player?')) {
                deletePlayer(e.target.dataset.id);
            }
        }
        if (e.target.classList.contains('edit-btn')) {
            openEditModal(JSON.parse(e.target.dataset.player));
        }
        if (e.target.classList.contains('copy-link-btn')) {
            const playerId = e.target.dataset.id;
            const slug = e.target.dataset.slug;
            const shareableLink = `${window.location.origin}/player/${playerId}-${slug}/`;
            navigator.clipboard.writeText(shareableLink).then(() => {
                const originalText = e.target.textContent;
                e.target.textContent = 'Copied!';
                setTimeout(() => { e.target.textContent = originalText; }, 2000);
            }).catch(err => console.error('Failed to copy text: ', err));
        }
    });

    async function deletePlayer(playerId) {
        try {
            const response = await fetch(`${apiBase}/players/${playerId}`, { method: 'DELETE', headers: { 'X-WP-Nonce': nonce } });
            if (!response.ok) throw new Error((await response.json()).message || 'Failed to delete player.');
            fetchPlayers();
        } catch (error) {
            alert(`Error: ${error.message}`);
        }
    }

    function openEditModal(player) {
        document.getElementById('edit-player-id').value = player.id;
        document.getElementById('edit-player-name').value = player.name;
        document.getElementById('edit-player-dob').value = player.dob ? player.dob.split(' ')[0] : '';
        document.getElementById('edit-player-role').value = player.role;
        document.getElementById('edit-player-country').value = player.country;
        editProfileImageUrlInput.value = player.profile_image_url || '';
        editImagePreview.innerHTML = player.profile_image_url ? `<img src="${player.profile_image_url}" alt="Profile Preview"/>` : '';
        document.getElementById('edit-player-role').dispatchEvent(new Event('change'));
        document.getElementById('edit-batting-style').value = player.batting_style;
        document.getElementById('edit-bowling-style').value = player.bowling_style;
        editModal.style.display = 'block';
    }

    closeModal.onclick = () => editModal.style.display = 'none';
    window.onclick = (e) => { if (e.target == editModal) editModal.style.display = 'none'; };

    async function handleEditPlayerSubmit(event) {
        event.preventDefault();
        editFormMessage.textContent = 'Updating...';
        const playerId = document.getElementById('edit-player-id').value;
        const editBattingGroup = document.getElementById('edit-batting-style-group');
        const editBowlingGroup = document.getElementById('edit-bowling-style-group');
        const playerData = {
            name: document.getElementById('edit-player-name').value,
            dob: document.getElementById('edit-player-dob').value,
            role: document.getElementById('edit-player-role').value,
            country: document.getElementById('edit-player-country').value,
            profile_image_url: editProfileImageUrlInput.value,
        };
        if (editBattingGroup.style.display === 'block') playerData.batting_style = document.getElementById('edit-batting-style').value;
        if (editBowlingGroup.style.display === 'block') playerData.bowling_style = document.getElementById('edit-bowling-style').value;
        try {
            const response = await fetch(`${apiBase}/players/${playerId}`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce }, body: JSON.stringify(playerData) });
            if (!response.ok) throw new Error((await response.json()).message || 'Failed to update player.');
            editFormMessage.textContent = 'Player updated!';
            setTimeout(() => {
                editModal.style.display = 'none';
                editFormMessage.textContent = '';
                fetchPlayers();
            }, 1000);
        } catch (error) {
            editFormMessage.textContent = `Error: ${error.message}`;
        }
    }

    function calculateAge(dobString) {
        if (!dobString) return 'N/A';
        const dob = new Date(dobString);
        const diff_ms = Date.now() - dob.getTime();
        const age_dt = new Date(diff_ms);
        return Math.abs(age_dt.getUTCFullYear() - 1970);
    }
    
    fetchPlayers();
});