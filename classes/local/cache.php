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
 * Cache helper for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

/**
 * Cache helper class for Navigatr API data.
 */
class cache {
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
     * Get user detail from cache.
     *
     * @return array|null User detail array or null if not cached
     */
    public static function get_user_detail() {
        $cache = \cache::make('local_navigatr', 'user_detail');
        return $cache->get('current_user');
    }

    /**
     * Set user detail in cache.
     *
     * @param array $userdetail User detail array
     */
    public static function set_user_detail($userdetail) {
        $cache = \cache::make('local_navigatr', 'user_detail');
        $cache->set('current_user', $userdetail);
    }
}
