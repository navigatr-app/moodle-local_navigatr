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
 * Unit tests for the Navigatr course settings output class.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\output;

use advanced_testcase;

/**
 * Unit tests for the Navigatr course settings output class.
 *
 * @covers \local_navigatr\output\course_settings_output
 */
final class course_settings_output_test extends advanced_testcase {
    /**
     * Data provider for clean_external_url().
     *
     * @return array
     */
    public static function url_provider(): array {
        return [
            'https kept' => ['https://navigatr.app/badge/1', 'https://navigatr.app/badge/1'],
            'http kept' => ['http://navigatr.app/badge/1', 'http://navigatr.app/badge/1'],
            'javascript dropped' => ['javascript:alert(1)', ''],
            'mixed case javascript dropped' => ['JavaScript:alert(1)', ''],
            'leading space javascript dropped' => [' javascript:alert(1)', ''],
            'data dropped' => ['data:text/html,x', ''],
            'protocol relative dropped' => ['//evil.example/x', ''],
            'relative dropped' => ['/relative/path', ''],
            'empty dropped' => ['', ''],
            'null dropped' => [null, ''],
        ];
    }

    /**
     * Test that only absolute http and https URLs are kept.
     *
     * @dataProvider url_provider
     * @param mixed $input The URL passed in
     * @param string $expected The expected result
     */
    public function test_clean_external_url($input, string $expected): void {
        $this->assertSame($expected, course_settings_output::clean_external_url($input));
    }

    /**
     * Render the current mapping for a badge array.
     *
     * @param array $badge The badge data
     * @return string Rendered HTML
     */
    private function render(array $badge): string {
        return course_settings_output::render_current_mapping(
            (object) ['provider_id' => 1],
            ['name' => 'Provider'],
            $badge,
            2
        );
    }

    /**
     * Test that an https badge URL is rendered as a link.
     */
    public function test_render_keeps_https_url(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $html = $this->render(['name' => 'Badge', 'url' => 'https://navigatr.app/badge/1']);
        $this->assertStringContainsString('href="https://navigatr.app/badge/1"', $html);
        $this->assertStringContainsString(get_string('view_badge', 'local_navigatr'), $html);
    }

    /**
     * Test that a javascript badge URL is not rendered.
     */
    public function test_render_drops_javascript_url(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $html = $this->render(['name' => 'Badge', 'url' => 'javascript:alert(1)']);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString(get_string('view_badge', 'local_navigatr'), $html);
    }

    /**
     * Test that a missing badge URL hides the link.
     */
    public function test_render_without_url_hides_link(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $html = $this->render(['name' => 'Badge']);
        $this->assertStringNotContainsString(get_string('view_badge', 'local_navigatr'), $html);
    }
}
