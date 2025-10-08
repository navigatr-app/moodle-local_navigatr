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
 * Admin settings form for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Admin settings form class.
 */
class admin_settings_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        // Credentials
        $mform->addElement('text', 'username', get_string('username', 'local_navigatr'));
        $mform->setType('username', PARAM_TEXT);
        $stored_username = get_config('local_navigatr', 'username');
        if (!empty($stored_username)) {
            $mform->setDefault('username', $stored_username);
        }
        $mform->addRule('username', get_string('required'), 'required', null, 'client');

        $mform->addElement('passwordunmask', 'password', get_string('password', 'local_navigatr'));
        $mform->setType('password', PARAM_TEXT);
        $mform->addRule('password', get_string('required'), 'required', null, 'client');

        // Test connection button - moved outside advanced settings for better visibility
        $mform->addElement('submit', 'testconnection', get_string('test_connection', 'local_navigatr'));

        // Advanced settings
        $mform->addElement('header', 'advanced', get_string('advanced_settings', 'local_navigatr'));

        $mform->addElement('text', 'timeout', get_string('timeout', 'local_navigatr'));
        $mform->setType('timeout', PARAM_INT);
        $stored_timeout = get_config('local_navigatr', 'timeout');
        $mform->setDefault('timeout', $stored_timeout ?: 30);
        $mform->addRule('timeout', get_string('required'), 'required', null, 'client');

        $mform->addElement('select', 'loglevel', get_string('loglevel', 'local_navigatr'), [
            'error' => get_string('loglevel_error', 'local_navigatr'),
            'info' => get_string('loglevel_info', 'local_navigatr'),
            'debug' => get_string('loglevel_debug', 'local_navigatr'),
        ]);
        $mform->setType('loglevel', PARAM_ALPHA);
        $mform->setDefault('loglevel', get_config('local_navigatr', 'loglevel') ?: 'info');

        // Environment selection
        $mform->addElement('select', 'env', get_string('environment', 'local_navigatr'), [
            'production' => get_string('environment_production', 'local_navigatr'),
            'staging' => get_string('environment_staging', 'local_navigatr'),
        ]);
        $mform->setType('env', PARAM_ALPHA);
        $stored_env = get_config('local_navigatr', 'env');
        $mform->setDefault('env', $stored_env ?: 'production');

        // Save button
        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Form validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['timeout'] < 1 || $data['timeout'] > 300) {
            $errors['timeout'] = get_string('timeout_invalid', 'local_navigatr');
        }

        return $errors;
    }
}
