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
 * Upgrade script for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post upgrade hook.
 *
 * @param int $oldversion The old version number
 * @return bool True on success
 */
function xmldb_local_navigatr_upgrade($oldversion) {
    if ($oldversion < 2026030401) {
        // Migrating from username/password authentication to Personal Access Tokens.
        // Clear all stale authentication config keys from the old auth system.
        unset_config('username', 'local_navigatr');
        unset_config('password', 'local_navigatr');
        unset_config('encryption_key', 'local_navigatr');
        unset_config('access_token', 'local_navigatr');
        unset_config('access_expires_at', 'local_navigatr');
        unset_config('refresh_token', 'local_navigatr');
        unset_config('refresh_expires_at', 'local_navigatr');
        unset_config('nav_user_id', 'local_navigatr');

        upgrade_plugin_savepoint(true, 2026030401, 'local', 'navigatr');
    }

    return true;
}
