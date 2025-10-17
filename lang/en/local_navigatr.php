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
 * Language strings for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Navigatr';
$string['menu_name'] = 'Navigatr Badge';

// Admin settings
$string['environment'] = 'Environment';
$string['environment_help'] = 'Select the Navigatr environment to use for API calls';
$string['environment_production'] = 'Production';
$string['environment_staging'] = 'Staging';
$string['username'] = 'Navigatr Username';
$string['password'] = 'Navigatr Password';
$string['timeout'] = 'HTTP Timeout (seconds)';
$string['loglevel'] = 'Log Level';
$string['loglevel_error'] = 'Error';
$string['loglevel_info'] = 'Info';
$string['loglevel_debug'] = 'Debug';
$string['advanced_settings'] = 'Advanced Settings';
$string['test_connection'] = 'Test Connection';
$string['settingssaved'] = 'Settings saved successfully';

// Connection test
$string['connection_success'] = 'Authenticated as {$a->email}. Found {$a->count} providers.';
$string['connection_error'] = 'Authentication failed: HTTP {$a->code} — {$a->message}';

// Course mapping
$string['provider'] = 'Provider';
$string['badge'] = 'Badge';
$string['select_provider'] = 'Select a provider';
$string['select_badge'] = 'Select a badge';
$string['badgedesc'] = 'Badge Description';
$string['save_mapping'] = 'Save Mapping';
$string['mapping_saved'] = 'Badge mapping saved successfully';

// Errors
$string['missing_mapping'] = 'No badge mapping found for this course';
$string['missing_user_fields'] = 'User missing required fields (email, firstname, lastname)';
$string['issue_failed'] = 'Badge issuance failed: HTTP {$a}';
$string['auth_failed'] = 'Authentication failed';
$string['timeout_invalid'] = 'Timeout must be between 1 and 300 seconds';

// Privacy
$string['privacy:metadata:local_navigatr_audit'] = 'Badge issuance audit records';
$string['privacy:metadata:local_navigatr_audit:userid'] = 'User ID';
$string['privacy:metadata:local_navigatr_audit:courseid'] = 'Course ID';
$string['privacy:metadata:local_navigatr_audit:provider_id'] = 'Provider ID';
$string['privacy:metadata:local_navigatr_audit:badge_id'] = 'Badge ID';
$string['privacy:metadata:local_navigatr_audit:status'] = 'Issuance status';
$string['privacy:metadata:local_navigatr_audit:timecreated'] = 'Creation time';
$string['privacy:metadata:navigatr'] = 'Navigatr API';
$string['privacy:metadata:navigatr:recipient_email'] = 'Recipient email address';
$string['privacy:metadata:navigatr:recipient_firstname'] = 'Recipient first name';
$string['privacy:metadata:navigatr:recipient_lastname'] = 'Recipient last name';
$string['privacy:metadata:navigatr:purpose'] = 'Issue a digital badge upon course completion';
$string['select_provider_continue'] = 'Continue to Select Badge';
$string['select_badge_continue'] = 'Save Mapping';
$string['selected_provider'] = 'Selected Provider';
$string['navigatr_settings'] = 'Navigatr Settings';
$string['current_mapping'] = 'Current Badge Mapping';
$string['change_mapping'] = 'Change Badge';
$string['view_badge'] = 'View Badge';
$string['remove_mapping'] = 'Remove Badge';
$string['remove_mapping_confirm'] = 'Are you sure you want to remove the badge mapping for this course? Removing the badge stops future issuances but preserves existing badges.';
$string['mapping_removed'] = 'Badge mapping removed successfully';
$string['provider_admin_notice'] = 'Please ensure that the configured Navigatr user is a provider admin on Navigatr to access and manage badge mappings.';
$string['provider_config_notice'] = 'Please make sure a site administrator has added your Navigatr username and password in <a href="{$a}">Site Administration → Plugins → Navigatr</a>';
$string['remove_connection'] = 'Remove Connection';
$string['remove_connection_confirm'] = 'Are you sure you want to remove the current Navigatr connection? This will clear your stored username and password. All existing badge mappings will be disabled.';
$string['connection_removed'] = 'Navigatr connection has been removed successfully.';

// Connection test messages
$string['connection_success_simple'] = 'Connection successful!';
$string['connection_failed'] = 'Connection failed';
$string['network_error_or_timeout'] = 'Network error or timeout';
$string['connection_failed_details'] = 'Connection failed: {$a}';

// Badge preview
$string['badge_preview'] = 'Badge Preview';

// Help text for form fields
$string['username_help'] = 'Enter your Navigatr username. This should be a provider admin account that has access to manage badges.';
$string['password_help'] = 'Enter your Navigatr password. This will be encrypted before storage.';
$string['timeout_help'] = 'HTTP request timeout in seconds. Increase this if you experience timeout errors when communicating with the Navigatr API.';
$string['loglevel_help'] = 'Set the logging level for debugging. Error: Only log errors. Info: Log important events. Debug: Log detailed information for troubleshooting.';
$string['environment_help'] = 'Select the Navigatr environment to use for API calls. Production: Live environment for real badge issuance. Staging: Test environment for development and testing.';
$string['provider_help'] = 'Select the Navigatr provider that owns the badges you want to issue. You must be a provider admin to access and manage badges.';
$string['badge_help'] = 'Select the badge to issue when learners complete this course. The badge will be automatically issued to students upon course completion.';

// Enhanced error messages
$string['error_auth_failed'] = 'Authentication failed. Please check your username and password.';
$string['error_network'] = 'Network error: Unable to reach Navigatr API. Check your server\'s internet connection.';
$string['error_timeout'] = 'Request timed out after {$a} seconds. Try increasing the timeout setting.';
$string['error_invalid_credentials'] = 'Invalid credentials (HTTP 401). Verify your username and password.';
$string['error_server'] = 'Navigatr API server error (HTTP {$a}). Please try again later.';
$string['error_not_found'] = 'Resource not found (HTTP 404). The badge or provider may have been deleted.';
$string['error_no_providers'] = 'No providers found. Ensure your account has provider admin access.';

// Password encryption errors
$string['encryption_failed'] = 'Password encryption failed. Please try again.';
$string['decryption_failed'] = 'Password decryption failed. Please reconfigure your credentials.';

// Help documentation links
$string['help_center_url'] = 'https://help.navigatr.app/';
$string['help_center_link'] = 'Visit Navigatr Help Centre';
$string['help_setup_guide'] = 'Need help setting up? Visit our {$a}.';
$string['help_badge_config'] = 'Learn more about badge configuration';

// Security and password warnings
$string['password_unmask_warning'] = 'Password will be visible when editing. It is encrypted before storage.';
$string['security_note'] = 'All credentials are encrypted using AES-256-CBC encryption with site-specific keys.';

// Menu descriptions
$string['menu_description'] = 'Configure automatic badge issuance for course completion';
$string['settings_description'] = 'Map this course to a Navigatr badge that will be automatically issued when learners complete the course.';

// Capability strings
$string['navigatr:managecredentials'] = 'Manage Navigatr credentials';
$string['navigatr:managecredentials_desc'] = 'Allows users to configure Navigatr API credentials and settings';
$string['navigatr:configurecourse'] = 'Configure Navigatr badge mapping';
$string['navigatr:configurecourse_desc'] = 'Allows users to map courses to Navigatr badges for automatic issuance';

// Cache definition strings
$string['cachedef_providers'] = 'Navigatr providers cache';
$string['cachedef_providers_desc'] = 'Caches provider information from Navigatr API to improve performance';
$string['cachedef_badges'] = 'Navigatr badges cache';
$string['cachedef_badges_desc'] = 'Caches badge information from Navigatr API to improve performance';
$string['cachedef_user_detail'] = 'Navigatr user detail cache';
$string['cachedef_user_detail_desc'] = 'Caches user detail information from Navigatr API to improve performance';
$string['cachedef_locks'] = 'Navigatr locks cache';
$string['cachedef_locks_desc'] = 'Caches lock information for Navigatr operations to prevent conflicts';
