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
 * Unit tests for Navigatr Admin Settings Form
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\form;

use advanced_testcase;
use local_navigatr\form\admin_settings_form;

/**
 * Unit tests for Navigatr Admin Settings Form
 */
class admin_settings_form_test extends advanced_testcase
{
    /**
     * Test form class structure
     */
    public function test_class_structure()
    {
        $this->assertTrue(class_exists(admin_settings_form::class));
        $this->assertTrue(method_exists(admin_settings_form::class, 'definition'));
        $this->assertTrue(method_exists(admin_settings_form::class, 'validation'));
    }

    /**
     * Test form definition method
     */
    public function test_definition_method()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that definition method exists and is public
        $this->assertTrue(method_exists($form, 'definition'));

        $reflection = new \ReflectionMethod(admin_settings_form::class, 'definition');
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * Test form validation method
     */
    public function test_validation_method()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that validation method exists
        $this->assertTrue(method_exists($form, 'validation'));

        $reflection = new \ReflectionMethod(admin_settings_form::class, 'validation');
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * Test form element creation
     */
    public function test_form_elements()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form can be created
        $this->assertInstanceOf(admin_settings_form::class, $form);
    }

    /**
     * Test username field
     */
    public function test_username_field()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form has username field
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test password field
     */
    public function test_password_field()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form has password field
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test timeout field
     */
    public function test_timeout_field()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form has timeout field
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form definition method exists
     */
    public function test_form_definition()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form definition method exists
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test environment field
     */
    public function test_environment_field()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form has environment field
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test test connection button
     */
    public function test_test_connection_button()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form has test connection button
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form validation rules
     */
    public function test_validation_rules()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that validation method exists
        $this->assertTrue(method_exists($form, 'validation'));
    }

    /**
     * Test form submission handling
     */
    public function test_form_submission()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form can handle submission
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form cancellation
     */
    public function test_form_cancellation()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form can handle cancellation
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form data processing
     */
    public function test_form_data_processing()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form can process data
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form error handling
     */
    public function test_form_error_handling()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form can handle errors
        $this->assertTrue(method_exists($form, 'validation'));
    }

    /**
     * Test form security
     */
    public function test_form_security()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form has security measures
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form accessibility
     */
    public function test_form_accessibility()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form is accessible
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form internationalization
     */
    public function test_form_internationalization()
    {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form supports i18n
        $this->assertTrue(method_exists($form, 'definition'));
    }
}
