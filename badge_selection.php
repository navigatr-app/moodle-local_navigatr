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
 * Badge selection page for Navigatr plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/navigatr/classes/form/badge_selection_form.php');

// Get course ID from URL parameter or form data
$courseid = optional_param('id', 0, PARAM_INT);
if (empty($courseid)) {
    // Try to get from form data
    $courseid = optional_param('courseid', 0, PARAM_INT);
}
if (empty($courseid)) {
    throw new \moodle_exception('missingparam', 'error', '', 'id');
}

$provider_id = required_param('provider_id', PARAM_INT);

// Get course
$course = get_course($courseid);
require_login($course);

// Check capabilities
require_capability('local/navigatr:configurecourse', context_course::instance($courseid));

// Get provider name
$providers = [];
try {
    $client = new \local_navigatr\local\api_client();
    $response = $client->get('/user_detail/0');
    if ($response->ok && isset($response->body['providers'])) {
        foreach ($response->body['providers'] as $provider) {
            if ($provider['id'] == $provider_id) {
                $provider_name = $provider['name'];
                break;
            }
        }
    }
} catch (Exception $e) {
    $provider_name = 'Unknown Provider';
}

// Set up page
$PAGE->set_url('/local/navigatr/badge_selection.php', ['id' => $courseid, 'provider_id' => $provider_id]);
$PAGE->set_title(get_string('select_badge', 'local_navigatr'));
$PAGE->set_heading(get_string('select_badge', 'local_navigatr'));

// Create form
$form = new \local_navigatr\form\badge_selection_form(null, [
    'courseid' => $courseid,
    'provider_id' => $provider_id,
    'provider_name' => $provider_name ?? 'Unknown Provider'
]);

// Handle form submission
if ($form->is_cancelled()) {
    redirect(new \moodle_url('/local/navigatr/course_settings.php', ['id' => $courseid]));
} else if ($data = $form->get_data()) {
    // Save the mapping
    global $DB;
    
    $mapping = new \stdClass();
    $mapping->courseid = $courseid;
    $mapping->provider_id = $data->provider_id;
    $mapping->badge_id = $data->badge_id;
    $mapping->timecreated = time();
    $mapping->timemodified = time();
    
    // Check if mapping already exists
    $existing = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
    if ($existing) {
        $mapping->id = $existing->id;
        $DB->update_record('local_navigatr_map', $mapping);
    } else {
        $DB->insert_record('local_navigatr_map', $mapping);
    }
    
    redirect(new \moodle_url('/local/navigatr/course_settings.php', ['id' => $courseid]));
}

// Output page
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('select_badge', 'local_navigatr'));

// Show navigation breadcrumb
$PAGE->navbar->add($course->shortname, new \moodle_url('/course/view.php', ['id' => $courseid]));
$PAGE->navbar->add(get_string('navigatr_settings', 'local_navigatr'), new \moodle_url('/local/navigatr/course_settings.php', ['id' => $courseid]));
$PAGE->navbar->add(get_string('select_badge', 'local_navigatr'));

$form->display();

echo $OUTPUT->footer();
