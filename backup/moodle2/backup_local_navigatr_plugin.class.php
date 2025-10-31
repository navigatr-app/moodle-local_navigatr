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
 * Backup plugin class for local_navigatr
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/moodle2/backup_local_plugin.class.php');

/**
 * Backup plugin class for local_navigatr
 *
 * Provides backup functionality for course badge mappings and audit records.
 */
class backup_local_navigatr_plugin extends backup_local_plugin
{
    /**
     * Define the course plugin structure for backup
     *
     * @return backup_plugin_element
     */
    protected function define_course_plugin_structure()
    {

        // Get the user info setting to determine if we should backup audit records.
        $userinfo = $this->get_setting_value('users');

        // Debug logging to track backup operations.
        debugging('local_navigatr: Starting backup structure definition (userinfo: ' .
                 ($userinfo ? 'included' : 'excluded') . ')', DEBUG_DEVELOPER);

        // Create the plugin element - this will be the root of our backup structure.
        $plugin = $this->get_plugin_element();

        // Create the navigatr wrapper element.
        $navigatr = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($navigatr);

        // Define the mappings structure.
        // This contains course-to-badge mapping configuration.
        $mappings = new backup_nested_element('mappings');
        $mapping = new backup_nested_element('mapping', ['id'], [
            'courseid',
            'provider_id',
            'badge_id',
            'badge_name',
            'badge_image_url',
            'timemodified',
        ]);

        // Build the tree for mappings.
        $navigatr->add_child($mappings);
        $mappings->add_child($mapping);

        // Set source for mappings - always backup course badge mappings.
        // These are course configuration data, not user data.
        $mapping->set_source_table('local_navigatr_map', [
            'courseid' => backup::VAR_COURSEID,
        ]);

        // No ID annotations needed:
        // - courseid: filtered by VAR_COURSEID, will be explicitly set during restore
        // - provider_id and badge_id: external references to Navigatr platform (not Moodle entities)

        debugging('local_navigatr: Badge mappings structure defined for backup', DEBUG_DEVELOPER);

        // Define the audits structure (only if user data is included).
        // Audit records contain user-specific data about badge issuances.
        if ($userinfo) {
            $audits = new backup_nested_element('audits');
            $audit = new backup_nested_element('audit', ['id'], [
                'userid',
                'courseid',
                'provider_id',
                'badge_id',
                'status',
                'http_code',
                'response_json',
                'dedupe_key',
                'timecreated',
            ]);

            // Build the tree for audits.
            $navigatr->add_child($audits);
            $audits->add_child($audit);

            // Set source for audits - only backup if user data is included.
            $audit->set_source_table('local_navigatr_audit', [
                'courseid' => backup::VAR_COURSEID,
            ]);

            // Annotate user IDs for proper mapping during restore.
            // userid varies across records and must be remapped to new user IDs.
            $audit->annotate_ids('user', 'userid');

            // Note: courseid not annotated as it's filtered by VAR_COURSEID and will be explicitly set during restore.
            // provider_id and badge_id are external references to Navigatr platform (not Moodle entities).

            debugging('local_navigatr: Audit records structure defined for backup (user data included)', DEBUG_DEVELOPER);
        } else {
            debugging('local_navigatr: Audit records excluded from backup (user data not included)', DEBUG_DEVELOPER);
        }

        debugging('local_navigatr: Backup structure definition complete', DEBUG_DEVELOPER);

        return $plugin;
    }
}
