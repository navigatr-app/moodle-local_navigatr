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
 * Unit tests for the Navigatr privacy provider.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\privacy;

use advanced_testcase;
use core_privacy\local\metadata\collection;

/**
 * Unit tests for the Navigatr privacy provider.
 *
 * @covers \local_navigatr\privacy\provider
 */
final class provider_test extends advanced_testcase {
    /**
     * Get the metadata collection items keyed by name.
     *
     * @return array
     */
    private function get_items(): array {
        $collection = provider::get_metadata(new collection('local_navigatr'));
        $items = [];
        foreach ($collection->get_collection() as $item) {
            $items[$item->get_name()] = $item;
        }
        return $items;
    }

    /**
     * Test the external location declares everything sent to Navigatr.
     */
    public function test_external_location_declares_sent_fields(): void {
        $items = $this->get_items();
        $this->assertArrayHasKey('navigatr', $items);
        $fields = array_keys($items['navigatr']->get_privacy_fields());
        foreach (['recipient_email', 'recipient_firstname', 'recipient_lastname', 'evidence_text', 'score'] as $field) {
            $this->assertContains($field, $fields);
        }
    }

    /**
     * Test the audit table declares everything stored.
     */
    public function test_audit_table_declares_stored_fields(): void {
        $items = $this->get_items();
        $this->assertArrayHasKey('local_navigatr_audit', $items);
        $fields = array_keys($items['local_navigatr_audit']->get_privacy_fields());
        $expected = ['userid', 'courseid', 'provider_id', 'badge_id', 'status', 'http_code', 'response_json', 'dedupe_key',
            'timecreated'];
        foreach ($expected as $field) {
            $this->assertContains($field, $fields);
        }
    }

    /**
     * Test every declared field and summary has a language string.
     */
    public function test_declared_strings_exist(): void {
        $stringmanager = get_string_manager();
        foreach ($this->get_items() as $item) {
            $this->assertTrue($stringmanager->string_exists($item->get_summary(), 'local_navigatr'), $item->get_summary());
            foreach ($item->get_privacy_fields() as $identifier) {
                $this->assertTrue($stringmanager->string_exists($identifier, 'local_navigatr'), $identifier);
            }
        }
    }
}
