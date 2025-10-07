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
 * Adhoc task for issuing Navigatr badges.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\task;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../classes/local/token_manager.php');
require_once(__DIR__ . '/../../classes/local/api_client.php');

/**
 * Adhoc task for issuing Navigatr badges.
 */
class issue_badge_task extends \core\task\adhoc_task {

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        $data = $this->get_custom_data();
        $userid = $data->userid;
        $courseid = $data->courseid;

        // Get mapping for this course
        $mapping = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);
        if (!$mapping) {
            $this->write_audit($userid, $courseid, 0, 0, 'error', 404, 
                json_encode(['error' => 'No mapping found for course']));
            return;
        }

        // Get user details
        $user = \core_user::get_user($userid, 'id,email,firstname,lastname', MUST_EXIST);
        
        // Validate required user fields
        $requiredfields = ['email', 'firstname', 'lastname'];
        foreach ($requiredfields as $field) {
            if (empty($user->$field)) {
                $this->write_audit($userid, $courseid, $mapping->provider_id, $mapping->badge_id, 
                    'error', 400, json_encode(['error' => "Missing user field: {$field}"]));
                return;
            }
        }

        // Check for existing successful issuance (idempotency)
        $dedupekey = "{$userid}:{$courseid}:{$mapping->badge_id}";
        $existing = $DB->get_record('local_navigatr_audit', [
            'dedupe_key' => $dedupekey,
            'status' => 'success'
        ]);
        if ($existing) {
            return; // Already successfully issued
        }

        try {
            // Get access token
            $token = \local_navigatr\local\token_manager::get_access_token();
            
            // Get API client
            $env = get_config('local_navigatr', 'env') ?: 'prod';
            $timeout = get_config('local_navigatr', 'timeout') ?: 10;
            
            $baseurls = [
                'prod' => 'https://api.navigatr.app/v1',
                'staging' => 'https://stagapi.navigatr.app/v1',
                'dev' => 'http://127.0.0.1:5000/v1',
            ];
            $baseurl = $baseurls[$env] ?? $baseurls['prod'];
            
            $client = new \local_navigatr\local\api_client($baseurl, $timeout);

            // Prepare badge issuance payload
            $payload = [
                'recipient_email' => $user->email,
                'recipient_firstname' => $user->firstname,
                'recipient_lastname' => $user->lastname,
            ];

            // Issue badge
            $response = $client->put("/badge/{$mapping->badge_id}/issue", $payload, [
                'Authorization: Bearer ' . $token
            ]);

            // Handle 401 - try re-authentication once
            if ($response->code === 401) {
                \local_navigatr\local\token_manager::reauth();
                $token = \local_navigatr\local\token_manager::get_access_token();
                
                $response = $client->put("/badge/{$mapping->badge_id}/issue", $payload, [
                    'Authorization: Bearer ' . $token
                ]);
            }

            // Write audit record
            $this->write_audit($userid, $courseid, $mapping->provider_id, $mapping->badge_id,
                $response->ok ? 'success' : 'error', $response->code, json_encode($response->body));

            // Throw exception for terminal failures to allow Moodle retries
            if (!$response->ok && $response->code >= 500) {
                throw new \moodle_exception('issue_failed', 'local_navigatr', '', $response->code);
            }

        } catch (\Exception $e) {
            // Write error audit record
            $this->write_audit($userid, $courseid, $mapping->provider_id, $mapping->badge_id,
                'error', 500, json_encode(['error' => $e->getMessage()]));
            
            // Re-throw to allow Moodle retry mechanism
            throw $e;
        }
    }

    /**
     * Write audit record.
     *
     * @param int $userid User ID
     * @param int $courseid Course ID
     * @param int $providerid Provider ID
     * @param int $badgeid Badge ID
     * @param string $status Status (success/error)
     * @param int $httpcode HTTP response code
     * @param string $responsejson Response JSON
     */
    private function write_audit($userid, $courseid, $providerid, $badgeid, $status, $httpcode, $responsejson) {
        global $DB;

        $dedupekey = "{$userid}:{$courseid}:{$badgeid}";
        
        $record = (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'provider_id' => $providerid,
            'badge_id' => $badgeid,
            'status' => $status,
            'http_code' => $httpcode,
            'response_json' => $responsejson,
            'dedupe_key' => $dedupekey,
            'timecreated' => time(),
        ];

        // Use insert_or_update to handle duplicate dedupe_key
        $DB->insert_or_update_record('local_navigatr_audit', $record, ['dedupe_key' => $dedupekey]);
    }
}
