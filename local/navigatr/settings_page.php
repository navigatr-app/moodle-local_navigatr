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
 * Settings page for Navigatr plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_url('/local/navigatr/settings.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_navigatr'));
$PAGE->set_heading(get_string('pluginname', 'local_navigatr'));

// Handle form submission
$data = data_submitted();
if ($data && confirm_sesskey()) {
    if (isset($data->testconnection)) {
        // Handle test connection
        $username = $data->username ?? '';
        $password = $data->password ?? '';
        $environment = $data->env ?? 'production';
        
        $result = \local_navigatr\local\api_client::test_connection($username, $password, $environment);
        if ($result->ok) {
            \core\notification::success('Connection successful!');
        } else {
            \core\notification::error('Connection failed: ' . ($result->error ?? 'Unknown error'));
        }
    } else {
        // Save settings
        if (isset($data->env)) {
            set_config('env', $data->env, 'local_navigatr');
        }
        if (isset($data->username)) {
            set_config('username', $data->username, 'local_navigatr');
        }
        if (isset($data->password)) {
            set_config('password', $data->password, 'local_navigatr');
        }
        if (isset($data->timeout)) {
            set_config('timeout', $data->timeout, 'local_navigatr');
        }
        if (isset($data->loglevel)) {
            set_config('loglevel', $data->loglevel, 'local_navigatr');
        }
        \core\notification::success(get_string('settingssaved', 'local_navigatr'));
    }
}

echo $OUTPUT->header();

// Create form
$form = new \local_navigatr\form\admin_settings_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/admin/settings.php', ['section' => 'localplugins']));
}

if ($data = $form->get_data()) {
    // Form was submitted and validated
    set_config('env', $data->env, 'local_navigatr');
    set_config('username', $data->username, 'local_navigatr');
    set_config('password', $data->password, 'local_navigatr');
    set_config('timeout', $data->timeout, 'local_navigatr');
    set_config('loglevel', $data->loglevel, 'local_navigatr');
    \core\notification::success(get_string('settingssaved', 'local_navigatr'));
}

$form->display();

echo $OUTPUT->footer();
