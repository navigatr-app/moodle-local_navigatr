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
 * Unit tests for the Navigatr settings manager.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

use advanced_testcase;

/**
 * Unit tests for the Navigatr settings manager.
 *
 * @covers \local_navigatr\local\settings_manager
 */
final class settings_manager_test extends advanced_testcase {
    /**
     * Test that save() fires settings_updated without leaking the PAT.
     *
     * @covers \local_navigatr\local\settings_manager::save
     */
    public function test_save_fires_settings_updated_without_pat(): void {
        $this->resetAfterTest();
        $pat = 'nav_pat_secret_value_123';
        $sink = $this->redirectEvents();

        settings_manager::save('staging', $pat, 45);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf(\local_navigatr\event\settings_updated::class, $event);
        $this->assertEquals(\context_system::instance(), $event->get_context());
        $this->assertSame(['env' => 'staging', 'timeout' => 45], $event->other);
        $this->assertStringNotContainsString($pat, json_encode($event->get_data()));
        $this->assertStringNotContainsString($pat, $event->get_description());
        $this->assertSame($pat, password_manager::get_pat());
        $this->assertSame('staging', get_config('local_navigatr', 'env'));
        $sink->close();
    }

    /**
     * Test that remove_connection() clears the PAT and fires connection_removed.
     *
     * @covers \local_navigatr\local\settings_manager::remove_connection
     */
    public function test_remove_connection_fires_event_and_clears_pat(): void {
        $this->resetAfterTest();
        $pat = 'nav_pat_secret_value_123';
        password_manager::store_pat($pat);
        $sink = $this->redirectEvents();

        settings_manager::remove_connection();

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf(\local_navigatr\event\connection_removed::class, $event);
        $this->assertEquals(\context_system::instance(), $event->get_context());
        $this->assertStringNotContainsString($pat, json_encode($event->get_data()));
        $this->assertStringNotContainsString($pat, $event->get_description());
        $this->assertSame('', password_manager::get_pat());
        $sink->close();
    }
}
