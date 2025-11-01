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
 * Password manager for secure storage of Navigatr credentials.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

/**
 * Password manager class for handling encrypted password storage.
 */
class password_manager
{
    /**
     * Encrypt a password for secure storage.
     *
     * @param string $password Plain text password
     * @return string Encrypted password
     */
    public static function encrypt_password($password)
    {
        if (empty($password)) {
            return '';
        }

        // Use OpenSSL encryption with a site-specific key
        // Note: We use our own encryption method rather than Moodle's encrypt_user_password()
        // as that function is designed for user passwords and may not be suitable for API credentials.
        $key = self::get_encryption_key();
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($password, 'AES-256-CBC', $key, 0, $iv);

        if ($encrypted === false) {
            throw new \moodle_exception('encryption_failed', 'local_navigatr');
        }

        // Combine IV and encrypted data, then base64 encode
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a password from secure storage.
     *
     * @param string $encrypted_password Encrypted password
     * @return string Plain text password
     */
    public static function decrypt_password($encrypted_password)
    {
        if (empty($encrypted_password)) {
            return '';
        }

        $key = self::get_encryption_key();
        $data = base64_decode($encrypted_password);

        if ($data === false || strlen($data) < 16) {
            throw new \moodle_exception('decryption_failed', 'local_navigatr');
        }

        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);

        if ($decrypted === false) {
            throw new \moodle_exception('decryption_failed', 'local_navigatr');
        }

        return $decrypted;
    }

    /**
     * Get or generate encryption key for this site.
     *
     * @return string Encryption key
     */
    private static function get_encryption_key()
    {
        global $CFG;

        // Use a site-specific key stored in config
        $key = get_config('local_navigatr', 'encryption_key');

        if (empty($key)) {
            // Generate a new key based on site URL and available secrets
            $site_key = $CFG->wwwroot;

            // Use available password salt properties (different versions have different names)
            if (isset($CFG->passwordsaltmain)) {
                $site_key .= $CFG->passwordsaltmain;
            } elseif (isset($CFG->passwordsalt)) {
                $site_key .= $CFG->passwordsalt;
            } else {
                // Fallback to a combination of site-specific values
                $site_key .= $CFG->dataroot . $CFG->dbname;
            }

            $key = hash('sha256', $site_key . 'navigatr_plugin_key', true);

            // Store the key for future use
            set_config('encryption_key', base64_encode($key), 'local_navigatr');
        } else {
            $key = base64_decode($key);
        }

        return $key;
    }

    /**
     * Store password securely.
     *
     * @param string $password Plain text password
     */
    public static function store_password($password)
    {
        if (empty($password)) {
            unset_config('password', 'local_navigatr');
            return;
        }

        $encrypted = self::encrypt_password($password);
        set_config('password', $encrypted, 'local_navigatr');
    }

    /**
     * Retrieve and decrypt password.
     *
     * @return string Plain text password
     */
    public static function get_password()
    {
        $encrypted = get_config('local_navigatr', 'password');

        if (empty($encrypted)) {
            return '';
        }

        return self::decrypt_password($encrypted);
    }

    /**
     * Clear stored password.
     */
    public static function clear_password()
    {
        unset_config('password', 'local_navigatr');
    }
}
