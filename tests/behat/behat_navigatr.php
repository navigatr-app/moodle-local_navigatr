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
 * Navigatr plugin Behat context
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;

/**
 * Navigatr plugin Behat context
 */
class behat_navigatr extends \behat_base implements Context {

    /**
     * Navigate to Navigatr admin settings
     *
     * @Given I navigate to Navigatr admin settings
     */
    public function i_navigate_to_navigatr_admin_settings() {
        $this->execute('behat_navigation::i_navigate_to_node_in', [
            'Site administration',
            'Plugins',
            'Local plugins',
            'Navigatr'
        ]);
    }

    /**
     * Configure Navigatr credentials
     *
     * @Given I configure Navigatr credentials
     * @param TableNode $table
     */
    public function i_configure_navigatr_credentials(TableNode $table) {
        $data = $table->getRowsHash();
        
        // Set username
        if (isset($data['username'])) {
            $this->execute('behat_forms::i_set_the_field_to', [
                'Username',
                $data['username']
            ]);
        }
        
        // Set password
        if (isset($data['password'])) {
            $this->execute('behat_forms::i_set_the_field_to', [
                'Password',
                $data['password']
            ]);
        }
    }

    /**
     * Test Navigatr connection
     *
     * @When I test the Navigatr connection
     */
    public function i_test_the_navigatr_connection() {
        $this->execute('behat_general::i_click_on', [
            'Test Connection',
            'button'
        ]);
    }

    /**
     * Map course to Navigatr badge
     *
     * @Given I map course to Navigatr badge
     * @param TableNode $table
     */
    public function i_map_course_to_navigatr_badge(TableNode $table) {
        $data = $table->getRowsHash();
        
        // Set provider
        if (isset($data['provider'])) {
            $this->execute('behat_forms::i_set_the_field_to', [
                'Provider',
                $data['provider']
            ]);
        }
        
        // Set badge
        if (isset($data['badge'])) {
            $this->execute('behat_forms::i_set_the_field_to', [
                'Badge',
                $data['badge']
            ]);
        }
    }

    /**
     * Complete a course
     *
     * @When I complete the course
     */
    public function i_complete_the_course() {
        // Navigate to course completion
        $this->execute('behat_navigation::i_navigate_to_node_in', [
            'Course administration',
            'Completion'
        ]);
        
        // Mark course as complete
        $this->execute('behat_general::i_click_on', [
            'Mark as complete',
            'button'
        ]);
    }

    /**
     * Check badge issuance in audit log
     *
     * @Then I should see badge issuance in audit log
     */
    public function i_should_see_badge_issuance_in_audit_log() {
        $this->execute('behat_general::i_should_see', [
            'Badge issued successfully'
        ]);
    }

    /**
     * Simulate API unavailability
     *
     * @Given the Navigatr API is unavailable
     */
    public function the_navigatr_api_is_unavailable() {
        // Set a configuration to simulate API unavailability
        set_config('api_unavailable', 1, 'local_navigatr');
    }

    /**
     * Check queued badge issuance
     *
     * @Then the badge issuance should be queued for retry
     */
    public function the_badge_issuance_should_be_queued_for_retry() {
        $this->execute('behat_general::i_should_see', [
            'Badge issuance queued for retry'
        ]);
    }

    /**
     * Check duplicate prevention
     *
     * @Then only one badge should be issued
     */
    public function only_one_badge_should_be_issued() {
        $this->execute('behat_general::i_should_see', [
            'Duplicate badge issuance prevented'
        ]);
    }

    /**
     * Export user data for GDPR
     *
     * @When I create a data export request for :username
     * @param string $username
     */
    public function i_create_a_data_export_request_for($username) {
        // Navigate to privacy settings
        $this->execute('behat_navigation::i_navigate_to_node_in', [
            'Site administration',
            'Privacy and policies',
            'Data requests'
        ]);
        
        // Create export request
        $this->execute('behat_general::i_click_on', [
            'Create new data export request',
            'button'
        ]);
        
        // Select user
        $this->execute('behat_forms::i_set_the_field_to', [
            'User',
            $username
        ]);
        
        // Submit request
        $this->execute('behat_general::i_click_on', [
            'Submit',
            'button'
        ]);
    }

    /**
     * Check data export contains Navigatr data
     *
     * @Then I should see :text in the export
     * @param string $text
     */
    public function i_should_see_in_the_export($text) {
        $this->execute('behat_general::i_should_see', [$text]);
    }

    /**
     * Delete user data for GDPR
     *
     * @When I create a data deletion request for :username
     * @param string $username
     */
    public function i_create_a_data_deletion_request_for($username) {
        // Navigate to privacy settings
        $this->execute('behat_navigation::i_navigate_to_node_in', [
            'Site administration',
            'Privacy and policies',
            'Data requests'
        ]);
        
        // Create deletion request
        $this->execute('behat_general::i_click_on', [
            'Create new data deletion request',
            'button'
        ]);
        
        // Select user
        $this->execute('behat_forms::i_set_the_field_to', [
            'User',
            $username
        ]);
        
        // Submit request
        $this->execute('behat_general::i_click_on', [
            'Submit',
            'button'
        ]);
    }

    /**
     * Check data deletion
     *
     * @Then the Navigatr audit records should be deleted
     */
    public function the_navigatr_audit_records_should_be_deleted() {
        $this->execute('behat_general::i_should_see', [
            'User data deleted successfully'
        ]);
    }

}
