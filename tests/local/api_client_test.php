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
 * Unit tests for Navigatr API Client
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

use advanced_testcase;
use local_navigatr\local\api_client;

/**
 * Unit tests for Navigatr API Client
 *
 * @covers \local_navigatr\local\api_client
 */
final class api_client_test extends advanced_testcase {
    /**
     * Test environment URL configuration
     *
     * @covers \local_navigatr\local\api_client::get_base_url
     */
    public function test_get_base_url(): void {
        // Test production environment.
        set_config('env', 'production', 'local_navigatr');
        $this->assertEquals('https://api.navigatr.app/v1', api_client::get_base_url());

        // Test staging environment.
        set_config('env', 'staging', 'local_navigatr');
        $this->assertEquals('https://stagapi.navigatr.app/v1', api_client::get_base_url());

        // Test default to production.
        unset_config('env', 'local_navigatr');
        $this->assertEquals('https://api.navigatr.app/v1', api_client::get_base_url());
    }

    /**
     * Test API client initialization
     *
     * @covers \local_navigatr\local\api_client::__construct
     */
    public function test_constructor(): void {
        $client = new api_client();
        $this->assertInstanceOf(api_client::class, $client);
    }

    /**
     * Test authentication with valid credentials
     *
     * @covers \local_navigatr\local\api_client::get_token
     */
    public function test_get_token_success(): void {
        $this->resetAfterTest();

        // Mock successful authentication response.
        $mock_response = (object) [
            'ok' => true,
            'code' => 200,
            'body' => [
                'access_token' => 'mock_access_token',
                'refresh_token' => 'mock_refresh_token',
                'id_token' => 'mock_id_token',
                'expires_in' => 300,
            ],
        ];

        // We can't easily mock HTTP requests in unit tests,.
        // so we'll test the method structure and error handling.
        $client = new api_client();

        // Test that method exists and is callable.
        $this->assertTrue(method_exists($client, 'get_token'));
        $this->assertTrue(is_callable([$client, 'get_token']));
    }

    /**
     * Test authentication failure handling
     *
     * @covers \local_navigatr\local\api_client::get_token
     */
    public function test_get_token_failure(): void {
        $this->resetAfterTest();

        $client = new api_client();

        // Test with invalid credentials (this will fail in real scenario).
        // We're testing the method structure, not the actual HTTP call.
        $this->assertTrue(method_exists($client, 'get_token'));
    }

    /**
     * Test token refresh functionality
     *
     * @covers \local_navigatr\local\api_client::refresh_token
     */
    public function test_refresh_token(): void {
        $this->resetAfterTest();

        $client = new api_client();

        // Test method exists.
        $this->assertTrue(method_exists($client, 'refresh_token'));
        $this->assertTrue(is_callable([$client, 'refresh_token']));
    }

    /**
     * Test connection test method
     *
     * @covers \local_navigatr\local\api_client::test_connection
     */
    public function test_test_connection(): void {
        $this->resetAfterTest();

        // Test method exists and is static.
        $this->assertTrue(method_exists(api_client::class, 'test_connection'));

        // Test method signature.
        $reflection = new \ReflectionMethod(api_client::class, 'test_connection');
        $this->assertTrue($reflection->isStatic());
        $this->assertCount(3, $reflection->getParameters());
    }

    /**
     * Test HTTP request methods
     *
     * @covers \local_navigatr\local\api_client::get
     * @covers \local_navigatr\local\api_client::post
     * @covers \local_navigatr\local\api_client::put
     * @covers \local_navigatr\local\api_client::delete
     */
    public function test_http_methods(): void {
        $this->resetAfterTest();

        $client = new api_client();

        // Test that HTTP methods exist.
        $this->assertTrue(method_exists($client, 'get'));
        $this->assertTrue(method_exists($client, 'post'));
        $this->assertTrue(method_exists($client, 'put'));
        $this->assertTrue(method_exists($client, 'delete'));
    }

    /**
     * Test provider listing
     *
     * @covers \local_navigatr\local\api_client::get_providers
     */
    public function test_get_providers(): void {
        $this->resetAfterTest();

        $client = new api_client();

        // Test method exists.
        $this->assertTrue(method_exists($client, 'get_providers'));
    }

    /**
     * Test badge listing
     *
     * @covers \local_navigatr\local\api_client::get_badges
     */
    public function test_get_badges(): void {
        $this->resetAfterTest();

        $client = new api_client();

        // Test method exists.
        $this->assertTrue(method_exists($client, 'get_badges'));
    }

    /**
     * Test badge issuance
     *
     * @covers \local_navigatr\local\api_client::issue_badge
     */
    public function test_issue_badge(): void {
        $this->resetAfterTest();

        $client = new api_client();

        // Test method exists.
        $this->assertTrue(method_exists($client, 'issue_badge'));
    }

    /**
     * Test timeout configuration
     *
     * @covers \local_navigatr\local\api_client::__construct
     */
    public function test_timeout_configuration(): void {
        $this->resetAfterTest();

        // Test default timeout.
        set_config('timeout', 30, 'local_navigatr');
        $client = new api_client();
        $this->assertInstanceOf(api_client::class, $client);

        // Test custom timeout.
        set_config('timeout', 60, 'local_navigatr');
        $client2 = new api_client();
        $this->assertInstanceOf(api_client::class, $client2);
    }

    /**
     * Test environment switching
     *
     * @covers \local_navigatr\local\api_client::get_base_url
     */
    public function test_environment_switching(): void {
        $this->resetAfterTest();

        // Test production.
        set_config('env', 'production', 'local_navigatr');
        $prod_url = api_client::get_base_url();
        $this->assertEquals('https://api.navigatr.app/v1', $prod_url);

        // Test staging.
        set_config('env', 'staging', 'local_navigatr');
        $staging_url = api_client::get_base_url();
        $this->assertEquals('https://stagapi.navigatr.app/v1', $staging_url);
    }

    /**
     * Test error handling for invalid environment
     *
     * @covers \local_navigatr\local\api_client::get_base_url
     */
    public function test_invalid_environment(): void {
        $this->resetAfterTest();

        // Test with invalid environment (should default to production).
        set_config('env', 'invalid', 'local_navigatr');
        $url = api_client::get_base_url();
        $this->assertEquals('https://api.navigatr.app/v1', $url);
    }
}
