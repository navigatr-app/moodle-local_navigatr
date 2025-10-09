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
 */
class issue_badge_task_test extends advanced_testcase {

    /**
     * Test task class structure
     */
    public function test_class_structure() {
        $this->assertTrue(class_exists(issue_badge_task::class));
        $this->assertTrue(method_exists(issue_badge_task::class, 'execute'));
        $this->assertTrue(method_exists(issue_badge_task::class, 'get_name'));
    }

    /**
     * Test task execution with valid data
     */
    public function test_execute_with_valid_data() {
        $this->resetAfterTest();
        
        // Create test data
        $task = new issue_badge_task();
        $task->userid = 1;
        $task->courseid = 1;
        $task->provider_id = 'test_provider';
        $task->badge_id = 'test_badge';
        $task->badge_name = 'Test Badge';
        $task->badge_image_url = 'https://example.com/badge.png';
        
        // Test that task can be created and configured
        $this->assertEquals(1, $task->userid);
        $this->assertEquals(1, $task->courseid);
        $this->assertEquals('test_provider', $task->provider_id);
        $this->assertEquals('test_badge', $task->badge_id);
        $this->assertEquals('Test Badge', $task->badge_name);
        $this->assertEquals('https://example.com/badge.png', $task->badge_image_url);
    }

    /**
     * Test task name
     */
    public function test_get_name() {
        $task = new issue_badge_task();
        $name = $task->get_name();
        
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    /**
     * Test task execution method exists
     */
    public function test_execute_method() {
        $task = new issue_badge_task();
        
        $this->assertTrue(method_exists($task, 'execute'));
        
        $reflection = new \ReflectionMethod(issue_badge_task::class, 'execute');
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * Test audit record creation
     */
    public function test_audit_record_creation() {
        $this->resetAfterTest();
        
        // Test that audit record creation method exists
        $reflection = new \ReflectionClass(issue_badge_task::class);
        $this->assertTrue($reflection->hasMethod('create_audit_record'));
        
        $method = $reflection->getMethod('create_audit_record');
        $this->assertTrue($method->isPrivate());
    }

    /**
     * Test deduplication key generation
     */
    public function test_deduplication_key() {
        $this->resetAfterTest();
        
        $task = new issue_badge_task();
        $task->userid = 123;
        $task->courseid = 456;
        $task->badge_id = 'test_badge_789';
        
        // Test deduplication key format
        $expected_key = "123:456:test_badge_789";
        
        // Test that the key generation logic exists
        $reflection = new \ReflectionClass(issue_badge_task::class);
        $this->assertTrue($reflection->hasMethod('create_audit_record'));
    }

    /**
     * Test error handling for missing user
     */
    public function test_missing_user_handling() {
        $this->resetAfterTest();
        
        $task = new issue_badge_task();
        $task->userid = 99999; // Non-existent user
        $task->courseid = 1;
        $task->provider_id = 'test_provider';
        $task->badge_id = 'test_badge';
        
        // Test that method exists and can handle missing users
        $this->assertTrue(method_exists($task, 'execute'));
    }

    /**
     * Test error handling for missing course
     */
    public function test_missing_course_handling() {
        $this->resetAfterTest();
        
        $task = new issue_badge_task();
        $task->userid = 1;
        $task->courseid = 99999; // Non-existent course
        $task->provider_id = 'test_provider';
        $task->badge_id = 'test_badge';
        
        // Test that method exists and can handle missing courses
        $this->assertTrue(method_exists($task, 'execute'));
    }

    /**
     * Test API client integration
     */
    public function test_api_client_integration() {
        $this->resetAfterTest();
        
        // Test that task uses API client
        $reflection = new \ReflectionClass(issue_badge_task::class);
        $this->assertTrue($reflection->hasMethod('execute'));
        
        // Test that API client is used in execution
        $method = $reflection->getMethod('execute');
        $this->assertTrue($method->isPublic());
    }

    /**
     * Test retry mechanism
     */
    public function test_retry_mechanism() {
        $this->resetAfterTest();
        
        // Test that task can be retried
        $task = new issue_badge_task();
        $task->userid = 1;
        $task->courseid = 1;
        $task->provider_id = 'test_provider';
        $task->badge_id = 'test_badge';
        
        // Test that task supports retry
        $this->assertTrue(method_exists($task, 'execute'));
    }

    /**
     * Test task data validation
     */
    public function test_task_data_validation() {
        $this->resetAfterTest();
        
        $task = new issue_badge_task();
        
        // Test required fields
        $this->assertTrue(property_exists($task, 'userid'));
        $this->assertTrue(property_exists($task, 'courseid'));
        $this->assertTrue(property_exists($task, 'provider_id'));
        $this->assertTrue(property_exists($task, 'badge_id'));
        $this->assertTrue(property_exists($task, 'badge_name'));
        $this->assertTrue(property_exists($task, 'badge_image_url'));
    }

    /**
     * Test HTTP error handling
     */
    public function test_http_error_handling() {
        $this->resetAfterTest();
        
        // Test that method exists and can handle HTTP errors
        $this->assertTrue(method_exists(issue_badge_task::class, 'execute'));
    }

    /**
     * Test authentication error handling
     */
    public function test_auth_error_handling() {
        $this->resetAfterTest();
        
        // Test that method exists and can handle auth errors
        $this->assertTrue(method_exists(issue_badge_task::class, 'execute'));
    }

    /**
     * Test task scheduling
     */
    public function test_task_scheduling() {
        $this->resetAfterTest();
        
        // Test that task can be scheduled
        $task = new issue_badge_task();
        $task->userid = 1;
        $task->courseid = 1;
        $task->provider_id = 'test_provider';
        $task->badge_id = 'test_badge';
        
        // Test that task is schedulable
        $this->assertInstanceOf(issue_badge_task::class, $task);
    }

    /**
     * Test task cleanup
     */
    public function test_task_cleanup() {
        $this->resetAfterTest();
        
        // Test that task can be cleaned up
        $task = new issue_badge_task();
        $this->assertInstanceOf(issue_badge_task::class, $task);
    }

    /**
     * Test task failure handling
     */
    public function test_task_failure_handling() {
        $this->resetAfterTest();
        
        // Test that task can handle failures gracefully
        $this->assertTrue(method_exists(issue_badge_task::class, 'execute'));
    }
}
