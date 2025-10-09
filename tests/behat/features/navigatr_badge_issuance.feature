Feature: Navigatr Badge Issuance
  As a Moodle administrator
  I want to configure Navigatr badge issuance
  So that learners automatically receive badges when completing courses

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | John      | Doe      | john.doe@example.com |
      | student2 | Jane      | Smith    | jane.smith@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Test Course 1 | TC1 |
      | Test Course 2 | TC2 |

  Scenario: Configure Navigatr credentials
    Given I log in as "admin"
    And I navigate to "Site administration > Plugins > Local plugins > Navigatr"
    When I set the following form fields to these values:
      | Username | test_user |
      | Password | test_password |
    And I press "Test Connection"
    Then I should see "Connection successful!"

  Scenario: Map course to Navigatr badge
    Given I log in as "admin"
    And I navigate to "Course: Test Course 1"
    And I navigate to "Course administration > Navigatr Badge"
    When I set the following form fields to these values:
      | Provider | Test Provider |
      | Badge | Test Badge |
    And I press "Save changes"
    Then I should see "Badge mapping saved successfully"

  Scenario: Issue badge on course completion
    Given I log in as "student1"
    And I navigate to "Course: Test Course 1"
    And I complete the course
    When I log in as "admin"
    And I navigate to "Site administration > Reports > Navigatr Audit"
    Then I should see "Badge issued successfully for user student1"

  Scenario: Handle API errors gracefully
    Given I log in as "student1"
    And I navigate to "Course: Test Course 1"
    And I complete the course
    When the Navigatr API is unavailable
    Then the badge issuance should be queued for retry
    And I should see "Badge issuance queued for retry" in the logs

  Scenario: Prevent duplicate badge issuance
    Given I log in as "student1"
    And I navigate to "Course: Test Course 1"
    And I complete the course
    And I complete the course again
    Then only one badge should be issued
    And I should see "Duplicate badge issuance prevented" in the logs

  Scenario: Export user data for GDPR compliance
    Given I log in as "admin"
    And I navigate to "Site administration > Privacy and policies > Data requests"
    When I create a data export request for "student1"
    Then I should see "Navigatr badge issuance records" in the export
    And the export should contain "Test Course 1" completion data

  Scenario: Delete user data for GDPR compliance
    Given I log in as "admin"
    And I navigate to "Site administration > Privacy and policies > Data requests"
    When I create a data deletion request for "student1"
    Then the Navigatr audit records should be deleted
    And I should see "User data deleted successfully" in the logs
