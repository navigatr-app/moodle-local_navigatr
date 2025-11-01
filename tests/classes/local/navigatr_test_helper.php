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
 * Navigatr plugin test helper
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Navigatr plugin test helper
 */
class local_navigatr_test_helper {
    /**
     * Set up test configuration
     *
     * @param array $config Configuration data
     */
    public static function setup_test_config($config = []) {
        $defaults = [
            'username' => 'test_user',
            'password' => 'test_password',
            'api_url' => 'https://api.navigatr.test',
            'api_unavailable' => 0,
            'retry_attempts' => 3,
            'retry_delay' => 60,
        ];

        $config = array_merge($defaults, $config);

        foreach ($config as $key => $value) {
            set_config($key, $value, 'local_navigatr');
        }
    }

    /**
     * Create test user with specific capabilities
     *
     * @param array $capabilities Capabilities to assign
     * @return stdClass Created user
     */
    public static function create_user_with_capabilities($capabilities = []) {
        global $DB;

        $user = new stdClass();
        $user->username = 'testuser_' . time();
        $user->firstname = 'Test';
        $user->lastname = 'User';
        $user->email = 'testuser@example.com';
        $user->id = $DB->insert_record('user', $user);

        // Assign capabilities.
        foreach ($capabilities as $capability) {
            $context = context_system::instance();
            assign_capability($capability, CAP_ALLOW, $user->id, $context->id);
        }

        return $user;
    }

    /**
     * Create test course with completion enabled
     *
     * @param array $data Course data
     * @return stdClass Created course
     */
    public static function create_course_with_completion($data = []) {
        global $DB;

        $defaults = [
            'fullname' => 'Test Course',
            'shortname' => 'TC' . time(),
            'enablecompletion' => 1,
            'completionstartonenrol' => 1,
        ];

        $data = array_merge($defaults, $data);

        $course = (object) $data;
        $course->id = $DB->insert_record('course', $course);

        return $course;
    }

    /**
     * Simulate API response
     *
     * @param string $type Response type (success, error, timeout)
     * @param array $data Response data
     * @return array API response
     */
    public static function simulate_api_response($type = 'success', $data = []) {
        switch ($type) {
            case 'success':
                return [
                    'code' => 200,
                    'body' => json_encode([
                        'success' => true,
                        'badge_id' => $data['badge_id'] ?? 'test_badge',
                        'message' => 'Badge issued successfully',
                    ]),
                ];

            case 'error':
                return [
                    'code' => 400,
                    'body' => json_encode([
                        'success' => false,
                        'error' => $data['error'] ?? 'Bad request',
                        'message' => 'Badge issuance failed',
                    ]),
                ];

            case 'timeout':
                return [
                    'code' => 408,
                    'body' => json_encode([
                        'success' => false,
                        'error' => 'Request timeout',
                        'message' => 'API request timed out',
                    ]),
                ];

            case 'unauthorized':
                return [
                    'code' => 401,
                    'body' => json_encode([
                        'success' => false,
                        'error' => 'Unauthorized',
                        'message' => 'Invalid credentials',
                    ]),
                ];

            default:
                return [
                    'code' => 500,
                    'body' => json_encode([
                        'success' => false,
                        'error' => 'Internal server error',
                        'message' => 'Unexpected error occurred',
                    ]),
                ];
        }
    }

    /**
     * Create test audit record
     *
     * @param array $data Audit data
     * @return stdClass Created audit record
     */
    public static function create_audit_record($data = []) {
        global $DB;

        $defaults = [
            'userid' => 1,
            'courseid' => 1,
            'provider_id' => 'test_provider',
            'badge_id' => 'test_badge',
            'badge_name' => 'Test Badge',
            'badge_image_url' => 'https://example.com/badge.png',
            'status' => 'success',
            'error_message' => null,
            'api_response' => null,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $data = array_merge($defaults, $data);

        $audit = (object) $data;
        $audit->id = $DB->insert_record('local_navigatr_audit', $audit);

        return $audit;
    }

    /**
     * Clean up test data
     */
    public static function cleanup_test_data() {
        global $DB;

        // Clean up audit records.
        $DB->delete_records('local_navigatr_audit');

        // Clean up course badge mappings.
        $DB->delete_records('local_navigatr_course_badges');

        // Clean up badges.
        $DB->delete_records('local_navigatr_badges');

        // Clean up providers.
        $DB->delete_records('local_navigatr_providers');

        // Clean up configuration.
        $DB->delete_records('config_plugins', ['plugin' => 'local_navigatr']);
    }

    /**
     * Assert audit record exists
     *
     * @param array $criteria Search criteria
     * @param string $message Assertion message
     */
    public static function assert_audit_record_exists($criteria, $message = '') {
        global $DB;

        $records = $DB->get_records('local_navigatr_audit', $criteria);
        $this->assertCount(1, $records, $message);

        return reset($records);
    }

    /**
     * Assert audit record does not exist
     *
     * @param array $criteria Search criteria
     * @param string $message Assertion message
     */
    public static function assert_audit_record_not_exists($criteria, $message = '') {
        global $DB;

        $records = $DB->get_records('local_navigatr_audit', $criteria);
        $this->assertCount(0, $records, $message);
    }
}
