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
 * Test script to manually trigger badge issuance for specific user and course.
 * 
 * Usage: php test_badge_issuance.php
 * 
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This script should be run from Moodle root directory
// require_once('config.php');

// For testing from plugin directory, we'll create a standalone version
echo "🧪 Navigatr Badge Issuance Test Script\n";
echo "=====================================\n\n";

// Configuration
$courseid = 2;
$useremail = 'omid+mchevin@navigatr.org';

echo "📋 Test Configuration:\n";
echo "   Course ID: {$courseid}\n";
echo "   User Email: {$useremail}\n\n";

// Check if we're in a Moodle environment
if (!defined('MOODLE_INTERNAL')) {
    echo "⚠️  This script needs to be run from within a Moodle environment.\n";
    echo "   Please copy this file to your Moodle root directory and run:\n";
    echo "   php test_badge_issuance.php\n\n";
    
    echo "📝 Instructions:\n";
    echo "1. Copy this file to your Moodle root directory\n";
    echo "2. Make sure the Navigatr plugin is installed\n";
    echo "3. Run: php test_badge_issuance.php\n";
    echo "4. Check the logs for badge issuance events\n\n";
    
    echo "🔍 What this script will do:\n";
    echo "   - Find user by email: {$useremail}\n";
    echo "   - Check course {$courseid} for badge mapping\n";
    echo "   - Trigger badge issuance task\n";
    echo "   - Generate audit log events\n";
    echo "   - Show results and next steps\n";
    
    exit(0);
}

// If we're in Moodle, continue with the actual test
global $DB, $CFG;

echo "🔍 Looking up user by email...\n";
$user = $DB->get_record('user', ['email' => $useremail]);
if (!$user) {
    echo "❌ User not found with email: {$useremail}\n";
    echo "   Please check the email address and try again.\n";
    exit(1);
}
echo "✅ Found user: {$user->firstname} {$user->lastname} (ID: {$user->id})\n\n";

echo "🔍 Checking course {$courseid}...\n";
$course = $DB->get_record('course', ['id' => $courseid]);
if (!$course) {
    echo "❌ Course not found with ID: {$courseid}\n";
    exit(1);
}
echo "✅ Found course: {$course->fullname} (ID: {$courseid})\n\n";

echo "🔍 Checking for badge mapping...\n";
$mapping = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
if (!$mapping) {
    echo "❌ No badge mapping found for course {$courseid}\n";
    echo "   Please configure badge mapping first:\n";
    echo "   1. Go to Course administration → Navigatr Badge\n";
    echo "   2. Select a provider and badge\n";
    echo "   3. Save the mapping\n";
    exit(1);
}
echo "✅ Found badge mapping:\n";
echo "   Provider ID: {$mapping->provider_id}\n";
echo "   Badge ID: {$mapping->badge_id}\n";
echo "   Badge Name: {$mapping->badge_name}\n\n";

echo "🔍 Checking if user is enrolled in course...\n";
$enrollment = $DB->get_record('user_enrolments', [
    'userid' => $user->id,
    'enrolid' => $DB->get_field('enrol', 'id', ['courseid' => $courseid, 'enrol' => 'manual'])
]);
if (!$enrollment) {
    echo "⚠️  User is not enrolled in course {$courseid}\n";
    echo "   Enrolling user...\n";
    
    // Enroll user in course
    $enrol = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
    if ($enrol) {
        $enrolinstance = new stdClass();
        $enrolinstance->userid = $user->id;
        $enrolinstance->enrolid = $enrol->id;
        $enrolinstance->status = 0; // Active
        $enrolinstance->timestart = time();
        $enrolinstance->timeend = 0;
        $enrolinstance->modifierid = 2; // Admin
        $enrolinstance->timecreated = time();
        $enrolinstance->timemodified = time();
        
        $DB->insert_record('user_enrolments', $enrolinstance);
        echo "✅ User enrolled successfully\n\n";
    } else {
        echo "❌ Could not find manual enrollment method for course\n";
        exit(1);
    }
} else {
    echo "✅ User is enrolled in course\n\n";
}

echo "🚀 Triggering badge issuance...\n";

// Create and queue the badge issuance task
$task = new \local_navigatr\task\issue_badge_task();
$task->set_custom_data([
    'userid' => $user->id,
    'courseid' => $courseid
]);

\core\task\manager::queue_adhoc_task($task);
echo "✅ Badge issuance task queued successfully\n\n";

echo "📊 Next Steps:\n";
echo "1. Check Site Administration → Server → Scheduled tasks\n";
echo "   Look for 'Issue Navigatr badge' task\n\n";

echo "2. Check Site Administration → Reports → Logs\n";
echo "   Filter by:\n";
echo "   - Module: local_navigatr\n";
echo "   - User: {$user->firstname} {$user->lastname}\n";
echo "   - Course: {$course->fullname}\n\n";

echo "3. Look for these events:\n";
echo "   - 'Badge issuance successful' (if API call succeeds)\n";
echo "   - 'Badge issuance failed' (if API call fails)\n";
echo "   - 'Badge issuance queued for retry' (if API is unavailable)\n\n";

echo "4. Check database audit table:\n";
echo "   SELECT * FROM mdl_local_navigatr_audit WHERE userid = {$user->id} AND courseid = {$courseid};\n\n";

echo "🎉 Test setup complete! Check the logs to see the audit events.\n";
