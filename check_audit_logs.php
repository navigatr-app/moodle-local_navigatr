<?php
define('CLI_SCRIPT', true);
require_once('config.php');

echo "🔍 Checking Audit Logs\n";
echo "=====================\n\n";

$courseid = 2;
$userid = 10; // Mark Chevin

echo "📊 Checking local_navigatr_audit table...\n";
$audit_records = $DB->get_records('local_navigatr_audit', [
    'userid' => $userid,
    'courseid' => $courseid
], 'timecreated DESC', '*', 0, 5);

if (empty($audit_records)) {
    echo "❌ No audit records found in local_navigatr_audit table\n";
} else {
    echo "✅ Found " . count($audit_records) . " audit record(s):\n";
    foreach ($audit_records as $record) {
        echo "   - ID: {$record->id}, Status: {$record->status}, Time: " . date('Y-m-d H:i:s', $record->timecreated) . "\n";
        echo "     Badge: {$record->badge_id}, Provider: {$record->provider_id}\n";
        echo "     HTTP Code: {$record->http_code}\n";
        if (!empty($record->response_json)) {
            echo "     Response: " . substr($record->response_json, 0, 100) . "...\n";
        }
        echo "\n";
    }
}

echo "\n📊 Checking Moodle logstore_standard_log table...\n";
$log_records = $DB->get_records('logstore_standard_log', [
    'userid' => $userid,
    'courseid' => $courseid,
    'component' => 'local_navigatr'
], 'timecreated DESC', '*', 0, 5);

if (empty($log_records)) {
    echo "❌ No log records found in logstore_standard_log table\n";
} else {
    echo "✅ Found " . count($log_records) . " log record(s):\n";
    foreach ($log_records as $record) {
        echo "   - ID: {$record->id}, Event: {$record->eventname}\n";
        echo "     Time: " . date('Y-m-d H:i:s', $record->timecreated) . "\n";
        echo "     Description: " . substr($record->description, 0, 100) . "...\n";
        echo "\n";
    }
}

echo "\n📊 Checking if task is scheduled...\n";
$tasks = $DB->get_records('task_adhoc', [
    'classname' => 'local_navigatr\\task\\issue_badge_task'
], 'timecreated DESC', '*', 0, 3);

if (empty($tasks)) {
    echo "❌ No badge issuance tasks found in queue\n";
} else {
    echo "✅ Found " . count($tasks) . " task(s) in queue:\n";
    foreach ($tasks as $task) {
        echo "   - ID: {$task->id}, Status: " . ($task->timestarted ? 'Running' : 'Pending') . "\n";
        echo "     Created: " . date('Y-m-d H:i:s', $task->timecreated) . "\n";
        echo "     Custom Data: " . substr($task->customdata, 0, 100) . "...\n";
        echo "\n";
    }
}

echo "🎉 Audit log check complete!\n";
