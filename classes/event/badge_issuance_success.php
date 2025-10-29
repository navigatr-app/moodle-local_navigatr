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
 * Badge issuance success event.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Badge issuance success event class.
 */
class badge_issuance_success extends \core\event\base
{
    /**
     * Initialise the event.
     */
    protected function init()
    {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'local_navigatr_audit';
        $this->data['objectid'] = 0; // Set objectid when objecttable is defined
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name()
    {
        return get_string('event_badge_issuance_success', 'local_navigatr');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description()
    {
        $badgeid = isset($this->other['badge_id']) ? $this->other['badge_id'] : 'unknown';
        $providerid = isset($this->other['provider_id']) ? $this->other['provider_id'] : 'unknown';
        $httpcode = isset($this->other['http_code']) ? $this->other['http_code'] : 'unknown';

        return "User {$this->userid} successfully received badge '{$badgeid}' from provider '{$providerid}' for course {$this->courseid} (HTTP {$httpcode})";
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url()
    {
        return new \moodle_url('/course/view.php', ['id' => $this->courseid]);
    }

    /**
     * Return the legacy event log data.
     *
     * @return array
     */
    protected function get_legacy_logdata()
    {
        return [
            $this->courseid,
            'local_navigatr',
            'badge issued',
            '',
            "Badge {$this->other['badge_id']} issued to user {$this->userid}"
        ];
    }
}
