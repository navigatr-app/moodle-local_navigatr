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
 * Event observers for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname'   => '\core\event\course_completed',
        'callback'    => '\local_navigatr\observer::course_completed',
        'priority'    => 9999,
        'internal'    => false,
    ],
    [
        'eventname'   => '\core\event\course_restored',
        'callback'    => '\local_navigatr\observer::course_restored',
        'priority'    => 9999,
        'internal'    => false,
    ],
];

// Define custom events for audit logging
$events = [
    [
        'eventname' => '\local_navigatr\event\badge_issuance_success',
        'component' => 'local_navigatr',
    ],
    [
        'eventname' => '\local_navigatr\event\badge_issuance_failed',
        'component' => 'local_navigatr',
    ],
    [
        'eventname' => '\local_navigatr\event\badge_issuance_retry',
        'component' => 'local_navigatr',
    ],
];
