<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,.
// but WITHOUT ANY WARRANTY; without even the implied warranty of.
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the.
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

// Get course ID from URL parameter.
$courseid = required_param('id', PARAM_INT);

$course = get_course($courseid);
$context = context_course::instance($courseid);

require_login($course);
require_capability('local/navigatr:configurecourse', $context);

$PAGE->set_url('/local/navigatr/course_settings.php', ['id' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('select_provider', 'local_navigatr'));
$PAGE->set_heading($course->fullname . ' - ' . get_string('select_provider', 'local_navigatr'));

// Handle remove mapping action.
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

// Check for existing mapping.
$existingMapping = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
$existingProvider = null;
$existingBadge = null;

if ($existingMapping) {
    try {
        // Fetch provider details.
        $client = new \local_navigatr\local\api_client();
        $providerResponse = $client->get("/provider/{$existingMapping->provider_id}");
        if ($providerResponse->ok) {
            $existingProvider = $providerResponse->body;
        }

        // Fetch badge details.
        $badgeResponse = $client->get("/badge/{$existingMapping->badge_id}");
        if ($badgeResponse->ok) {
            $existingBadge = $badgeResponse->body;

            // Update cached badge metadata if it has changed.
            $needsUpdate = false;
            if ($existingMapping->badge_name !== ($existingBadge['name'] ?? null)) {
                $existingMapping->badge_name = $existingBadge['name'] ?? null;
                $needsUpdate = true;
            }
            if ($existingMapping->badge_image_url !== ($existingBadge['image_url'] ?? null)) {
                $existingMapping->badge_image_url = $existingBadge['image_url'] ?? null;
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $existingMapping->timemodified = time();
                $DB->update_record('local_navigatr_map', $existingMapping);
            }
        } else {
            // API call failed, use cached data.
            $existingBadge = [
                'name' => $existingMapping->badge_name ?? 'Unknown Badge',
                'image_url' => $existingMapping->badge_image_url ?? null,
                'description' => null,
                'url' => null,
            ];
        }
    } catch (Exception $e) {
        // If API calls fail, use cached data.
        $existingBadge = [
            'name' => $existingMapping->badge_name ?? 'Unknown Badge',
            'image_url' => $existingMapping->badge_image_url ?? null,
            'description' => null,
            'url' => null,
        ];
    }
}

// Create form.
$form = new \local_navigatr\form\provider_selection_form(null, ['courseid' => $courseid]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
}

if ($data = $form->get_data()) {
    // Check if provider was selected.
    if (isset($data->select_provider) && !empty($data->provider_id)) {
        // Redirect to badge selection page.
        redirect(new \moodle_url('/local/navigatr/badge_selection.php', [
            'id' => $courseid,
            'provider_id' => $data->provider_id,
        ]));
    }
}

echo $OUTPUT->header();

// Display help documentation link.
$helpUrl = get_string('help_center_url', 'local_navigatr');
$helpLink = \html_writer::link($helpUrl, get_string('help_center_link', 'local_navigatr'), [
    'target' => '_blank',
    'class' => 'btn btn-outline-info btn-sm',
]);
$helpText = get_string('help_badge_config', 'local_navigatr') . ' ' . $helpLink;
echo \core\notification::info($helpText);

// Check if providers are available.
$providers = [];
try {
    $client = new \local_navigatr\local\api_client();
    $userResponse = $client->get("/user_detail/0");
    if ($userResponse->ok && isset($userResponse->body['providers'])) {
        $providers = $userResponse->body['providers'];
    }
} catch (Exception $e) {
    // API call failed, providers will be empty.
    debugging('Failed to fetch providers for course settings: ' . $e->getMessage(), DEBUG_NORMAL);
}

// Display provider configuration notice if no providers available.
if (empty($providers)) {
    echo \core\notification::info(
        get_string('provider_config_notice', 'local_navigatr', new \moodle_url('/local/navigatr/settings_page.php'))
    );
}

// Display existing mapping if it exists using template.
echo \local_navigatr\output\course_settings_output::render_current_mapping(
    $existingMapping,
    $existingProvider,
    $existingBadge,
    $courseid
);

$form->display();

echo $OUTPUT->footer();
