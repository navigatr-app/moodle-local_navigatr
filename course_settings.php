<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Provider selection page for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/navigatr/classes/form/provider_selection_form.php');

// Get course ID from URL parameter or form data
$courseid = optional_param('id', 0, PARAM_INT);

// Validate course ID
if (empty($courseid)) {
    throw new \moodle_exception('missingparam', 'error', '', 'id');
}

$course = get_course($courseid);
$context = context_course::instance($courseid);

require_login($course);
require_capability('local/navigatr:configurecourse', $context);

$PAGE->set_url('/local/navigatr/course_settings.php', ['id' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('select_provider', 'local_navigatr'));
$PAGE->set_heading($course->fullname . ' - ' . get_string('select_provider', 'local_navigatr'));

// Handle AJAX requests
if (optional_param('action', '', PARAM_ALPHA) === 'getbadges') {
    $providerid = required_param('provider_id', PARAM_INT);
    
    $badges = \local_navigatr\local\api_client::get_badges_for_provider($providerid);
    
    header('Content-Type: application/json');
    echo json_encode($badges);
    exit;
}

// Handle remove mapping action
if (optional_param('action', '', PARAM_ALPHA) === 'removemapping') {
    require_sesskey();
    
    $mapping = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
    if ($mapping) {
        $DB->delete_records('local_navigatr_map', ['courseid' => $courseid]);
        redirect(new moodle_url('/local/navigatr/course_settings.php', ['id' => $courseid]), 
                get_string('mapping_removed', 'local_navigatr'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

// Check for existing mapping
$existing_mapping = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
$existing_provider = null;
$existing_badge = null;

if ($existing_mapping) {
    try {
        // Fetch provider details
        $client = new \local_navigatr\local\api_client();
        $provider_response = $client->get("/provider/{$existing_mapping->provider_id}");
        if ($provider_response->ok) {
            $existing_provider = $provider_response->body;
        }
        
        // Fetch badge details
        $badge_response = $client->get("/badge/{$existing_mapping->badge_id}");
        if ($badge_response->ok) {
            $existing_badge = $badge_response->body;
        }
    } catch (Exception $e) {
        // If API calls fail, we'll just show the form without existing info
        $existing_mapping = null;
    }
}

// Create form
$form = new \local_navigatr\form\provider_selection_form(null, ['courseid' => $courseid]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
}

if ($data = $form->get_data()) {
    // Check if provider was selected
    if (isset($data->select_provider) && !empty($data->provider_id)) {
        // Redirect to badge selection page
        redirect(new \moodle_url('/local/navigatr/badge_selection.php', [
            'id' => $courseid,
            'provider_id' => $data->provider_id
        ]));
    }
}

echo $OUTPUT->header();

// Check if providers are available
$providers = [];
try {
    $client = new \local_navigatr\local\api_client();
    $user_response = $client->get("/user_detail/0");
    if ($user_response->ok && isset($user_response->body['providers'])) {
        $providers = $user_response->body['providers'];
    }
} catch (Exception $e) {
    // API call failed, providers will be empty
}

// Display provider configuration notice if no providers available
if (empty($providers)) {
    echo '<div class="alert alert-info">';
    echo '<i class="fa fa-info-circle" aria-hidden="true"></i> ';
    echo get_string('provider_config_notice', 'local_navigatr', new \moodle_url('/local/navigatr/settings_page.php'));
    echo '</div>';
}

// Display existing mapping if it exists
if ($existing_mapping && $existing_provider && $existing_badge) {
    echo '<div class="alert alert-info">';
    echo '<h4>' . get_string('current_mapping', 'local_navigatr') . '</h4>';
    
    // Add badge image if available
    if (!empty($existing_badge['image_url'])) {
        echo '<div class="badge-image-container" style="float: left; margin-right: 15px; margin-bottom: 10px;">';
        echo '<img src="' . s($existing_badge['image_url']) . '" alt="' . s($existing_badge['name']) . '" style="max-width: 100px; max-height: 100px; border-radius: 8px;">';
        echo '</div>';
    }
    
    echo '<div class="mapping-details">';
    echo '<p>' . get_string('provider', 'local_navigatr') . ': ' . s($existing_provider['name']) . '</p>';
    echo '<p>' . get_string('badge', 'local_navigatr') . ': ' . s($existing_badge['name']);
    // Add badge link if available
    if (!empty($existing_badge['url'])) {
        echo ' <a href="' . s($existing_badge['url']) . '" target="_blank" class="btn btn-sm btn-link">' . get_string('view_badge', 'local_navigatr') . '</a>';
    }
    echo '</p>';
    if (!empty($existing_badge['description'])) {
        echo '<p>' . get_string('badgedesc', 'local_navigatr') . ': ' . s($existing_badge['description']) . '</p>';
    }
    echo '</div>';
    
    // Clear float
    echo '<div style="clear: both;"></div>';
    echo '<div class="mt-3 d-flex gap-2">';
    
    // Change mapping button
    $change_url = new \moodle_url('/local/navigatr/badge_selection.php', [
        'id' => $courseid,
        'provider_id' => $existing_mapping->provider_id
    ]);
    $change_button = $OUTPUT->single_button($change_url, get_string('change_mapping', 'local_navigatr'), 'get', ['class' => 'btn-link']);
    echo $change_button;
    
    // Remove mapping button with confirmation
    $remove_url = new \moodle_url('/local/navigatr/course_settings.php', [
        'id' => $courseid,
        'action' => 'removemapping',
        'sesskey' => sesskey()
    ]);
    $remove_button = $OUTPUT->single_button($remove_url, get_string('remove_mapping', 'local_navigatr'), 'get', [
        'class' => 'btn-danger',
        'onclick' => 'return confirm(\'' . get_string('remove_mapping_confirm', 'local_navigatr') . '\')'
    ]);
    echo $remove_button;
    
    echo '</div>';
    echo '</div>';
}

$form->display();

echo $OUTPUT->footer();
