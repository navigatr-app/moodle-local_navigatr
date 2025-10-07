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
 * Provider selection form for Navigatr Badges plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Provider selection form class.
 */
class provider_selection_form extends \moodleform {

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

        // Add submit button for provider selection
        $mform->addElement('submit', 'select_provider', get_string('select_provider_continue', 'local_navigatr'));

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
        debugging("NAVIGATR DEBUG: get_providers() called", DEBUG_NORMAL);
        
        try {
            // Check cache first for user details
            $cached_user = \local_navigatr\local\cache::get_user_detail();
            if ($cached_user !== null && isset($cached_user['providers'])) {
                debugging("NAVIGATR DEBUG: Found cached user details with " . count($cached_user['providers']) . " providers", DEBUG_NORMAL);
                return $cached_user['providers'];
            }
            debugging("NAVIGATR DEBUG: No cached user details found, fetching from API", DEBUG_NORMAL);

            // Fetch user details from API (this includes providers)
            debugging("NAVIGATR DEBUG: Creating API client", DEBUG_NORMAL);
            $client = new \local_navigatr\local\api_client();
            
            $api_path = "/user_detail/0";
            debugging("NAVIGATR DEBUG: Making API call to: " . $api_path, DEBUG_NORMAL);
            
            $response = $client->get($api_path);
            
            debugging("NAVIGATR DEBUG: API response received", DEBUG_NORMAL);
            debugging("NAVIGATR DEBUG: Response OK: " . ($response->ok ? 'YES' : 'NO'), DEBUG_NORMAL);
            debugging("NAVIGATR DEBUG: Response code: " . $response->code, DEBUG_NORMAL);
            debugging("NAVIGATR DEBUG: Response body type: " . gettype($response->body), DEBUG_NORMAL);
            
            if ($response->body !== null) {
                debugging("NAVIGATR DEBUG: Response body keys: " . implode(', ', array_keys($response->body)), DEBUG_NORMAL);
                if (isset($response->body['providers'])) {
                    debugging("NAVIGATR DEBUG: Found " . count($response->body['providers']) . " providers in response", DEBUG_NORMAL);
                } else {
                    debugging("NAVIGATR DEBUG: No providers key in response", DEBUG_NORMAL);
                }
            } else {
                debugging("NAVIGATR DEBUG: Response body is NULL", DEBUG_NORMAL);
            }

            if ($response->ok && is_array($response->body) && isset($response->body['providers'])) {
                debugging("NAVIGATR DEBUG: Response is OK and contains providers", DEBUG_NORMAL);
                
                // Cache the entire user detail response
                \local_navigatr\local\cache::set_user_detail($response->body);
                
                // Store the user ID for future use
                if (isset($response->body['id'])) {
                    set_config('nav_user_id', $response->body['id'], 'local_navigatr');
                    debugging("NAVIGATR DEBUG: Stored user ID: " . $response->body['id'], DEBUG_NORMAL);
                }
                
                return $response->body['providers'];
            } else {
                debugging("NAVIGATR DEBUG: Response not OK or missing providers", DEBUG_NORMAL);
                if (!$response->ok) {
                    debugging("NAVIGATR DEBUG: Response error: " . ($response->error ?? 'Unknown error'), DEBUG_NORMAL);
                }
            }

            debugging("NAVIGATR DEBUG: Returning empty array", DEBUG_NORMAL);
            return [];

        } catch (\Exception $e) {
            debugging("NAVIGATR DEBUG: Exception in get_providers: " . $e->getMessage(), DEBUG_NORMAL);
            debugging("NAVIGATR DEBUG: Exception trace: " . $e->getTraceAsString(), DEBUG_NORMAL);
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
        debugging("NAVIGATR DEBUG: get_badges() called with providerid: " . $providerid, DEBUG_NORMAL);
        
        try {
            // Check cache first
            debugging("NAVIGATR DEBUG: Checking cache for providerid: " . $providerid, DEBUG_NORMAL);
            $cached = \local_navigatr\local\cache::get_badges($providerid, 1, 50);
            if ($cached !== null) {
                debugging("NAVIGATR DEBUG: Found cached badges: " . (is_array($cached) ? count($cached) : 'not array') . " items", DEBUG_NORMAL);
                return $cached;
            }
            debugging("NAVIGATR DEBUG: No cached badges found, fetching from API", DEBUG_NORMAL);

            // Fetch from API
            debugging("NAVIGATR DEBUG: Creating API client", DEBUG_NORMAL);
            $client = new \local_navigatr\local\api_client();
            
            $api_path = "/badge?provider_id={$providerid}&status=Published&source=Internal&page=1&size=50";
            debugging("NAVIGATR DEBUG: Making API call to: " . $api_path, DEBUG_NORMAL);
            
            $response = $client->get($api_path);
            
            debugging("NAVIGATR DEBUG: API response received", DEBUG_NORMAL);
            debugging("NAVIGATR DEBUG: Response OK: " . ($response->ok ? 'YES' : 'NO'), DEBUG_NORMAL);
            debugging("NAVIGATR DEBUG: Response code: " . $response->code, DEBUG_NORMAL);
            debugging("NAVIGATR DEBUG: Response body type: " . gettype($response->body), DEBUG_NORMAL);
            
            if ($response->body !== null) {
                debugging("NAVIGATR DEBUG: Response body keys: " . implode(', ', array_keys($response->body)), DEBUG_NORMAL);
                if (isset($response->body['items'])) {
                    debugging("NAVIGATR DEBUG: Found " . count($response->body['items']) . " badges in response", DEBUG_NORMAL);
                } else {
                    debugging("NAVIGATR DEBUG: No items key in response", DEBUG_NORMAL);
                }
                debugging("NAVIGATR DEBUG: Response body: " . json_encode($response->body), DEBUG_NORMAL);
            } else {
                debugging("NAVIGATR DEBUG: Response body is NULL", DEBUG_NORMAL);
            }

            if ($response->ok && isset($response->body['items']) && is_array($response->body['items'])) {
                debugging("NAVIGATR DEBUG: Response is OK and contains badges", DEBUG_NORMAL);
                $badges = $response->body['items'];
                \local_navigatr\local\cache::set_badges($providerid, 1, 50, $badges);
                debugging("NAVIGATR DEBUG: Cached " . count($badges) . " badges", DEBUG_NORMAL);
                return $badges;
            } else {
                debugging("NAVIGATR DEBUG: Response not OK or missing items", DEBUG_NORMAL);
                if (!$response->ok) {
                    debugging("NAVIGATR DEBUG: Response error: " . ($response->error ?? 'Unknown error'), DEBUG_NORMAL);
                }
            }

            debugging("NAVIGATR DEBUG: Returning empty array", DEBUG_NORMAL);
            return [];

        } catch (\Exception $e) {
            debugging("NAVIGATR DEBUG: Exception in get_badges: " . $e->getMessage(), DEBUG_NORMAL);
            debugging("NAVIGATR DEBUG: Exception trace: " . $e->getTraceAsString(), DEBUG_NORMAL);
            return [];
        }
    }
}
