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
 * Course settings output class for Navigatr plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\output;

/**
 * Course settings output class.
 */
class course_settings_output {
    /**
     * Render the current mapping display.
     *
     * @param object $existingMapping The existing mapping data
     * @param object $existingProvider The provider data
     * @param object $existingBadge The badge data
     * @param int $courseid The course ID
     * @return string Rendered HTML
     */
    public static function render_current_mapping($existingMapping, $existingProvider, $existingBadge, $courseid) {
        global $OUTPUT;

        if (!$existingMapping || !$existingProvider || !$existingBadge) {
            return '';
        }

        // Additional safety check.
        if (!is_object($existingMapping) || !isset($existingMapping->provider_id)) {
            return '';
        }

        // Build URLs first to avoid object access issues.
        $changeUrl = new \moodle_url('/local/navigatr/badge_selection.php', [
            'id' => $courseid,
            'provider_id' => $existingMapping->provider_id,
        ]);

        $removeUrl = new \moodle_url('/local/navigatr/course_settings.php', [
            'id' => $courseid,
            'action' => 'removemapping',
            'sesskey' => sesskey(),
        ]);

        $templateData = [
            'existing_mapping' => true,
            'provider_name' => $existingProvider['name'],
            'badge_name' => $existingBadge['name'],
            'badge_description' => isset($existingBadge['description']) ? $existingBadge['description'] : '',
            'badge_image_url' => isset($existingBadge['image_url']) ? $existingBadge['image_url'] : '',
            'badge_url' => isset($existingBadge['url']) ? $existingBadge['url'] : '',
            'change_url' => $changeUrl->out(),
            'remove_url' => $removeUrl->out(),
        ];

        // Ensure URLs are properly encoded.
        $templateData['change_url'] = $changeUrl->out(false); // False = don't HTML encode.
        $templateData['remove_url'] = $removeUrl->out(false);

        return $OUTPUT->render_from_template('local_navigatr/course/course_settings', $templateData);
    }
}
