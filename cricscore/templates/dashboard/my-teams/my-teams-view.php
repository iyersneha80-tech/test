<?php
/**
 * The view file for the "My Teams" page.
 *
 * @package CricScore
 * @version 1.2.0
 */
?>

<div class="cricscore-grid">

    <div class="cricscore-col cricscore-col-8">
        <h3>Your Teams</h3>
        <table class="cricscore-table">
            <thead>
                <tr>
                    <th>Team</th>
                    <th>Actions</th>
                    <th>Shareable Link</th>
                </tr>
            </thead>
            <tbody id="my-teams-list">
                <tr><td colspan="3">Loading teams...</td></tr>
            </tbody>
        </table>
    </div>

    <div class="cricscore-col cricscore-col-4">
        <h3>Add New Team</h3>
        <form id="add-team-form" class="cricscore-form">
            <h4>Basic Information</h4>
            <div class="form-group">
                <label for="team-name">Team Name</label>
                <input type="text" id="team-name" required>
            </div>
            <div class="form-group">
                <label for="team-short-name">Short Name (e.g., IND)</label>
                <input type="text" id="team-short-name">
            </div>
            <div class="form-group">
                <label for="team-logo">Team Logo</label>
                <input type="file" id="team-logo" accept="image/*">
                <div id="logo-preview" class="image-preview"></div>
                <input type="hidden" id="logo-url">
            </div>
            <div class="form-group">
                <label for="team-country">Country / Region</label>
                <input type="text" id="team-country">
            </div>
            <div class="form-group">
                <label for="team-captain">Team's Captain</label>
                <select id="team-captain">
                    <option value="">Select a Player</option>
                    </select>
            </div>
            <div class="form-group">
                <label for="team-vice-captain">Team's Vice-Captain</label>
                <select id="team-vice-captain">
                    <option value="">Select a Player</option>
                    </select>
            </div>
            <div class="form-group">
                <button type="submit" class="button-primary">Save Team</button>
            </div>
            <div id="add-form-message" class="form-message"></div>
        </form>
    </div>

</div>

<div id="edit-team-modal" class="cricscore-modal" style="display: none;">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Edit Team</h2>
        <form id="edit-team-form" class="cricscore-form">
            <input type="hidden" id="edit-team-id">
            <h4>Basic Information</h4>
            <div class="form-group">
                <label for="edit-team-name">Team Name</label>
                <input type="text" id="edit-team-name" required>
            </div>
            <div class="form-group">
                <label for="edit-team-short-name">Short Name</label>
                <input type="text" id="edit-team-short-name">
            </div>
            <div class="form-group">
                <label for="edit-team-logo">Team Logo</label>
                <div id="edit-logo-preview" class="image-preview"></div>
                <input type="file" id="edit-team-logo" accept="image/*">
                <input type="hidden" id="edit-logo-url">
            </div>
            <div class="form-group">
                <label for="edit-team-country">Country / Region</label>
                <input type="text" id="edit-team-country">
            </div>
            <div class="form-group">
                <label for="edit-team-captain">Team's Captain</label>
                <select id="edit-team-captain">
                    <option value="">Select a Player</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit-team-vice-captain">Team's Vice-Captain</label>
                <select id="edit-team-vice-captain">
                    <option value="">Select a Player</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="button-primary">Update Team</button>
            </div>
            <div id="edit-form-message" class="form-message"></div>
        </form>
    </div>
</div>