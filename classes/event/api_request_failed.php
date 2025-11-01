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
 * API request failed event.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\event;

/**
 * API request failed event class.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_request_failed extends \core\event\base
{
    /**
     * Init method.
     *
     * @return void
     */
    protected function init()
    {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description()
    {
        $operation = isset($this->other['operation']) ? $this->other['operation'] : 'unknown operation';
        $error = isset($this->other['error']) ? $this->other['error'] : 'unknown error';

        return "API request failed during {$operation}. Error: {$error}.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name()
    {
        return get_string('eventapirequestfailed', 'local_navigatr');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url()
    {
        return new \moodle_url('/admin/settings.php', ['section' => 'local_navigatr']);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data()
    {
        parent::validate_data();

        if (!isset($this->other['operation'])) {
            throw new \coding_exception('The \'operation\' value must be set in other.');
        }

        if (!isset($this->other['error'])) {
            throw new \coding_exception('The \'error\' value must be set in other.');
        }
    }
}
