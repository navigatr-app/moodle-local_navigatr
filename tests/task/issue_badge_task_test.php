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
 * Unit tests for Navigatr Badge Issuance Task.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\task;

use advanced_testcase;
use local_navigatr\task\issue_badge_task;

/**
 * Unit tests for Navigatr Badge Issuance Task.
 *
 * @covers \local_navigatr\task\issue_badge_task
 */
final class issue_badge_task_test extends advanced_testcase {
    /**
     * Test class and execute method exist.
     */
    public function test_class_structure(): void {
        $this->assertTrue(class_exists(issue_badge_task::class));
        $this->assertTrue(method_exists(issue_badge_task::class, 'execute'));
    }

    /**
     * Test get_name returns a non-empty string (inherited from adhoc_task).
     *
     * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_get_name(): void {
        $task = new issue_badge_task();
        $this->assertIsString($task->get_name());
        $this->assertNotEmpty($task->get_name());
    }

    /**
     * Test execute() is a public method.
     *
     * @covers \local_navigatr\task\issue_badge_task::execute
     */
    public function test_execute_method_is_public(): void {
        $reflection = new \ReflectionMethod(issue_badge_task::class, 'execute');
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * Test execute() writes an error audit record when no PAT is configured.
     *
     * This test exercises the full task execution path — DB reads, user validation,
     * api_client invocation — without making any network calls. With no PAT stored,
     * api_client::put() returns a 401 response immediately, and the task writes an
     * error audit record.
     *
     * @covers \local_navigatr\task\issue_badge_task::execute
     */
    public function test_execute_records_error_audit_when_no_pat_configured(): void {
        global $DB;
        $this->resetAfterTest();

        // Ensure no PAT is configured so the API client returns 401 without a network call.
        unset_config('personal_access_token', 'local_navigatr');

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        $DB->insert_record('local_navigatr_map', (object) [
            'courseid' => $course->id,
            'provider_id' => 1,
            'badge_id' => 42,
            'badge_name' => 'Test Badge',
            'badge_image_url' => 'https://example.com/badge.png',
            'timemodified' => time(),
        ]);

        $task = new issue_badge_task();
        $task->set_custom_data(['userid' => $user->id, 'courseid' => $course->id]);
        $task->execute();

        $record = $DB->get_record('local_navigatr_audit', [
            'userid' => $user->id,
            'courseid' => $course->id,
        ]);

        $this->assertNotFalse($record, 'An audit record should have been written.');
        $this->assertSame('error', $record->status);
        $this->assertSame(401, (int) $record->http_code);
        $this->assertSame("{$user->id}:{$course->id}:42", $record->dedupe_key);
    }

    /**
     * Test execute() is idempotent: a second run is skipped when a success record exists.
     *
     * @covers \local_navigatr\task\issue_badge_task::execute
     */
    public function test_execute_skips_when_already_successfully_issued(): void {
        global $DB;
        $this->resetAfterTest();

        unset_config('personal_access_token', 'local_navigatr');

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        $DB->insert_record('local_navigatr_map', (object) [
            'courseid' => $course->id,
            'provider_id' => 1,
            'badge_id' => 42,
            'badge_name' => 'Test Badge',
            'badge_image_url' => 'https://example.com/badge.png',
            'timemodified' => time(),
        ]);

        // Simulate a prior successful issuance.
        $dedupekey = "{$user->id}:{$course->id}:42";
        $DB->insert_record('local_navigatr_audit', (object) [
            'userid' => $user->id,
            'courseid' => $course->id,
            'provider_id' => 1,
            'badge_id' => 42,
            'status' => 'success',
            'http_code' => 200,
            'response_json' => '{}',
            'dedupe_key' => $dedupekey,
            'timecreated' => time(),
        ]);

        $task = new issue_badge_task();
        $task->set_custom_data(['userid' => $user->id, 'courseid' => $course->id]);
        $task->execute();

        // The pre-existing success record must not have been changed.
        $record = $DB->get_record('local_navigatr_audit', ['dedupe_key' => $dedupekey]);
        $this->assertSame('success', $record->status);
        $this->assertSame(1, $DB->count_records('local_navigatr_audit', ['userid' => $user->id]));
    }

    /**
     * Test execute() writes a 400 error audit when the user is missing required fields.
     *
     * @covers \local_navigatr\task\issue_badge_task::execute
     */
    public function test_execute_records_error_when_user_missing_email(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        // Clear the user's email to trigger the missing-field validation.
        $DB->set_field('user', 'email', '', ['id' => $user->id]);

        $DB->insert_record('local_navigatr_map', (object) [
            'courseid' => $course->id,
            'provider_id' => 1,
            'badge_id' => 42,
            'badge_name' => 'Test Badge',
            'badge_image_url' => '',
            'timemodified' => time(),
        ]);

        $task = new issue_badge_task();
        $task->set_custom_data(['userid' => $user->id, 'courseid' => $course->id]);
        $task->execute();

        $record = $DB->get_record('local_navigatr_audit', [
            'userid' => $user->id,
            'courseid' => $course->id,
        ]);

        $this->assertNotFalse($record);
        $this->assertSame('error', $record->status);
        $this->assertSame(400, (int) $record->http_code);
    }
}
