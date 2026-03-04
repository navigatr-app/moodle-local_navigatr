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
     * @param object $existingmapping The existing mapping data
     * @param object $existingprovider The provider data
     * @param object $existingbadge The badge data
     * @param int $courseid The course ID
     * @return string Rendered HTML
     */
    public static function render_current_mapping($existingmapping, $existingprovider, $existingbadge, $courseid) {
        global $OUTPUT;

        if (!$existingmapping || !$existingprovider || !$existingbadge) {
            return '';
        }

        // Additional safety check.
        if (!is_object($existingmapping) || !isset($existingmapping->provider_id)) {
            return '';
        }

        // Build URLs first to avoid object access issues.
        $changeurl = new \moodle_url('/local/navigatr/badge_selection.php', [
            'id' => $courseid,
            'provider_id' => $existingmapping->provider_id,
        ]);

        $removeurl = new \moodle_url('/local/navigatr/course_settings.php', [
            'id' => $courseid,
            'action' => 'removemapping',
            'sesskey' => sesskey(),
        ]);

        $templatedata = [
            'existing_mapping' => true,
            'provider_name' => $existingprovider['name'],
            'badge_name' => $existingbadge['name'],
            'badge_description' => isset($existingbadge['description']) ? $existingbadge['description'] : '',
            'badge_image_url' => isset($existingbadge['image_url']) ? $existingbadge['image_url'] : '',
            'badge_url' => isset($existingbadge['url']) ? $existingbadge['url'] : '',
            'change_url' => $changeurl->out(),
            'remove_url' => $removeurl->out(),
        ];

        // Ensure URLs are properly encoded.
        $templatedata['change_url'] = $changeurl->out(false); // False = don't HTML encode.
        $templatedata['remove_url'] = $removeurl->out(false);

        return $OUTPUT->render_from_template('local_navigatr/course/course_settings', $templatedata);
    }
}
