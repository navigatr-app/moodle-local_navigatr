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

// Display existing mapping if it exists
if ($existing_mapping && $existing_provider && $existing_badge) {
    echo '<div class="alert alert-info">';
    echo '<h4>' . get_string('current_mapping', 'local_navigatr') . '</h4>';
    echo '<p><strong>' . get_string('provider', 'local_navigatr') . ':</strong> ' . s($existing_provider['name']) . '</p>';
    echo '<p><strong>' . get_string('badge', 'local_navigatr') . ':</strong> ' . s($existing_badge['name']) . '</p>';
    if (!empty($existing_badge['description'])) {
        echo '<p><strong>' . get_string('badgedesc', 'local_navigatr') . ':</strong> ' . s($existing_badge['description']) . '</p>';
    }
    echo '<p><a href="' . new \moodle_url('/local/navigatr/badge_selection.php', [
        'id' => $courseid,
        'provider_id' => $existing_mapping->provider_id
    ]) . '" class="btn btn-primary">' . get_string('change_mapping', 'local_navigatr') . '</a></p>';
    echo '</div>';
}

$form->display();

echo $OUTPUT->footer();
