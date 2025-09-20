document.addEventListener('DOMContentLoaded', function () {
    const apiBase = '/wp-json/cricscore/v1';
    const nonce = cricscore_dashboard.nonce;

    const venuesList = document.getElementById('my-venues-list');
    const addVenueForm = document.getElementById('add-venue-form');
    const addFormMessage = document.getElementById('add-form-message');

    const editModal = document.getElementById('edit-venue-modal');
    const editVenueForm = document.getElementById('edit-venue-form');
    const editFormMessage = document.getElementById('edit-form-message');
    const closeModal = editModal.querySelector('.modal-close');

    async function fetchVenues() {
        try {
            const response = await fetch(`${apiBase}/venues`, { headers: { 'X-WP-Nonce': nonce } });
            if (!response.ok) throw new Error('Failed to fetch venues.');
            
            const venues = await response.json();
            venuesList.innerHTML = '';

            if (venues.length > 0) {
                venues.forEach(venue => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${venue.name}</td>
                        <td>${venue.city}</td>
                        <td>${venue.country}</td>
                        <td>
                            <button class="button-secondary button-small edit-btn" data-id="${venue.id}" data-name="${venue.name}" data-city="${venue.city}" data-country="${venue.country}">Edit</button>
                            <button class="button-danger button-small delete-btn" data-id="${venue.id}">Delete</button>
                        </td>
                    `;
                    venuesList.appendChild(row);
                });
            } else {
                venuesList.innerHTML = '<tr><td colspan="4">You have not created any venues yet.</td></tr>';
            }
        } catch (error) {
            venuesList.innerHTML = `<tr><td colspan="4">Error: ${error.message}</td></tr>`;
        }
    }

    addVenueForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        addFormMessage.textContent = 'Saving...';
        addFormMessage.className = 'form-message notice notice-info';
        const venueData = {
            name: document.getElementById('venue-name').value,
            city: document.getElementById('venue-city').value,
            country: document.getElementById('venue-country').value,
        };

        try {
            const response = await fetch(`${apiBase}/venues`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify(venueData),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'An error occurred.');

            addFormMessage.textContent = 'Venue saved successfully!';
            addFormMessage.className = 'form-message notice notice-success';
            addVenueForm.reset();
            fetchVenues();
        } catch (error) {
            addFormMessage.textContent = `Error: ${error.message}`;
            addFormMessage.className = 'form-message notice notice-error';
        }
    });

    venuesList.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-btn')) {
            if (confirm('Are you sure?')) deleteVenue(e.target.dataset.id);
        }
        if (e.target.classList.contains('edit-btn')) {
            const { id, name, city, country } = e.target.dataset;
            openEditModal(id, name, city, country);
        }
    });

    async function deleteVenue(venueId) {
        try {
            const response = await fetch(`${apiBase}/venues/${venueId}`, { method: 'DELETE', headers: { 'X-WP-Nonce': nonce } });
            if (!response.ok) throw new Error('Failed to delete venue.');
            fetchVenues();
        } catch (error) {
            alert(`Error: ${error.message}`);
        }
    }

    function openEditModal(id, name, city, country) {
        document.getElementById('edit-venue-id').value = id;
        document.getElementById('edit-venue-name').value = name;
        document.getElementById('edit-venue-city').value = city;
        document.getElementById('edit-venue-country').value = country;
        editModal.style.display = 'block';
    }

    closeModal.onclick = () => editModal.style.display = 'none';
    window.onclick = (e) => { if (e.target == editModal) editModal.style.display = 'none'; };

    editVenueForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        editFormMessage.textContent = 'Updating...';
        const venueId = document.getElementById('edit-venue-id').value;
        const venueData = {
            name: document.getElementById('edit-venue-name').value,
            city: document.getElementById('edit-venue-city').value,
            country: document.getElementById('edit-venue-country').value,
        };

        try {
            const response = await fetch(`${apiBase}/venues/${venueId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify(venueData),
            });
            if (!response.ok) throw new Error('Failed to update venue.');
            
            editFormMessage.textContent = 'Venue updated!';
            editFormMessage.className = 'form-message notice notice-success';
            setTimeout(() => {
                editModal.style.display = 'none';
                editFormMessage.textContent = '';
                fetchVenues();
            }, 1000);
        } catch (error) {
            editFormMessage.textContent = `Error: ${error.message}`;
            editFormMessage.className = 'form-message notice notice-error';
        }
    });

    fetchVenues();
});