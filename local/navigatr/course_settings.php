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

$form->display();

echo $OUTPUT->footer();
