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
 * Badge issuance queued event.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Badge issuance queued event class.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class badge_issuance_queued extends \core\event\base
{
    /**
     * Init method.
     *
     * @return void
     */
    protected function init()
    {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'local_navigatr_map';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description()
    {
        $badgeid = isset($this->other['badgeid']) ? $this->other['badgeid'] : 'unknown';
        $providerid = isset($this->other['provider_id']) ? $this->other['provider_id'] : 'unknown';

        return "Badge issuance task queued for user {$this->userid} in course {$this->courseid}. " .
               "Badge ID: {$badgeid}, Provider ID: {$providerid}.";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name()
    {
        return get_string('eventbadgeissuancequeued', 'local_navigatr');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url()
    {
        return new \moodle_url('/course/view.php', ['id' => $this->courseid]);
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

        if (!isset($this->other['badgeid'])) {
            throw new \coding_exception('The \'badgeid\' value must be set in other.');
        }

        if (!isset($this->other['provider_id'])) {
            throw new \coding_exception('The \'provider_id\' value must be set in other.');
        }
    }
}
