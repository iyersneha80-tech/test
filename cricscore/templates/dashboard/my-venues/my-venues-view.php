<?php
/**
 * The view file for the "My Venues" page.
 * @package CricScore
 * @version 1.1.0
 */
?>
<div class="cricscore-grid">
    <div class="cricscore-col cricscore-col-8">
        <h3>Your Venues</h3>
        <table class="cricscore-table">
            <thead>
                <tr>
                    <th>Venue Name</th>
                    <th>City</th>
                    <th>Country</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="my-venues-list">
                <tr><td colspan="4">Loading venues...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="cricscore-col cricscore-col-4">
        <h3>Add New Venue</h3>
        <form id="add-venue-form" class="cricscore-form">
            <div class="form-group"><label for="venue-name">Venue Name</label><input type="text" id="venue-name" required></div>
            <div class="form-group"><label for="venue-city">City</label><input type="text" id="venue-city"></div>
            <div class="form-group"><label for="venue-country">Country</label><input type="text" id="venue-country"></div>
            <div class="form-group"><button type="submit" class="button-primary">Save Venue</button></div>
            <div id="add-form-message" class="form-message"></div>
        </form>
    </div>
</div>

<div id="edit-venue-modal" class="cricscore-modal" style="display: none;">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Edit Venue</h2>
        <form id="edit-venue-form" class="cricscore-form">
            <input type="hidden" id="edit-venue-id">
            <div class="form-group"><label for="edit-venue-name">Venue Name</label><input type="text" id="edit-venue-name" required></div>
            <div class="form-group"><label for="edit-venue-city">City</label><input type="text" id="edit-venue-city"></div>
            <div class="form-group"><label for="edit-venue-country">Country</label><input type="text" id="edit-venue-country"></div>
            <div class="form-group"><button type="submit" class="button-primary">Update Venue</button></div>
            <div id="edit-form-message" class="form-message"></div>
        </form>
    </div>
</div>