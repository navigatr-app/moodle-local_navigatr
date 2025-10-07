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
 * Badge selection form for Navigatr plugin.
 *
 * @package    local_navigatr
 * @copyright  2025 Navigatr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_navigatr\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Badge selection form class.
 */
class badge_selection_form extends \moodleform {

    /**
     * Form definition.
     */
    protected function definition() {
        global $DB;
        
        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'];
        $provider_id = $this->_customdata['provider_id'];
        $provider_name = $this->_customdata['provider_name'];

        // Add hidden course ID
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        // Add hidden provider ID
        $mform->addElement('hidden', 'provider_id', $provider_id);
        $mform->setType('provider_id', PARAM_INT);

        // Show selected provider
        $mform->addElement('html', '<div class="alert alert-info">');
        $mform->addElement('html', '<strong>' . get_string('selected_provider', 'local_navigatr') . ':</strong> ' . s($provider_name));
        $mform->addElement('html', '</div>');

        // Get badges for the selected provider
        $badges = $this->get_badges($provider_id);
        $badgeoptions = ['' => get_string('select_badge', 'local_navigatr')];
        
        foreach ($badges as $badge) {
            $badgeoptions[$badge['id']] = $badge['name'];
        }

        $mform->addElement('select', 'badge_id', get_string('badge', 'local_navigatr'), $badgeoptions);
        $mform->setType('badge_id', PARAM_INT);
        $mform->addRule('badge_id', get_string('required'), 'required', null, 'client');

        // Add submit button
        $mform->addElement('submit', 'select_badge', get_string('select_badge_continue', 'local_navigatr'));
        
        // Add cancel button
        $mform->addElement('cancel', 'cancel', get_string('cancel'));
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
