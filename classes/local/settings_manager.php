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
 * Connection setting writes for the Navigatr plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

use local_navigatr\event\connection_removed;
use local_navigatr\event\settings_updated;

/**
 * Saves and clears the Navigatr connection settings, and logs each change as an event.
 *
 * Events carry only the environment and timeout, never the Personal Access Token.
 */
class settings_manager {
    /**
     * Save the connection settings.
     *
     * @param string $env The environment, 'production' or 'staging'
     * @param string $pat The Personal Access Token
     * @param int $timeout The HTTP timeout in seconds
     */
    public static function save(string $env, string $pat, int $timeout): void {
        set_config('env', $env, 'local_navigatr');
        password_manager::store_pat(trim($pat));
        set_config('timeout', $timeout, 'local_navigatr');

        settings_updated::create([
            'context' => \context_system::instance(),
            'other' => ['env' => $env, 'timeout' => $timeout],
        ])->trigger();
    }

    /**
     * Remove the stored Personal Access Token.
     */
    public static function remove_connection(): void {
        password_manager::clear_pat();

        connection_removed::create(['context' => \context_system::instance()])->trigger();
    }
}
