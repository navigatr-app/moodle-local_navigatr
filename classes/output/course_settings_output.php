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
 * Course settings output class for Navigatr plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Course settings output class.
 */
class course_settings_output
{
    /**
     * Render the current mapping display.
     *
     * @param object $existing_mapping The existing mapping data
     * @param object $existing_provider The provider data
     * @param object $existing_badge The badge data
     * @param int $courseid The course ID
     * @return string Rendered HTML
     */
    public static function render_current_mapping($existing_mapping, $existing_provider, $existing_badge, $courseid)
    {
        global $OUTPUT;

        if (!$existing_mapping || !$existing_provider || !$existing_badge) {
            return '';
        }

        // Additional safety check
        if (!is_object($existing_mapping) || !isset($existing_mapping->provider_id)) {
            return '';
        }

        // Build URLs first to avoid object access issues
        $change_url = new \moodle_url('/local/navigatr/badge_selection.php', [
            'id' => $courseid,
            'provider_id' => $existing_mapping->provider_id
        ]);

        $remove_url = new \moodle_url('/local/navigatr/course_settings.php', [
            'id' => $courseid,
            'action' => 'removemapping',
            'sesskey' => sesskey()
        ]);

        $template_data = [
            'existing_mapping' => true,
            'provider_name' => $existing_provider['name'],
            'badge_name' => $existing_badge['name'],
            'badge_description' => isset($existing_badge['description']) ? $existing_badge['description'] : '',
            'badge_image_url' => isset($existing_badge['image_url']) ? $existing_badge['image_url'] : '',
            'badge_url' => isset($existing_badge['url']) ? $existing_badge['url'] : '',
            'change_url' => $change_url->out(),
            'remove_url' => $remove_url->out()
        ];

        // Ensure URLs are properly encoded
        $template_data['change_url'] = $change_url->out(false); // false = don't HTML encode
        $template_data['remove_url'] = $remove_url->out(false);

        return $OUTPUT->render_from_template('local_navigatr/course/course_settings', $template_data);
    }
}
