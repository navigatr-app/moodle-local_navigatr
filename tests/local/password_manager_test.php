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
 * Unit tests for Navigatr Password Manager PAT methods.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

use advanced_testcase;
use local_navigatr\local\password_manager;

/**
 * Unit tests for password_manager PAT storage methods.
 *
 * @covers \local_navigatr\local\password_manager
 */
final class password_manager_test extends advanced_testcase {
    /**
     * Test get_pat returns empty string when no token has been stored.
     *
     * @covers \local_navigatr\local\password_manager::get_pat
     */
    public function test_get_pat_returns_empty_when_not_stored(): void {
        $this->resetAfterTest();
        unset_config('personal_access_token', 'local_navigatr');

        $this->assertSame('', password_manager::get_pat());
    }

    /**
     * Test that store_pat followed by get_pat returns the original token.
     *
     * @covers \local_navigatr\local\password_manager::store_pat
     * @covers \local_navigatr\local\password_manager::get_pat
     */
    public function test_store_and_get_pat_roundtrip(): void {
        $this->resetAfterTest();

        $pat = 'nav_pat_abc123xyz456';
        password_manager::store_pat($pat);

        $this->assertSame($pat, password_manager::get_pat());
    }

    /**
     * Test that the value written to config is encrypted (not plain text).
     *
     * @covers \local_navigatr\local\password_manager::store_pat
     */
    public function test_stored_pat_is_encrypted(): void {
        $this->resetAfterTest();

        $pat = 'nav_pat_abc123xyz456';
        password_manager::store_pat($pat);

        $stored = get_config('local_navigatr', 'personal_access_token');
        $this->assertNotEmpty($stored);
        $this->assertNotSame($pat, $stored);
    }

    /**
     * Test clear_pat removes the stored token and its config key.
     *
     * @covers \local_navigatr\local\password_manager::clear_pat
     * @covers \local_navigatr\local\password_manager::get_pat
     */
    public function test_clear_pat_removes_stored_value(): void {
        $this->resetAfterTest();

        password_manager::store_pat('nav_pat_abc123');
        password_manager::clear_pat();

        $this->assertSame('', password_manager::get_pat());
        $this->assertFalse(get_config('local_navigatr', 'personal_access_token'));
    }

    /**
     * Test storing an empty string clears the config key rather than storing empty ciphertext.
     *
     * @covers \local_navigatr\local\password_manager::store_pat
     */
    public function test_store_empty_pat_clears_config(): void {
        $this->resetAfterTest();

        password_manager::store_pat('nav_pat_abc123');
        password_manager::store_pat('');

        $this->assertSame('', password_manager::get_pat());
        $this->assertFalse(get_config('local_navigatr', 'personal_access_token'));
    }

    /**
     * Test that storing the same token twice produces different ciphertexts (random IV per call).
     *
     * @covers \local_navigatr\local\password_manager::store_pat
     */
    public function test_each_store_produces_different_ciphertext(): void {
        $this->resetAfterTest();

        $pat = 'nav_pat_same_token';

        password_manager::store_pat($pat);
        $first = get_config('local_navigatr', 'personal_access_token');

        password_manager::store_pat($pat);
        $second = get_config('local_navigatr', 'personal_access_token');

        // AES-256-CBC uses a random IV so each encryption produces a unique ciphertext.
        $this->assertNotSame($first, $second);
        // Both ciphertexts must still decrypt to the original token.
        $this->assertSame($pat, password_manager::get_pat());
    }

    /**
     * Test that a token containing special characters survives the encrypt/decrypt round-trip.
     *
     * @covers \local_navigatr\local\password_manager::store_pat
     * @covers \local_navigatr\local\password_manager::get_pat
     */
    public function test_roundtrip_preserves_special_characters(): void {
        $this->resetAfterTest();

        $pat = 'nav_pat_special-chars_123!@#$%^&*()';
        password_manager::store_pat($pat);

        $this->assertSame($pat, password_manager::get_pat());
    }
}
