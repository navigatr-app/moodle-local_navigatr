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
 * Settings page for Navigatr plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Check capability explicitly for consistency.
require_capability('local/navigatr:managecredentials', context_system::instance());

admin_externalpage_setup('local_navigatr_settings');

$PAGE->set_url('/local/navigatr/settings_page.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_navigatr'));
$PAGE->set_heading(get_string('pluginname', 'local_navigatr'));

// Handle form submission.
$data = data_submitted();
if ($data && confirm_sesskey()) {
    if (isset($data->removeconnection)) {
        // Handle remove connection.
        \local_navigatr\local\password_manager::clear_pat();
        \core\notification::success(get_string('connection_removed', 'local_navigatr'));
    } else if (isset($data->testconnection)) {
        // Handle test connection.
        // Trim to remove accidental whitespace/newlines from copy-paste.
        $pat = trim($data->personal_access_token ?? '');
        if (empty($pat)) {
            $pat = \local_navigatr\local\password_manager::get_pat();
        }
        $environment = $data->env ?? 'production';

        $result = \local_navigatr\local\api_client::test_connection($pat, $environment);
        if ($result->ok) {
            \core\notification::success(get_string('connection_success_simple', 'local_navigatr'));
        } else {
            // Build detailed error message including environment so the user can diagnose mismatches.
            $errormsg = get_string('connection_failed', 'local_navigatr') . ' (' . $environment . ')';
            if (!empty($result->error)) {
                $errormsg .= ': ' . s($result->error);
            } else if (!empty($result->body) && is_array($result->body) && isset($result->body['error'])) {
                $errormsg .= ': ' . s($result->body['error']);
            } else if ($result->code > 0) {
                $errormsg .= ' HTTP ' . $result->code;
            } else {
                $errormsg .= ': ' . get_string('network_error_or_timeout', 'local_navigatr');
            }
            \core\notification::error($errormsg);
        }
    }
}

// Create form and handle cancellation/submission BEFORE any output.
$form = new \local_navigatr\form\admin_settings_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/admin/category.php', ['category' => 'localplugins']));
}

if ($data = $form->get_data()) {
    // Form was submitted and validated.
    set_config('env', $data->env, 'local_navigatr');
    \local_navigatr\local\password_manager::store_pat(trim($data->personal_access_token));
    set_config('timeout', $data->timeout, 'local_navigatr');
    \core\notification::success(get_string('settingssaved', 'local_navigatr'));
} else {
    // Set form data with current configuration values.
    $form->set_form_data();
}

echo $OUTPUT->header();

// Display admin notice.
echo \core\notification::info(get_string('provider_admin_notice', 'local_navigatr'));

// Display help documentation link.
$helpurl = get_string('help_center_url', 'local_navigatr');
$helplink = \html_writer::link($helpurl, get_string('help_center_link', 'local_navigatr'), [
    'target' => '_blank',
    'class' => 'btn btn-outline-info btn-sm',
]);
$helptext = get_string('help_setup_guide', 'local_navigatr', $helplink);
echo \core\notification::info($helptext);

// Check if a PAT is configured.
$currentpat = \local_navigatr\local\password_manager::get_pat();

// Display Remove Connection button if a PAT is configured.
if (!empty($currentpat)) {
    echo \html_writer::start_div('mb-4');
    $removeurl = new \moodle_url('/local/navigatr/settings_page.php', [
        'removeconnection' => 1,
        'sesskey' => sesskey(),
    ]);
    $removebutton = $OUTPUT->single_button($removeurl, get_string('remove_connection', 'local_navigatr'), 'post', [
        'class' => 'btn-danger',
        'onclick' => 'return confirm(\'' . get_string('remove_connection_confirm', 'local_navigatr') . '\')',
    ]);
    echo $removebutton;
    echo \html_writer::end_div();
}

$form->display();

echo $OUTPUT->footer();
