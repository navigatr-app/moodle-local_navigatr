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
 * Course settings form for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Course settings form class.
 */
class course_settings_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $DB;
        
        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'];

        // Get existing mapping
        $mapping = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);

        // Provider selection
        $providers = $this->get_providers();
        $provideroptions = ['' => get_string('select_provider', 'local_navigatr')];
        foreach ($providers as $provider) {
            $provideroptions[$provider['id']] = $provider['name'] . ' (' . $provider['short_name'] . ')';
        }

        $mform->addElement('select', 'provider_id', get_string('provider', 'local_navigatr'), $provideroptions);
        $mform->setType('provider_id', PARAM_INT);
        $mform->addRule('provider_id', get_string('required'), 'required', null, 'client');

        // Badge selection
        $badgeoptions = ['' => get_string('select_badge', 'local_navigatr')];
        if ($mapping && $mapping->provider_id) {
            $badges = $this->get_badges($mapping->provider_id);
            foreach ($badges as $badge) {
                $badgeoptions[$badge['id']] = $badge['name'];
            }
        }

        $mform->addElement('select', 'badge_id', get_string('badge', 'local_navigatr'), $badgeoptions);
        $mform->setType('badge_id', PARAM_INT);
        $mform->addRule('badge_id', get_string('required'), 'required', null, 'client');

        // Badge preview
        if ($mapping && $mapping->badge_image_url) {
            $mform->addElement('html', '<div class="badge-preview">');
            $mform->addElement('html', '<img src="' . s($mapping->badge_image_url) . '" alt="Badge preview" style="max-width: 100px; max-height: 100px;">');
            $mform->addElement('html', '</div>');
        }

        // Hidden fields for badge details
        $mform->addElement('hidden', 'badge_name');
        $mform->setType('badge_name', PARAM_TEXT);
        $mform->addElement('hidden', 'badge_image_url');
        $mform->setType('badge_image_url', PARAM_URL);

        // Set defaults
        if ($mapping) {
            $mform->setDefault('provider_id', $mapping->provider_id);
            $mform->setDefault('badge_id', $mapping->badge_id);
            $mform->setDefault('badge_name', $mapping->badge_name);
            $mform->setDefault('badge_image_url', $mapping->badge_image_url);
        }

        $this->add_action_buttons(true, get_string('save_mapping', 'local_navigatr'));
    }

    /**
     * Get providers from API.
     *
     * @return array Providers array
     */
    private function get_providers() {
        try {
            $userid = get_config('local_navigatr', 'nav_user_id');
            if (empty($userid)) {
                return [];
            }

            // Check cache first
            $cached = \local_navigatr\local\cache::get_providers($userid);
            if ($cached !== null) {
                return $cached;
            }

            // Fetch from API
            $env = get_config('local_navigatr', 'env') ?: 'prod';
            $timeout = get_config('local_navigatr', 'timeout') ?: 10;
            
            $baseurls = [
                'prod' => 'https://api.navigatr.app/v1',
                'staging' => 'https://stagapi.navigatr.app/v1',
                'dev' => 'http://127.0.0.1:5000/v1',
            ];
            $baseurl = $baseurls[$env] ?? $baseurls['prod'];
            
            $client = new \local_navigatr\local\api_client($baseurl, $timeout);
            $token = \local_navigatr\local\token_manager::get_access_token();
            
            $response = $client->get("/user_detail/{$userid}/providers", [
                'Authorization: Bearer ' . $token
            ]);

            if ($response->ok && is_array($response->body)) {
                \local_navigatr\local\cache::set_providers($userid, $response->body);
                return $response->body;
            }

            return [];

        } catch (\Exception $e) {
            debugging("Failed to fetch providers: " . $e->getMessage(), DEBUG_NORMAL);
            return [];
        }
    }

    /**
     * Get badges for a provider.
     *
     * @param int $providerid Provider ID
     * @return array Badges array
     */
    private function get_badges($providerid) {
        try {
            // Check cache first
            $cached = \local_navigatr\local\cache::get_badges($providerid, 1, 50);
            if ($cached !== null) {
                return $cached;
            }

            // Fetch from API
            $env = get_config('local_navigatr', 'env') ?: 'prod';
            $timeout = get_config('local_navigatr', 'timeout') ?: 10;
            
            $baseurls = [
                'prod' => 'https://api.navigatr.app/v1',
                'staging' => 'https://stagapi.navigatr.app/v1',
                'dev' => 'http://127.0.0.1:5000/v1',
            ];
            $baseurl = $baseurls[$env] ?? $baseurls['prod'];
            
            $client = new \local_navigatr\local\api_client($baseurl, $timeout);
            $token = \local_navigatr\local\token_manager::get_access_token();
            
            $response = $client->get("/badge?provider_id={$providerid}&page=1&size=50", [
                'Authorization: Bearer ' . $token
            ]);

            if ($response->ok && isset($response->body['items']) && is_array($response->body['items'])) {
                $badges = $response->body['items'];
                \local_navigatr\local\cache::set_badges($providerid, 1, 50, $badges);
                return $badges;
            }

            return [];

        } catch (\Exception $e) {
            debugging("Failed to fetch badges: " . $e->getMessage(), DEBUG_NORMAL);
            return [];
        }
    }
}
