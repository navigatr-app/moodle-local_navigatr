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
 * Upgrade script for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Post upgrade hook to ensure event observers are registered.
 */
function xmldb_local_navigatr_upgrade($oldversion) {
    global $DB;
    
    // Force registration of event observers for all versions
    $observers = [
        [
            'eventname'   => '\core\event\course_completed',
            'callback'    => '\local_navigatr\observer::course_completed',
            'priority'    => 9999,
            'internal'    => false,
        ],
    ];
    
    // Check if observer is already registered
    $existing = $DB->get_record('events_handlers', [
        'component' => 'local_navigatr',
        'eventname' => '\core\event\course_completed'
    ]);
    
    if (!$existing) {
        // Register the observer
        foreach ($observers as $observer) {
            $DB->insert_record('events_handlers', (object) [
                'eventname' => $observer['eventname'],
                'component' => 'local_navigatr',
                'handlerfile' => '/local/navigatr/classes/observer.php',
                'handlerfunction' => $observer['callback'],
                'schedule' => null,
                'status' => 1,
                'internal' => $observer['internal'] ? 1 : 0
            ]);
        }
    }
    
    return true;
}
