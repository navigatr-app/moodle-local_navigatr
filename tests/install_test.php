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
 * Tests for the database schema created at install.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr;

use advanced_testcase;

/**
 * Tests for the audit table indexes.
 */
final class install_test extends advanced_testcase {
    /**
     * The audit table is indexed for the privacy provider's queries.
     *
     * @coversNothing
     */
    public function test_audit_table_indexes_exist(): void {
        global $DB;

        $dbman = $DB->get_manager();
        $table = new \xmldb_table('local_navigatr_audit');

        $index = new \xmldb_index('courseid-userid', XMLDB_INDEX_NOTUNIQUE, ['courseid', 'userid']);
        $this->assertTrue($dbman->index_exists($table, $index));

        $index = new \xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        $this->assertTrue($dbman->index_exists($table, $index));
    }
}
