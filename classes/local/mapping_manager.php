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
 * Course badge mapping writes for the Navigatr plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

use local_navigatr\event\course_mapping_created;
use local_navigatr\event\course_mapping_deleted;
use local_navigatr\event\course_mapping_updated;

/**
 * Creates, updates and removes course badge mappings, and logs each change as an event.
 */
class mapping_manager {
    /**
     * Create or update the badge mapping for a course.
     *
     * Badge name and image URL are only written when they are given, so an update
     * keeps the previously stored values when the badge metadata could not be fetched.
     *
     * @param int $courseid The course ID
     * @param int $providerid The Navigatr provider ID
     * @param int $badgeid The Navigatr badge ID
     * @param string|null $badgename The badge name, if fetched
     * @param string|null $badgeimageurl The badge image URL, if fetched
     * @return int The mapping record ID
     */
    public static function save(
        int $courseid,
        int $providerid,
        int $badgeid,
        ?string $badgename = null,
        ?string $badgeimageurl = null
    ): int {
        global $DB;

        $mapping = new \stdClass();
        $mapping->courseid = $courseid;
        $mapping->provider_id = $providerid;
        $mapping->badge_id = $badgeid;
        $mapping->timemodified = time();
        if ($badgename !== null) {
            $mapping->badge_name = $badgename;
        }
        if ($badgeimageurl !== null) {
            $mapping->badge_image_url = $badgeimageurl;
        }

        $existing = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
        if ($existing) {
            $mapping->id = $existing->id;
            $DB->update_record('local_navigatr_map', $mapping);
            $eventclass = course_mapping_updated::class;
        } else {
            $mapping->timecreated = $mapping->timemodified;
            $mapping->id = $DB->insert_record('local_navigatr_map', $mapping);
            $eventclass = course_mapping_created::class;
        }

        $eventclass::create([
            'objectid' => $mapping->id,
            'context' => \context_course::instance($courseid),
            'other' => ['badge_id' => $badgeid, 'provider_id' => $providerid],
        ])->trigger();

        return (int) $mapping->id;
    }

    /**
     * Remove the badge mapping for a course.
     *
     * @param int $courseid The course ID
     * @return bool True if a mapping was removed, false if the course had none
     */
    public static function remove(int $courseid): bool {
        global $DB;

        $mapping = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
        if (!$mapping) {
            return false;
        }

        $DB->delete_records('local_navigatr_map', ['id' => $mapping->id]);

        course_mapping_deleted::create([
            'objectid' => $mapping->id,
            'context' => \context_course::instance($courseid),
            'other' => ['badge_id' => (int) $mapping->badge_id, 'provider_id' => (int) $mapping->provider_id],
        ])->trigger();

        return true;
    }
}
