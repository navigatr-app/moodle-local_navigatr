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
 * Language strings for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['advanced_settings'] = 'Advanced Settings';
$string['api_unavailable'] = 'API unavailable';
$string['auth_failed'] = 'Authentication failed';
$string['badge'] = 'Badge';
$string['badge_help'] = 'Select the badge to issue when learners complete this course. The badge will be automatically issued to students upon course completion.';
$string['badge_issuance_queued'] = 'Badge issuance queued for retry';
$string['badge_issued_successfully'] = 'Badge issued successfully';
$string['badge_mapping_saved'] = 'Badge mapping saved successfully';
$string['badge_preview'] = 'Badge Preview';
$string['badgedesc'] = 'Badge Description';
$string['cachedef_badges'] = 'Navigatr badges cache';
$string['cachedef_badges_desc'] = 'Caches badge information from Navigatr API to improve performance';
$string['cachedef_locks'] = 'Navigatr locks cache';
$string['cachedef_locks_desc'] = 'Caches lock information for Navigatr operations to prevent conflicts';
$string['cachedef_providers'] = 'Navigatr providers cache';
$string['cachedef_providers_desc'] = 'Caches provider information from Navigatr API to improve performance';
$string['cachedef_tokens'] = 'Navigatr tokens cache';
$string['cachedef_tokens_desc'] = 'Caches authentication tokens from Navigatr API to improve performance and reduce authentication requests';
$string['cachedef_user_detail'] = 'Navigatr user detail cache';
$string['cachedef_user_detail_desc'] = 'Caches user detail information from Navigatr API to improve performance';
$string['change_mapping'] = 'Change Badge';
$string['connection_failed'] = 'Connection failed';
$string['connection_failed_generic'] = 'Connection failed';
$string['connection_removed'] = 'Navigatr connection has been removed successfully.';
$string['connection_success_simple'] = 'Connection successful!';
$string['connection_successful'] = 'Connection successful!';
$string['course_not_found'] = 'Course not found';
$string['create_deletion_request'] = 'Create new data deletion request';
$string['create_export_request'] = 'Create new data export request';
$string['current_mapping'] = 'Current Badge Mapping';
$string['decryption_failed'] = 'Password decryption failed. Please reconfigure your credentials.';
$string['duplicate_badge_prevented'] = 'Duplicate badge issuance prevented';
$string['encryption_failed'] = 'Password encryption failed. Please try again.';
$string['environment'] = 'Environment';
$string['environment_help'] = 'Select the Navigatr environment to use for API calls. Production: Live environment for real badge issuance. Staging: Test environment for development and testing.';
$string['environment_production'] = 'Production';
$string['environment_staging'] = 'Staging';
$string['error_auth_failed'] = 'Authentication failed. Please check your username and password.';
$string['error_invalid_credentials'] = 'Invalid credentials (HTTP 401). Verify your username and password.';
$string['error_network'] = 'Network error: Unable to reach Navigatr API. Check your server\'s internet connection.';
$string['error_no_providers'] = 'No providers found. Ensure your account has provider admin access.';
$string['error_not_found'] = 'Resource not found (HTTP 404). The badge or provider may have been deleted.';
$string['event_badge_issuance_failed'] = 'Badge issuance failed';
$string['event_badge_issuance_retry'] = 'Badge issuance queued for retry';
$string['event_badge_issuance_success'] = 'Badge issuance successful';
$string['eventapiconnectiontested'] = 'API connection tested';
$string['eventapirequestfailed'] = 'API request failed';
$string['eventbadgeissuancequeued'] = 'Badge issuance queued';
$string['eventcoursemappingrestored'] = 'Course mapping restored';
$string['eventcoursemappingskipped'] = 'Course mapping skipped';
$string['eventtokenrefreshfailed'] = 'Token refresh failed';
$string['help_badge_config'] = 'Learn more about badge configuration';
$string['help_center_link'] = 'Visit Navigatr Help Centre';
$string['help_center_url'] = 'https://help.navigatr.app/';
$string['help_setup_guide'] = 'Need help setting up? Visit our {$a}.';
$string['invalid_method'] = 'Invalid method: {$a}';
$string['issue_failed'] = 'Badge issuance failed with HTTP code {$a}. The task will be retried automatically.';
$string['mapping_removed'] = 'Badge mapping removed successfully';
$string['mapping_saved'] = 'Badge mapping saved successfully';
$string['menu_description'] = 'Configure automatic badge issuance for course completion';
$string['menu_name'] = 'Navigatr Badge';
$string['missing_mapping'] = 'No badge mapping found for this course';
$string['missing_user_field'] = 'Missing user field: {$a}';
$string['missing_user_fields'] = 'User missing required fields (email, firstname, lastname)';
$string['navigatr:configurecourse'] = 'Configure Navigatr badge mapping';
$string['navigatr:configurecourse_desc'] = 'Allows users to map courses to Navigatr badges for automatic issuance';
$string['navigatr:managecredentials'] = 'Manage Navigatr credentials';
$string['navigatr:managecredentials_desc'] = 'Allows users to configure Navigatr API credentials and settings';
$string['navigatr_badge_records'] = 'Navigatr badge issuance records';
$string['navigatr_settings'] = 'Navigatr Settings';
$string['network_error_or_timeout'] = 'Network error or timeout';
$string['no_mapping_found'] = 'No mapping found for course';
$string['password'] = 'Navigatr Password';
$string['password_help'] = 'Enter your Navigatr password. This will be encrypted before storage.';
$string['password_unmask_warning'] = 'Password will be visible when editing. It is encrypted before storage.';
$string['pluginname'] = 'Navigatr';
$string['privacy:metadata:local_navigatr_audit'] = 'Badge issuance audit records';
$string['privacy:metadata:local_navigatr_audit:badge_id'] = 'Badge ID';
$string['privacy:metadata:local_navigatr_audit:courseid'] = 'Course ID';
$string['privacy:metadata:local_navigatr_audit:provider_id'] = 'Provider ID';
$string['privacy:metadata:local_navigatr_audit:status'] = 'Issuance status';
$string['privacy:metadata:local_navigatr_audit:timecreated'] = 'Creation time';
$string['privacy:metadata:local_navigatr_audit:userid'] = 'User ID';
$string['privacy:metadata:local_navigatr_map'] = 'Course badge mapping configuration';
$string['privacy:metadata:local_navigatr_map:badge_id'] = 'Badge ID';
$string['privacy:metadata:local_navigatr_map:courseid'] = 'Course ID';
$string['privacy:metadata:local_navigatr_map:provider_id'] = 'Provider ID';
$string['privacy:metadata:local_navigatr_map:timemodified'] = 'Last modified time';
$string['privacy:metadata:navigatr'] = 'Navigatr API';
$string['privacy:metadata:navigatr:purpose'] = 'Issue a digital badge upon course completion';
$string['privacy:metadata:navigatr:recipient_email'] = 'Recipient email address';
$string['privacy:metadata:navigatr:recipient_firstname'] = 'Recipient first name';
$string['privacy:metadata:navigatr:recipient_lastname'] = 'Recipient last name';
$string['provider'] = 'Provider';
$string['provider_admin_notice'] = 'Please ensure that the configured Navigatr user is a provider admin on Navigatr to access and manage badge mappings.';
$string['provider_config_notice'] = 'Please make sure a site administrator has added your Navigatr username and password in <a href="{$a}">Site Administration → Plugins → Navigatr</a>';
$string['provider_help'] = 'Select the Navigatr provider that owns the badges you want to issue. You must be a provider admin to access and manage badges.';
$string['remove_connection'] = 'Remove Connection';
$string['remove_connection_confirm'] = 'Are you sure you want to remove the current Navigatr connection? This will clear your stored username and password. All existing badge mappings will be disabled.';
$string['remove_mapping'] = 'Remove Badge';
$string['remove_mapping_confirm'] = 'Are you sure you want to remove the badge mapping for this course? Removing the badge stops future issuances but preserves existing badges.';
$string['save_changes'] = 'Save changes';
$string['save_mapping'] = 'Save Mapping';
$string['security_note'] = 'All credentials are encrypted using AES-256-CBC encryption with site-specific keys.';
$string['select_badge'] = 'Select a badge';
$string['select_badge_continue'] = 'Save Mapping';
$string['select_provider'] = 'Select a provider';
$string['select_provider_continue'] = 'Continue to Select Badge';
$string['selected_provider'] = 'Selected Provider';
$string['settings_description'] = 'Map this course to a Navigatr badge that will be automatically issued when learners complete the course.';
$string['settingssaved'] = 'Settings saved successfully';
$string['test_connection'] = 'Test Connection';
$string['timeout'] = 'HTTP Timeout (seconds)';
$string['timeout_help'] = 'HTTP request timeout in seconds. Increase this if you experience timeout errors when communicating with the Navigatr API.';
$string['timeout_invalid'] = 'Timeout must be between 1 and 300 seconds';
$string['unknown_course'] = 'Unknown Course';
$string['unknown_provider'] = 'Unknown Provider';
$string['user_agent'] = 'Moodle-Navigatr-Plugin/1.0';
$string['user_data_deleted'] = 'User data deleted successfully';
$string['user_not_found'] = 'User not found';
$string['username'] = 'Navigatr Username';
$string['username_help'] = 'Enter your Navigatr username. This should be a provider admin account that has access to manage badges.';
$string['view_badge'] = 'View Badge';
