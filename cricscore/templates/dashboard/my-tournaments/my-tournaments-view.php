<?php
/**
 * The view file for the "My Tournaments" page.
 * @package CricScore
 * @version 1.1.0
 */
?>
<div class="cricscore-grid">
    <div class="cricscore-col cricscore-col-8">
        <h3>Your Tournaments</h3>
        <table class="cricscore-table">
            <thead>
                <tr>
                    <th>Tournament Name</th>
                    <th>Format</th>
                    <th>Dates</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="my-tournaments-list">
                <tr><td colspan="4">Loading tournaments...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="cricscore-col cricscore-col-4">
        <h3>Add New Tournament</h3>
        <form id="add-tournament-form" class="cricscore-form">
            <div class="form-group"><label for="tournament-name">Tournament Name</label><input type="text" id="tournament-name" required></div>
            <div class="form-group"><label for="tournament-format">Format</label><select id="tournament-format"><option value="League">League</option><option value="Knockout">Knockout</option><option value="Bilateral Series">Bilateral Series</option></select></div>
            <div class="form-group"><label for="start-date">Start Date</label><input type="date" id="start-date"></div>
            <div class="form-group"><label for="end-date">End Date</label><input type="date" id="end-date"></div>
            <div class="form-group"><button type="submit" class="button-primary">Save Tournament</button></div>
            <div id="add-form-message" class="form-message"></div>
        </form>
    </div>
</div>

<div id="edit-tournament-modal" class="cricscore-modal" style="display: none;">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Edit Tournament</h2>
        <form id="edit-tournament-form" class="cricscore-form">
            <input type="hidden" id="edit-tournament-id">
            <div class="form-group"><label for="edit-tournament-name">Tournament Name</label><input type="text" id="edit-tournament-name" required></div>
            <div class="form-group"><label for="edit-tournament-format">Format</label><select id="edit-tournament-format"><option value="League">League</option><option value="Knockout">Knockout</option><option value="Bilateral Series">Bilateral Series</option></select></div>
            <div class="form-group"><label for="edit-start-date">Start Date</label><input type="date" id="edit-start-date"></div>
            <div class="form-group"><label for="edit-end-date">End Date</label><input type="date" id="edit-end-date"></div>
            <div class="form-group"><button type="submit" class="button-primary">Update Tournament</button></div>
            <div id="edit-form-message" class="form-message"></div>
        </form>
    </div>
</div>