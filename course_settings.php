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

// Get course ID from URL parameter
$courseid = required_param('id', PARAM_INT);

$course = get_course($courseid);
$context = context_course::instance($courseid);

require_login($course);
require_capability('local/navigatr:configurecourse', $context);

$PAGE->set_url('/local/navigatr/course_settings.php', ['id' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('select_provider', 'local_navigatr'));
$PAGE->set_heading($course->fullname . ' - ' . get_string('select_provider', 'local_navigatr'));

// Handle remove mapping action
if (optional_param('action', '', PARAM_ALPHA) === 'removemapping') {
    require_sesskey();

    $mapping = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
    if ($mapping) {
        $DB->delete_records('local_navigatr_map', ['courseid' => $courseid]);
        redirect(
            new moodle_url('/local/navigatr/course_settings.php', ['id' => $courseid]),
            get_string('mapping_removed', 'local_navigatr'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
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

            // Update cached badge metadata if it has changed
            $needs_update = false;
            if ($existing_mapping->badge_name !== ($existing_badge['name'] ?? null)) {
                $existing_mapping->badge_name = $existing_badge['name'] ?? null;
                $needs_update = true;
            }
            if ($existing_mapping->badge_image_url !== ($existing_badge['image_url'] ?? null)) {
                $existing_mapping->badge_image_url = $existing_badge['image_url'] ?? null;
                $needs_update = true;
            }

            if ($needs_update) {
                $existing_mapping->timemodified = time();
                $DB->update_record('local_navigatr_map', $existing_mapping);
            }
        } else {
            // API call failed, use cached data
            $existing_badge = [
                'name' => $existing_mapping->badge_name ?? 'Unknown Badge',
                'image_url' => $existing_mapping->badge_image_url ?? null,
                'description' => null,
                'url' => null
            ];
        }
    } catch (Exception $e) {
        // If API calls fail, use cached data
        $existing_badge = [
            'name' => $existing_mapping->badge_name ?? 'Unknown Badge',
            'image_url' => $existing_mapping->badge_image_url ?? null,
            'description' => null,
            'url' => null
        ];
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

// Display help documentation link
$help_url = get_string('help_center_url', 'local_navigatr');
$help_link = \html_writer::link($help_url, get_string('help_center_link', 'local_navigatr'), [
    'target' => '_blank',
    'class' => 'btn btn-outline-info btn-sm'
]);
$help_text = get_string('help_badge_config', 'local_navigatr') . ' ' . $help_link;
echo \core\notification::info($help_text);

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
    echo \core\notification::info(get_string('provider_config_notice', 'local_navigatr', new \moodle_url('/local/navigatr/settings_page.php')));
}

// Display existing mapping if it exists using template
echo \local_navigatr\output\course_settings_output::render_current_mapping(
    $existing_mapping,
    $existing_provider,
    $existing_badge,
    $courseid
);

$form->display();

echo $OUTPUT->footer();
