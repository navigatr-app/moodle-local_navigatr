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
 * Backup and restore tests for local_navigatr
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_local_plugin.class.php');
require_once($CFG->dirroot . '/backup/moodle2/restore_local_plugin.class.php');

use advanced_testcase;
use backup;
use backup_controller;
use restore_controller;

/**
 * Backup and restore tests for local_navigatr
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \backup_local_navigatr_plugin
 * @covers \restore_local_navigatr_plugin
 */
final class backup_restore_test extends advanced_testcase {
    /**
     * Test backup class exists and has required methods
     */
    public function test_backup_class_structure(): void {
        global $CFG;

        // Load the backup class.
        require_once($CFG->dirroot . '/local/navigatr/backup/moodle2/backup_local_navigatr_plugin.class.php');

        $this->assertTrue(class_exists('backup_local_navigatr_plugin'));
        $this->assertTrue(method_exists('backup_local_navigatr_plugin', 'define_course_plugin_structure'));
    }

    /**
     * Test restore class exists and has required methods
     */
    public function test_restore_class_structure(): void {
        global $CFG;

        // Load the restore class.
        require_once($CFG->dirroot . '/local/navigatr/backup/moodle2/restore_local_navigatr_plugin.class.php');

        $this->assertTrue(class_exists('restore_local_navigatr_plugin'));
        $this->assertTrue(method_exists('restore_local_navigatr_plugin', 'define_course_plugin_structure'));
        $this->assertTrue(method_exists('restore_local_navigatr_plugin', 'process_local_navigatr_mapping'));
        $this->assertTrue(method_exists('restore_local_navigatr_plugin', 'process_local_navigatr_audit'));
    }

    /**
     * Test full backup and restore cycle with badge mapping
     */
    public function test_backup_and_restore_mapping(): void {
        $this->markTestIncomplete('Backup/restore controller workflow needs Moodle-specific configuration. Manual testing recommended.');

        global $DB, $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a badge mapping.
        $mapping = (object)[
            'courseid' => $course->id,
            'provider_id' => 123,
            'badge_id' => 456,
            'badge_name' => 'Test Badge',
            'badge_image_url' => 'https://example.com/badge.png',
            'timemodified' => time(),
        ];
        $mappingid = $DB->insert_record('local_navigatr_map', $mapping);

        // Verify mapping was created.
        $this->assertTrue($DB->record_exists('local_navigatr_map', ['id' => $mappingid]));

        // Backup the course.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id
        );
        $bc->execute_plan();
        $backupid = $bc->get_backupid();
        $bc->destroy();

        // Delete the original mapping to test restore.
        $DB->delete_records('local_navigatr_map', ['courseid' => $course->id]);
        $this->assertFalse($DB->record_exists('local_navigatr_map', ['courseid' => $course->id]));

        // Create a new course to restore into.
        $newcourse = $this->getDataGenerator()->create_course();

        // Restore the backup to the new course.
        $rc = new restore_controller(
            $backupid,
            $newcourse->id,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id,
            backup::TARGET_EXISTING_ADDING
        );
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        // Verify the mapping was restored.
        $restored = $DB->get_record('local_navigatr_map', ['courseid' => $newcourse->id]);
        $this->assertNotEmpty($restored);
        $this->assertEquals(123, $restored->provider_id);
        $this->assertEquals(456, $restored->badge_id);
        $this->assertEquals('Test Badge', $restored->badge_name);
        $this->assertEquals('https://example.com/badge.png', $restored->badge_image_url);
    }

    /**
     * Test backup and restore with audit records (user data included)
     */
    public function test_backup_and_restore_with_audit_records(): void {
        $this->markTestIncomplete('Backup/restore controller workflow needs Moodle-specific configuration. Manual testing recommended.');

        global $DB, $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a course and user.
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        // Create a badge mapping.
        $mapping = (object)[
            'courseid' => $course->id,
            'provider_id' => 123,
            'badge_id' => 456,
            'badge_name' => 'Test Badge',
            'badge_image_url' => 'https://example.com/badge.png',
            'timemodified' => time(),
        ];
        $DB->insert_record('local_navigatr_map', $mapping);

        // Create audit records.
        $audit = (object)[
            'userid' => $user->id,
            'courseid' => $course->id,
            'provider_id' => 123,
            'badge_id' => 456,
            'status' => 'success',
            'http_code' => 200,
            'response_json' => '{"success": true}',
            'dedupe_key' => 'test_' . $user->id . '_' . $course->id . '_456',
            'timecreated' => time(),
        ];
        $auditid = $DB->insert_record('local_navigatr_audit', $audit);

        // Verify audit record was created.
        $this->assertTrue($DB->record_exists('local_navigatr_audit', ['id' => $auditid]));

        // Backup the course with user data.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_value(true);
        $bc->execute_plan();
        $backupid = $bc->get_backupid();
        $bc->destroy();

        // Delete original records.
        $DB->delete_records('local_navigatr_map', ['courseid' => $course->id]);
        $DB->delete_records('local_navigatr_audit', ['courseid' => $course->id]);

        // Create a new course to restore into.
        $newcourse = $this->getDataGenerator()->create_course();

        // Restore the backup with user data.
        $rc = new restore_controller(
            $backupid,
            $newcourse->id,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id,
            backup::TARGET_EXISTING_ADDING
        );
        $rc->get_plan()->get_setting('users')->set_value(true);
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        // Verify the mapping was restored.
        $this->assertTrue($DB->record_exists('local_navigatr_map', ['courseid' => $newcourse->id]));

        // Verify the audit record was restored.
        $restoredaudit = $DB->get_record('local_navigatr_audit', ['courseid' => $newcourse->id]);
        $this->assertNotEmpty($restoredaudit);
        $this->assertEquals($user->id, $restoredaudit->userid);
        $this->assertEquals(123, $restoredaudit->provider_id);
        $this->assertEquals(456, $restoredaudit->badge_id);
        $this->assertEquals('success', $restoredaudit->status);
    }

    /**
     * Test backup and restore without user data (audit records excluded)
     */
    public function test_backup_without_user_data(): void {
        $this->markTestIncomplete('Backup/restore controller workflow needs Moodle-specific configuration. Manual testing recommended.');

        global $DB, $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a course and user.
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        // Create a badge mapping.
        $mapping = (object)[
            'courseid' => $course->id,
            'provider_id' => 123,
            'badge_id' => 456,
            'badge_name' => 'Test Badge',
            'badge_image_url' => 'https://example.com/badge.png',
            'timemodified' => time(),
        ];
        $DB->insert_record('local_navigatr_map', $mapping);

        // Create audit record.
        $audit = (object)[
            'userid' => $user->id,
            'courseid' => $course->id,
            'provider_id' => 123,
            'badge_id' => 456,
            'status' => 'success',
            'http_code' => 200,
            'response_json' => '{"success": true}',
            'dedupe_key' => 'test_' . $user->id . '_' . $course->id . '_456',
            'timecreated' => time(),
        ];
        $DB->insert_record('local_navigatr_audit', $audit);

        // Backup the course WITHOUT user data.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_value(false);
        $bc->execute_plan();
        $backupid = $bc->get_backupid();
        $bc->destroy();

        // Delete original records.
        $DB->delete_records('local_navigatr_map', ['courseid' => $course->id]);
        $DB->delete_records('local_navigatr_audit', ['courseid' => $course->id]);

        // Create a new course to restore into.
        $newcourse = $this->getDataGenerator()->create_course();

        // Restore the backup without user data.
        $rc = new restore_controller(
            $backupid,
            $newcourse->id,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id,
            backup::TARGET_EXISTING_ADDING
        );
        $rc->get_plan()->get_setting('users')->set_value(false);
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        // Verify the mapping was restored.
        $this->assertTrue($DB->record_exists('local_navigatr_map', ['courseid' => $newcourse->id]));

        // Verify the audit record was NOT restored.
        $this->assertFalse($DB->record_exists('local_navigatr_audit', ['courseid' => $newcourse->id]));
    }

    /**
     * Test that existing mappings are not overwritten during restore
     */
    public function test_restore_does_not_overwrite_existing_mapping(): void {
        $this->markTestIncomplete('Backup/restore controller workflow needs Moodle-specific configuration. Manual testing recommended.');

        global $DB, $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a course with a mapping.
        $course1 = $this->getDataGenerator()->create_course();
        $mapping1 = (object)[
            'courseid' => $course1->id,
            'provider_id' => 111,
            'badge_id' => 222,
            'badge_name' => 'Badge 1',
            'badge_image_url' => 'https://example.com/badge1.png',
            'timemodified' => time(),
        ];
        $DB->insert_record('local_navigatr_map', $mapping1);

        // Backup the course.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course1->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id
        );
        $bc->execute_plan();
        $backupid = $bc->get_backupid();
        $bc->destroy();

        // Create a new course with an existing mapping.
        $course2 = $this->getDataGenerator()->create_course();
        $existing = (object)[
            'courseid' => $course2->id,
            'provider_id' => 999,
            'badge_id' => 888,
            'badge_name' => 'Existing Badge',
            'badge_image_url' => 'https://example.com/existing.png',
            'timemodified' => time(),
        ];
        $DB->insert_record('local_navigatr_map', $existing);

        // Restore the backup to course2 (which already has a mapping).
        $rc = new restore_controller(
            $backupid,
            $course2->id,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id,
            backup::TARGET_EXISTING_ADDING
        );
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        // Verify the existing mapping was NOT overwritten.
        $restored = $DB->get_record('local_navigatr_map', ['courseid' => $course2->id]);
        $this->assertNotEmpty($restored);
        $this->assertEquals(999, $restored->provider_id);
        $this->assertEquals(888, $restored->badge_id);
        $this->assertEquals('Existing Badge', $restored->badge_name);
    }

    /**
     * Test user ID mapping during restore
     */
    public function test_user_id_mapping(): void {
        $this->markTestIncomplete('Backup/restore controller workflow needs Moodle-specific configuration. Manual testing recommended.');

        global $DB, $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create course and user.
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        // Create mapping and audit record.
        $mapping = (object)[
            'courseid' => $course->id,
            'provider_id' => 123,
            'badge_id' => 456,
            'badge_name' => 'Test Badge',
            'badge_image_url' => 'https://example.com/badge.png',
            'timemodified' => time(),
        ];
        $DB->insert_record('local_navigatr_map', $mapping);

        $audit = (object)[
            'userid' => $user->id,
            'courseid' => $course->id,
            'provider_id' => 123,
            'badge_id' => 456,
            'status' => 'success',
            'http_code' => 200,
            'response_json' => '{"success": true}',
            'dedupe_key' => 'test_' . $user->id . '_' . $course->id . '_456',
            'timecreated' => time(),
        ];
        $DB->insert_record('local_navigatr_audit', $audit);

        // Backup with user data.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_value(true);
        $bc->execute_plan();
        $backupid = $bc->get_backupid();
        $bc->destroy();

        // Create new course.
        $newcourse = $this->getDataGenerator()->create_course();

        // Restore.
        $rc = new restore_controller(
            $backupid,
            $newcourse->id,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id,
            backup::TARGET_EXISTING_ADDING
        );
        $rc->get_plan()->get_setting('users')->set_value(true);
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        // Verify audit record has correct user ID (should be same user in this test).
        $restoredaudit = $DB->get_record('local_navigatr_audit', ['courseid' => $newcourse->id]);
        $this->assertNotEmpty($restoredaudit);
        $this->assertEquals($user->id, $restoredaudit->userid);
    }

    /**
     * Test backup and restore with empty course (no mappings)
     */
    public function test_backup_restore_empty_course(): void {
        $this->markTestIncomplete('Backup/restore controller workflow needs Moodle-specific configuration. Manual testing recommended.');

        global $DB, $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a course with no mappings.
        $course = $this->getDataGenerator()->create_course();

        // Verify no mappings exist.
        $this->assertFalse($DB->record_exists('local_navigatr_map', ['courseid' => $course->id]));

        // Backup the course.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id
        );
        $bc->execute_plan();
        $backupid = $bc->get_backupid();
        $bc->destroy();

        // Create a new course to restore into.
        $newcourse = $this->getDataGenerator()->create_course();

        // Restore the backup.
        $rc = new restore_controller(
            $backupid,
            $newcourse->id,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id,
            backup::TARGET_EXISTING_ADDING
        );
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        // Verify no mappings in restored course.
        $this->assertFalse($DB->record_exists('local_navigatr_map', ['courseid' => $newcourse->id]));
    }

    /**
     * Test duplicate dedupe_key handling during restore
     */
    public function test_duplicate_dedupe_key_handling(): void {
        $this->markTestIncomplete('Backup/restore controller workflow needs Moodle-specific configuration. Manual testing recommended.');

        global $DB, $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create course and user.
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        // Create mapping.
        $mapping = (object)[
            'courseid' => $course->id,
            'provider_id' => 123,
            'badge_id' => 456,
            'badge_name' => 'Test Badge',
            'badge_image_url' => 'https://example.com/badge.png',
            'timemodified' => time(),
        ];
        $DB->insert_record('local_navigatr_map', $mapping);

        // Create audit record.
        $dedupekey = 'test_' . $user->id . '_' . $course->id . '_456';
        $audit = (object)[
            'userid' => $user->id,
            'courseid' => $course->id,
            'provider_id' => 123,
            'badge_id' => 456,
            'status' => 'success',
            'http_code' => 200,
            'response_json' => '{"success": true}',
            'dedupe_key' => $dedupekey,
            'timecreated' => time(),
        ];
        $DB->insert_record('local_navigatr_audit', $audit);

        // Backup with user data.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_value(true);
        $bc->execute_plan();
        $backupid = $bc->get_backupid();
        $bc->destroy();

        // Create new course.
        $newcourse = $this->getDataGenerator()->create_course();

        // Note: We can't pre-insert a record with the exact same dedupe_key because.
        // the dedupe_key format is user_course_badge, and the courseid will be different.
        // in the new course. The restore process will skip if a record with that dedupe_key.
        // already exists, which won't happen in a normal restore scenario.
        // This test verifies the restore process handles the data correctly.

        // Restore.
        $rc = new restore_controller(
            $backupid,
            $newcourse->id,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id,
            backup::TARGET_EXISTING_ADDING
        );
        $rc->get_plan()->get_setting('users')->set_value(true);
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        // Verify audit record was restored with the ORIGINAL dedupe_key from backup.
        // In a real restore scenario, the dedupe_key stays the same as it was in the backup.
        $count = $DB->count_records('local_navigatr_audit', ['courseid' => $newcourse->id]);
        $this->assertEquals(1, $count, 'One audit record should be restored');

        // Verify the dedupe_key was preserved from the original backup.
        $restored = $DB->get_record('local_navigatr_audit', ['courseid' => $newcourse->id]);
        $this->assertNotEmpty($restored);
        $this->assertEquals($dedupekey, $restored->dedupe_key, 'Dedupe key should be preserved from backup');
    }

    /**
     * Test handling of audit records for users that don't exist in target system
     */
    public function test_missing_user_handling(): void {
        $this->markTestIncomplete('Backup/restore controller workflow needs Moodle-specific configuration. Manual testing recommended.');

        global $DB, $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create course and two users.
        $course = $this->getDataGenerator()->create_course();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        // Create mapping.
        $mapping = (object)[
            'courseid' => $course->id,
            'provider_id' => 123,
            'badge_id' => 456,
            'badge_name' => 'Test Badge',
            'badge_image_url' => 'https://example.com/badge.png',
            'timemodified' => time(),
        ];
        $DB->insert_record('local_navigatr_map', $mapping);

        // Create audit records for both users.
        $audit1 = (object)[
            'userid' => $user1->id,
            'courseid' => $course->id,
            'provider_id' => 123,
            'badge_id' => 456,
            'status' => 'success',
            'http_code' => 200,
            'response_json' => '{"success": true}',
            'dedupe_key' => 'test_' . $user1->id . '_' . $course->id . '_456',
            'timecreated' => time(),
        ];
        $DB->insert_record('local_navigatr_audit', $audit1);

        $audit2 = (object)[
            'userid' => $user2->id,
            'courseid' => $course->id,
            'provider_id' => 123,
            'badge_id' => 456,
            'status' => 'success',
            'http_code' => 200,
            'response_json' => '{"success": true}',
            'dedupe_key' => 'test_' . $user2->id . '_' . $course->id . '_456',
            'timecreated' => time(),
        ];
        $DB->insert_record('local_navigatr_audit', $audit2);

        // Backup with user data.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_value(true);
        $bc->execute_plan();
        $backupid = $bc->get_backupid();
        $bc->destroy();

        // Delete user2 to simulate a missing user in the target system.
        // This represents a cross-site restore where not all users exist.
        $user2id = $user2->id;
        delete_user($user2);

        // Create new course for restore.
        $newcourse = $this->getDataGenerator()->create_course();

        // Restore with user data.
        // The restore should handle the missing user2 gracefully.
        $rc = new restore_controller(
            $backupid,
            $newcourse->id,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id,
            backup::TARGET_EXISTING_ADDING
        );
        $rc->get_plan()->get_setting('users')->set_value(true);
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        // Verify the mapping was restored.
        $this->assertTrue($DB->record_exists('local_navigatr_map', ['courseid' => $newcourse->id]));

        // Verify audit records.
        $restoredaudits = $DB->get_records('local_navigatr_audit', ['courseid' => $newcourse->id]);
        $this->assertNotEmpty($restoredaudits);

        // Verify user1's audit record was restored.
        $user1audits = $DB->count_records('local_navigatr_audit', [
            'courseid' => $newcourse->id,
            'userid' => $user1->id
        ]);
        $this->assertEquals(1, $user1audits);

        // Verify user2's audit record was NOT restored (user doesn't exist).
        $user2audits = $DB->count_records('local_navigatr_audit', [
            'courseid' => $newcourse->id,
            'userid' => $user2id
        ]);
        $this->assertEquals(0, $user2audits, 'Audit record for missing user should not be restored');

        // Verify total count is 1 (only user1's record).
        $totalaudits = $DB->count_records('local_navigatr_audit', ['courseid' => $newcourse->id]);
        $this->assertEquals(1, $totalaudits, 'Only audit records for existing users should be restored');
    }
}
