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
 * Admin settings form for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\form;

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

        // Resolve PAT creation URL based on current environment.
        $env = get_config('local_navigatr', 'env') ?: 'production';
        $paturl = ($env === 'staging')
            ? 'https://stag.navigatr.app/settings/personal-access-tokens/'
            : 'https://navigatr.app/settings/personal-access-tokens/';

        // Personal Access Token.
        $mform->addElement(
            'passwordunmask',
            'personal_access_token',
            get_string('personal_access_token', 'local_navigatr')
        );
        $mform->setType('personal_access_token', PARAM_TEXT);
        $mform->addHelpButton('personal_access_token', 'personal_access_token', 'local_navigatr');
        $mform->addRule('personal_access_token', get_string('required'), 'required', null, 'client');

        // Add PAT note with create link.
        $patwarning = \html_writer::div(
            get_string('pat_unmask_warning', 'local_navigatr', $paturl),
            'alert alert-info'
        );
        $mform->addElement('html', $patwarning);

        // Test connection button - moved outside advanced settings for better visibility.
        $mform->addElement(
            'submit',
            'testconnection',
            get_string('test_connection', 'local_navigatr'),
            ['class' => 'btn-secondary mb-3']
        );

        // Advanced settings.
        $mform->addElement('header', 'advanced', get_string('advanced_settings', 'local_navigatr'));

        $mform->addElement('text', 'timeout', get_string('timeout', 'local_navigatr'));
        $mform->setType('timeout', PARAM_INT);
        $mform->addHelpButton('timeout', 'timeout', 'local_navigatr');
        $mform->addRule('timeout', get_string('required'), 'required', null, 'client');

        // Environment selection.
        $mform->addElement('select', 'env', get_string('environment', 'local_navigatr'), [
            'production' => get_string('environment_production', 'local_navigatr'),
            'staging' => get_string('environment_staging', 'local_navigatr'),
        ]);
        $mform->setType('env', PARAM_ALPHA);
        $mform->addHelpButton('env', 'environment', 'local_navigatr');

        // Save button.
        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Set form data with current configuration values.
     */
    public function set_form_data() {
        $data = [
            'timeout' => get_config('local_navigatr', 'timeout') ?: 30,
            'env' => get_config('local_navigatr', 'env') ?: 'production',
        ];
        $this->set_data($data);
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
