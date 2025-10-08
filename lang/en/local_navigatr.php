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

// Admin settings
$string['environment'] = 'Environment';
$string['environment_help'] = 'Select the Navigatr environment to use for API calls';
$string['environment_prod'] = 'Production';
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
$string['change_mapping'] = 'Change Mapping';
