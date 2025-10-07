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
 * Cache helper for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Cache helper class for Navigatr API data.
 */
class cache {

    /**
     * Get providers for a user.
     *
     * @param string $userid Navigatr user ID
     * @return array|null Providers array or null if not cached
     */
    public static function get_providers($userid) {
        $cache = \cache::make('local_navigatr', 'providers');
        return $cache->get($userid);
    }

    /**
     * Set providers for a user.
     *
     * @param string $userid Navigatr user ID
     * @param array $providers Providers array
     */
    public static function set_providers($userid, $providers) {
        $cache = \cache::make('local_navigatr', 'providers');
        $cache->set($userid, $providers);
    }

    /**
     * Get badges for a provider.
     *
     * @param string $providerid Provider ID
     * @param int $page Page number
     * @param int $size Page size
     * @return array|null Badges array or null if not cached
     */
    public static function get_badges($providerid, $page = 1, $size = 50) {
        $cache = \cache::make('local_navigatr', 'badges');
        $key = "{$providerid}:{$page}:{$size}";
        return $cache->get($key);
    }

    /**
     * Set badges for a provider.
     *
     * @param string $providerid Provider ID
     * @param int $page Page number
     * @param int $size Page size
     * @param array $badges Badges array
     */
    public static function set_badges($providerid, $page, $size, $badges) {
        $cache = \cache::make('local_navigatr', 'badges');
        $key = "{$providerid}:{$page}:{$size}";
        $cache->set($key, $badges);
    }

    /**
     * Clear all caches.
     */
    public static function clear_all() {
        $providerscache = \cache::make('local_navigatr', 'providers');
        $badgescache = \cache::make('local_navigatr', 'badges');
        
        $providerscache->purge();
        $badgescache->purge();
    }

    /**
     * Clear providers cache for a user.
     *
     * @param string $userid Navigatr user ID
     */
    public static function clear_providers($userid) {
        $cache = \cache::make('local_navigatr', 'providers');
        $cache->delete($userid);
    }

    /**
     * Clear badges cache for a provider.
     *
     * @param string $providerid Provider ID
     */
    public static function clear_badges($providerid) {
        $cache = \cache::make('local_navigatr', 'badges');
        
        // Clear all pages for this provider
        for ($page = 1; $page <= 10; $page++) { // Reasonable limit
            for ($size = 10; $size <= 100; $size += 10) { // Common page sizes
                $key = "{$providerid}:{$page}:{$size}";
                $cache->delete($key);
            }
        }
    }
}
