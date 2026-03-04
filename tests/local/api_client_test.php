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
 * Unit tests for Navigatr API Client.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

use advanced_testcase;
use local_navigatr\local\api_client;

/**
 * Unit tests for Navigatr API Client.
 *
 * @covers \local_navigatr\local\api_client
 */
final class api_client_test extends advanced_testcase {
    /**
     * Test get_base_url returns correct URL for each environment.
     *
     * @covers \local_navigatr\local\api_client::get_base_url
     */
    public function test_get_base_url(): void {
        $this->resetAfterTest();

        set_config('env', 'production', 'local_navigatr');
        $this->assertEquals('https://api.navigatr.app/v1', api_client::get_base_url());

        set_config('env', 'staging', 'local_navigatr');
        $this->assertEquals('https://stagapi.navigatr.app/v1', api_client::get_base_url());

        // No config set: should default to production.
        unset_config('env', 'local_navigatr');
        $this->assertEquals('https://api.navigatr.app/v1', api_client::get_base_url());

        // Unknown environment: should fall back to production.
        set_config('env', 'invalid_env', 'local_navigatr');
        $this->assertEquals('https://api.navigatr.app/v1', api_client::get_base_url());
    }

    /**
     * Test get_advanced_base_url returns correct advanced API URL for each environment.
     *
     * @covers \local_navigatr\local\api_client::get_advanced_base_url
     */
    public function test_get_advanced_base_url(): void {
        $this->resetAfterTest();

        set_config('env', 'production', 'local_navigatr');
        $this->assertEquals('https://api.navigatr.app/advanced/v1', api_client::get_advanced_base_url());

        set_config('env', 'staging', 'local_navigatr');
        $this->assertEquals('https://stagapi.navigatr.app/advanced/v1', api_client::get_advanced_base_url());

        // No config set: should default to production.
        unset_config('env', 'local_navigatr');
        $this->assertEquals('https://api.navigatr.app/advanced/v1', api_client::get_advanced_base_url());

        // Unknown environment: should fall back to production.
        set_config('env', 'unknown', 'local_navigatr');
        $this->assertEquals('https://api.navigatr.app/advanced/v1', api_client::get_advanced_base_url());
    }

    /**
     * Test that the client can be instantiated.
     *
     * @covers \local_navigatr\local\api_client::__construct
     */
    public function test_constructor(): void {
        $client = new api_client();
        $this->assertInstanceOf(api_client::class, $client);
    }

    /**
     * Test HTTP request methods exist.
     *
     * @covers \local_navigatr\local\api_client::get
     * @covers \local_navigatr\local\api_client::post
     * @covers \local_navigatr\local\api_client::put
     */
    public function test_http_methods_exist(): void {
        $client = new api_client();
        $this->assertTrue(method_exists($client, 'get'));
        $this->assertTrue(method_exists($client, 'post'));
        $this->assertTrue(method_exists($client, 'put'));
    }

    /**
     * Test test_connection is static and accepts exactly 2 parameters.
     *
     * @covers \local_navigatr\local\api_client::test_connection
     */
    public function test_test_connection_signature(): void {
        $this->assertTrue(method_exists(api_client::class, 'test_connection'));

        $reflection = new \ReflectionMethod(api_client::class, 'test_connection');
        $this->assertTrue($reflection->isStatic());
        $this->assertCount(2, $reflection->getParameters());
    }

    /**
     * Test that get() returns a 401 response immediately when no PAT is configured,
     * without making any network request.
     *
     * @covers \local_navigatr\local\api_client::get
     */
    public function test_get_returns_auth_error_when_no_pat(): void {
        $this->resetAfterTest();
        unset_config('personal_access_token', 'local_navigatr');

        $client = new api_client();
        $response = $client->get('/user_detail/0');

        $this->assertFalse($response->ok);
        $this->assertSame(401, $response->code);
    }

    /**
     * Test timeout configuration is accepted without error.
     *
     * @covers \local_navigatr\local\api_client::__construct
     */
    public function test_timeout_configuration(): void {
        $this->resetAfterTest();

        set_config('timeout', 30, 'local_navigatr');
        $this->assertInstanceOf(api_client::class, new api_client());

        set_config('timeout', 60, 'local_navigatr');
        $this->assertInstanceOf(api_client::class, new api_client());
    }
}
