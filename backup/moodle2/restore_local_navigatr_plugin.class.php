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
 * Restore plugin class for local_navigatr
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/moodle2/restore_local_plugin.class.php');

/**
 * Restore plugin class for local_navigatr
 *
 * Provides restore functionality for course badge mappings and audit records.
 */
class restore_local_navigatr_plugin extends restore_local_plugin
{
    /**
     * Define the course plugin structure for restore
     *
     * @return array Array of restore_path_element objects
     */
    protected function define_course_plugin_structure()
    {

        $paths = [];

        // Debug logging to track restore operations.
        debugging('local_navigatr: Starting restore structure definition', DEBUG_DEVELOPER);

        // Define the path to badge mappings in the backup XML.
        // This tells Moodle where to find the mapping data and which method to call to process it.
        $paths[] = new restore_path_element(
            'local_navigatr_mapping',
            $this->get_pathfor('/mappings/mapping')
        );

        debugging('local_navigatr: Badge mappings path defined for restore', DEBUG_DEVELOPER);

        // Define the path to audit records if user data is being restored.
        if ($this->get_setting_value('users')) {
            $paths[] = new restore_path_element(
                'local_navigatr_audit',
                $this->get_pathfor('/audits/audit')
            );

            debugging('local_navigatr: Audit records path defined for restore (user data included)', DEBUG_DEVELOPER);
        } else {
            debugging('local_navigatr: Audit records excluded from restore (user data not included)', DEBUG_DEVELOPER);
        }

        debugging('local_navigatr: Restore structure definition complete', DEBUG_DEVELOPER);

        return $paths;
    }

    /**
     * Process badge mapping data during restore
     *
     * @param array|object $data The mapping data from backup
     */
    public function process_local_navigatr_mapping($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        mtrace('local_navigatr: Processing mapping restore (old courseid: ' . $data->courseid .
                  ', badge_id: ' . $data->badge_id . ')');
        debugging('local_navigatr: Processing mapping restore (old courseid: ' . $data->courseid .
                  ', badge_id: ' . $data->badge_id . ')', DEBUG_DEVELOPER);

        // Map old course ID to new course ID.
        // This is critical - the course ID will be different in the target system.
        $data->courseid = $this->get_courseid();

        debugging('local_navigatr: Mapped to new courseid: ' . $data->courseid, DEBUG_DEVELOPER);

        // Check if a mapping already exists for this course.
        // We don't want to overwrite existing badge configurations.
        $existing = $DB->get_record('local_navigatr_map', ['courseid' => $data->courseid]);

        if ($existing) {
            debugging('local_navigatr: Mapping already exists for courseid ' . $data->courseid .
                     ' - skipping restore to preserve existing configuration', DEBUG_DEVELOPER);
            return;
        }

        // Remove the old ID - we'll get a new one when inserting.
        unset($data->id);

        // Insert the restored mapping.
        try {
            $newid = $DB->insert_record('local_navigatr_map', $data);
            debugging('local_navigatr: Mapping restored successfully (new id: ' . $newid . ')', DEBUG_DEVELOPER);

            // Set mapping for potential future reference.
            // This allows other restore processes to reference our data if needed.
            $this->set_mapping('local_navigatr_map', $oldid, $newid);
        } catch (Exception $e) {
            debugging('local_navigatr: Failed to restore mapping - ' . $e->getMessage(), DEBUG_NORMAL);
        }
    }

    /**
     * Process audit record data during restore
     *
     * @param array|object $data The audit data from backup
     */
    public function process_local_navigatr_audit($data)
    {
        global $DB;

        $data = (object)$data;

        debugging('local_navigatr: Processing audit restore (old userid: ' . $data->userid .
                 ', old courseid: ' . $data->courseid . ', badge_id: ' . $data->badge_id . ')', DEBUG_DEVELOPER);

        // Map old course ID to new course ID.
        $data->courseid = $this->get_courseid();

        // Map old user ID to new user ID.
        // The get_mappingid method returns the new ID based on Moodle's user mapping table.
        // Returns 0 if user doesn't exist in target system.
        $data->userid = $this->get_mappingid('user', $data->userid);

        // Skip if user doesn't exist in target system.
        if (empty($data->userid)) {
            debugging('local_navigatr: Skipping audit record - user not found in target system', DEBUG_DEVELOPER);
            return;
        }

        debugging('local_navigatr: Mapped to new userid: ' . $data->userid . ', new courseid: ' . $data->courseid, DEBUG_DEVELOPER);

        // Remove the old ID - we'll get a new one when inserting.
        unset($data->id);

        // The dedupe_key has a unique constraint in the database.
        // We need to handle potential duplicates gracefully.
        // This can happen if the audit record already exists in the target course.
        try {
            // Check if this dedupe_key already exists.
            $existing = $DB->get_record('local_navigatr_audit', ['dedupe_key' => $data->dedupe_key]);

            if ($existing) {
                debugging('local_navigatr: Audit record with dedupe_key ' . $data->dedupe_key .
                         ' already exists - skipping to avoid duplicate', DEBUG_DEVELOPER);
                return;
            }

            // Insert the restored audit record.
            $newid = $DB->insert_record('local_navigatr_audit', $data);
            debugging('local_navigatr: Audit record restored successfully (new id: ' . $newid . ')', DEBUG_DEVELOPER);
        } catch (dml_exception $e) {
            // Handle database exceptions (like unique constraint violations).
            debugging('local_navigatr: Failed to restore audit record - ' . $e->getMessage(), DEBUG_NORMAL);
        }
    }
}
