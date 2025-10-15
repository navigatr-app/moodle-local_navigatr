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
        
        // Form action will be set automatically by Moodle

        // Add hidden field to preserve course ID
        $mform->addElement('hidden', 'id', $courseid);
        $mform->setType('id', PARAM_INT);

        // Get existing mapping
        $mapping = $DB->get_record('local_navigatr_map', ['courseid' => $courseid]);

        // Provider selection
        $providers = $this->get_providers();
        $provideroptions = ['' => get_string('select_provider', 'local_navigatr')];
        foreach ($providers as $provider) {
            $provideroptions[$provider['id']] = $provider['name'];
        }

        $mform->addElement('select', 'provider_id', get_string('provider', 'local_navigatr'), $provideroptions);
        $mform->setType('provider_id', PARAM_INT);
        $mform->addHelpButton('provider_id', 'provider', 'local_navigatr');
        $mform->addRule('provider_id', get_string('required'), 'required', null, 'client');

        // Add submit button for provider selection
        $mform->addElement('submit', 'select_provider', get_string('select_provider_continue', 'local_navigatr'), ['class' => 'btn-primary mb-3']);

        // Badge preview
        if ($mapping && $mapping->badge_image_url) {
            $badge_img = \html_writer::img($mapping->badge_image_url, get_string('badge_preview', 'local_navigatr'), [
                'style' => 'max-width: 100px; max-height: 100px;'
            ]);
            $badge_preview = \html_writer::div($badge_img, 'badge-preview');
            $mform->addElement('html', $badge_preview);
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
            // Check cache first for user details
            $cached_user = \local_navigatr\local\cache::get_user_detail();
            if ($cached_user !== null && isset($cached_user['providers'])) {
                return $cached_user['providers'];
            }

            // Fetch user details from API (this includes providers)
            $client = new \local_navigatr\local\api_client();
            $api_path = "/user_detail/0";
            $response = $client->get($api_path);

            if ($response->ok && is_array($response->body) && isset($response->body['providers'])) {
                // Cache the entire user detail response
                \local_navigatr\local\cache::set_user_detail($response->body);
                
                // Store the user ID for future use
                if (isset($response->body['id'])) {
                    set_config('nav_user_id', $response->body['id'], 'local_navigatr');
                }
                
                return $response->body['providers'];
            }

            return [];

        } catch (\Exception $e) {
            // Log error but don't output to page
            error_log("Exception in get_providers: " . $e->getMessage());
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
            if ($cached !== null && is_array($cached)) {
                return $cached;
            }

            // Fetch from API
            $client = new \local_navigatr\local\api_client();
            $api_path = "/badge?provider_id={$providerid}&status=Published&source=Internal&page=1&size=50";
            $response = $client->get($api_path);

            if ($response->ok && isset($response->body['items']) && is_array($response->body['items'])) {
                $badges = $response->body['items'];
                \local_navigatr\local\cache::set_badges($providerid, 1, 50, $badges);
                return $badges;
            }

            return [];

        } catch (\Exception $e) {
            // Log error but don't output to page
            error_log("Exception in get_badges: " . $e->getMessage());
            return [];
        }
    }
}
