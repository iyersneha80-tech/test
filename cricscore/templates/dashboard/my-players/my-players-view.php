<?php
/**
 * The view file for the "My Players" page.
 *
 * @package CricScore
 * @version 1.3.0
 */
?>
<div class="cricscore-grid">
    <div class="cricscore-col cricscore-col-8">
        <h3>Your Player Pool</h3>
        <table class="cricscore-table">
            <thead>
                <tr>
                    <th>Player</th>
                    <th>Role</th>
                    <th>Age</th>
                    <th>Actions</th>
                    <th>Shareable Link</th>
                </tr>
            </thead>
            <tbody id="my-players-list">
                <tr><td colspan="5">Loading players...</td></tr>
            </tbody>
        </table>
    </div>
    <div class="cricscore-col cricscore-col-4">
        <h3>Add New Player</h3>
        <form id="add-player-form" class="cricscore-form">
            <div class="form-group">
                <label for="player-name">Player's Full Name</label>
                <input type="text" id="player-name" required>
            </div>
            <div class="form-group">
                <label for="player-dob">Date of Birth</label>
                <input type="date" id="player-dob">
            </div>
            <div class="form-group">
                <label for="player-role">Player Role</label>
                <select id="player-role">
                    <option value="">Select Role</option>
                    <option value="Batsman">Batsman</option>
                    <option value="Bowler">Bowler</option>
                    <option value="All-Rounder">All-Rounder</option>
                    <option value="Wicket-Keeper">Wicket-Keeper</option>
                </select>
            </div>
            <div class="form-group conditional" id="batting-style-group" style="display: none;">
                <label for="batting-style">Batting Style</label>
                <select id="batting-style">
                    <option value="Right-hand">Right-hand</option>
                    <option value="Left-hand">Left-hand</option>
                </select>
            </div>
            <div class="form-group conditional" id="bowling-style-group" style="display: none;">
                <label for="bowling-style">Bowling Style</label>
                <select id="bowling-style">
                    <option value="Right-arm Fast">Right-arm Fast</option>
                    <option value="Right-arm Medium">Right-arm Medium</option>
                    <option value="Right-arm Off-spin">Right-arm Off-spin</option>
                    <option value="Right-arm Leg-spin">Right-arm Leg-spin</option>
                    <option value="Left-arm Fast">Left-arm Fast</option>
                    <option value="Left-arm Medium">Left-arm Medium</option>
                    <option value="Left-arm Orthodox">Left-arm Orthodox</option>
                    <option value="Left-arm Chinaman">Left-arm Chinaman</option>
                </select>
            </div>
            <div class="form-group">
                <label for="player-country">Country</label>
                <input type="text" id="player-country">
            </div>
            <div class="form-group">
                <label for="profile-image">Profile Picture</label>
                <input type="file" id="profile-image" accept="image/*">
                <div id="image-preview" class="image-preview"></div>
                <input type="hidden" id="profile-image-url">
            </div>
            <div class="form-group">
                <button type="submit" class="button-primary">Save Player</button>
            </div>
            <div id="add-form-message" class="form-message"></div>
        </form>
    </div>
</div>

<div id="edit-player-modal" class="cricscore-modal" style="display: none;">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h2>Edit Player</h2>
        <form id="edit-player-form" class="cricscore-form">
            <input type="hidden" id="edit-player-id">
            <div class="form-group">
                <label for="edit-player-name">Player's Full Name</label>
                <input type="text" id="edit-player-name" required>
            </div>
             <div class="form-group">
                <label for="edit-player-dob">Date of Birth</label>
                <input type="date" id="edit-player-dob">
            </div>
            <div class="form-group">
                <label for="edit-player-role">Player Role</label>
                <select id="edit-player-role">
                    <option value="">Select Role</option>
                    <option value="Batsman">Batsman</option>
                    <option value="Bowler">Bowler</option>
                    <option value="All-Rounder">All-Rounder</option>
                    <option value="Wicket-Keeper">Wicket-Keeper</option>
                </select>
            </div>
            <div class="form-group conditional" id="edit-batting-style-group" style="display: none;">
                <label for="edit-batting-style">Batting Style</label>
                <select id="edit-batting-style">
                    <option value="Right-hand">Right-hand</option>
                    <option value="Left-hand">Left-hand</option>
                </select>
            </div>
            <div class="form-group conditional" id="edit-bowling-style-group" style="display: none;">
                <label for="edit-bowling-style">Bowling Style</label>
                <select id="edit-bowling-style">
                     <option value="Right-arm Fast">Right-arm Fast</option>
                    <option value="Right-arm Medium">Right-arm Medium</option>
                    <option value="Right-arm Off-spin">Right-arm Off-spin</option>
                    <option value="Right-arm Leg-spin">Right-arm Leg-spin</option>
                    <option value="Left-arm Fast">Left-arm Fast</option>
                    <option value="Left-arm Medium">Left-arm Medium</option>
                    <option value="Left-arm Orthodox">Left-arm Orthodox</option>
                    <option value="Left-arm Chinaman">Left-arm Chinaman</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit-player-country">Country</label>
                <input type="text" id="edit-player-country">
            </div>
            <div class="form-group">
                <label for="edit-profile-image">Profile Picture</label>
                <div id="edit-image-preview" class="image-preview"></div>
                <input type="file" id="edit-profile-image" accept="image/*">
                <input type="hidden" id="edit-profile-image-url">
            </div>
            <div class="form-group">
                <button type="submit" class="button-primary">Update Player</button>
            </div>
            <div id="edit-form-message" class="form-message"></div>
        </form>
    </div>
</div>