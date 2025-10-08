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

$PAGE->set_url('/local/navigatr/settings_page.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_navigatr'));
$PAGE->set_heading(get_string('pluginname', 'local_navigatr'));

// Handle form submission
$data = data_submitted();
if ($data && confirm_sesskey()) {
    if (isset($data->removeconnection)) {
        // Handle remove connection
        unset_config('username', 'local_navigatr');
        unset_config('password', 'local_navigatr');
        unset_config('access_token', 'local_navigatr');
        unset_config('access_expires_at', 'local_navigatr');
        unset_config('refresh_token', 'local_navigatr');
        unset_config('refresh_expires_at', 'local_navigatr');
        unset_config('nav_user_id', 'local_navigatr');
        \core\notification::success(get_string('connection_removed', 'local_navigatr'));
    } elseif (isset($data->testconnection)) {
        // Handle test connection
        $username = $data->username ?? '';
        $password = $data->password ?? '';
        $environment = $data->env ?? 'production';
        
        $result = \local_navigatr\local\api_client::test_connection($username, $password, $environment);
        if ($result->ok) {
            \core\notification::success('Connection successful!');
        } else {
            // Build detailed error message
            $errormsg = 'Connection failed';
            if (!empty($result->error)) {
                $errormsg .= ': ' . s($result->error);
            } elseif (!empty($result->body) && is_array($result->body) && isset($result->body['error'])) {
                $errormsg .= ': ' . s($result->body['error']);
            } elseif ($result->code > 0) {
                $errormsg .= ' (HTTP ' . $result->code . ')';
            } else {
                $errormsg .= ': Network error or timeout';
            }
            \core\notification::error($errormsg);
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

// Display admin notice
echo '<div class="alert alert-warning">';
echo '<i class="fa fa-info-circle" aria-hidden="true"></i> ';
echo get_string('provider_admin_notice', 'local_navigatr');
echo '</div>';

// Check if credentials are configured
$current_username = get_config('local_navigatr', 'username');
$current_password = get_config('local_navigatr', 'password');

// Display Remove Connection button if credentials are configured
if (!empty($current_username) && !empty($current_password)) {
    echo '<div class="mb-3">';
    echo '<form method="post" action="" style="display: inline;">';
    echo '<input type="hidden" name="sesskey" value="' . sesskey() . '">';
    echo '<button type="submit" name="removeconnection" class="btn btn-danger" ';
    echo 'onclick="return confirm(\'' . get_string('remove_connection_confirm', 'local_navigatr') . '\')">';
    echo '<i class="fa fa-trash" aria-hidden="true"></i> ' . get_string('remove_connection', 'local_navigatr');
    echo '</button>';
    echo '</form>';
    echo '</div>';
}

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
