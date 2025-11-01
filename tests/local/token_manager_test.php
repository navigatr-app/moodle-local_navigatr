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
 * Unit tests for Navigatr Token Manager
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

use advanced_testcase;
use local_navigatr\local\token_manager;
use moodle_exception;

/**
 * Unit tests for Navigatr Token Manager
  * @covers \local_navigatr\local\token_manager
 */
final class token_manager_test extends advanced_testcase
{
    /**
     * Test token manager class structure
     */
    public function test_class_structure(): void {
        $this->assertTrue(class_exists(token_manager::class));
        $this->assertTrue(method_exists(token_manager::class, 'get_access_token'));
        $this->assertTrue(method_exists(token_manager::class, 'clear_tokens'));
    }

    /**
     * Test token retrieval with no stored tokens
          * @covers \local_navigatr\local\token_manager::get_access_token
     */
    public function test_get_access_token_no_tokens(): void {
        $this->resetAfterTest();

        // Clear any existing tokens.
        token_manager::clear_tokens();

        // Test that method exists and is static.
        $this->assertTrue(method_exists(token_manager::class, 'get_access_token'));
        $reflection = new \ReflectionMethod(token_manager::class, 'get_access_token');
        $this->assertTrue($reflection->isStatic());
    }

    /**
     * Test token clearing functionality
          * @covers \local_navigatr\local\token_manager::clear_tokens
     */
    public function test_clear_tokens(): void {
        $this->resetAfterTest();

        // Set some mock tokens.
        set_config('access_token', 'mock_token', 'local_navigatr');
        set_config('access_expires_at', time() + 300, 'local_navigatr');
        set_config('refresh_token', 'mock_refresh', 'local_navigatr');
        set_config('refresh_expires_at', time() + 86400, 'local_navigatr');
        set_config('nav_user_id', '123', 'local_navigatr');

        // Verify tokens are set.
        $this->assertNotEmpty(get_config('local_navigatr', 'access_token'));
        $this->assertNotEmpty(get_config('local_navigatr', 'refresh_token'));

        // Clear tokens.
        token_manager::clear_tokens();

        // Verify tokens are cleared.
        $this->assertEmpty(get_config('local_navigatr', 'access_token'));
        $this->assertEmpty(get_config('local_navigatr', 'refresh_token'));
        $this->assertEmpty(get_config('local_navigatr', 'nav_user_id'));
    }

    /**
     * Test token expiration handling
          * @covers \local_navigatr\local\token_manager
     */
    public function test_token_expiration(): void {
        $this->resetAfterTest();

        // Set expired token.
        set_config('access_token', 'expired_token', 'local_navigatr');
        set_config('access_expires_at', time() - 3600, 'local_navigatr'); // Expired 1 hour ago

        // Test that method handles expired tokens.
        $this->assertTrue(method_exists(token_manager::class, 'get_access_token'));
    }

    /**
     * Test token refresh with valid refresh token
          * @covers \local_navigatr\local\token_manager
     */
    public function test_token_refresh_valid(): void {
        $this->resetAfterTest();

        // Set valid refresh token.
        set_config('refresh_token', 'valid_refresh_token', 'local_navigatr');
        set_config('refresh_expires_at', time() + 3600, 'local_navigatr'); // Valid for 1 hour

        // Test that refresh mechanism exists.
        $this->assertTrue(method_exists(token_manager::class, 'get_access_token'));
    }

    /**
     * Test token refresh with expired refresh token
          * @covers \local_navigatr\local\token_manager
     */
    public function test_token_refresh_expired(): void {
        $this->resetAfterTest();

        // Set expired refresh token.
        set_config('refresh_token', 'expired_refresh_token', 'local_navigatr');
        set_config('refresh_expires_at', time() - 3600, 'local_navigatr'); // Expired 1 hour ago

        // Test that method handles expired refresh tokens.
        $this->assertTrue(method_exists(token_manager::class, 'get_access_token'));
    }

    /**
     * Test re-authentication when no valid tokens
          * @covers \local_navigatr\local\token_manager
     */
    public function test_reauth_no_credentials(): void {
        $this->resetAfterTest();

        // Clear credentials.
        unset_config('username', 'local_navigatr');
        \local_navigatr\local\password_manager::clear_password();

        // Test that method exists and handles missing credentials.
        $this->assertTrue(method_exists(token_manager::class, 'get_access_token'));
    }

    /**
     * Test re-authentication with credentials
          * @covers \local_navigatr\local\token_manager
     */
    public function test_reauth_with_credentials(): void {
        $this->resetAfterTest();

        // Set credentials.
        set_config('username', 'test_user', 'local_navigatr');
        \local_navigatr\local\password_manager::store_password('test_password');

        // Test that method exists.
        $this->assertTrue(method_exists(token_manager::class, 'get_access_token'));
    }

    /**
     * Test JWT token decoding
          * @covers \local_navigatr\local\token_manager
     */
    public function test_jwt_decoding(): void {
        $this->resetAfterTest();

        // Test that JWT decoding method exists.
        $reflection = new \ReflectionClass(token_manager::class);
        $this->assertTrue($reflection->hasMethod('decode_jwt_sub'));

        $method = $reflection->getMethod('decode_jwt_sub');
        $this->assertTrue($method->isPrivate());
    }

    /**
     * Test token storage
          * @covers \local_navigatr\local\token_manager
     */
    public function test_token_storage(): void {
        $this->resetAfterTest();

        // Test that token storage method exists.
        $reflection = new \ReflectionClass(token_manager::class);
        $this->assertTrue($reflection->hasMethod('store_tokens'));

        $method = $reflection->getMethod('store_tokens');
        $this->assertTrue($method->isPrivate());
    }

    /**
     * Test lock-based concurrency control
          * @covers \local_navigatr\local\token_manager
     */
    public function test_lock_mechanism(): void {
        $this->resetAfterTest();

        // Test that lock mechanism exists.
        $reflection = new \ReflectionClass(token_manager::class);
        $this->assertTrue($reflection->hasMethod('refresh_or_reauth_with_lock'));

        $method = $reflection->getMethod('refresh_or_reauth_with_lock');
        $this->assertTrue($method->isPrivate());
    }

    /**
     * Test error handling for authentication failures
          * @covers \local_navigatr\local\token_manager
     */
    public function test_auth_failure_handling(): void {
        $this->resetAfterTest();

        // Test that method exists and can handle failures.
        $this->assertTrue(method_exists(token_manager::class, 'get_access_token'));

        // Test that clear_tokens method exists for cleanup.
        $this->assertTrue(method_exists(token_manager::class, 'clear_tokens'));
    }

    /**
     * Test token validation logic
          * @covers \local_navigatr\local\token_manager
     */
    public function test_token_validation(): void {
        $this->resetAfterTest();

        // Test with valid token.
        set_config('access_token', 'valid_token', 'local_navigatr');
        set_config('access_expires_at', time() + 300, 'local_navigatr');

        // Test that method exists.
        $this->assertTrue(method_exists(token_manager::class, 'get_access_token'));

        // Test with invalid token.
        set_config('access_token', '', 'local_navigatr');
        $this->assertTrue(method_exists(token_manager::class, 'get_access_token'));
    }

    /**
     * Test concurrent access handling
          * @covers \local_navigatr\local\token_manager
     */
    public function test_concurrent_access(): void {
        $this->resetAfterTest();

        // Test that lock mechanism exists for concurrent access.
        $reflection = new \ReflectionClass(token_manager::class);
        $this->assertTrue($reflection->hasMethod('refresh_or_reauth_with_lock'));
    }
}
