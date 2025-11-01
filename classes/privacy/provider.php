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
 * Privacy provider for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;

/**
 * Privacy provider class.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider
{
    /**
     * Returns metadata about this plugin.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this plugin.
     */
    public static function get_metadata(collection $collection): collection
    {
        $collection->add_database_table('local_navigatr_audit', [
            'userid' => 'privacy:metadata:local_navigatr_audit:userid',
            'courseid' => 'privacy:metadata:local_navigatr_audit:courseid',
            'provider_id' => 'privacy:metadata:local_navigatr_audit:provider_id',
            'badge_id' => 'privacy:metadata:local_navigatr_audit:badge_id',
            'status' => 'privacy:metadata:local_navigatr_audit:status',
            'timecreated' => 'privacy:metadata:local_navigatr_audit:timecreated',
        ], 'privacy:metadata:local_navigatr_audit');

        $collection->add_external_location_link('navigatr', [
            'recipient_email' => 'privacy:metadata:navigatr:recipient_email',
            'recipient_firstname' => 'privacy:metadata:navigatr:recipient_firstname',
            'recipient_lastname' => 'privacy:metadata:navigatr:recipient_lastname',
        ], 'privacy:metadata:navigatr:purpose');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist
    {
        $contextlist = new contextlist();

        // Add course contexts where user has audit records.
        $sql = "SELECT DISTINCT c.id
                FROM {context} c
                JOIN {course} co ON co.id = c.instanceid
                JOIN {local_navigatr_audit} na ON na.courseid = co.id
                WHERE c.contextlevel = :contextlevel
                AND na.userid = :userid";

        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $userid
        ]);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist)
    {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $sql = "SELECT na.userid
                FROM {local_navigatr_audit} na
                WHERE na.courseid = :courseid";

        $userlist->add_from_sql('userid', $sql, ['courseid' => $context->instanceid]);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist)
    {
        global $DB;

        $user = $contextlist->get_user();
        $userid = $user->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_COURSE) {
                continue;
            }

            $courseid = $context->instanceid;

            // Get audit records for this user and course.
            $auditrecords = $DB->get_records('local_navigatr_audit', [
                'userid' => $userid,
                'courseid' => $courseid
            ]);

            if (!empty($auditrecords)) {
                $data = (object) [
                    'audit_records' => array_values($auditrecords)
                ];

                \core_privacy\local\request\writer::with_context($context)
                    ->export_data([get_string('pluginname', 'local_navigatr')], $data);
            }
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist)
    {
        global $DB;

        $user = $contextlist->get_user();
        $userid = $user->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_COURSE) {
                continue;
            }

            $courseid = $context->instanceid;

            // Delete audit records for this user and course.
            $DB->delete_records('local_navigatr_audit', [
                'userid' => $userid,
                'courseid' => $courseid
            ]);
        }
    }

    /**
     * Delete all user data for the specified users, in the specified context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist)
    {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $courseid = $context->instanceid;
        $userids = $userlist->get_userids();

        if (!empty($userids)) {
            [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
            $params = array_merge(['courseid' => $courseid], $inparams);

            $DB->delete_records_select(
                'local_navigatr_audit',
                "courseid = :courseid AND userid $insql",
                $params
            );
        }
    }

    /**
     * Delete all user data for the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context)
    {
        global $DB;

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $courseid = $context->instanceid;

        // Delete all audit records for this course.
        $DB->delete_records('local_navigatr_audit', ['courseid' => $courseid]);
    }
}
