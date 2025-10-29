<?php
define('CLI_SCRIPT', true);
require_once('config.php');
require_once($CFG->dirroot . '/local/navigatr/classes/task/issue_badge_task.php');

echo "🚀 Running Badge Issuance Task Manually\n";
echo "======================================\n\n";

$courseid = 2;
$userid = 10; // Mark Chevin

echo "📋 Task Details:\n";
echo "   User ID: {$userid}\n";
echo "   Course ID: {$courseid}\n\n";

// Create and run the task directly
$task = new \local_navigatr\task\issue_badge_task();
$task->set_custom_data([
    'userid' => $userid,
    'courseid' => $courseid
]);

echo "🔄 Executing task...\n";
try {
    $task->execute();
    echo "✅ Task executed successfully!\n\n";
} catch (Exception $e) {
    echo "❌ Task failed with error: " . $e->getMessage() . "\n\n";
}

echo "🔍 Checking for audit records...\n";
$audit_records = $DB->get_records('local_navigatr_audit', [
    'userid' => $userid,
    'courseid' => $courseid
], 'timecreated DESC', '*', 0, 3);

if (empty($audit_records)) {
    echo "❌ No audit records found\n";
} else {
    echo "✅ Found " . count($audit_records) . " audit record(s):\n";
    foreach ($audit_records as $record) {
        echo "   - Status: {$record->status}, Time: " . date('Y-m-d H:i:s', $record->timecreated) . "\n";
        echo "     Badge: {$record->badge_id}, Provider: {$record->provider_id}\n";
        echo "     HTTP Code: {$record->http_code}\n";
        if (!empty($record->response_json)) {
            echo "     Response: " . substr($record->response_json, 0, 150) . "...\n";
        }
        echo "\n";
    }
}

echo "🔍 Checking for log events...\n";
$log_records = $DB->get_records('logstore_standard_log', [
    'userid' => $userid,
    'courseid' => $courseid,
    'component' => 'local_navigatr'
], 'timecreated DESC', '*', 0, 3);

if (empty($log_records)) {
    echo "❌ No log events found\n";
} else {
    echo "✅ Found " . count($log_records) . " log event(s):\n";
    foreach ($log_records as $record) {
        echo "   - Event: {$record->eventname}\n";
        echo "     Time: " . date('Y-m-d H:i:s', $record->timecreated) . "\n";
        echo "     Description: " . substr($record->description, 0, 150) . "...\n";
        echo "\n";
    }
}

echo "🎉 Manual task execution complete!\n";
