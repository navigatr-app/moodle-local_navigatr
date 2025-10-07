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
 * Event observer for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer class.
 */
class observer {

    /**
     * Handle course completion event.
     *
     * @param \core\event\course_completed $event Course completion event
     */
    public static function course_completed(\core\event\course_completed $event) {
        global $DB;

        $courseid = $event->courseid;
        $userid = $event->relateduserid;

        // Check if there's a mapping for this course
        $mapping = $DB->get_record('local_navi_map', ['courseid' => $courseid]);
        if (!$mapping) {
            return; // No badge mapping for this course
        }

        // Enqueue badge issuance task
        $task = new \local_navigatr\task\issue_badge_task();
        $task->set_custom_data([
            'userid' => $userid,
            'courseid' => $courseid,
        ]);
        \core\task\manager::queue_adhoc_task($task);
    }
}
