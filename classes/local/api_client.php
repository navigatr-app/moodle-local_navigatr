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
 * Navigatr API Client
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

defined('MOODLE_INTERNAL') || die();

// Include Moodle's cURL library
require_once($CFG->libdir . '/filelib.php');

/**
 * Navigatr API Client
 *
 * @package    local_navigatr
 * @copyright  2024 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_client
{
    /** @var string Base URL for Navigatr API */
    private $baseurl;

    /** @var int Request timeout in seconds */
    private $timeout;

    /**
     * Get base URL based on environment configuration
     *
     * @return string Base URL for Navigatr API
     */
    public static function get_base_url()
    {
        $env = get_config('local_navigatr', 'env') ?: 'production';

        $baseurls = [
            'production' => 'https://api.navigatr.app/v1',
            'staging' => 'https://stagapi.navigatr.app/v1',
        ];

        return $baseurls[$env] ?? $baseurls['production'];
    }

    /**
     * Constructor
     *
     * @param string|null $baseurl Base URL for Navigatr API (optional, will use config if null)
     * @param int $timeout Request timeout in seconds
     */
    public function __construct($baseurl = null, $timeout = null)
    {
        $this->baseurl = $baseurl ? rtrim($baseurl, '/') : self::get_base_url();
        $this->timeout = $timeout ?? get_config('local_navigatr', 'timeout') ?: 30;
    }

    /**
     * Make HTTP request to Navigatr API using simple Moodle cURL approach
     *
     * @param string $method HTTP method
     * @param string $path API path
     * @param mixed $data JSON data to send
     * @param array $headers Additional headers
     * @param bool $require_auth Whether to include Bearer token (default: true)
     * @return object Response object with ok, code, body properties
     */
    private function make_request($method, $path, $data = null, $headers = [], $require_auth = true)
    {
        $url = $this->baseurl . '/' . ltrim($path, '/');

        // Use Moodle cURL wrapper
        $curl = new \curl();

        // Prepare JSON data
        $json_data = null;
        if ($data !== null) {
            $json_data = json_encode($data);
        }

        // Build headers
        $http_headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: ' . get_string('user_agent', 'local_navigatr'),
        ];

        // Add Bearer token if authentication is required
        if ($require_auth) {
            try {
                $token = token_manager::get_access_token();
                $http_headers[] = 'Authorization: Bearer ' . $token;
            } catch (\moodle_exception $e) {
                // If token retrieval fails, return error response
                return (object) [
                    'ok' => false,
                    'code' => 401,
                    'body' => ['error' => get_string('error_auth_failed', 'local_navigatr') . ': ' . $e->getMessage()],
                    'error' => get_string('error_auth_failed', 'local_navigatr'),
                ];
            }
        }

        // Add any custom headers
        foreach ($headers as $header) {
            $http_headers[] = $header;
        }

        // Set cURL options
        $curloptions = [
            'CURLOPT_TIMEOUT' => $this->timeout,
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FOLLOWLOCATION' => true,
            'CURLOPT_SSL_VERIFYPEER' => true,
            'CURLOPT_SSL_VERIFYHOST' => 2,
            'CURLOPT_HTTPHEADER' => $http_headers,
        ];

        $response = false;
        if ($method === 'POST') {
            $response = $curl->post($url, $json_data, $curloptions);
        } else if ($method === 'PUT') {
            $response = $curl->put($url, $json_data, $curloptions);
        } else if ($method === 'GET') {
            $response = $curl->get($url, null, $curloptions);
        } else {
            // Raise an error.
            throw new \moodle_exception(get_string('invalid_method', 'local_navigatr', $method));
        }

        // Get response info
        $httpcode = $curl->get_info(CURLINFO_HTTP_CODE);
        $error = $curl->get_errno();
        $curlerror = $curl->error;

        // Handle various response formats from get_info
        if (is_array($httpcode) && isset($httpcode['http_code'])) {
            $httpcode = $httpcode['http_code'];
        } else if ($httpcode === false || $httpcode === null) {
            $httpcode = 0;
        }

        // Check for cURL errors
        $haserror = $error !== 0 || !empty($curlerror);

        // Parse response
        $body = null;
        if ($response !== false && !$haserror) {
            $body = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $body = $response; // Return raw response if JSON decode fails
            }
        } else if ($haserror) {
            $body = ['error' => $curlerror ?: get_string('error_network', 'local_navigatr')];
        }

        return (object) [
            'ok' => !$haserror && $httpcode >= 200 && $httpcode < 300,
            'code' => $httpcode,
            'body' => $body,
            'error' => $haserror ? ($curlerror ?: get_string('error_network', 'local_navigatr')) : null,
        ];
    }

    /**
     * Get authentication token using form-encoded data
     *
     * @param string $username Navigatr username
     * @param string $password Navigatr password
     * @return object Response object
     */
    public function get_token($username, $password)
    {
        $url = $this->baseurl . '/token';

        $curl = new \curl();

        // Set cURL options for form-encoded data
        $curloptions = [
            'CURLOPT_TIMEOUT' => $this->timeout,
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FOLLOWLOCATION' => true,
            'CURLOPT_SSL_VERIFYPEER' => true,
            'CURLOPT_SSL_VERIFYHOST' => 2,
            'CURLOPT_HTTPHEADER' => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: ' . get_string('user_agent', 'local_navigatr'),
            ],
        ];

        // Prepare form-encoded data
        $form_data = http_build_query([
            'username' => $username,
            'password' => $password,
        ]);

        // Make POST request with form data (no auth required for token endpoint)
        $response = $curl->post($url, $form_data, $curloptions);

        // Get response info
        $httpcode = $curl->get_info(CURLINFO_HTTP_CODE);
        $error = $curl->get_errno();
        $curlerror = $curl->error;

        // Handle various response formats from get_info
        if (is_array($httpcode) && isset($httpcode['http_code'])) {
            $httpcode = $httpcode['http_code'];
        } else if ($httpcode === false || $httpcode === null) {
            $httpcode = 0;
        }

        // Check for cURL errors
        $haserror = $error !== 0 || !empty($curlerror);

        // Parse response
        $body = null;
        if ($response !== false && !$haserror) {
            $body = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $body = $response; // Return raw response if JSON decode fails
            }
        } else if ($haserror) {
            $body = ['error' => $curlerror ?: get_string('error_network', 'local_navigatr')];
        }

        return (object) [
            'ok' => !$haserror && $httpcode >= 200 && $httpcode < 300,
            'code' => $httpcode,
            'body' => $body,
            'error' => $haserror ? ($curlerror ?: get_string('error_network', 'local_navigatr')) : null,
        ];
    }

    /**
     * Refresh authentication token using refresh token
     *
     * @param string $refresh_token Navigatr refresh token
     * @return object Response object
     */
    public function refresh_token($refresh_token)
    {
        $url = $this->baseurl . '/token';

        $curl = new \curl();

        // Set cURL options for form-encoded data
        $curloptions = [
            'CURLOPT_TIMEOUT' => $this->timeout,
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FOLLOWLOCATION' => true,
            'CURLOPT_SSL_VERIFYPEER' => true,
            'CURLOPT_SSL_VERIFYHOST' => 2,
            'CURLOPT_HTTPHEADER' => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: ' . get_string('user_agent', 'local_navigatr'),
            ],
        ];

        // Prepare form-encoded data for refresh
        $form_data = http_build_query([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
        ]);

        // Make POST request with form data
        $response = $curl->post($url, $form_data, $curloptions);

        // Get response info
        $httpcode = $curl->get_info(CURLINFO_HTTP_CODE);
        $error = $curl->get_errno();
        $curlerror = $curl->error;

        // Handle various response formats from get_info
        if (is_array($httpcode) && isset($httpcode['http_code'])) {
            $httpcode = $httpcode['http_code'];
        } else if ($httpcode === false || $httpcode === null) {
            $httpcode = 0;
        }

        // Check for cURL errors
        $haserror = $error !== 0 || !empty($curlerror);

        // Parse response
        $body = null;
        if ($response !== false && !$haserror) {
            $body = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $body = $response; // Return raw response if JSON decode fails
            }
        } else if ($haserror) {
            $body = ['error' => $curlerror ?: get_string('error_network', 'local_navigatr')];
        }

        return (object) [
            'ok' => !$haserror && $httpcode >= 200 && $httpcode < 300,
            'code' => $httpcode,
            'body' => $body,
            'error' => $haserror ? ($curlerror ?: get_string('error_network', 'local_navigatr')) : null,
        ];
    }

    /**
     * Test connection to Navigatr API
     *
     * @param string $username Navigatr username
     * @param string $password Navigatr password
     * @param string $environment Environment (staging, production)
     * @return object Response object
     */
    public static function test_connection($username, $password, $environment = 'production')
    {
        // Temporarily set environment for this test
        $original_env = get_config('local_navigatr', 'env');
        set_config('env', $environment, 'local_navigatr');

        // Create API client (will use the environment we just set)
        $client = new self();

        // Authenticate using form-encoded data
        $authresponse = $client->get_token($username, $password);

        // If authentication successful, store tokens for future use
        if ($authresponse->ok && isset($authresponse->body['access_token'])) {
            // Store tokens temporarily for this test
            set_config('access_token', $authresponse->body['access_token'], 'local_navigatr');
            set_config('access_expires_at', time() + 300, 'local_navigatr'); // 5 minutes

            if (isset($authresponse->body['refresh_token'])) {
                set_config('refresh_token', $authresponse->body['refresh_token'], 'local_navigatr');
                set_config('refresh_expires_at', time() + 86400, 'local_navigatr'); // 1 day
            }
        }

        // Restore original environment
        if ($original_env !== false) {
            set_config('env', $original_env, 'local_navigatr');
        }

        // Trigger event for connection test
        $eventdata = \local_navigatr\event\api_connection_tested::create([
            'context' => \context_system::instance(),
            'other' => [
                'environment' => $environment,
                'success' => $authresponse->ok,
                'response_code' => $authresponse->code,
            ],
        ]);
        $eventdata->trigger();

        return $authresponse;
    }

    /**
     * POST request
     *
     * @param string $path API path
     * @param mixed $data Request data
     * @param array $headers Additional headers
     * @return object Response object
     */
    public function post($path, $data = null, $headers = [])
    {
        return $this->make_request('POST', $path, $data, $headers);
    }

    /**
     * PUT request
     *
     * @param string $path API path
     * @param mixed $data Request data
     * @param array $headers Additional headers
     * @return object Response object
     */
    public function put($path, $data = null, $headers = [])
    {
        return $this->make_request('PUT', $path, $data, $headers);
    }

    /**
     * GET request
     *
     * @param string $path API path
     * @param array $headers Additional headers
     * @param bool $require_auth Whether authentication is required
     * @return object Response object
     */
    public function get($path, $headers = [], $require_auth = true)
    {
        return $this->make_request('GET', $path, null, $headers, $require_auth);
    }
}
