document.addEventListener('DOMContentLoaded', function () {
    const apiBase = '/wp-json/cricscore/v1';
    const nonce = cricscore_dashboard.nonce;

    const list = document.getElementById('my-tournaments-list');
    const addForm = document.getElementById('add-tournament-form');
    const addFormMessage = document.getElementById('add-form-message');

    const editModal = document.getElementById('edit-tournament-modal');
    const editForm = document.getElementById('edit-tournament-form');
    const editFormMessage = document.getElementById('edit-form-message');
    const closeModal = editModal.querySelector('.modal-close');

    async function fetchItems() {
        try {
            const response = await fetch(`${apiBase}/tournaments`, { headers: { 'X-WP-Nonce': nonce } });
            if (!response.ok) throw new Error('Failed to fetch tournaments.');
            const items = await response.json();
            list.innerHTML = '';
            if (items.length > 0) {
                items.forEach(item => {
                    const row = document.createElement('tr');
                    const startDate = item.start_date ? new Date(item.start_date).toLocaleDateString() : 'N/A';
                    const endDate = item.end_date ? new Date(item.end_date).toLocaleDateString() : 'N/A';
                    row.innerHTML = `
                        <td>${item.name}</td>
                        <td>${item.format}</td>
                        <td>${startDate} - ${endDate}</td>
                        <td>
                            <button class="button-secondary button-small edit-btn" data-id="${item.id}" data-item='${JSON.stringify(item)}'>Edit</button>
                            <button class="button-danger button-small delete-btn" data-id="${item.id}">Delete</button>
                        </td>
                    `;
                    list.appendChild(row);
                });
            } else {
                list.innerHTML = '<tr><td colspan="4">No tournaments found.</td></tr>';
            }
        } catch (error) {
            list.innerHTML = `<tr><td colspan="4">Error: ${error.message}</td></tr>`;
        }
    }

    addForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        addFormMessage.textContent = 'Saving...';
        const data = {
            name: document.getElementById('tournament-name').value,
            format: document.getElementById('tournament-format').value,
            start_date: document.getElementById('start-date').value,
            end_date: document.getElementById('end-date').value,
        };
        try {
            const response = await fetch(`${apiBase}/tournaments`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify(data),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Error saving.');
            addFormMessage.textContent = 'Saved successfully!';
            addForm.reset();
            fetchItems();
        } catch (error) {
            addFormMessage.textContent = `Error: ${error.message}`;
        }
    });

    list.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-btn')) {
            if (confirm('Are you sure?')) deleteItem(e.target.dataset.id);
        }
        if (e.target.classList.contains('edit-btn')) {
            const item = JSON.parse(e.target.dataset.item);
            openEditModal(item);
        }
    });

    async function deleteItem(id) {
        try {
            const response = await fetch(`${apiBase}/tournaments/${id}`, { method: 'DELETE', headers: { 'X-WP-Nonce': nonce } });
            if (!response.ok) throw new Error('Failed to delete.');
            fetchItems();
        } catch (error) {
            alert(`Error: ${error.message}`);
        }
    }

    function openEditModal(item) {
        document.getElementById('edit-tournament-id').value = item.id;
        document.getElementById('edit-tournament-name').value = item.name;
        document.getElementById('edit-tournament-format').value = item.format;
        document.getElementById('edit-start-date').value = item.start_date ? item.start_date.split(' ')[0] : '';
        document.getElementById('edit-end-date').value = item.end_date ? item.end_date.split(' ')[0] : '';
        editModal.style.display = 'block';
    }

    closeModal.onclick = () => editModal.style.display = 'none';
    window.onclick = (e) => { if (e.target == editModal) editModal.style.display = 'none'; };

    editForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        editFormMessage.textContent = 'Updating...';
        const id = document.getElementById('edit-tournament-id').value;
        const data = {
            name: document.getElementById('edit-tournament-name').value,
            format: document.getElementById('edit-tournament-format').value,
            start_date: document.getElementById('edit-start-date').value,
            end_date: document.getElementById('edit-end-date').value,
        };
        try {
            const response = await fetch(`${apiBase}/tournaments/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify(data),
            });
            if (!response.ok) throw new Error('Failed to update.');
            
            editFormMessage.textContent = 'Updated!';
            setTimeout(() => {
                editModal.style.display = 'none';
                editFormMessage.textContent = '';
                fetchItems();
            }, 1000);
        } catch (error) {
            editFormMessage.textContent = `Error: ${error.message}`;
        }
    });

    fetchItems();
});