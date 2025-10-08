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
 * Library functions for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add Navigatr Badges settings to course settings menu.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to add settings for
 * @param context $context The context of the course
 */
function local_navigatr_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('local/navigatr:configurecourse', $context)) {
        $url = new moodle_url('/local/navigatr/course_settings.php', ['id' => $course->id]);
        $navigation->add(
            get_string('menu_name', 'local_navigatr'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'navigatr',
            new pix_icon('i/badge', '')
        );
    }
}

/**
 * Add Navigatr Badges settings to course administration menu.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to add settings for
 * @param context $context The context of the course
 */
function local_navigatr_extend_navigation_course_settings($navigation, $course, $context) {
    if (has_capability('local/navigatr:configurecourse', $context)) {
        $url = new moodle_url('/local/navigatr/course_settings.php', ['id' => $course->id]);
        $navigation->add(
            get_string('menu_name', 'local_navigatr'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'navigatr',
            new pix_icon('i/badge', '')
        );
    }
}
