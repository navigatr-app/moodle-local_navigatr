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

// Include Moodle's cURL library
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
     * Constructor
     *
     * @param string $baseurl Base URL for Navigatr API
     * @param int $timeout Request timeout in seconds
     */
    public function __construct($baseurl, $timeout = 30) {
        $this->baseurl = rtrim($baseurl, '/');
        $this->timeout = $timeout;
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
        
        
        // Use Moodle cURL wrapper
        $curl = new \curl();
        
        // Prepare JSON data
        $json_data = null;
        if ($data !== null) {
            $json_data = json_encode($data);
        }

        // Set cURL options
        $curloptions = [
            'CURLOPT_TIMEOUT' => $this->timeout,
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FOLLOWLOCATION' => true,
            'CURLOPT_SSL_VERIFYPEER' => true,
            'CURLOPT_SSL_VERIFYHOST' => 2,
            'CURLOPT_HTTPHEADER' => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded', // Changed from application/json
                'User-Agent: Moodle-Navigatr-Plugin/1.0',
            ],
        ];

        $response = false;
        if ($method === 'POST' && $data !== null) {
            // Convert array to form-encoded string
            $form_data = http_build_query($data);
            $response = $curl->post($url, $form_data, $curloptions);
        } elseif ($method === 'POST') {
            $response = $curl->post($url, $data, $curloptions);
        } elseif ($method === 'PUT') {
            $response = $curl->put($url, $data, $curloptions);
        } elseif ($method === 'GET') {
            $response = $curl->get($url, null, $curloptions);
        } else {
            $curloptions['CURLOPT_CUSTOMREQUEST'] = strtoupper($method);
            $response = $curl->post($url, $data, $curloptions);
        }
        
        // Get response info
        $httpcode = $curl->get_info(CURLINFO_HTTP_CODE);
        $error = $curl->get_errno();
        
        // Handle array response from get_info
        if (is_array($httpcode) && isset($httpcode['http_code'])) {
            $httpcode = $httpcode['http_code'];
        }
        

        // Parse response
        $body = null;
        if ($response !== false) {
            $body = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $body = $response; // Return raw response if JSON decode fails
            }
        }

        return (object) [
            'ok' => $error === 0 && $httpcode >= 200 && $httpcode < 300,
            'code' => $httpcode,
            'body' => $body,
            'error' => $error !== 0 ? $curl->error : null,
        ];
    }

    /**
     * Test connection to Navigatr API
     *
     * @param string $username Navigatr username
     * @param string $password Navigatr password
     * @param string $environment Environment (development, staging, production)
     * @return object Response object
     */
    public static function test_connection($username, $password, $environment = 'production') {
        // Get base URL based on environment
        $baseurls = [
            'development' => 'http://127.0.0.1:5000/v1',
            'staging' => 'https://stagapi.navigatr.app/v1',
            'production' => 'https://api.navigatr.app/v1',
        ];
        
        $baseurl = $baseurls[$environment] ?? $baseurls['production'];
        $timeout = 30;
        
        
        // Create API client
        $client = new self($baseurl, $timeout);
        
        // Authenticate
        $authresponse = $client->post('/token', [
            'username' => $username,
            'password' => $password,
        ]);

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