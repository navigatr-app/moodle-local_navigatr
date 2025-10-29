<?php
// Copy this file to your Moodle root directory and run: php trigger_badge_test_fixed.php

define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->dirroot . '/local/navigatr/classes/task/issue_badge_task.php');

echo "🧪 Navigatr Badge Issuance Test\n";
echo "==============================\n\n";

$courseid = 2;
$useremail = 'omid+mchevin@navigatr.org';

// Find user
$user = $DB->get_record('user', ['email' => $useremail]);
if (!$user) {
    echo "❌ User not found: {$useremail}\n";
    exit(1);
}
echo "✅ User: {$user->firstname} {$user->lastname} (ID: {$user->id})\n";

// Check course
$course = $DB->get_record('course', ['id' => $courseid]);
if (!$course) {
    echo "❌ Course not found: {$courseid}\n";
    exit(1);
}
echo "✅ Course: {$course->fullname} (ID: {$courseid})\n";

// Check mapping
$mapping = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
if (!$mapping) {
    echo "❌ No badge mapping found for course {$courseid}\n";
    echo "   Configure at: Course administration → Navigatr Badge\n";
    exit(1);
}
echo "✅ Badge mapping: {$mapping->badge_name} (Provider: {$mapping->provider_id})\n";

// Enroll user if not enrolled
$enrol = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
if ($enrol) {
    $enrollment = $DB->get_record('user_enrolments', [
        'userid' => $user->id,
        'enrolid' => $enrol->id
    ]);
    
    if (!$enrollment) {
        echo "📝 Enrolling user in course...\n";
        $enrolinstance = new stdClass();
        $enrolinstance->userid = $user->id;
        $enrolinstance->enrolid = $enrol->id;
        $enrolinstance->status = 0;
        $enrolinstance->timestart = time();
        $enrolinstance->timeend = 0;
        $enrolinstance->modifierid = 2;
        $enrolinstance->timecreated = time();
        $enrolinstance->timemodified = time();
        $DB->insert_record('user_enrolments', $enrolinstance);
        echo "✅ User enrolled\n";
    } else {
        echo "✅ User already enrolled\n";
    }
}

// Queue badge issuance task
echo "🚀 Queuing badge issuance task...\n";
$task = new \local_navigatr\task\issue_badge_task();
$task->set_custom_data([
    'userid' => $user->id,
    'courseid' => $courseid
]);
\core\task\manager::queue_adhoc_task($task);

echo "✅ Task queued successfully!\n\n";

echo "📊 Check these locations for audit events:\n";
echo "1. Site Administration → Reports → Logs\n";
echo "   Filter by Module: local_navigatr\n";
echo "   Filter by User: {$user->firstname} {$user->lastname}\n\n";

echo "2. Course {$course->fullname} → Reports → Logs\n";
echo "   Look for 'Badge issuance' events\n\n";

echo "3. Database query:\n";
echo "   SELECT * FROM mdl_local_navigatr_audit WHERE userid = {$user->id} AND courseid = {$courseid};\n\n";

echo "🎉 Test complete! Check the logs to see the new audit events.\n";
