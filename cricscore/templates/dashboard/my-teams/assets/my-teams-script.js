/**
 * Handles all functionality for the advanced "My Teams" page.
 *
 * @package CricScore
 * @version 1.2.0
 */
document.addEventListener('DOMContentLoaded', function () {
    const apiBase = '/wp-json/cricscore/v1';
    const nonce = cricscore_dashboard.nonce;

    // --- Element Selectors ---
    const teamsList = document.getElementById('my-teams-list');
    
    // Add Form Elements
    const addTeamForm = document.getElementById('add-team-form');
    const addFormMessage = document.getElementById('add-form-message');
    const teamLogoInput = document.getElementById('team-logo');
    const logoPreview = document.getElementById('logo-preview');
    const logoUrlInput = document.getElementById('logo-url');
    const captainSelect = document.getElementById('team-captain');
    const viceCaptainSelect = document.getElementById('team-vice-captain');

    // Edit Modal Elements
    const editModal = document.getElementById('edit-team-modal');
    const editTeamForm = document.getElementById('edit-team-form');
    const editFormMessage = document.getElementById('edit-form-message');
    const closeModal = editModal.querySelector('.modal-close');
    const editCaptainSelect = document.getElementById('edit-team-captain');
    const editViceCaptainSelect = document.getElementById('edit-team-vice-captain');
    const editLogoInput = document.getElementById('edit-team-logo');
    const editLogoPreview = document.getElementById('edit-logo-preview');
    const editLogoUrlInput = document.getElementById('edit-logo-url');


    let playerPool = []; // To store the user's players

    // --- Main Functions ---

    // Fetch all user's players to populate dropdowns
    async function fetchPlayers() {
        try {
            const response = await fetch(`${apiBase}/players`, { headers: { 'X-WP-Nonce': nonce } });
            if (!response.ok) throw new Error('Could not fetch players for dropdowns.');
            playerPool = await response.json();
            populatePlayerDropdowns(playerPool);
        } catch (error) {
            console.error(error);
        }
    }

    // Populate all captain/vice-captain dropdowns
    function populatePlayerDropdowns(players) {
        const selects = [captainSelect, viceCaptainSelect, editCaptainSelect, editViceCaptainSelect];
        selects.forEach(select => {
            // Clear existing options except the first one
            while (select.options.length > 1) {
                select.remove(1);
            }
            players.forEach(player => {
                const option = document.createElement('option');
                option.value = player.id;
                option.textContent = player.name;
                select.appendChild(option);
            });
        });
    }

    // Fetch and display teams in the main list
    async function fetchTeams() {
        try {
            const response = await fetch(`${apiBase}/teams`, { headers: { 'X-WP-Nonce': nonce } });
            if (!response.ok) throw new Error('Failed to fetch teams.');
            const teams = await response.json();
            teamsList.innerHTML = '';

            if (teams.length > 0) {
                teams.forEach(team => {
                    const row = document.createElement('tr');
                    const logoImg = team.logo_url ? `<img src="${team.logo_url}" class="player-avatar" alt="${team.name} Logo" />` : '';
                    
                    // --- THIS IS THE CORRECTED LINE ---
                    const teamDataString = JSON.stringify(team).replace(/"/g, '&quot;');

                    row.innerHTML = `
                        <td><div class="player-info">${logoImg} ${team.name} (${team.short_name})</div></td>
                        <td>
                            <button class="button-secondary button-small edit-btn" data-team="${teamDataString}">Edit</button>
                            <button class="button-danger button-small delete-btn" data-id="${team.id}">Delete</button>
                        </td>
                        <td><button class="button-secondary button-small copy-link-btn" data-id="${team.id}" data-slug="${team.share_slug}">Copy Link</button></td>
                    `;
                    teamsList.appendChild(row);
                });
            } else {
                teamsList.innerHTML = '<tr><td colspan="3">You have not created any teams yet.</td></tr>';
            }
        } catch (error) {
            teamsList.innerHTML = `<tr><td colspan="3">Error: ${error.message}</td></tr>`;
        }
    }

    // --- Event Handlers ---

    // Handle Logo Upload (for Add New form)
    teamLogoInput.addEventListener('change', async function() {
        await handleImageUpload(this, addFormMessage, logoPreview, logoUrlInput);
    });

    // Handle Logo Upload (for Edit form)
    editLogoInput.addEventListener('change', async function() {
        await handleImageUpload(this, editFormMessage, editLogoPreview, editLogoUrlInput);
    });

    // Universal image upload handler
    async function handleImageUpload(inputElement, messageElement, previewElement, urlInputElement) {
        if (inputElement.files.length === 0) return;
        
        const file = inputElement.files[0];
        const formData = new FormData();
        formData.append('file', file);
        messageElement.textContent = 'Uploading logo...';
        messageElement.className = 'form-message notice notice-info';

        try {
            const response = await fetch(`${apiBase}/media/upload`, {
                method: 'POST',
                headers: { 'X-WP-Nonce': nonce },
                body: formData,
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Image upload failed.');

            urlInputElement.value = result.url;
            previewElement.innerHTML = `<img src="${result.url}" alt="Logo Preview"/>`;
            messageElement.textContent = 'Logo uploaded successfully.';
            messageElement.className = 'form-message notice notice-success';

        } catch (error) {
            messageElement.textContent = `Error: ${error.message}`;
            messageElement.className = 'form-message notice notice-error';
            inputElement.value = '';
        }
    }
    
    // Handle Add New Team form submission
    addTeamForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        addFormMessage.textContent = 'Saving...';
        addFormMessage.className = 'form-message notice notice-info';

        const teamData = {
            name: document.getElementById('team-name').value,
            short_name: document.getElementById('team-short-name').value,
            logo_url: logoUrlInput.value,
            country: document.getElementById('team-country').value,
            captain_id: captainSelect.value,
            vice_captain_id: viceCaptainSelect.value,
        };

        try {
            const response = await fetch(`${apiBase}/teams`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify(teamData),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'An error occurred.');

            addFormMessage.textContent = 'Team saved successfully!';
            addFormMessage.className = 'form-message notice notice-success';
            addTeamForm.reset();
            logoPreview.innerHTML = '';
            logoUrlInput.value = '';
            fetchTeams();
        } catch (error) {
            addFormMessage.textContent = `Error: ${error.message}`;
            addFormMessage.className = 'form-message notice notice-error';
        }
    });

    // Handle clicks on Edit/Delete buttons in the list
    teamsList.addEventListener('click', function(e) {
        // Handle Edit Button Click
        if (e.target.classList.contains('edit-btn')) {
            const teamData = JSON.parse(e.target.dataset.team);
            openEditModal(teamData);
        }

        // Handle Delete Button Click
        if (e.target.classList.contains('delete-btn')) {
            if (confirm('Are you sure you want to delete this team?')) {
                deleteTeam(e.target.dataset.id);
            }
        }

        // Handle Copy Link Button Click
        if (e.target.classList.contains('copy-link-btn')) {
            const teamId = e.target.dataset.id;
            const slug = e.target.dataset.slug; // Get the new slug
            
            // Construct the new, secure URL
            const shareableLink = `${window.location.origin}/team/${teamId}-${slug}/`;
            
            navigator.clipboard.writeText(shareableLink).then(() => {
                const originalText = e.target.textContent;
                e.target.textContent = 'Copied!';
                setTimeout(() => {
                    e.target.textContent = originalText;
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                alert('Failed to copy link.');
            });
        }
    });

    async function deleteTeam(teamId) {
        try {
            const response = await fetch(`${apiBase}/teams/${teamId}`, { method: 'DELETE', headers: { 'X-WP-Nonce': nonce } });
            if (!response.ok) throw new Error('Failed to delete team.');
            fetchTeams();
        } catch (error) {
            alert(`Error: ${error.message}`);
        }
    }

    // --- Edit Modal Logic ---
    function openEditModal(team) {
        editTeamForm.reset();
        document.getElementById('edit-team-id').value = team.id;
        document.getElementById('edit-team-name').value = team.name;
        document.getElementById('edit-team-short-name').value = team.short_name;
        document.getElementById('edit-team-country').value = team.country;
        editLogoUrlInput.value = team.logo_url || '';
        editLogoPreview.innerHTML = team.logo_url ? `<img src="${team.logo_url}" alt="Logo Preview"/>` : '';
        
        // Correctly set the captain and vice-captain dropdowns
        editCaptainSelect.value = team.captain_id || '';
        editViceCaptainSelect.value = team.vice_captain_id || '';
        
        editModal.style.display = 'block';
    }
    
    closeModal.onclick = () => editModal.style.display = 'none';
    window.onclick = (e) => { if (e.target == editModal) editModal.style.display = 'none'; };

    editTeamForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        editFormMessage.textContent = 'Updating...';
        editFormMessage.className = 'form-message notice notice-info';
        const teamId = document.getElementById('edit-team-id').value;
        const teamData = {
            name: document.getElementById('edit-team-name').value,
            short_name: document.getElementById('edit-team-short-name').value,
            logo_url: editLogoUrlInput.value,
            country: document.getElementById('edit-team-country').value,
            captain_id: editCaptainSelect.value,
            vice_captain_id: editViceCaptainSelect.value,
        };

        try {
            const response = await fetch(`${apiBase}/teams/${teamId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify(teamData),
            });
            if (!response.ok) throw new Error('Failed to update team.');

            editFormMessage.textContent = 'Team updated successfully!';
            editFormMessage.className = 'form-message notice notice-success';
            setTimeout(() => {
                editModal.style.display = 'none';
                editFormMessage.textContent = '';
                fetchTeams();
            }, 1000);
        } catch (error) {
            editFormMessage.textContent = `Error: ${error.message}`;
            editFormMessage.className = 'form-message notice notice-error';
        }
    });

    // --- Initial Load ---
    async function initializePage() {
        await fetchPlayers(); // Fetch players first to populate dropdowns
        await fetchTeams();   // Then fetch the teams
    }

    initializePage();
});