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
 * Event observer for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr;

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
        $userid = $event->userid;

        // Check if there's a mapping for this course.
        $mapping = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
        if (!$mapping) {
            return; // No badge mapping for this course.
        }

        // Enqueue badge issuance task.
        $task = new \local_navigatr\task\issue_badge_task();
        $task->set_custom_data([
            'userid' => $userid,
            'courseid' => $courseid,
        ]);
        \core\task\manager::queue_adhoc_task($task);

        // Trigger event for badge issuance being queued.
        $eventdata = \local_navigatr\event\badge_issuance_queued::create([
            'context' => \context_course::instance($courseid),
            'userid' => $userid,
            'courseid' => $courseid,
            'other' => [
                'badgeid' => $mapping->badge_id,
                'provider_id' => $mapping->provider_id,
            ],
        ]);
        $eventdata->trigger();
    }

    /**
     * Handle course restored event.
     *
     * This method is called after a course is restored from a backup.
     * It manually processes the navigatr badge mappings from the backup XML
     * because the standard backup/restore API doesn't process course-level
     * local plugin data when restoring to existing courses.
     *
     * @param \core\event\course_restored $event Course restored event
     */
    public static function course_restored(\core\event\course_restored $event) {
        global $DB, $CFG;

        $courseid = $event->courseid;

        // The restore event doesn't provide direct access to the backup directory,.
        // but we can search for recent restore directories in the temp folder.
        debugging('local_navigatr: Looking for recent backup directories', DEBUG_DEVELOPER);

        // Get all backup directories.
        $backupbasedir = $CFG->tempdir . '/backup';
        if (!is_dir($backupbasedir)) {
            debugging('local_navigatr: Backup base directory not found', DEBUG_DEVELOPER);
            return;
        }

        // Find the most recent directory (this is a heuristic, but should work in most cases).
        $dirs = glob($backupbasedir . '/*', GLOB_ONLYDIR);
        if (empty($dirs)) {
            debugging('local_navigatr: No backup directories found', DEBUG_DEVELOPER);
            return;
        }

        // Sort by modification time, most recent first.
        usort($dirs, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Try the most recent directories.
        $navigatrdata = null;
        foreach ($dirs as $backupdir) {
            $coursexml = $backupdir . '/course/course.xml';

            if (!file_exists($coursexml)) {
                continue; // Try next directory.
            }

            // Load and parse the course XML.
            $xml = simplexml_load_file($coursexml);
            if ($xml === false) {
                continue; // Try next directory.
            }

            // Find the navigatr plugin data.
            $plugindata = $xml->xpath('//plugin_local_navigatr_course');
            if (!empty($plugindata)) {
                $navigatrdata = $plugindata[0];
                debugging('local_navigatr: Found navigatr data in ' . basename($backupdir), DEBUG_DEVELOPER);
                break; // Found it!
            }
        }

        if (!$navigatrdata) {
            debugging('local_navigatr: No navigatr data found in any recent backup directory', DEBUG_DEVELOPER);
            return;
        }

        // Check if a mapping already exists for this course.
        $existing = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
        if ($existing) {
            // Trigger event for skipped mapping.
            $eventdata = \local_navigatr\event\course_mapping_skipped::create([
                'context' => \context_course::instance($courseid),
                'courseid' => $courseid,
                'other' => [
                    'reason' => 'Mapping already exists - preserving existing configuration',
                ],
            ]);
            $eventdata->trigger();
            return;
        }

        // Process badge mappings.
        if (isset($navigatrdata->mappings->mapping)) {
            foreach ($navigatrdata->mappings->mapping as $mapping) {
                $record = new \stdClass();
                $record->courseid = $courseid; // Use the NEW course ID.
                $record->provider_id = (int)$mapping->provider_id;
                $record->badge_id = (int)$mapping->badge_id;
                $record->badge_name = (string)$mapping->badge_name;
                $record->badge_image_url = (string)$mapping->badge_image_url;
                $record->timemodified = time();

                try {
                    $newid = $DB->insert_record('local_navigatr_map', $record);

                    // Trigger event for successful restore.
                    $eventdata = \local_navigatr\event\course_mapping_restored::create([
                        'context' => \context_course::instance($courseid),
                        'courseid' => $courseid,
                        'other' => [
                            'badge_id' => $record->badge_id,
                            'provider_id' => $record->provider_id,
                            'new_mapping_id' => $newid,
                        ],
                    ]);
                    $eventdata->trigger();
                } catch (\Exception $e) {
                    debugging('local_navigatr: Failed to restore mapping - ' . $e->getMessage(), DEBUG_NORMAL);
                }

                // Only restore the first mapping (there should only be one per course).
                break;
            }
        }

        // Note: Audit records are not restored here as they contain user-specific data.
        // and are typically excluded when restoring to different systems.
        debugging('local_navigatr: Restore processing complete for course ' . $courseid, DEBUG_DEVELOPER);
    }
}
