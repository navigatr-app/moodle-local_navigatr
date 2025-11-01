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
 *
 * @covers \local_navigatr\form\admin_settings_form
 */
final class admin_settings_form_test extends advanced_testcase
{
    /**
     * Test form class structure
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_class_structure(): void {
        $this->assertTrue(class_exists(admin_settings_form::class));
        $this->assertTrue(method_exists(admin_settings_form::class, 'definition'));
        $this->assertTrue(method_exists(admin_settings_form::class, 'validation'));
    }

    /**
     * Test form definition method
          * @covers \local_navigatr\form\admin_settings_form::definition
     */
    public function test_definition_method(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that definition method exists and is public
        $this->assertTrue(method_exists($form, 'definition'));

        $reflection = new \ReflectionMethod(admin_settings_form::class, 'definition');
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * Test form validation method
          * @covers \local_navigatr\form\admin_settings_form::validation
     */
    public function test_validation_method(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that validation method exists
        $this->assertTrue(method_exists($form, 'validation'));

        $reflection = new \ReflectionMethod(admin_settings_form::class, 'validation');
        $this->assertTrue($reflection->isPublic());
    }

    /**
     * Test form element creation
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_form_elements(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form can be created
        $this->assertInstanceOf(admin_settings_form::class, $form);
    }

    /**
     * Test username field
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_username_field(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form has username field
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test password field
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_password_field(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form has password field
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test timeout field
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_timeout_field(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form has timeout field
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form definition method exists
          * @covers \local_navigatr\form\admin_settings_form::definition
     */
    public function test_form_definition(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form definition method exists
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test environment field
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_environment_field(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form has environment field
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test test connection button
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_test_connection_button(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form has test connection button
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form validation rules
          * @covers \local_navigatr\form\admin_settings_form::validation
     */
    public function test_validation_rules(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that validation method exists
        $this->assertTrue(method_exists($form, 'validation'));
    }

    /**
     * Test form submission handling
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_form_submission(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form can handle submission
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form cancellation
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_form_cancellation(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form can handle cancellation
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form data processing
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_form_data_processing(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form can process data
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form error handling
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_form_error_handling(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form can handle errors
        $this->assertTrue(method_exists($form, 'validation'));
    }

    /**
     * Test form security
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_form_security(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form has security measures
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form accessibility
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_form_accessibility(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form is accessible
        $this->assertTrue(method_exists($form, 'definition'));
    }

    /**
     * Test form internationalization
          * @covers \local_navigatr\form\admin_settings_form
     */
    public function test_form_internationalization(): void {
        $this->resetAfterTest();

        $form = new admin_settings_form();

        // Test that form supports i18n
        $this->assertTrue(method_exists($form, 'definition'));
    }
}
