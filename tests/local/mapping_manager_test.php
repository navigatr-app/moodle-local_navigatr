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
 * Unit tests for the Navigatr mapping manager.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

use advanced_testcase;

/**
 * Unit tests for the Navigatr mapping manager.
 *
 * @covers \local_navigatr\local\mapping_manager
 */
final class mapping_manager_test extends advanced_testcase {
    /**
     * Test that save() fires created, then updated, and keeps one row.
     *
     * @covers \local_navigatr\local\mapping_manager::save
     */
    public function test_save_fires_created_then_updated(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $sink = $this->redirectEvents();

        $id = mapping_manager::save($course->id, 5, 9, 'Badge', 'https://example.com/b.png');
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(\local_navigatr\event\course_mapping_created::class, $events[0]);
        $this->assertEquals(\context_course::instance($course->id), $events[0]->get_context());
        $this->assertEquals($id, $events[0]->objectid);
        $this->assertEquals(9, $events[0]->other['badge_id']);
        $this->assertEquals(5, $events[0]->other['provider_id']);
        $sink->clear();

        mapping_manager::save($course->id, 6, 10);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(\local_navigatr\event\course_mapping_updated::class, $events[0]);
        $this->assertEquals(10, $events[0]->other['badge_id']);
        $this->assertEquals(1, $DB->count_records('local_navigatr_map', ['courseid' => $course->id]));
        $record = $DB->get_record('local_navigatr_map', ['courseid' => $course->id]);
        $this->assertNull($record->badge_name);
        $this->assertNull($record->badge_image_url);
        $sink->close();
    }

    /**
     * Test that remove() deletes the row and fires deleted with the old ids.
     *
     * @covers \local_navigatr\local\mapping_manager::remove
     */
    public function test_remove_fires_deleted(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        mapping_manager::save($course->id, 5, 9);
        $sink = $this->redirectEvents();

        $this->assertTrue(mapping_manager::remove($course->id));
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(\local_navigatr\event\course_mapping_deleted::class, $events[0]);
        $this->assertEquals(9, $events[0]->other['badge_id']);
        $this->assertEquals(5, $events[0]->other['provider_id']);
        $this->assertFalse($DB->record_exists('local_navigatr_map', ['courseid' => $course->id]));
        $sink->close();
    }

    /**
     * Test that remove() with no mapping returns false and fires nothing.
     *
     * @covers \local_navigatr\local\mapping_manager::remove
     */
    public function test_remove_without_mapping_fires_nothing(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $sink = $this->redirectEvents();

        $this->assertFalse(mapping_manager::remove($course->id));
        $this->assertCount(0, $sink->get_events());
        $sink->close();
    }
}
