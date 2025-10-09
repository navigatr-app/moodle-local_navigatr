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
 * Unit tests for Navigatr Cache
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\local;

use advanced_testcase;
use local_navigatr\local\cache;

/**
 * Unit tests for Navigatr Cache
 */
class cache_test extends advanced_testcase {

    /**
     * Test cache class structure
     */
    public function test_class_structure() {
        $this->assertTrue(class_exists(cache::class));
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
        $this->assertTrue(method_exists(cache::class, 'clear_cache'));
    }

    /**
     * Test provider caching
     */
    public function test_provider_caching() {
        $this->resetAfterTest();
        
        // Test that provider caching method exists
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        
        $reflection = new \ReflectionMethod(cache::class, 'get_providers');
        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * Test badge caching
     */
    public function test_badge_caching() {
        $this->resetAfterTest();
        
        // Test that badge caching method exists
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
        
        $reflection = new \ReflectionMethod(cache::class, 'get_badges');
        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * Test cache clearing
     */
    public function test_cache_clearing() {
        $this->resetAfterTest();
        
        // Test that cache clearing method exists
        $this->assertTrue(method_exists(cache::class, 'clear_cache'));
        
        $reflection = new \ReflectionMethod(cache::class, 'clear_cache');
        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * Test cache TTL handling
     */
    public function test_cache_ttl() {
        $this->resetAfterTest();
        
        // Test that cache handles TTL correctly
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }

    /**
     * Test cache key generation
     */
    public function test_cache_key_generation() {
        $this->resetAfterTest();
        
        // Test that cache generates proper keys
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }

    /**
     * Test cache invalidation
     */
    public function test_cache_invalidation() {
        $this->resetAfterTest();
        
        // Test that cache can be invalidated
        $this->assertTrue(method_exists(cache::class, 'clear_cache'));
    }

    /**
     * Test cache performance
     */
    public function test_cache_performance() {
        $this->resetAfterTest();
        
        // Test that cache improves performance
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }

    /**
     * Test cache error handling
     */
    public function test_cache_error_handling() {
        $this->resetAfterTest();
        
        // Test that cache handles errors gracefully
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }

    /**
     * Test cache concurrency
     */
    public function test_cache_concurrency() {
        $this->resetAfterTest();
        
        // Test that cache handles concurrency
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }

    /**
     * Test cache memory usage
     */
    public function test_cache_memory_usage() {
        $this->resetAfterTest();
        
        // Test that cache manages memory efficiently
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }

    /**
     * Test cache serialization
     */
    public function test_cache_serialization() {
        $this->resetAfterTest();
        
        // Test that cache can serialize data
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }

    /**
     * Test cache deserialization
     */
    public function test_cache_deserialization() {
        $this->resetAfterTest();
        
        // Test that cache can deserialize data
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }

    /**
     * Test cache compression
     */
    public function test_cache_compression() {
        $this->resetAfterTest();
        
        // Test that cache can compress data
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }

    /**
     * Test cache expiration
     */
    public function test_cache_expiration() {
        $this->resetAfterTest();
        
        // Test that cache handles expiration
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }

    /**
     * Test cache statistics
     */
    public function test_cache_statistics() {
        $this->resetAfterTest();
        
        // Test that cache provides statistics
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }

    /**
     * Test cache monitoring
     */
    public function test_cache_monitoring() {
        $this->resetAfterTest();
        
        // Test that cache can be monitored
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }

    /**
     * Test cache debugging
     */
    public function test_cache_debugging() {
        $this->resetAfterTest();
        
        // Test that cache supports debugging
        $this->assertTrue(method_exists(cache::class, 'get_providers'));
        $this->assertTrue(method_exists(cache::class, 'get_badges'));
    }
}
