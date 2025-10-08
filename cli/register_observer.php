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
 * CLI script to register Navigatr event observers.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

// Get cli options
list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
        'force' => false,
    ),
    array(
        'h' => 'help',
        'f' => 'force',
    )
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help = "Register Navigatr event observers.

Options:
-h, --help            Print out this help
-f, --force           Force registration even if already exists

Example:
\$sudo -u www-data /usr/bin/php local/navigatr/cli/register_observer.php
";

    echo $help;
    exit(0);
}

// Check if observer is already registered
$existing = $DB->get_record('events_handlers', [
    'component' => 'local_navigatr',
    'eventname' => '\core\event\course_completed'
]);

if ($existing && !$options['force']) {
    cli_writeln('Observer is already registered.');
    exit(0);
}

// Define observers
$observers = [
    [
        'eventname'   => '\core\event\course_completed',
        'callback'    => '\local_navigatr\observer::course_completed',
        'priority'    => 9999,
        'internal'    => false,
    ],
];

// Register observers
$registered = 0;
foreach ($observers as $observer) {
    if ($existing && $options['force']) {
        // Update existing
        $existing->handlerfile = '/local/navigatr/classes/observer.php';
        $existing->handlerfunction = $observer['callback'];
        $existing->status = 1;
        $existing->internal = $observer['internal'] ? 1 : 0;
        $DB->update_record('events_handlers', $existing);
        $registered++;
    } elseif (!$existing) {
        // Insert new
        $DB->insert_record('events_handlers', (object) [
            'eventname' => $observer['eventname'],
            'component' => 'local_navigatr',
            'handlerfile' => '/local/navigatr/classes/observer.php',
            'handlerfunction' => $observer['callback'],
            'schedule' => null,
            'status' => 1,
            'internal' => $observer['internal'] ? 1 : 0
        ]);
        $registered++;
    }
}

cli_writeln("Registered $registered observer(s) successfully.");
exit(0);
