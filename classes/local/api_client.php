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
 * Navigatr API Client
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

defined('MOODLE_INTERNAL') || die();

// Include Moodle's cURL library.
require_once($CFG->libdir . '/filelib.php');

/**
 * Navigatr API Client
 *
 * @package    local_navigatr
 * @copyright  2024 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_client {
    /** @var string Base URL for Navigatr API */
    private $baseurl;

    /** @var int Request timeout in seconds */
    private $timeout;

    /**
     * Get base URL based on environment configuration
     *
     * @return string Base URL for Navigatr API
     */
    public static function get_base_url() {
        $env = get_config('local_navigatr', 'env') ?: 'production';

        $baseurls = [
            'production' => 'https://api.navigatr.app/v1',
            'staging' => 'https://stagapi.navigatr.app/v1',
        ];

        return $baseurls[$env] ?? $baseurls['production'];
    }

    /**
     * Get advanced base URL based on environment configuration
     *
     * @param string|null $env Environment override ('production' or 'staging'); reads config when null.
     * @return string Advanced base URL for Navigatr API
     */
    public static function get_advanced_base_url($env = null) {
        if ($env === null) {
            $env = get_config('local_navigatr', 'env') ?: 'production';
        }

        $urls = [
            'production' => 'https://api.navigatr.app/advanced/v1',
            'staging' => 'https://stagapi.navigatr.app/advanced/v1',
        ];

        return $urls[$env] ?? $urls['production'];
    }

    /**
     * Constructor
     *
     * @param string|null $baseurl Base URL for Navigatr API (optional, will use config if null)
     * @param int $timeout Request timeout in seconds
     */
    public function __construct($baseurl = null, $timeout = null) {
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
     * @return object Response object with ok, code, body properties
     */
    private function make_request($method, $path, $data = null, $headers = []) {
        $url = $this->baseurl . '/' . ltrim($path, '/');

        // Use Moodle cURL wrapper.
        $curl = new \curl();

        // Prepare JSON data.
        $jsondata = null;
        if ($data !== null) {
            $jsondata = json_encode($data);
        }

        // Get PAT and validate it is configured.
        $pat = password_manager::get_pat();
        if (empty($pat)) {
            return (object) [
                'ok' => false,
                'code' => 401,
                'body' => ['error' => get_string('error_auth_failed', 'local_navigatr')],
                'error' => get_string('error_auth_failed', 'local_navigatr'),
            ];
        }

        // Build headers.
        $httpheaders = [
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: ' . get_string('user_agent', 'local_navigatr'),
            'X-Access-Token: ' . $pat,
        ];

        // Add any custom headers.
        foreach ($headers as $header) {
            $httpheaders[] = $header;
        }

        // Set cURL options.
        $curloptions = [
            'CURLOPT_TIMEOUT' => $this->timeout,
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FOLLOWLOCATION' => true,
            'CURLOPT_SSL_VERIFYPEER' => true,
            'CURLOPT_SSL_VERIFYHOST' => 2,
            'CURLOPT_HTTPHEADER' => $httpheaders,
        ];

        $response = false;
        if ($method === 'POST') {
            $response = $curl->post($url, $jsondata, $curloptions);
        } else if ($method === 'PUT') {
            $response = $curl->put($url, $jsondata, $curloptions);
        } else if ($method === 'GET') {
            $response = $curl->get($url, null, $curloptions);
        } else {
            // Raise an error.
            throw new \moodle_exception(get_string('invalid_method', 'local_navigatr', $method));
        }

        // Get response info.
        $httpcode = $curl->get_info(CURLINFO_HTTP_CODE);
        $error = $curl->get_errno();
        $curlerror = $curl->error;

        // Handle various response formats from get_info.
        if (is_array($httpcode) && isset($httpcode['http_code'])) {
            $httpcode = $httpcode['http_code'];
        } else if ($httpcode === false || $httpcode === null) {
            $httpcode = 0;
        }

        // Check for cURL errors.
        $haserror = $error !== 0 || !empty($curlerror);

        // Parse response.
        $body = null;
        if ($response !== false && !$haserror) {
            $body = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $body = $response; // Return raw response if JSON decode fails.
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
     * Verify a personal access token against the Navigatr API
     *
     * @param string $pat Personal access token to verify
     * @param string|null $url Full verify endpoint URL; derived from config when null.
     * @return object Response object
     */
    private function verify_pat($pat, $url = null) {
        if ($url === null) {
            $url = self::get_advanced_base_url() . '/personal_access_token/verify';
        }

        $curl = new \curl();

        $curloptions = [
            'CURLOPT_TIMEOUT' => $this->timeout,
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FOLLOWLOCATION' => true,
            'CURLOPT_SSL_VERIFYPEER' => true,
            'CURLOPT_SSL_VERIFYHOST' => 2,
            'CURLOPT_HTTPHEADER' => [
                'Accept: application/json',
                'X-Access-Token: ' . $pat,
                'User-Agent: ' . get_string('user_agent', 'local_navigatr'),
            ],
        ];

        $response = $curl->get($url, null, $curloptions);

        $httpcode = $curl->get_info(CURLINFO_HTTP_CODE);
        $error = $curl->get_errno();
        $curlerror = $curl->error;

        if (is_array($httpcode) && isset($httpcode['http_code'])) {
            $httpcode = $httpcode['http_code'];
        } else if ($httpcode === false || $httpcode === null) {
            $httpcode = 0;
        }

        $haserror = $error !== 0 || !empty($curlerror);

        $body = null;
        if ($response !== false && !$haserror) {
            $body = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $body = $response;
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
     * Test connection to Navigatr API using a personal access token
     *
     * @param string $pat Personal access token
     * @param string $environment Environment (staging, production)
     * @return object Response object
     */
    public static function test_connection($pat, $environment = 'production') {
        // Build the verify URL directly from the requested environment so we never
        // need to mutate the global config (which is not safe under concurrent requests).
        $verifyurl = self::get_advanced_base_url($environment) . '/personal_access_token/verify';

        $client = new self();
        $response = $client->verify_pat($pat, $verifyurl);

        // Trigger event for connection test.
        $eventdata = \local_navigatr\event\api_connection_tested::create([
            'context' => \context_system::instance(),
            'other' => [
                'environment' => $environment,
                'success' => $response->ok,
                'response_code' => $response->code,
            ],
        ]);
        $eventdata->trigger();

        return $response;
    }

    /**
     * POST request
     *
     * @param string $path API path
     * @param mixed $data Request data
     * @param array $headers Additional headers
     * @return object Response object
     */
    public function post($path, $data = null, $headers = []) {
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
    public function put($path, $data = null, $headers = []) {
        return $this->make_request('PUT', $path, $data, $headers);
    }

    /**
     * GET request
     *
     * @param string $path API path
     * @param array $headers Additional headers
     * @return object Response object
     */
    public function get($path, $headers = []) {
        return $this->make_request('GET', $path, null, $headers);
    }
}
