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
 * Adhoc task for issuing Navigatr badges.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\task;

/**
 * Adhoc task for issuing Navigatr badges.
 */
class issue_badge_task extends \core\task\adhoc_task {
    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        $data = $this->get_custom_data();
        $userid = $data->userid;
        $courseid = $data->courseid;

        // Get mapping for this course (may have been deleted since the task was queued).
        $mapping = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
        if (!$mapping) {
            return; // Mapping was removed after the task was queued; nothing to do.
        }

        // Get user details.
        $user = \core_user::get_user($userid, 'id,email,firstname,lastname', MUST_EXIST);

        // Validate required user fields.
        $requiredfields = ['email', 'firstname', 'lastname'];
        foreach ($requiredfields as $field) {
            if (empty($user->$field)) {
                $this->write_audit(
                    $userid,
                    $courseid,
                    $mapping->provider_id,
                    $mapping->badge_id,
                    'error',
                    400,
                    json_encode(['error' => get_string('missing_user_field', 'local_navigatr', $field)])
                );
                return;
            }
        }

        // Check for existing successful issuance (idempotency).
        $dedupekey = "{$userid}:{$courseid}:{$mapping->badge_id}";
        $existing = $DB->get_record('local_navigatr_audit', [
            'dedupe_key' => $dedupekey,
            'status' => 'success',
        ]);
        if ($existing) {
            return; // Already successfully issued.
        }

        $auditwritten = false;
        try {
            // Get API client.
            $client = new \local_navigatr\local\api_client();

            // Get course name for evidence text.
            $course = $DB->get_record('course', ['id' => $courseid], 'fullname');
            $coursename = $course ? $course->fullname : get_string('unknown_course', 'local_navigatr');

            // Get course completion score if available.
            $score = $this->get_course_score($userid, $courseid);

            // Prepare badge issuance payload.
            $payload = [
                'evidence_text' => "Recipient completed course {$coursename}",
                'provider_id' => $mapping->provider_id,
                'recipient_email' => $user->email,
                'recipient_firstname' => $user->firstname,
                'recipient_lastname' => $user->lastname,
            ];

            // Add score to payload only if available.
            if ($score !== null) {
                $payload['score'] = $score;
            }

            // Issue badge.
            $response = $client->put("/badge/{$mapping->badge_id}/issue", $payload);

            // Write audit record (before any throw, so catch does not duplicate it).
            $this->write_audit(
                $userid,
                $courseid,
                $mapping->provider_id,
                $mapping->badge_id,
                $response->ok ? 'success' : 'error',
                $response->code,
                json_encode($response->body)
            );
            $auditwritten = true;

            // Throw exception for terminal failures to allow Moodle retries.
            if (!$response->ok && $response->code >= 500) {
                throw new \moodle_exception('issue_failed', 'local_navigatr', '', $response->code);
            }
        } catch (\Exception $e) {
            // Only write audit for unexpected exceptions; API-response audits are written above.
            if (!$auditwritten) {
                $this->write_audit(
                    $userid,
                    $courseid,
                    $mapping->provider_id,
                    $mapping->badge_id,
                    'error',
                    500,
                    json_encode(['error' => $e->getMessage()])
                );
            }

            // Re-throw to allow Moodle retry mechanism.
            throw $e;
        }
    }

    /**
     * Get course score for a user.
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     * @return int|null Course score (0-100) or null if not available
     */
    private function get_course_score($userid, $courseid) {
        global $DB;

        // Try to get final grade from gradebook.
        $grade = $DB->get_record_sql(
            "SELECT gg.finalgrade, gi.grademax
             FROM {grade_grades} gg
             JOIN {grade_items} gi ON gg.itemid = gi.id
             WHERE gg.userid = ? AND gi.courseid = ? AND gi.itemtype = 'course'",
            [$userid, $courseid]
        );

        if ($grade && $grade->finalgrade !== null && $grade->grademax > 0) {
            // Convert to percentage (0-100).
            $percentage = ($grade->finalgrade / $grade->grademax) * 100;
            return round($percentage);
        }

        // Try to get completion percentage if gradebook doesn't have final grade.
        if (class_exists('\core_completion\progress')) {
            $course = get_course($courseid);
            $progress = \core_completion\progress::get_course_progress_percentage($course, $userid);
            if ($progress !== null) {
                return round($progress);
            }
        }

        // Try course completion criteria.
        $completion = $DB->get_record('course_completions', [
            'userid' => $userid,
            'course' => $courseid,
        ]);

        if ($completion && $completion->timecompleted) {
            // If course is completed but no specific score, return 100.
            return 100;
        }

        return null;
    }

    /**
     * Write audit record.
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     * @param int $providerid Provider ID
     * @param int $badgeid Badge ID
     * @param string $status Status (success/error)
     * @param int $httpcode HTTP response code
     * @param string $responsejson Response JSON
     */
    private function write_audit($userid, $courseid, $providerid, $badgeid, $status, $httpcode, $responsejson) {
        global $DB;

        $dedupekey = "{$userid}:{$courseid}:{$badgeid}";

        $record = (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'provider_id' => $providerid,
            'badge_id' => $badgeid,
            'status' => $status,
            'http_code' => $httpcode,
            'response_json' => $responsejson,
            'dedupe_key' => $dedupekey,
            'timecreated' => time(),
        ];

        // Use insert_or_update to handle duplicate dedupe_key.
        $existing = $DB->get_record('local_navigatr_audit', ['dedupe_key' => $dedupekey]);
        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record('local_navigatr_audit', $record);
        } else {
            $DB->insert_record('local_navigatr_audit', $record);
        }

        // Trigger appropriate Moodle event for core logging integration.
        $this->trigger_audit_event($userid, $courseid, $providerid, $badgeid, $status, $httpcode, $responsejson);
    }

    /**
     * Trigger appropriate Moodle event based on audit status.
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     * @param int $providerid Provider ID
     * @param int $badgeid Badge ID
     * @param string $status Status (success/error)
     * @param int $httpcode HTTP response code
     * @param string $responsejson Response JSON
     */
    private function trigger_audit_event($userid, $courseid, $providerid, $badgeid, $status, $httpcode, $responsejson) {
        $context = \context_course::instance($courseid);
        $other = [
            'badge_id' => $badgeid,
            'provider_id' => $providerid,
            'http_code' => $httpcode,
        ];

        if ($status === 'success') {
            $event = \local_navigatr\event\badge_issuance_success::create([
                'context' => $context,
                'userid' => $userid,
                'other' => $other,
            ]);
        } else {
            // Add error details for failed attempts.
            $other['error'] = $responsejson;
            $event = \local_navigatr\event\badge_issuance_failed::create([
                'context' => $context,
                'userid' => $userid,
                'other' => $other,
            ]);
        }

        $event->trigger();
    }
}
