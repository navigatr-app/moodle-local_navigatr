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
 * Unit tests for the course settings mapping display.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

use advanced_testcase;

/**
 * Unit tests for the course settings mapping display.
 *
 * @covers \local_navigatr\local\mapping_display
 */
final class mapping_display_test extends advanced_testcase {
    /**
     * Create a course and its stored mapping.
     *
     * @return \stdClass The local_navigatr_map row
     */
    private function create_mapping(): \stdClass {
        global $DB;
        $course = $this->getDataGenerator()->create_course();
        $id = $DB->insert_record('local_navigatr_map', (object) [
            'courseid' => $course->id,
            'provider_id' => 5,
            'badge_id' => 9,
            'badge_name' => 'Stored name',
            'badge_image_url' => 'https://example.com/stored.png',
            'timemodified' => 1,
        ]);
        return $DB->get_record('local_navigatr_map', ['id' => $id]);
    }

    /**
     * Build a mocked API client response.
     *
     * @param bool $ok Whether the response is OK
     * @param mixed $body Response body
     * @return \stdClass
     */
    private function response(bool $ok, $body): \stdClass {
        return (object) ['ok' => $ok, 'body' => $body];
    }

    /**
     * A cache hit makes no API call.
     */
    public function test_cache_hit_makes_no_api_call(): void {
        $this->resetAfterTest();
        $mapping = $this->create_mapping();
        cache::set_badge(9, ['name' => 'Cached', 'image_url' => 'https://example.com/c.png',
            'description' => 'Desc', 'url' => 'https://example.com/b']);

        $client = $this->createMock(api_client::class);
        $client->expects($this->never())->method('get');

        $result = mapping_display::resolve($mapping, [['id' => 5, 'name' => 'Provider']], $client);
        $this->assertSame('Cached', $result['badge']['name']);
        $this->assertSame('Desc', $result['badge']['description']);
        $this->assertSame('https://example.com/b', $result['badge']['url']);
        $this->assertSame('Provider', $result['provider']['name']);
    }

    /**
     * A cache miss calls the badge endpoint once, caches it and updates the stored row.
     */
    public function test_cache_miss_fetches_caches_and_writes_back(): void {
        global $DB;
        $this->resetAfterTest();
        $mapping = $this->create_mapping();

        $client = $this->createMock(api_client::class);
        $client->expects($this->once())->method('get')->with('/badge/9')->willReturn($this->response(true, [
            'name' => 'New name',
            'image_url' => 'https://example.com/new.png',
            'description' => 'D',
            'url' => 'https://example.com/b',
        ]));

        $result = mapping_display::resolve($mapping, [], $client);
        $this->assertSame('New name', $result['badge']['name']);
        $this->assertSame('New name', cache::get_badge(9)['name']);
        $row = $DB->get_record('local_navigatr_map', ['id' => $mapping->id]);
        $this->assertSame('New name', $row->badge_name);
        $this->assertSame('https://example.com/new.png', $row->badge_image_url);
    }

    /**
     * When the API throws, the stored row is used.
     */
    public function test_api_exception_falls_back_to_stored_row(): void {
        $this->resetAfterTest();
        $mapping = $this->create_mapping();

        $client = $this->createMock(api_client::class);
        $client->method('get')->willThrowException(new \Exception('timeout'));

        $result = mapping_display::resolve($mapping, [['id' => 5, 'name' => 'Provider']], $client);
        $this->assertSame('Stored name', $result['badge']['name']);
        $this->assertSame('https://example.com/stored.png', $result['badge']['image_url']);
        $this->assertSame('Provider', $result['provider']['name']);
        $this->assertNull(cache::get_badge(9));
    }

    /**
     * When the API answers not OK, the stored row is used and nothing is cached.
     */
    public function test_api_not_ok_falls_back_to_stored_row(): void {
        $this->resetAfterTest();
        $mapping = $this->create_mapping();

        $client = $this->createMock(api_client::class);
        $client->method('get')->willReturn($this->response(false, null));

        $result = mapping_display::resolve($mapping, [], $client);
        $this->assertSame('Stored name', $result['badge']['name']);
        $this->assertNull(cache::get_badge(9));
    }

    /**
     * A provider missing from the list resolves to the unknown provider string.
     */
    public function test_unknown_provider_never_null(): void {
        $this->resetAfterTest();
        $mapping = $this->create_mapping();
        cache::set_badge(9, ['name' => 'Cached']);

        $result = mapping_display::resolve($mapping, [['id' => 77, 'name' => 'Other']], $this->createMock(api_client::class));
        $this->assertSame(get_string('unknown_provider', 'local_navigatr'), $result['provider']['name']);
    }

    /**
     * The provider endpoint is never called.
     */
    public function test_provider_endpoint_never_called(): void {
        $this->resetAfterTest();
        $mapping = $this->create_mapping();

        $client = $this->createMock(api_client::class);
        $client->expects($this->once())->method('get')
            ->with($this->stringStartsWith('/badge/'))
            ->willReturn($this->response(false, null));

        mapping_display::resolve($mapping, [], $client);
    }

    /**
     * The cache key is scoped to the environment.
     */
    public function test_badge_cache_is_scoped_by_environment(): void {
        $this->resetAfterTest();
        set_config('env', 'production', 'local_navigatr');
        cache::set_badge(9, ['name' => 'Production badge']);
        set_config('env', 'staging', 'local_navigatr');
        $this->assertNull(cache::get_badge(9));
        set_config('env', 'production', 'local_navigatr');
        $this->assertSame('Production badge', cache::get_badge(9)['name']);
    }

    /**
     * The fallback data still renders the mapping card.
     */
    public function test_fallback_data_renders_current_mapping(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $mapping = $this->create_mapping();

        $client = $this->createMock(api_client::class);
        $client->method('get')->willThrowException(new \Exception('down'));
        $result = mapping_display::resolve($mapping, [], $client);

        $html = \local_navigatr\output\course_settings_output::render_current_mapping(
            $mapping,
            $result['provider'],
            $result['badge'],
            $mapping->courseid
        );
        $this->assertNotSame('', $html);
        $this->assertStringContainsString('Stored name', $html);
    }
}
