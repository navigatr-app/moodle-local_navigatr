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
 * Token manager for Navigatr API authentication.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Token manager class for handling Navigatr API authentication.
 */
class token_manager
{
    /**
     * Get a valid access token, refreshing if necessary.
     *
     * @return string Access token
     * @throws \moodle_exception If authentication fails
     */
    public static function get_access_token()
    {
        $cache = \cache::make('local_navigatr', 'tokens');
        $accesstoken = $cache->get('access_token');

        if (!empty($accesstoken)) {
            return $accesstoken;
        }

        // Token missing or expired, need to refresh/re-authenticate
        self::refresh_or_reauth_with_lock();

        $accesstoken = $cache->get('access_token');
        if (empty($accesstoken)) {
            throw new \moodle_exception('auth_failed', 'local_navigatr');
        }

        return $accesstoken;
    }

    /**
     * Refresh or re-authenticate with lock to prevent thundering herds.
     *
     * @throws \moodle_exception If authentication fails
     */
    private static function refresh_or_reauth_with_lock()
    {
        $lockfactory = \core\lock\lock_config::get_lock_factory('cachelock');
        $lock = $lockfactory->get_lock('local_navigatr_token_refresh', 30);

        if (!$lock) {
            throw new \moodle_exception('auth_failed', 'local_navigatr');
        }

        try {
            // Double-check after acquiring lock
            $cache = \cache::make('local_navigatr', 'tokens');
            $accesstoken = $cache->get('access_token');

            if (!empty($accesstoken)) {
                return; // Another process already refreshed
            }

            // Try refresh first, then re-auth if that fails
            if (!self::refresh()) {
                self::reauth();
            }
        } finally {
            $lock->release();
        }
    }

    /**
     * Attempt to refresh the access token using refresh token.
     *
     * @return bool True if refresh successful, false otherwise
     */
    private static function refresh()
    {
        $refreshtoken = get_config('local_navigatr', 'refresh_token');
        $refreshexpires = get_config('local_navigatr', 'refresh_expires_at');

        if (empty($refreshtoken) || empty($refreshexpires) || time() >= $refreshexpires) {
            return false;
        }

        try {
            $client = new api_client();
            $response = $client->refresh_token($refreshtoken);

            if ($response->ok) {
                self::store_tokens($response->body);
                return true;
            }
        } catch (\Exception $e) {
            // Trigger event for token refresh failure
            $eventdata = \local_navigatr\event\token_refresh_failed::create([
                'context' => \context_system::instance(),
                'other' => [
                    'error' => $e->getMessage(),
                    'environment' => get_config('local_navigatr', 'env') ?: 'production',
                ]
            ]);
            $eventdata->trigger();
        }

        return false;
    }

    /**
     * Re-authenticate using username and password.
     *
     * @throws \moodle_exception If authentication fails
     */
    public static function reauth()
    {
        $username = get_config('local_navigatr', 'username');
        $password = \local_navigatr\local\password_manager::get_password();

        if (empty($username) || empty($password)) {
            throw new \moodle_exception('auth_failed', 'local_navigatr');
        }

        $client = new api_client();
        $response = $client->get_token($username, $password);

        if (!$response->ok) {
            throw new \moodle_exception('auth_failed', 'local_navigatr');
        }

        // Decode JWT to get user ID
        $idtoken = $response->body['id_token'] ?? '';
        $userid = self::decode_jwt_sub($idtoken);

        if (!empty($userid)) {
            set_config('nav_user_id', $userid, 'local_navigatr');
        }

        self::store_tokens($response->body);
    }

    /**
     * Store tokens and expiration times.
     *
     * @param array $tokens Token response from API
     */
    private static function store_tokens($tokens)
    {
        $accesstoken = $tokens['access_token'] ?? '';
        $refreshtoken = $tokens['refresh_token'] ?? '';
        $idtoken = $tokens['id_token'] ?? '';

        // Store access token in cache (4-minute TTL)
        if (!empty($accesstoken)) {
            $cache = \cache::make('local_navigatr', 'tokens');
            $cache->set('access_token', $accesstoken);
        }

        // Store refresh token encrypted in config table (1 day TTL)
        if (!empty($refreshtoken)) {
            $refreshexpires = time() + 86400;
            set_config('refresh_token', $refreshtoken, 'local_navigatr');
            set_config('refresh_expires_at', $refreshexpires, 'local_navigatr');
        }
    }

    /**
     * Decode JWT sub claim (without verification).
     *
     * @param string $jwt JWT token
     * @return string|null User ID from sub claim
     */
    private static function decode_jwt_sub($jwt)
    {
        if (empty($jwt)) {
            return null;
        }

        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = base64_decode(str_pad(strtr($parts[1], '-_', '+/'), strlen($parts[1]) % 4, '=', STR_PAD_RIGHT));
        $data = json_decode($payload, true);

        return $data['sub'] ?? null;
    }
}
