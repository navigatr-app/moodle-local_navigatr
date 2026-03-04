@local @local_navigatr
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
    And I navigate to Navigatr admin settings
    When I configure Navigatr credentials:
      | username | test_user |
      | password | test_password |
    And I test the Navigatr connection
    Then I should see "Connection successful"

  Scenario: Map course to Navigatr badge
    Given I log in as "admin"
    And I navigate to "Course: Test Course 1"
    And I navigate to "Course administration > Navigatr Badge"
    When I map course to Navigatr badge:
      | provider | Test Provider |
      | badge | Test Badge |
    And I press "Save changes"
    Then I should see "Badge mapping saved successfully"

  Scenario: Issue badge on course completion
    Given I log in as "student1"
    And I navigate to "Course: Test Course 1"
    When I complete the course
    And I log in as "admin"
    And I navigate to "Site administration > Reports > Navigatr Audit"
    Then I should see badge issuance in audit log

  Scenario: Handle API errors gracefully
    Given I log in as "student1"
    And I navigate to "Course: Test Course 1"
    And the Navigatr API is unavailable
    When I complete the course
    Then the badge issuance should be queued for retry

  Scenario: Prevent duplicate badge issuance
    Given I log in as "student1"
    And I navigate to "Course: Test Course 1"
    When I complete the course
    And I complete the course
    Then only one badge should be issued

  Scenario: Export user data for GDPR compliance
    Given I log in as "admin"
    When I create a data export request for "student1"
    Then I should see "Navigatr badge issuance records" in the export

  Scenario: Delete user data for GDPR compliance
    Given I log in as "admin"
    When I create a data deletion request for "student1"
    Then the Navigatr audit records should be deleted
