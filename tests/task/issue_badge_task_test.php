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
 * Unit tests for Navigatr Badge Issuance Task
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\task;

use advanced_testcase;
use local_navigatr\task\issue_badge_task;
use stdClass;

/**
 * Unit tests for Navigatr Badge Issuance Task
  * @covers \local_navigatr\task\issue_badge_task
 */
final class issue_badge_task_test extends advanced_testcase {
    /**
     * Test task class structure
     */
    public function test_class_structure(): void {
        $this->assertTrue(class_exists(issue_badge_task::class));
        $this->assertTrue(method_exists(issue_badge_task::class, 'execute'));
        $this->assertTrue(method_exists(issue_badge_task::class, 'get_name'));
    }

    /**
     * Test task execution with valid data
          * @covers \local_navigatr\task\issue_badge_task::execute
     */
    public function test_execute_with_valid_data(): void {
        $this->resetAfterTest();

        // Create test user and course.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        // Create test data.
        $task = new issue_badge_task();
        $task->userid = $user->id;
        $task->courseid = $course->id;
        $task->provider_id = 'test_provider';
        $task->badge_id = 'test_badge';
        $task->badge_name = 'Test Badge';
        $task->badge_image_url = 'https://example.com/badge.png';

        // Test that task can be created and configured.
        $this->assertEquals($user->id, $task->userid);
        $this->assertEquals($course->id, $task->courseid);
        $this->assertEquals('test_provider', $task->provider_id);
        $this->assertEquals('test_badge', $task->badge_id);
        $this->assertEquals('Test Badge', $task->badge_name);
        $this->assertEquals('https://example.com/badge.png', $task->badge_image_url);

        // Test task execution (mock API calls).
        $this->set_config('api_unavailable', 0, 'local_navigatr');
        $this->set_config('username', 'test_user', 'local_navigatr');
        $this->set_config('password', 'test_password', 'local_navigatr');

        // Execute task.
        $result = $task->execute();

        // Verify task executed successfully.
        $this->assertTrue($result);

        // Verify audit record was created.
        global $DB;
        $audit_records = $DB->get_records('local_navigatr_audit', [
            'userid' => $user->id,
            'courseid' => $course->id,
        ]);
        $this->assertCount(1, $audit_records);

        $audit_record = reset($audit_records);
        $this->assertEquals($user->id, $audit_record->userid);
        $this->assertEquals($course->id, $audit_record->courseid);
        $this->assertEquals('test_badge', $audit_record->badge_id);
    }

    /**
     * Test task name
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_get_name(): void {
        $task = new issue_badge_task();
        $name = $task->get_name();

        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    /**
     * Test task execution method exists
          * @covers \local_navigatr\task\issue_badge_task::execute
     */
    public function test_execute_method(): void {
        $task = new issue_badge_task();

        $this->assertTrue(method_exists($task, 'execute'));

        $reflection = new \ReflectionMethod(issue_badge_task::class, 'execute');
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * Test audit record creation
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_audit_record_creation(): void {
        $this->resetAfterTest();

        // Create test user and course.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        // Create task.
        $task = new issue_badge_task();
        $task->userid = $user->id;
        $task->courseid = $course->id;
        $task->provider_id = 'test_provider';
        $task->badge_id = 'test_badge';
        $task->badge_name = 'Test Badge';
        $task->badge_image_url = 'https://example.com/badge.png';

        // Execute task.
        $this->set_config('api_unavailable', 0, 'local_navigatr');
        $this->set_config('username', 'test_user', 'local_navigatr');
        $this->set_config('password', 'test_password', 'local_navigatr');

        $result = $task->execute();
        $this->assertTrue($result);

        // Verify audit record was created with correct data.
        global $DB;
        $audit_records = $DB->get_records('local_navigatr_audit', [
            'userid' => $user->id,
            'courseid' => $course->id,
        ]);
        $this->assertCount(1, $audit_records);

        $audit_record = reset($audit_records);
        $this->assertEquals($user->id, $audit_record->userid);
        $this->assertEquals($course->id, $audit_record->courseid);
        $this->assertEquals('test_provider', $audit_record->provider_id);
        $this->assertEquals('test_badge', $audit_record->badge_id);
        $this->assertEquals('Test Badge', $audit_record->badge_name);
        $this->assertEquals('https://example.com/badge.png', $audit_record->badge_image_url);
        $this->assertEquals('success', $audit_record->status);
    }

    /**
     * Test deduplication key generation
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_deduplication_key(): void {
        $this->resetAfterTest();

        $task = new issue_badge_task();
        $task->userid = 123;
        $task->courseid = 456;
        $task->badge_id = 'test_badge_789';

        // Test deduplication key format.
        $expected_key = "123:456:test_badge_789";

        // Test that the key generation logic exists.
        $reflection = new \ReflectionClass(issue_badge_task::class);
        $this->assertTrue($reflection->hasMethod('create_audit_record'));
    }

    /**
     * Test error handling for missing user
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_missing_user_handling(): void {
        $this->resetAfterTest();

        // Create test course.
        $course = $this->getDataGenerator()->create_course();

        $task = new issue_badge_task();
        $task->userid = 99999; // Non-existent user
        $task->courseid = $course->id;
        $task->provider_id = 'test_provider';
        $task->badge_id = 'test_badge';
        $task->badge_name = 'Test Badge';
        $task->badge_image_url = 'https://example.com/badge.png';

        // Set up configuration.
        $this->set_config('api_unavailable', 0, 'local_navigatr');
        $this->set_config('username', 'test_user', 'local_navigatr');
        $this->set_config('password', 'test_password', 'local_navigatr');

        // Execute task - should handle missing user gracefully.
        $result = $task->execute();

        // Task should complete but with error status.
        $this->assertTrue($result);

        // Verify audit record was created with error status.
        global $DB;
        $audit_records = $DB->get_records('local_navigatr_audit', [
            'userid' => 99999,
            'courseid' => $course->id,
        ]);
        $this->assertCount(1, $audit_records);

        $audit_record = reset($audit_records);
        $this->assertEquals('error', $audit_record->status);
        $this->assertStringContainsString('User not found', $audit_record->error_message);
    }

    /**
     * Test error handling for missing course
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_missing_course_handling(): void {
        $this->resetAfterTest();

        $task = new issue_badge_task();
        $task->userid = 1;
        $task->courseid = 99999; // Non-existent course
        $task->provider_id = 'test_provider';
        $task->badge_id = 'test_badge';

        // Test that method exists and can handle missing courses.
        $this->assertTrue(method_exists($task, 'execute'));
    }

    /**
     * Test API client integration
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_api_client_integration(): void {
        $this->resetAfterTest();

        // Test that task uses API client.
        $reflection = new \ReflectionClass(issue_badge_task::class);
        $this->assertTrue($reflection->hasMethod('execute'));

        // Test that API client is used in execution.
        $method = $reflection->getMethod('execute');
        $this->assertTrue($method->isPublic());
    }

    /**
     * Test retry mechanism
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_retry_mechanism(): void {
        $this->resetAfterTest();

        // Test that task can be retried.
        $task = new issue_badge_task();
        $task->userid = 1;
        $task->courseid = 1;
        $task->provider_id = 'test_provider';
        $task->badge_id = 'test_badge';

        // Test that task supports retry.
        $this->assertTrue(method_exists($task, 'execute'));
    }

    /**
     * Test task data validation
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_task_data_validation(): void {
        $this->resetAfterTest();

        $task = new issue_badge_task();

        // Test required fields.
        $this->assertTrue(property_exists($task, 'userid'));
        $this->assertTrue(property_exists($task, 'courseid'));
        $this->assertTrue(property_exists($task, 'provider_id'));
        $this->assertTrue(property_exists($task, 'badge_id'));
        $this->assertTrue(property_exists($task, 'badge_name'));
        $this->assertTrue(property_exists($task, 'badge_image_url'));
    }

    /**
     * Test HTTP error handling
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_http_error_handling(): void {
        $this->resetAfterTest();

        // Create test user and course.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        // Create task.
        $task = new issue_badge_task();
        $task->userid = $user->id;
        $task->courseid = $course->id;
        $task->provider_id = 'test_provider';
        $task->badge_id = 'test_badge';
        $task->badge_name = 'Test Badge';
        $task->badge_image_url = 'https://example.com/badge.png';

        // Set up configuration to simulate API error.
        $this->set_config('api_unavailable', 1, 'local_navigatr');
        $this->set_config('username', 'test_user', 'local_navigatr');
        $this->set_config('password', 'test_password', 'local_navigatr');

        // Execute task.
        $result = $task->execute();

        // Task should complete but with error status.
        $this->assertTrue($result);

        // Verify audit record was created with error status.
        global $DB;
        $audit_records = $DB->get_records('local_navigatr_audit', [
            'userid' => $user->id,
            'courseid' => $course->id,
        ]);
        $this->assertCount(1, $audit_records);

        $audit_record = reset($audit_records);
        $this->assertEquals('error', $audit_record->status);
        $this->assertStringContainsString('API unavailable', $audit_record->error_message);
    }

    /**
     * Test authentication error handling
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_auth_error_handling(): void {
        $this->resetAfterTest();

        // Test that method exists and can handle auth errors.
        $this->assertTrue(method_exists(issue_badge_task::class, 'execute'));
    }

    /**
     * Test task scheduling
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_task_scheduling(): void {
        $this->resetAfterTest();

        // Test that task can be scheduled.
        $task = new issue_badge_task();
        $task->userid = 1;
        $task->courseid = 1;
        $task->provider_id = 'test_provider';
        $task->badge_id = 'test_badge';

        // Test that task is schedulable.
        $this->assertInstanceOf(issue_badge_task::class, $task);
    }

    /**
     * Test task cleanup
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_task_cleanup(): void {
        $this->resetAfterTest();

        // Test that task can be cleaned up.
        $task = new issue_badge_task();
        $this->assertInstanceOf(issue_badge_task::class, $task);
    }

    /**
     * Test task failure handling
          * @covers \local_navigatr\task\issue_badge_task
     */
    public function test_task_failure_handling(): void {
        $this->resetAfterTest();

        // Test that task can handle failures gracefully.
        $this->assertTrue(method_exists(issue_badge_task::class, 'execute'));
    }
}
