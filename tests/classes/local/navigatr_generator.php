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
 * Navigatr plugin test data generator
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Navigatr plugin test data generator
 */
class local_navigatr_generator extends component_generator_base {
    /**
     * Create a Navigatr provider
     *
     * @param array $data Provider data
     * @return stdClass Created provider
     */
    public function create_provider($data = []) {
        global $DB;

        $defaults = [
            'name' => 'Test Provider',
            'api_url' => 'https://api.navigatr.test',
            'username' => 'test_user',
            'password' => 'test_password',
            'enabled' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $data = array_merge($defaults, $data);

        $provider = (object) $data;
        $provider->id = $DB->insert_record('local_navigatr_providers', $provider);

        return $provider;
    }

    /**
     * Create a Navigatr badge
     *
     * @param array $data Badge data
     * @return stdClass Created badge
     */
    public function create_badge($data = []) {
        global $DB;

        $defaults = [
            'provider_id' => 1,
            'name' => 'Test Badge',
            'description' => 'A test badge for testing purposes',
            'image_url' => 'https://example.com/badge.png',
            'criteria' => 'Complete the test course',
            'enabled' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $data = array_merge($defaults, $data);

        $badge = (object) $data;
        $badge->id = $DB->insert_record('local_navigatr_badges', $badge);

        return $badge;
    }

    /**
     * Create a course-badge mapping
     *
     * @param array $data Mapping data
     * @return stdClass Created mapping
     */
    public function create_course_badge_mapping($data = []) {
        global $DB;

        $defaults = [
            'courseid' => 1,
            'provider_id' => 1,
            'badge_id' => 'test_badge',
            'badge_name' => 'Test Badge',
            'badge_image_url' => 'https://example.com/badge.png',
            'enabled' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $data = array_merge($defaults, $data);

        $mapping = (object) $data;
        $mapping->id = $DB->insert_record('local_navigatr_course_badges', $mapping);

        return $mapping;
    }

    /**
     * Create an audit record
     *
     * @param array $data Audit data
     * @return stdClass Created audit record
     */
    public function create_audit_record($data = []) {
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
     * Create a complete test setup
     *
     * @param array $data Setup data
     * @return stdClass Complete test setup
     */
    public function create_test_setup($data = []) {
        $defaults = [
            'user' => null,
            'course' => null,
            'provider' => null,
            'badge' => null,
            'mapping' => null,
        ];

        $data = array_merge($defaults, $data);

        $setup = new stdClass();

        // Create user if not provided.
        if (!$data['user']) {
            $setup->user = $this->getDataGenerator()->create_user();
        } else {
            $setup->user = $data['user'];
        }

        // Create course if not provided.
        if (!$data['course']) {
            $setup->course = $this->getDataGenerator()->create_course();
        } else {
            $setup->course = $data['course'];
        }

        // Create provider if not provided.
        if (!$data['provider']) {
            $setup->provider = $this->create_provider();
        } else {
            $setup->provider = $data['provider'];
        }

        // Create badge if not provided.
        if (!$data['badge']) {
            $setup->badge = $this->create_badge([
                'provider_id' => $setup->provider->id,
            ]);
        } else {
            $setup->badge = $data['badge'];
        }

        // Create mapping if not provided.
        if (!$data['mapping']) {
            $setup->mapping = $this->create_course_badge_mapping([
                'courseid' => $setup->course->id,
                'provider_id' => $setup->provider->id,
                'badge_id' => $setup->badge->id,
                'badge_name' => $setup->badge->name,
                'badge_image_url' => $setup->badge->image_url,
            ]);
        } else {
            $setup->mapping = $data['mapping'];
        }

        return $setup;
    }
}
